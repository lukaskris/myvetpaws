<?php

namespace App\Controllers;

use App\Models\CustomersModel;
use App\Models\PetsModel;
use App\Models\VisitsModel;
use App\Models\MedicalRecordsModel;
use App\Models\MedicalRecordServicesModel;
use App\Models\ServicesModel;

class VisitController extends BaseController
{
    public function index()
    {
        $activeModel = new VisitsModel();
        $activeVisits = $activeModel->select('visits.*, pets.name as pet_name, pets.photo as pet_photo, pets.species as pet_species, pets.breed as pet_breed, customers.name as customer_name')
                                    ->join('pets', 'pets.id = visits.pet_id', 'inner')
                                    ->join('customers', 'customers.id = visits.customer_id', 'inner')
                                    ->whereIn('visits.status', [1, 2])
                                    ->orderBy('visits.checkin_time', 'ASC')
                                    ->findAll();

        $historyModel = new VisitsModel();
        $historyVisits = $historyModel->select('visits.*, pets.name as pet_name, pets.photo as pet_photo, pets.species as pet_species, pets.breed as pet_breed, customers.name as customer_name')
                                       ->join('pets', 'pets.id = visits.pet_id', 'inner')
                                       ->join('customers', 'customers.id = visits.customer_id', 'inner')
                                       ->whereIn('visits.status', [3, 4])
                                       ->orderBy('visits.checkin_time', 'DESC')
                                       ->findAll();

        // Fetch all visits to populate the Calendar View
        $calendarModel = new VisitsModel();
        $calendarVisits = $calendarModel->select('visits.id, visits.checkin_time, visits.status, visits.complaints, pets.name as pet_name, pets.species as pet_species, customers.name as customer_name')
                                         ->join('pets', 'pets.id = visits.pet_id', 'inner')
                                         ->join('customers', 'customers.id = visits.customer_id', 'inner')
                                         ->orderBy('visits.checkin_time', 'ASC')
                                         ->findAll();

        $calendarEvents = [];
        foreach ($calendarVisits as $v) {
            $calendarEvents[] = [
                'id'            => (int)$v['id'],
                'date'          => date('Y-m-d', strtotime($v['checkin_time'])),
                'time'          => date('H:i', strtotime($v['checkin_time'])),
                'status'        => (int)$v['status'],
                'pet_name'      => $v['pet_name'],
                'pet_species'   => $v['pet_species'],
                'customer_name' => $v['customer_name'],
                'complaints'    => $v['complaints'] ?: '',
            ];
        }

        return view('visits/index', [
            'activeVisits'   => $activeVisits,
            'historyVisits'  => $historyVisits,
            'calendarEvents' => $calendarEvents,
        ]);
    }

    public function create()
    {
        $customersModel = new CustomersModel();
        $petsModel = new PetsModel();

        $customers = $customersModel->orderBy('name', 'ASC')->findAll();
        $pets = $petsModel->orderBy('name', 'ASC')->findAll();

        // Preselected customer ID
        $preselectedCustomerId = $this->request->getGet('customer_id');

        // Parse default check-in time from 'date' query parameter
        $dateParam = $this->request->getGet('date');
        if ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) {
            $defaultCheckinTime = $dateParam . 'T' . date('H:i');
        } else {
            $defaultCheckinTime = date('Y-m-d\TH:i');
        }

