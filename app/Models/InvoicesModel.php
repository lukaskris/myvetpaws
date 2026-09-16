<?php

namespace App\Models;

class InvoicesModel extends TenantModel
{
    protected $table            = 'invoices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'clinic_id',
        'customer_id',
        'medical_record_id',
        'invoice_number',
        'total_amount',
        'discount',
        'discount_type',
        'service_discount',
        'service_discount_type',
        'item_discount',
        'item_discount_type',
        'status',
    ];

    /**
     * Resolve stored discount fields into rupiah amounts.
     * Per-category mode is active when either category discount is set,
     * otherwise the invoice-level discount applies.
     */
    public static function resolveDiscount(array $invoice, float $serviceSubtotal, float $itemSubtotal): array
    {
        $svcValue = (float)($invoice['service_discount'] ?? 0);
        $itmValue = (float)($invoice['item_discount'] ?? 0);

        if ($svcValue > 0 || $itmValue > 0) {
            $svcAmount = $svcValue > 0
                ? (($invoice['service_discount_type'] ?? 'rupiah') === 'percent' ? $serviceSubtotal * $svcValue / 100 : $svcValue)
                : 0.0;
            $itmAmount = $itmValue > 0
                ? (($invoice['item_discount_type'] ?? 'rupiah') === 'percent' ? $itemSubtotal * $itmValue / 100 : $itmValue)
                : 0.0;

            // ponytail: silently cap at subtotal instead of erroring; server-side guard rejects new over-discounts
            return [
                'mode'    => 'category',
                'service' => min($svcAmount, $serviceSubtotal),
                'item'    => min($itmAmount, $itemSubtotal),
                'invoice' => 0.0,
            ];
        }

        $value  = (float)($invoice['discount'] ?? 0);
        $amount = ($value > 0 && ($invoice['discount_type'] ?? 'rupiah') === 'percent')
            ? ($serviceSubtotal + $itemSubtotal) * $value / 100
            : $value;

        return [
            'mode'    => 'invoice',
            'service' => 0.0,
            'item'    => 0.0,
            'invoice' => min($amount, $serviceSubtotal + $itemSubtotal),
        ];
    }

    /**
     * Total resolved discount in rupiah for one invoice row.
     */
    public static function discountTotal(array $invoice, float $serviceSubtotal, float $itemSubtotal): float
    {
        $resolved = self::resolveDiscount($invoice, $serviceSubtotal, $itemSubtotal);

        return $resolved['service'] + $resolved['item'] + $resolved['invoice'];
    }

    /**
     * Service and medicine/supply subtotals for one medical record.
     */
    public static function categorySubtotals($medicalRecordId): array
    {
        $db      = \Config\Database::connect();
        $svcRow  = $db->table('medical_record_services')
            ->select('COALESCE(SUM(medical_record_services.quantity * services.price), 0) as subtotal', false)
            ->join('services', 'services.id = medical_record_services.service_id', 'inner')
            ->where('medical_record_services.medical_record_id', $medicalRecordId)
            ->get()->getRowArray();
        $itmRow = $db->table('medical_record_items')
            ->select('COALESCE(SUM(medical_record_items.quantity * medical_record_items.sell_price), 0) as subtotal', false)
            ->join('items', 'items.id = medical_record_items.item_id', 'inner')
            ->where('medical_record_items.medical_record_id', $medicalRecordId)
            ->get()->getRowArray();

        return [
            'service' => (float)($svcRow['subtotal'] ?? 0),
            'item'    => (float)($itmRow['subtotal'] ?? 0),
        ];
    }

    /**
     * Total settled payments for one invoice.
     */
    public static function paidForInvoice($invoiceId): float
    {
        $db  = \Config\Database::connect();
        $row = $db->table('payments')
            ->select('COALESCE(SUM(amount), 0) as total_paid', false)
            ->where('invoice_id', $invoiceId)
            ->where('deleted_at', null)
            ->get()->getRowArray();

        return (float)($row['total_paid'] ?? 0);
    }

    /**
     * Spread a discount pot across line rows proportionally to row amount.
     * Rows need 'price' and 'quantity'; each gets a 'discount' key.
     * Rounded to 2 dp, last row absorbs the remainder.
     */
    public static function allocateDiscount(array $rows, float $pot): array
    {
        $subtotal = 0.0;
        foreach ($rows as $r) {
            $subtotal += $r['price'] * $r['quantity'];
        }
        if ($pot <= 0 || $subtotal <= 0) {
            foreach ($rows as $i => $r) {
                $rows[$i]['discount'] = 0.0;
            }
            return $rows;
        }

        $pot   = min($pot, $subtotal);
        $used  = 0.0;
        $count = count($rows);
        foreach ($rows as $i => $r) {
            if ($i === $count - 1) {
                $rows[$i]['discount'] = max(0.0, round($pot - $used, 2));
            } else {
                $d = round($pot * (($r['price'] * $r['quantity']) / $subtotal), 2);
                $rows[$i]['discount'] = $d;
                $used += $d;
            }
        }
        return $rows;
    }

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
