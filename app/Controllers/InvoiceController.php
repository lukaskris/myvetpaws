<?php

namespace App\Controllers;

use App\Models\InvoicesModel;
use App\Models\PaymentsModel;

class InvoiceController extends BaseController
{
    public function index()
    {
        $invoicesModel = new InvoicesModel();

        // Base query joining customer, medical record, pet
        $invoicesModel->select('invoices.*, customers.name as customer_name, pets.name as pet_name')
                      ->join('customers', 'customers.id = invoices.customer_id', 'inner')
                      ->join('medical_records', 'medical_records.id = invoices.medical_record_id', 'inner')
                      ->join('pets', 'pets.id = medical_records.pet_id', 'inner');

        // Apply filters
        $search = $this->request->getGet('q');
        if (!empty($search)) {
            $invoicesModel->groupStart()
                ->like('invoices.invoice_number', $search)
                ->orLike('customers.name', $search)
                ->orLike('pets.name', $search)
            ->groupEnd();
        }

        $status = $this->request->getGet('status');
        if ($status !== null && $status !== '') {
            $invoicesModel->where('invoices.status', $status);
        }

        $allInvoices = $invoicesModel->orderBy('invoices.created_at', 'DESC')->findAll();

        // Group invoices by customer_id and date (YYYY-MM-DD)
        $groupedInvoices = [];
        foreach ($allInvoices as $inv) {
            $dateKey = date('Y-m-d', strtotime($inv['created_at']));
            $groupKey = $inv['customer_id'] . '_' . $dateKey;

            if (!isset($groupedInvoices[$groupKey])) {
                $groupedInvoices[$groupKey] = [
                    'id'                => $inv['id'], // representative ID
                    'invoice_number'    => $inv['invoice_number'],
                    'customer_id'       => $inv['customer_id'],
                    'customer_name'     => $inv['customer_name'],
                    'pet_names'         => [$inv['pet_name']],
                    'medical_record_id' => $inv['medical_record_id'],
                    'created_at'        => $inv['created_at'],
                    'total_amount'      => 0.00,
                    'total_paid'        => 0.00,
                    'invoice_ids'       => [],
                    'statuses'          => [],
                ];
            } else {
                if (!in_array($inv['pet_name'], $groupedInvoices[$groupKey]['pet_names'])) {
                    $groupedInvoices[$groupKey]['pet_names'][] = $inv['pet_name'];
                }
            }

            $groupedInvoices[$groupKey]['invoice_ids'][] = $inv['id'];
            $groupedInvoices[$groupKey]['statuses'][] = (int)$inv['status'];
            $groupedInvoices[$groupKey]['total_amount'] += (float)$inv['total_amount'];
        }

        // Fetch payments for each group and aggregate
        $db = \Config\Database::connect();
        foreach ($groupedInvoices as $key => &$group) {
            $invoiceIds = $group['invoice_ids'];
            $totalPaid = 0.00;
            if (!empty($invoiceIds)) {
                $pRow = $db->table('payments')
                           ->select('SUM(amount) as total_paid')
                           ->whereIn('invoice_id', $invoiceIds)
                           ->where('deleted_at', null)
                           ->get()
                           ->getRowArray();
                if ($pRow && $pRow['total_paid'] !== null) {
                    $totalPaid = (float)$pRow['total_paid'];
                }
            }
            $group['total_paid'] = $totalPaid;

            // Combine pet names into a string
            $group['pet_name'] = implode(', ', $group['pet_names']);

            // Determine status
            if ($group['total_paid'] >= $group['total_amount'] - 0.01) {
                $group['status'] = 2; // Paid
            } elseif ($group['total_paid'] > 0.00) {
                $group['status'] = 3; // Partially Paid
            } else {
                $group['status'] = 1; // Unpaid
            }
        }
        unset($group);

        return view('invoices/index', [
            'invoices' => array_values($groupedInvoices),
            'search'   => $search,
            'status'   => $status,
        ]);
    }