        return view('visits/create', [
            'customers'             => $customers,
            'pets'                  => $pets,
            'preselectedCustomerId' => $preselectedCustomerId,
            'defaultCheckinTime'    => $defaultCheckinTime,
        ]);
    }

    public function store()
    {
        $rules = [
            'customer_id'            => 'required|numeric',
            'checkin_time'           => 'required|valid_date[Y-m-d\TH:i]',
            'visits'                 => 'required',
            'visits.*.pet_id'        => 'required|numeric',
            'visits.*.weight'        => 'permit_empty|decimal',
            'visits.*.temperature'   => 'permit_empty|decimal',
            'visits.*.complaints'    => 'permit_empty|max_length[2000]',
        ];

        // Format custom error messages for the array structure to be user-friendly
        $customErrors = [
            'checkin_time' => [
                'required'   => 'Please provide a valid check-in date and time.',
                'valid_date' => 'Please provide a valid check-in date and time format.',
            ],
            'visits.*.pet_id' => [
                'required' => 'Please select a valid pet for each check-in entry.',
                'numeric'  => 'Please select a valid pet for each check-in entry.',
            ],
            'visits.*.weight' => [
                'decimal' => 'Each pet weight must be a decimal number.',
            ],
            'visits.*.temperature' => [
                'decimal' => 'Each pet temperature must be a decimal number.',
            ],
            'visits.*.complaints' => [
                'max_length' => 'Each pet complaints note must not exceed 2000 characters.',
            ]
        ];

        if (!$this->validate($rules, $customErrors)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $customerId = $this->request->getPost('customer_id');
        $checkinTimeInput = $this->request->getPost('checkin_time');
        $visitsData = $this->request->getPost('visits');

        // Formats datetime-local value (YYYY-MM-DDTHH:MM) to database format (YYYY-MM-DD HH:MM:SS)
        $checkinTime = str_replace('T', ' ', $checkinTimeInput);
        if (strlen($checkinTime) === 16) {
            $checkinTime .= ':00';
        }

        $petsModel = new PetsModel();
        $visitsModel = new VisitsModel();

        // 1. Verify all selected pets exist and belong to the chosen customer
        foreach ($visitsData as $visit) {
            $pet = $petsModel->find($visit['pet_id']);
            if (!$pet || $pet['customer_id'] != $customerId) {
                return redirect()->back()->withInput()->with('error', 'One or more selected pets do not belong to the selected customer.');
            }
        }

        // 2. Perform insertions inside transaction
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($visitsData as $visit) {
            $visitData = [
                'pet_id'       => $visit['pet_id'],
                'customer_id'  => $customerId,
                'user_id'      => session()->get('user_id'),
                'checkin_time' => $checkinTime,
                'status'       => 1, // Queued
                'complaints'   => $visit['complaints'] ?: null,
                'weight'       => $visit['weight'] !== '' ? $visit['weight'] : null,
                'temperature'  => $visit['temperature'] !== '' ? $visit['temperature'] : null,
            ];
            $visitsModel->insert($visitData);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to register patient check-in.');
        }

        $count = count($visitsData);
        $message = $count === 1 ? 'Patient checked in successfully.' : "{$count} patients checked in successfully.";
        
        // If it was registered on a specific day in the past/future, return to index on the calendar tab
        $redirectUrl = '/visits';
        return redirect()->to($redirectUrl)->with('success', $message);
    }

    public function cancel($id)
    {
        $visitsModel = new VisitsModel();
        $visit = $visitsModel->find($id);

        if (!$visit) {
            return redirect()->to('/visits')->with('error', 'Visit not found.');
        }

        if ($visit['status'] == 3) {
            return redirect()->to('/visits')->with('error', 'Cannot cancel a completed visit.');
        }

        $visitsModel->update($id, ['status' => 4]); // 4 = Cancelled

        return redirect()->to('/visits')->with('success', 'Visit check-in has been cancelled.');
    }

    public function examine($id)
    {
        // Role check
        $role = session()->get('user_role');
        if ($role !== 'owner' && $role !== 'doctor') {
            return redirect()->to('/visits')->with('error', 'Only veterinarians and clinic owners are permitted to perform examinations.');
        }

        $visitsModel = new VisitsModel();
        $visit = $visitsModel->select('visits.*, pets.name as pet_name, pets.species as pet_species, pets.breed as pet_breed, pets.gender as pet_gender, pets.birth_date as pet_birth_date, customers.name as customer_name')
                             ->join('pets', 'pets.id = visits.pet_id', 'inner')
                             ->join('customers', 'customers.id = visits.customer_id', 'inner')
                             ->find($id);

        if (!$visit) {
            return redirect()->to('/visits')->with('error', 'Visit not found.');
        }

        // If completed or cancelled, redirect
        if ($visit['status'] == 3 || $visit['status'] == 4) {
            return redirect()->to('/visits')->with('error', 'Cannot examine a closed visit.');
        }

        // If status is 1 (Queued), transition to 2 (Under Examination)
        if ($visit['status'] == 1) {
            $visitsModel->update($id, ['status' => 2]);
        }

        // Fetch active services for checkboxes
        $servicesModel = new ServicesModel();
        $services = $servicesModel->where('status', 1)->orderBy('name', 'ASC')->findAll();

        // Fetch active inventory items for checkboxes
        $itemsModel = new \App\Models\ItemsModel();
        $items = $itemsModel->where('status', 1)->orderBy('name', 'ASC')->findAll();

        return view('visits/examine', [
            'visit'    => $visit,
            'services' => $services,
            'items'    => $items,
        ]);
    }

    public function saveExamination($id)
    {
        // Role check
        $role = session()->get('user_role');
        if ($role !== 'owner' && $role !== 'doctor') {
            return redirect()->to('/visits')->with('error', 'Only veterinarians and clinic owners are permitted to perform examinations.');
        }

        $visitsModel = new VisitsModel();
        $visit = $visitsModel->find($id);

        if (!$visit || $visit['status'] == 3 || $visit['status'] == 4) {
            return redirect()->to('/visits')->with('error', 'Invalid visit or visit is already closed.');
        }

        $rules = [
            'diagnosis'      => 'required|min_length[3]|max_length[5000]',
            'treatment_plan' => 'required|min_length[3]|max_length[5000]',
            'next_visit_at'  => 'permit_empty|valid_date[Y-m-d]',
            'services'       => 'permit_empty', // Array of service IDs
            'items'          => 'permit_empty', // Array of item IDs
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Create Medical Record
        $medicalRecordsModel = new MedicalRecordsModel();
        $medicalRecordData = [
            'visit_id'       => $id,
            'pet_id'         => $visit['pet_id'],
            'user_id'        => session()->get('user_id'),
            'diagnosis'      => $this->request->getPost('diagnosis'),
            'treatment_plan' => $this->request->getPost('treatment_plan'),
            'next_visit_at'  => $this->request->getPost('next_visit_at') ?: null,
        ];
        $medicalRecordId = $medicalRecordsModel->insert($medicalRecordData);

        // 2. Save Medical Record Services (Pivot)
        $selectedServices = $this->request->getPost('services') ?: [];
        $quantities = $this->request->getPost('quantities') ?: [];

        $totalAmount = 0.00;
        if (!empty($selectedServices)) {
            $mRecordServicesModel = new MedicalRecordServicesModel();
            $servicesModel = new ServicesModel();
            $dbServices = $servicesModel->whereIn('id', $selectedServices)->findAll();
            $dbServicesById = [];
            foreach ($dbServices as $s) {
                $dbServicesById[$s['id']] = $s;
            }

            foreach ($selectedServices as $serviceId) {
                $qty = isset($quantities[$serviceId]) ? (int)$quantities[$serviceId] : 1;
                if ($qty < 1) $qty = 1;

                $mRecordServicesModel->insert([
                    'medical_record_id' => $medicalRecordId,
                    'service_id'        => $serviceId,
                    'quantity'          => $qty,
                ]);

                if (isset($dbServicesById[$serviceId])) {
                    $totalAmount += $dbServicesById[$serviceId]['price'] * $qty;
                }
            }
        }

        // 2b. Save Medical Record Items (Pivot) and Decrement Stock
        $selectedItems = $this->request->getPost('items') ?: [];
        $itemQuantities = $this->request->getPost('item_quantities') ?: [];

        if (!empty($selectedItems)) {
            $mRecordItemsModel = new \App\Models\MedicalRecordItemsModel();
            $itemsModel = new \App\Models\ItemsModel();
            $dbItems = $itemsModel->whereIn('id', $selectedItems)->findAll();
            $dbItemsById = [];
            foreach ($dbItems as $item) {
                $dbItemsById[$item['id']] = $item;
            }

            foreach ($selectedItems as $itemId) {
                $qty = isset($itemQuantities[$itemId]) ? (int)$itemQuantities[$itemId] : 1;
                if ($qty < 1) $qty = 1;

                if (isset($dbItemsById[$itemId])) {
                    $itemData = $dbItemsById[$itemId];
                    $mRecordItemsModel->insert([
                        'medical_record_id' => $medicalRecordId,
                        'item_id'           => $itemId,
                        'quantity'          => $qty,
                        'buy_price'         => $itemData['buy_price'],
                        'sell_price'        => $itemData['sell_price'],
                    ]);

                    // Deduct stock (soft warning: permits stock to go negative)
                    $newStock = $itemData['stock'] - $qty;
                    $itemsModel->update($itemId, ['stock' => $newStock]);

                    $totalAmount += $itemData['sell_price'] * $qty;
                }
            }
        }

        // 3. Create Invoice automatically
        $invoicesModel = new \App\Models\InvoicesModel();
        $invoiceStatus = ($totalAmount <= 0) ? 2 : 1; // 2 = Paid if 0, 1 = Unpaid
        $invoiceId = $invoicesModel->insert([
            'clinic_id'         => $visit['clinic_id'],
            'customer_id'       => $visit['customer_id'],
            'medical_record_id' => $medicalRecordId,
            'total_amount'      => $totalAmount,
            'status'            => $invoiceStatus,
            'invoice_number'    => 'TEMP',
        ]);

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($invoiceId, 5, '0', STR_PAD_LEFT);
        $invoicesModel->update($invoiceId, ['invoice_number' => $invoiceNumber]);

        // 4. Mark Visit as Completed (3)
        $visitsModel->update($id, ['status' => 3]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save examination details and generate invoice. Please try again.');
        }

        return redirect()->to('/pets/show/' . $visit['pet_id'])->with('success', 'Examination saved, visit completed, and invoice generated.');
    }
}
