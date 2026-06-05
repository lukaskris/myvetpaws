<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= esc($invoice['invoice_number']) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1f2937;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        
        /* Clinic Branding & Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .header-table td {
            padding: 0;
            vertical-align: top;
        }
        .clinic-logo {
            max-height: 50px;
            max-width: 150px;
            margin-bottom: 8px;
            object-fit: contain;
        }
        .clinic-name {
            font-size: 16px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.3px;
        }
        .clinic-details {
            font-size: 9px;
            color: #4b5563;
            margin-top: 3px;
            line-height: 1.4;
        }
        
        .invoice-title {
            font-size: 26px;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: -0.8px;
            line-height: 1;
        }
        .invoice-meta {
            font-size: 10px;
            color: #4b5563;
            margin-top: 8px;
            line-height: 1.5;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 4px;
            letter-spacing: 0.3px;
            line-height: 1;
            vertical-align: middle;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-partial {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Billing and Patient Grid */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .details-table td {
            padding: 0;
            vertical-align: top;
        }
        .section-title {
            font-size: 8px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .customer-name {
            font-size: 12px;
            font-weight: 700;
            color: #111827;
        }
        .customer-info {
            font-size: 9px;
            color: #4b5563;
            margin-top: 3px;
            line-height: 1.4;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #4b5563;
            background-color: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
        }
        .items-table td {
            padding: 8px 10px;
            font-size: 9.5px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
        }
        .pet-header-row td {
            background-color: #f3f4f6;
            font-size: 10px;
            font-weight: 700;
            color: #111827;
            padding: 6px 10px;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }
        .indent-item {
            padding-left: 20px !important;
        }
        .price-col {
            text-align: right;
        }
        .qty-col {
            text-align: center;
        }
        .total-col {
            text-align: right;
            font-weight: 700;
            color: #111827;
        }
        .category-text {
            color: #6b7280;
            font-size: 9px;
        }

        /* Bottom Section: Payments & Summary */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .footer-table td {
            padding: 0;
            vertical-align: top;
        }
        .payment-history-title {
            font-size: 8px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .payments-table th {
            text-align: left;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
            padding: 4px 0;
            font-weight: 600;
        }
        .payments-table td {
            padding: 5px 0;
            color: #4b5563;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .totals-table td {
            padding: 5px 0;
            color: #4b5563;
        }
        .totals-label {
            text-align: right;
            padding-right: 15px !important;
        }
        .totals-val {
            text-align: right;
            font-weight: 600;
            color: #111827;
            width: 110px;
        }
        .grand-total-row td {
            border-top: 1.5px solid #111827;
            padding-top: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #111827;
        }
        .grand-total-val {
            font-size: 13px;
            font-weight: 800;
        }
    </style>
</head>
<body>

    <!-- Header Block -->
    <table class="header-table">
        <tr>
            <!-- Clinic Info -->
            <td style="width: 65%;">
                <table style="border-collapse: collapse; border: none; margin: 0; padding: 0; background: transparent;">
                    <tr>
                        <?php if (!empty($logoBase64)): ?>
                            <td style="vertical-align: middle; padding-right: 15px; border: none; padding-bottom: 0; background: transparent;">
                                <img class="clinic-logo" src="<?= $logoBase64 ?>" style="max-height: 50px; max-width: 120px; object-fit: contain; display: block; margin-bottom: 0;" alt="Logo">
                            </td>
                        <?php endif; ?>
                        <td style="vertical-align: middle; border: none; padding-bottom: 0; background: transparent;">
                            <div class="clinic-name"><?= esc($clinic['name'] ?? 'MyVetPaws') ?></div>
                            <div class="clinic-details">
                                <?= esc($clinic['address'] ?: 'Clinic Address') ?><?= !empty($clinic['city']) ? ', ' . esc($clinic['city']) : '' ?><?= !empty($clinic['province']) ? ', ' . esc($clinic['province']) : '' ?><br>
                                Phone: <?= esc($clinic['phone'] ?: '—') ?> | Email: <?= esc($clinic['email'] ?: 'contact@myvetpaws.com') ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <!-- Invoice Meta Info -->
            <td style="width: 35%; text-align: right;">
                <div class="invoice-title">Invoice</div>
                <table style="margin-left: auto; margin-top: 8px; border-collapse: collapse; font-size: 10px; color: #4b5563; text-align: right; background: transparent; border: none;">
                    <tr>
                        <td style="padding: 2px 0; border: none; background: transparent;">Invoice No:</td>
                        <td style="padding: 2px 0 2px 10px; font-weight: bold; color: #111827; border: none; background: transparent;"><?= esc($invoice['invoice_number']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; border: none; background: transparent;">Date:</td>
                        <td style="padding: 2px 0 2px 10px; border: none; background: transparent;"><?= date('M j, Y', strtotime($invoice['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; vertical-align: middle; border: none; background: transparent;">Status:</td>
                        <td style="padding: 2px 0 2px 10px; vertical-align: middle; border: none; background: transparent;">
                            <?php if ($invoice['status'] == 2): ?>
                                <span class="status-badge status-paid">Paid</span>
                            <?php elseif ($invoice['status'] == 3): ?>
                                <span class="status-badge status-partial">Partially Paid</span>
                            <?php else: ?>
                                <span class="status-badge status-unpaid">Unpaid</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Billing Details & Patient list -->
    <table class="details-table">
        <tr>
            <!-- Billed Customer Details -->
            <td style="width: 50%; padding-right: 25px;">
                <div class="section-title">Billed To</div>
                <div class="customer-name"><?= esc($invoice['customer_name']) ?></div>
                <div class="customer-info">
                    <?= esc($invoice['customer_address'] ?: 'No address provided') ?><br>
                    Phone: <?= esc($invoice['customer_phone'] ?: '—') ?><br>
                    Email: <?= esc($invoice['customer_email'] ?: '—') ?>
                </div>
            </td>
            <!-- Patients Info -->
            <td style="width: 50%;">
                <div class="section-title">Patients / Pets Billed</div>
                <div class="customer-info" style="font-size: 9.5px; line-height: 1.5; color: #111827;">
                    <?php 
                    $petLabels = [];
                    foreach ($groupedPets as $gp) {
                        $petStr = '<strong>' . esc($gp['pet_name']) . '</strong>';
                        if (!empty($gp['pet_species'])) {
                            $petStr .= ' (' . esc($gp['pet_species']) . ')';
                        }
                        $petLabels[] = $petStr;
                    }
                    echo implode(', ', $petLabels);
                    ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Description</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 15%; text-align: right;">Unit Price</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($groupedPets)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #9ca3af; font-style: italic; padding: 15px;">
                        No services or medicines were billed on this invoice.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($groupedPets as $gp): 
                    $petHeader = esc($gp['pet_name']);
                    if (!empty($gp['pet_species'])) {
                        $petHeader .= ' (' . esc($gp['pet_species']) . ')';
                    }
                ?>
                    <!-- Pet Group Header Row -->
                    <tr class="pet-header-row">
                        <td colspan="5">Patient: <?= $petHeader ?></td>
                    </tr>
                    
                    <!-- Services under this Pet -->
                    <?php foreach ($gp['services'] as $srv): ?>
                        <tr>
                            <td class="indent-item"><?= esc($srv['name']) ?></td>
                            <td class="category-text">Service / Procedure</td>
                            <td class="price-col">Rp<?= number_format($srv['price'], 0, ',', '.') ?></td>
                            <td class="qty-col"><?= esc($srv['quantity']) ?></td>
                            <td class="total-col">Rp<?= number_format($srv['price'] * $srv['quantity'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Medicines under this Pet -->
                    <?php foreach ($gp['items'] as $itm): ?>
                        <tr>
                            <td class="indent-item"><?= esc($itm['name']) ?></td>
                            <td class="category-text">Medicine / Supply</td>
                            <td class="price-col">Rp<?= number_format($itm['price'], 0, ',', '.') ?></td>
                            <td class="qty-col"><?= esc($itm['quantity']) ?></td>
                            <td class="total-col">Rp<?= number_format($itm['price'] * $itm['quantity'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer Summary Block -->
    <table class="footer-table">
        <tr>
            <!-- Left: Payments History list -->
            <td style="width: 55%; padding-right: 40px;">
                <?php if (!empty($payments)): ?>
                    <div class="payment-history-title">Payment Transactions</div>
                    <table class="payments-table">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Date</th>
                                <th style="width: 25%;">Method</th>
                                <th style="width: 30%; text-align: right;">Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?= date('M j, Y H:i', strtotime($p['payment_date'])) ?></td>
                                    <td style="text-transform: capitalize;"><?= esc($p['payment_method']) ?></td>
                                    <td style="text-align: right; font-weight: 700; color: #111827;">Rp<?= number_format($p['amount'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </td>
            
            <!-- Right: Totals Block -->
            <td style="width: 45%;">
                <table class="totals-table">
                    <tr>
                        <td class="totals-label">Subtotal:</td>
                        <td class="totals-val">Rp<?= number_format($totalInvoiceAmount, 0, ',', '.') ?></td>
                    </tr>
                    <?php if ($totalPaid > 0): ?>
                        <tr>
                            <td class="totals-label" style="color: #065f46;">Amount Paid:</td>
                            <td class="totals-val" style="color: #065f46; font-weight: 700;">- Rp<?= number_format($totalPaid, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td class="totals-label">Remaining Balance:</td>
                            <td class="totals-val" style="font-weight: 700;">Rp<?= number_format($remainingBalance, 0, ',', '.') ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr class="grand-total-row">
                        <td class="totals-label">Total Amount:</td>
                        <td class="totals-val grand-total-val">Rp<?= number_format($totalInvoiceAmount, 0, ',', '.') ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
