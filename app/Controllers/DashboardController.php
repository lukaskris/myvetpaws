<?php

namespace App\Controllers;

use App\Models\VisitsModel;
use App\Models\CustomersModel;
use App\Models\PetsModel;
use App\Models\ItemsModel;

class DashboardController extends BaseController
{
    public function index()
    {
        // Safety check (AuthFilter should also enforce this)
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $visitsModel = new VisitsModel();
        $customersModel = new CustomersModel();
        $petsModel = new PetsModel();

        // Calculate dynamic stats
        $today = date('Y-m-d');
        $todayVisits = $visitsModel->where('checkin_time >=', $today . ' 00:00:00')
                                   ->countAllResults();
        
        $activeCustomers = $customersModel->countAllResults();
        $activePets = $petsModel->countAllResults();

        // Query active queue (status 1 = Queued, 2 = Under Examination)
        $activeQueue = $visitsModel->select('visits.*, pets.name as pet_name, pets.photo as pet_photo, pets.species as pet_species, pets.breed as pet_breed, customers.name as customer_name')
                                   ->join('pets', 'pets.id = visits.pet_id', 'inner')
                                   ->join('customers', 'customers.id = visits.customer_id', 'inner')
                                   ->whereIn('visits.status', [1, 2])
                                   ->orderBy('visits.checkin_time', 'ASC')
                                   ->findAll();

        // Calculate dynamic revenue stats
        $db = \Config\Database::connect();
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $clinicId = session()->get('clinic_id');

        $todayRevenueResult = $db->table('payments')
                                 ->selectSum('amount')
                                 ->where('clinic_id', $clinicId)
                                 ->where('payment_date >=', $todayStart)
                                 ->where('payment_date <=', $todayEnd)
                                 ->where('deleted_at', null)
                                 ->get()
                                 ->getRowArray();
        $revenueSummary = isset($todayRevenueResult['amount']) ? (float)$todayRevenueResult['amount'] : 0.00;

        $outstandingResult = $db->query("
            SELECT SUM(invoices.total_amount - COALESCE(payments_sum.total_paid, 0)) as total_outstanding
            FROM invoices
            LEFT JOIN (
                SELECT invoice_id, SUM(amount) as total_paid
                FROM payments
                WHERE clinic_id = ? AND deleted_at IS NULL
                GROUP BY invoice_id
            ) as payments_sum ON payments_sum.invoice_id = invoices.id
            WHERE invoices.clinic_id = ? AND invoices.status IN (1, 3) AND invoices.deleted_at IS NULL
        ", [$clinicId, $clinicId])->getRowArray();
        $outstandingPayments = isset($outstandingResult['total_outstanding']) ? (float)$outstandingResult['total_outstanding'] : 0.00;
        if ($outstandingPayments < 0) $outstandingPayments = 0.00;

        // Query low stock items
        $itemsModel = new ItemsModel();
        $lowStockItems = $itemsModel->where('stock <= min_stock', null, false)
                                    ->where('status', 1)
                                    ->orderBy('stock', 'ASC')
                                    ->findAll();

        $data = [
            'today_visits'         => $todayVisits,
            'active_customers'     => $activeCustomers,
            'active_pets'          => $activePets,
            'revenue_summary'      => $revenueSummary,
            'outstanding_payments' => $outstandingPayments,
            'activeQueue'          => $activeQueue,
            'lowStockItems'        => $lowStockItems,
        ];

        return view('dashboard/owner', $data);
    }
}