    public function show($id)
    {
        $invoicesModel = new InvoicesModel();

        $invoice = $invoicesModel->select('invoices.*, customers.name as customer_name, customers.email as customer_email, customers.phone as customer_phone, customers.address as customer_address')
                                 ->join('customers', 'customers.id = invoices.customer_id', 'inner')
                                 ->find($id);

        if (!$invoice) {
            return redirect()->to('/invoices')->with('error', 'Invoice not found.');
        }

        $db = \Config\Database::connect();
        
        // Fetch all invoices for this customer on the same day to group pets
        $dateStr = date('Y-m-d', strtotime($invoice['created_at']));
        $allInvoices = $invoicesModel->select('invoices.id as inv_id, invoices.total_amount as inv_total, invoices.status as inv_status, medical_records.id as mr_id, pets.name as pet_name, pets.species as pet_species, pets.breed as pet_breed, medical_records.diagnosis, medical_records.treatment_plan, users.name as doctor_name')
                                     ->join('medical_records', 'medical_records.id = invoices.medical_record_id', 'inner')
                                     ->join('pets', 'pets.id = medical_records.pet_id', 'inner')
                                     ->join('users', 'users.id = medical_records.user_id', 'left')
                                     ->where('invoices.customer_id', $invoice['customer_id'])
                                     ->where('DATE(invoices.created_at)', $dateStr)
                                     ->findAll();

        $groupedPets = [];
        $totalInvoiceAmount = 0.00;
        
        foreach ($allInvoices as $inv) {
            $srvs = $db->table('medical_record_services')
                       ->select('medical_record_services.quantity, services.name, services.code, services.price')
                       ->join('services', 'services.id = medical_record_services.service_id', 'inner')
                       ->where('medical_record_services.medical_record_id', $inv['mr_id'])
                       ->get()
                       ->getResultArray();
            
            $itms = $db->table('medical_record_items')
                       ->select('medical_record_items.quantity, items.name, items.code, medical_record_items.sell_price as price')
                       ->join('items', 'items.id = medical_record_items.item_id', 'inner')
                       ->where('medical_record_items.medical_record_id', $inv['mr_id'])
                       ->get()
                       ->getResultArray();
            
            $groupedPets[] = [
                'invoice_id'     => $inv['inv_id'],
                'pet_name'       => $inv['pet_name'],
                'pet_species'    => $inv['pet_species'],
                'pet_breed'      => $inv['pet_breed'],
                'diagnosis'      => $inv['diagnosis'],
                'treatment_plan' => $inv['treatment_plan'],
                'doctor_name'    => $inv['doctor_name'],
                'services'       => $srvs,
                'items'          => $itms,
                'subtotal'       => $inv['inv_total']
            ];
            $totalInvoiceAmount += $inv['inv_total'];
        }

        // Fetch payments history aggregated across these invoices
        $invoiceIds = array_column($allInvoices, 'inv_id');
        $payments = [];
        $totalPaid = 0.00;
        if (!empty($invoiceIds)) {
            $paymentsModel = new PaymentsModel();
            $payments = $paymentsModel->whereIn('invoice_id', $invoiceIds)
                                      ->orderBy('payment_date', 'ASC')
                                      ->findAll();
            foreach ($payments as $p) {
                $totalPaid += $p['amount'];
            }
        }
        
        $remainingBalance = $totalInvoiceAmount - $totalPaid;
        if ($remainingBalance < 0.01) $remainingBalance = 0.00;

        return view('invoices/show', [
            'invoice'            => $invoice,
            'groupedPets'        => $groupedPets,
            'payments'           => $payments,
            'totalInvoiceAmount' => $totalInvoiceAmount,
            'totalPaid'          => $totalPaid,
            'remainingBalance'   => $remainingBalance,
        ]);
    }

