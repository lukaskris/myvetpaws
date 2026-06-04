<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= esc($invoice['invoice_number']) ?></title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333333;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        
        /* Clinic Header (Using Clinic's own Name/Logo) */
        .clinic-header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .clinic-name {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .clinic-details {
            font-size: 9px;
            color: #6b7280;
            margin-top: 3px;
        }
        
        /* Invoice Meta info (Number and Dates) */
        .invoice-meta-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .invoice-meta-table td {
            padding: 0;
            vertical-align: top;
        }
        .invoice-no-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            color: #111827;
        }
        
        /* Customer Billing Details at the Top */
        .customer-card {
            margin-bottom: 25px;
        }
        .customer-label {
            font-size: 8px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .customer-name {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }
        .customer-address {
            font-size: 10px;
            color: #4b5563;
            margin-top: 3px;
            max-width: 350px;
        }

        /* Items Table grouped by Pet */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .items-table th {
            text-align: left;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #d1d5db;
            padding: 6px 8px;
        }
        .items-table td {
            padding: 8px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }
        
        /* Pet Grouping Row */
        .pet-group-row td {
            background-color: #f9fafb;
            font-size: 11px;
            font-weight: 700;
            color: #111827;
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Sub-items (Medicines and Procedures) */
        .item-name {
            font-size: 10px;
            color: #374151;
            padding-left: 15px; /* Indent sub-items */
        }
        .item-price {
            font-size: 10px;
            color: #374151;
            text-align: right;
            font-weight: 600;
        }

        /* Invoice Summary totals */
        .totals-table {
            width: 250px;
            margin-left: auto;
            border-top: 1px solid #e5e7eb;
            margin-top: 10px;
        }
        .totals-table td {
            padding: 6px 8px;
            font-size: 11px;
        }
        .totals-label {
            text-align: right;
            color: #4b5563;
            font-weight: 600;
        }
        .totals-val {
            text-align: right;
            color: #111827;
            font-weight: 800;
        }
        
        /* Status Indicator text */
        .status-text {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #4b5563;
        }
    </style>
</head>
<body>

    <!-- Clinic Header (Logo MyVetPaws removed, using Clinic Name) -->
    <div class="clinic-header">
        <div class="clinic-name"><?= esc(session()->get('clinic_name') ?? 'MyVetPaws') ?></div>
        <div class="clinic-details">
            Email: <?= esc(session()->get('clinic_email') ?: 'contact@' . (session()->get('clinic_slug') ?: 'myvetpaws') . '.com') ?> | 
            Hotline: <?= esc(session()->get('clinic_phone') ?: 'Clinic Hotline') ?>
        </div>
    </div>

    <!-- Meta Details & Customer Card -->
    <table class="invoice-meta-table">
        <tr>
            <td>
                <!-- Billed Customer Details -->
                <div class="customer-card">
                    <div class="customer-label">Billed To</div>
                    <div class="customer-name"><?= esc($invoice['customer_name']) ?></div>
                    <div class="customer-address">
                        <?= esc($invoice['customer_address'] ?: 'No address provided') ?><br>
                        Phone: <?= esc($invoice['customer_phone'] ?: '—') ?>
                    </div>
                </div>
            </td>
            <td style="text-align: right; width: 220px;">
                <div class="invoice-no-title">Invoice</div>
                <div style="font-size: 10px; color: #4b5563; margin-top: 4px;">
                    No: <strong><?= esc($invoice['invoice_number']) ?></strong><br>
                    Date: <?= date('M j, Y', strtotime($invoice['created_at'])) ?><br>
                    Status: <span class="status-text"><?= $invoice['status'] == 2 ? 'Paid' : ($invoice['status'] == 3 ? 'Partially Paid' : 'Unpaid') ?></span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Line Items Table grouped by Pet -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 75%;">Description</th>
                <th style="width: 25%; text-align: right; padding-right: 8px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($groupedPets)): ?>
                <tr>
                    <td class="item-name" style="color: #9ca3af; italic">No medicine or procedures were billed.</td>
                    <td class="item-price" style="color: #9ca3af;">Rp0</td>
                </tr>
            <?php else: ?>
                <?php foreach ($groupedPets as $petIdx => $gp): 
                    $petHeader = esc($gp['pet_name']);
                    if (!empty($gp['pet_species'])) {
                        $petHeader .= ' (' . esc($gp['pet_species']) . ')';
                    }
                ?>
                    <!-- Pet Header Row -->
                    <tr class="pet-group-row">
                        <td colspan="2"><?= ($petIdx + 1) ?>. <?= $petHeader ?>:</td>
                    </tr>
                    
                    <!-- Services sub-section -->
                    <tr>
                        <td colspan="2" style="font-size: 8px; font-weight: bold; text-transform: uppercase; color: #6b7280; padding-left: 10px; padding-top: 6px; padding-bottom: 2px;">
                            Services & Procedures:
                        </td>
                    </tr>
                    <?php if (empty($gp['services'])): ?>
                        <tr>
                            <td class="item-name" style="color: #9ca3af; italic; padding-left: 20px;">- No services billed.</td>
                            <td class="item-price" style="color: #9ca3af;">Rp0</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($gp['services'] as $srv): 
                            $lineTotal = $srv['price'] * $srv['quantity'];
                            $desc = esc($srv['name']);
                            if ($srv['quantity'] > 1) {
                                $desc .= ' (x' . esc($srv['quantity']) . ')';
                            }
                        ?>
                            <tr>
                                <td class="item-name">- <?= $desc ?></td>
                                <td class="item-price">Rp<?= number_format($lineTotal, 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Medicines & Supplies sub-section -->
                    <tr>
                        <td colspan="2" style="font-size: 8px; font-weight: bold; text-transform: uppercase; color: #6b7280; padding-left: 10px; padding-top: 6px; padding-bottom: 2px;">
                            Medicines & Supplies:
                        </td>
                    </tr>
                    <?php if (empty($gp['items'])): ?>
                        <tr>
                            <td class="item-name" style="color: #9ca3af; italic; padding-left: 20px;">- No medicines/supplies used.</td>
                            <td class="item-price" style="color: #9ca3af;">Rp0</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($gp['items'] as $itm): 
                            $lineTotal = $itm['price'] * $itm['quantity'];
                            $desc = esc($itm['name']);
                            if ($itm['quantity'] > 1) {
                                $desc .= ' (x' . esc($itm['quantity']) . ')';
                            }
                        ?>
                            <tr>
                                <td class="item-name">- <?= $desc ?></td>
                                <td class="item-price">Rp<?= number_format($lineTotal, 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Grand Total -->
    <table class="totals-table">
        <tr>
            <td class="totals-label">Total:</td>
            <td class="totals-val">Rp<?= number_format($totalInvoiceAmount, 0, ',', '.') ?></td>
        </tr>
    </table>

</body>
</html>
