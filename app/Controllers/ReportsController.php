<?php

namespace App\Controllers;

class ReportsController extends BaseController
{
    public function index()
    {
        $clinicId = session()->get('clinic_id');
        $db = \Config\Database::connect();

        // 1. Revenue Trends (last 30 days)
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $paymentsRaw = $db->table('payments')
                          ->select("DATE(payment_date) as p_date, SUM(amount) as total_rev")
                          ->where('clinic_id', $clinicId)
                          ->where('payment_date >=', $thirtyDaysAgo . ' 00:00:00')
                          ->where('deleted_at', null)
                          ->groupBy("DATE(payment_date)")
                          ->orderBy("p_date", "ASC")
                          ->get()
                          ->getResultArray();

        $revenueTrends = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $revenueTrends[$date] = 0.00;
        }
        foreach ($paymentsRaw as $p) {
            if (isset($revenueTrends[$p['p_date']])) {
                $revenueTrends[$p['p_date']] = (float)$p['total_rev'];
            }
        }

        // 2. Visit Trends (last 30 days)
        $visitsRaw = $db->table('visits')
                        ->select("DATE(checkin_time) as v_date, COUNT(*) as visit_count")
                        ->where('clinic_id', $clinicId)
                        ->where('checkin_time >=', $thirtyDaysAgo . ' 00:00:00')
                        ->where('deleted_at', null)
                        ->groupBy("DATE(checkin_time)")
                        ->orderBy("v_date", "ASC")
                        ->get()
                        ->getResultArray();

        $visitTrends = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $visitTrends[$date] = 0;
        }
        foreach ($visitsRaw as $v) {
            if (isset($visitTrends[$v['v_date']])) {
                $visitTrends[$v['v_date']] = (int)$v['visit_count'];
            }
        }

        // 3. Top-performing services (revenue generated)
        $topServices = $db->table('medical_record_services')
                          ->select('services.name, services.code, services.price, SUM(medical_record_services.quantity) as total_qty, SUM(services.price * medical_record_services.quantity) as total_revenue')
                          ->join('medical_records', 'medical_records.id = medical_record_services.medical_record_id', 'inner')
                          ->join('services', 'services.id = medical_record_services.service_id', 'inner')
                          ->where('medical_records.clinic_id', $clinicId)
                          ->where('medical_records.deleted_at', null)
                          ->groupBy('services.id')
                          ->orderBy('total_revenue', 'DESC')
                          ->limit(5)
                          ->get()
                          ->getResultArray();

        // 4. Top-performing items (revenue & cost margin)
        $topItems = $db->table('medical_record_items')
                       ->select('items.name, items.code, medical_record_items.sell_price as price, SUM(medical_record_items.quantity) as total_qty, SUM(medical_record_items.sell_price * medical_record_items.quantity) as total_revenue, SUM((medical_record_items.sell_price - medical_record_items.buy_price) * medical_record_items.quantity) as total_profit')
                       ->join('medical_records', 'medical_records.id = medical_record_items.medical_record_id', 'inner')
                       ->join('items', 'items.id = medical_record_items.item_id', 'inner')
                       ->where('medical_records.clinic_id', $clinicId)
                       ->where('medical_records.deleted_at', null)
                       ->groupBy('items.id')
                       ->orderBy('total_revenue', 'DESC')
                       ->limit(5)
                       ->get()
                       ->getResultArray();

        return view('reports/index', [
            'revenueTrends' => $revenueTrends,
            'visitTrends'   => $visitTrends,
            'topServices'   => $topServices,
            'topItems'      => $topItems,
        ]);
    }
}
