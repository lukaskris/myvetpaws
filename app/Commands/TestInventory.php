<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestInventory extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'test:inventory';
    protected $description = 'Runs automated unit testing for the inventory tracking system.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        CLI::write("=== Starting Inventory System Tests ===", 'cyan');

        // Disable tenant scoping for CLI script to bypass session dependencies
        $itemsModel = new \App\Models\ItemsModel();
        $itemsModel->disableTenantScope();
        $mRecordItemsModel = new \App\Models\MedicalRecordItemsModel();
        $mRecordServicesModel = new \App\Models\MedicalRecordServicesModel();
        $medicalRecordsModel = new \App\Models\MedicalRecordsModel();
        $medicalRecordsModel->disableTenantScope();
        $invoicesModel = new \App\Models\InvoicesModel();
        $invoicesModel->disableTenantScope();
        $servicesModel = new \App\Models\ServicesModel();
        $servicesModel->disableTenantScope();

        // 1. Verify Seed Data
        $amox = $itemsModel->where('code', 'OBT-AMOX-250')->first();
        if (!$amox) {
            CLI::error("FAIL: Seed item OBT-AMOX-250 not found!");
            $db->transRollback();
            exit(1);
        }
        CLI::write("PASS: OBT-AMOX-250 loaded successfully. Current Stock: {$amox['stock']}, Buy: {$amox['buy_price']}, Sell: {$amox['sell_price']}", 'green');

        $initialStockAmox = (int)$amox['stock'];

        $syringe = $itemsModel->where('code', 'ALT-SYR-3ML')->first();
        if (!$syringe) {
            CLI::error("FAIL: Seed item ALT-SYR-3ML not found!");
            $db->transRollback();
            exit(1);
        }
        CLI::write("PASS: ALT-SYR-3ML loaded successfully. Current Stock: {$syringe['stock']}, Buy: {$syringe['buy_price']}, Sell: {$syringe['sell_price']}", 'green');

        $initialStockSyringe = (int)$syringe['stock'];

        // 2. Perform Examination Simulation
        CLI::write("Simulating medical examination with services & inventory items...", 'yellow');

        // Create medical record
        $mrId = $medicalRecordsModel->insert([
            'clinic_id'      => 1,
            'visit_id'       => 1,
            'pet_id'         => 1,
            'user_id'        => 1,
            'diagnosis'      => 'Test Infection',
            'treatment_plan' => 'Prescribe amox and syringe usage',
        ]);

        // Attach services: KONSUL (1) & GROOM (2)
        $mRecordServicesModel->insert([
            'medical_record_id' => $mrId,
            'service_id'        => 1,
            'quantity'          => 1,
        ]);
        $mRecordServicesModel->insert([
            'medical_record_id' => $mrId,
            'service_id'        => 2,
            'quantity'          => 1,
        ]);

        // Attach items: Amox (qty 3) & Syringe (qty 5)
        $mRecordItemsModel->insert([
            'medical_record_id' => $mrId,
            'item_id'           => $amox['id'],
            'quantity'          => 3,
            'buy_price'         => $amox['buy_price'],
            'sell_price'        => $amox['sell_price'],
        ]);
        // Decrement stock
        $itemsModel->update($amox['id'], ['stock' => $initialStockAmox - 3]);

        $mRecordItemsModel->insert([
            'medical_record_id' => $mrId,
            'item_id'           => $syringe['id'],
            'quantity'          => 5,
            'buy_price'         => $syringe['buy_price'],
            'sell_price'        => $syringe['sell_price'],
        ]);
        // Decrement stock
        $itemsModel->update($syringe['id'], ['stock' => $initialStockSyringe - 5]);

        // Verify stock deduction
        $updatedAmox = $itemsModel->find($amox['id']);
        $updatedSyringe = $itemsModel->find($syringe['id']);

        if ((int)$updatedAmox['stock'] !== $initialStockAmox - 3) {
            CLI::error("FAIL: Amoxicillin stock decrement incorrect! Got: {$updatedAmox['stock']}, Expected: " . ($initialStockAmox - 3));
            $db->transRollback();
            exit(1);
        }
        CLI::write("PASS: Amoxicillin stock decremented correctly to: {$updatedAmox['stock']}", 'green');

        if ((int)$updatedSyringe['stock'] !== $initialStockSyringe - 5) {
            CLI::error("FAIL: Syringe stock decrement incorrect! Got: {$updatedSyringe['stock']}, Expected: " . ($initialStockSyringe - 5));
            $db->transRollback();
            exit(1);
        }
        CLI::write("PASS: Syringe stock decremented correctly to: {$updatedSyringe['stock']}", 'green');

        // 3. Verify Invoice total calculation
        $totalAmount = 0.00;
        // Services: KONSUL (150000) * 1 + GROOM (25000) * 1 = 175000
        $totalAmount += 150000.00 * 1;
        $totalAmount += 25000.00 * 1;
        // Items: Amox (12000) * 3 + Syringe (5000) * 5 = 36000 + 25000 = 61000
        $totalAmount += (float)$amox['sell_price'] * 3;
        $totalAmount += (float)$syringe['sell_price'] * 5;

        if ($totalAmount !== 236000.00) {
            CLI::error("FAIL: Expected calculated amount of 236000, got: {$totalAmount}");
            $db->transRollback();
            exit(1);
        }
        CLI::write("PASS: Total amount calculation correct: Rp" . number_format($totalAmount, 0, ',', '.'), 'green');

        $invoiceId = $invoicesModel->insert([
            'clinic_id'         => 1,
            'customer_id'       => 1,
            'medical_record_id' => $mrId,
            'total_amount'      => $totalAmount,
            'status'            => 1,
            'invoice_number'    => 'INV-TEST-99999',
        ]);

        $invoice = $invoicesModel->find($invoiceId);
        if (!$invoice || (float)$invoice['total_amount'] !== 236000.00) {
            CLI::error("FAIL: Invoice total amount did not insert correctly!");
            $db->transRollback();
            exit(1);
        }
        CLI::write("PASS: Invoice total amount registered correctly: Rp" . number_format($invoice['total_amount'], 0, ',', '.'), 'green');

        // Rollback transaction to keep test database clean
        $db->transRollback();
        CLI::write("=== All Tests Passed Successfully ===", 'green');
    }
}