    public function pay($id)
    {
        $invoicesModel = new InvoicesModel();
        $invoice = $invoicesModel->find($id);

        if (!$invoice) {
            return redirect()->to('/invoices')->with('error', 'Invoice not found.');
        }

        // Fetch payments history to calculate outstanding balance
        $paymentsModel = new PaymentsModel();
        $payments = $paymentsModel->where('invoice_id', $id)->findAll();

        $totalPaid = 0.00;
        foreach ($payments as $p) {
            $totalPaid += $p['amount'];
        }
        $remainingBalance = $invoice['total_amount'] - $totalPaid;

        if ($remainingBalance <= 0) {
            return redirect()->back()->with('error', 'This invoice is already fully paid.');
        }

        $rules = [
            'amount'         => 'required|numeric|greater_than[0]',
            'payment_method' => 'required|in_list[Cash,Bank Transfer,Card]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $amount = (float)$this->request->getPost('amount');
        $paymentMethod = $this->request->getPost('payment_method');

        // Check if payment exceeds balance (with small epsilon tolerance)
        if ($amount > $remainingBalance + 0.01) {
            return redirect()->back()->withInput()->with('error', 'Payment amount cannot exceed the remaining outstanding balance of Rp' . number_format($remainingBalance, 0, ',', '.'));
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Insert payment transaction
        $paymentsModel->insert([
            'clinic_id'      => $invoice['clinic_id'],
            'invoice_id'     => $id,
            'amount'         => $amount,
            'payment_method' => $paymentMethod,
            'payment_date'   => date('Y-m-d H:i:s'),
        ]);

        // Update invoice status
        $newTotalPaid = $totalPaid + $amount;
        if ($newTotalPaid >= $invoice['total_amount'] - 0.01) {
            $invoicesModel->update($id, ['status' => 2]); // Paid
        } else {
            $invoicesModel->update($id, ['status' => 3]); // Partially Paid
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to record payment transaction. Please try again.');
        }

        return redirect()->to('/invoices/show/' . $id)->with('success', 'Payment of Rp' . number_format($amount, 0, ',', '.') . ' recorded successfully.');
    }

    public function download($id)
    {
        $invoicesModel = new \App\Models\InvoicesModel();

        $invoice = $invoicesModel->select('invoices.*, customers.name as customer_name, customers.email as customer_email, customers.phone as customer_phone, customers.address as customer_address')
                                 ->join('customers', 'customers.id = invoices.customer_id', 'inner')
                                 ->find($id);

        if (!$invoice) {
            return redirect()->to('/invoices')->with('error', 'Invoice not found.');
        }

        $db = \Config\Database::connect();
        
        // Fetch all invoices for this customer on the same day to group pets
        $dateStr = date('Y-m-d', strtotime($invoice['created_at']));
        $allInvoices = $invoicesModel->select('invoices.id as inv_id, invoices.total_amount as inv_total, invoices.status as inv_status, medical_records.id as mr_id, pets.name as pet_name, pets.species as pet_species, pets.breed as pet_breed, medical_records.diagnosis, medical_records.treatment_plan, users.name as doctor_name')
                                     ->join('medical_records', 'medical_records.id = invoices.medical_record_id', 'inner')
                                     ->join('pets', 'pets.id = medical_records.pet_id', 'inner')
                                     ->join('users', 'users.id = medical_records.user_id', 'left')
                                     ->where('invoices.customer_id', $invoice['customer_id'])
                                     ->where('DATE(invoices.created_at)', $dateStr)
                                     ->findAll();

        $groupedPets = [];
        $totalInvoiceAmount = 0.00;
        
        foreach ($allInvoices as $inv) {
            $srvs = $db->table('medical_record_services')
                       ->select('medical_record_services.quantity, services.name, services.code, services.price')
                       ->join('services', 'services.id = medical_record_services.service_id', 'inner')
                       ->where('medical_record_services.medical_record_id', $inv['mr_id'])
                       ->get()
                       ->getResultArray();
            
            $itms = $db->table('medical_record_items')
                       ->select('medical_record_items.quantity, items.name, items.code, medical_record_items.sell_price as price')
                       ->join('items', 'items.id = medical_record_items.item_id', 'inner')
                       ->where('medical_record_items.medical_record_id', $inv['mr_id'])
                       ->get()
                       ->getResultArray();
            
            $groupedPets[] = [
                'invoice_id'     => $inv['inv_id'],
                'pet_name'       => $inv['pet_name'],
                'pet_species'    => $inv['pet_species'],
                'pet_breed'      => $inv['pet_breed'],
                'diagnosis'      => $inv['diagnosis'],
                'treatment_plan' => $inv['treatment_plan'],
                'doctor_name'    => $inv['doctor_name'],
                'services'       => $srvs,
                'items'          => $itms,
                'subtotal'       => $inv['inv_total']
            ];
            $totalInvoiceAmount += $inv['inv_total'];
        }

        // Fetch payments history aggregated across these invoices
        $invoiceIds = array_column($allInvoices, 'inv_id');
        $payments = [];
        $totalPaid = 0.00;
        if (!empty($invoiceIds)) {
            $paymentsModel = new \App\Models\PaymentsModel();
            $payments = $paymentsModel->whereIn('invoice_id', $invoiceIds)
                                      ->orderBy('payment_date', 'ASC')
                                      ->findAll();
            foreach ($payments as $p) {
                $totalPaid += $p['amount'];
            }
        }
        
        $remainingBalance = $totalInvoiceAmount - $totalPaid;
        if ($remainingBalance < 0.01) $remainingBalance = 0.00;

        // Fetch clinic details for the invoice header
        $clinicModel = new \App\Models\ClinicsModel();
        $clinic = $clinicModel->find($invoice['clinic_id'] ?? session()->get('clinic_id'));

        // Prepare base64 logo if available
        $logoBase64 = '';
        if ($clinic && !empty($clinic['logo']) && file_exists(FCPATH . $clinic['logo'])) {
            $logoPath = FCPATH . $clinic['logo'];
            $logoData = @file_get_contents($logoPath);
            if ($logoData !== false) {
                $logoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode($logoData);
            }
        }

        // Render PDF HTML view
        $html = view('invoices/pdf_template', [
            'invoice'            => $invoice,
            'groupedPets'        => $groupedPets,
            'payments'           => $payments,
            'totalInvoiceAmount' => $totalInvoiceAmount,
            'totalPaid'          => $totalPaid,
            'remainingBalance'   => $remainingBalance,
            'clinic'             => $clinic,
            'logoBase64'         => $logoBase64,
        ]);

        // Setup Dompdf options
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultPaperSize', 'a4');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();

        // Format clean filename (e.g. invoice-INV-001.pdf)
        $cleanNumber = str_replace(['/', '\\', ' '], '-', $invoice['invoice_number']);
        $filename = 'invoice-' . $cleanNumber . '.pdf';

        $disposition = $this->request->getGet('inline') === 'true' ? 'inline' : 'attachment';

        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"')
                              ->setBody($dompdf->output());
    }
}
