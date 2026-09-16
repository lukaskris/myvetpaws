<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice <?= esc($invoice['invoice_number']) ?></title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: Helvetica, Arial, sans-serif;
        color: #111827;
        font-size: 11px;
        line-height: 1.5;
    }
    .doc {
        padding: 34px 44px 28px 44px;
    }

    /* ===== Header ===== */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }
    .clinic-logo {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }
    .clinic-name {
        font-size: 15px;
        font-weight: bold;
        color: #111827;
    }
    .clinic-details {
        font-size: 9.5px;
        color: #6b7280;
        line-height: 1.6;
    }
    .invoice-title {
        font-size: 30px;
        font-weight: bold;
        letter-spacing: 4px;
        color: #111827;
        text-align: right;
        line-height: 1;
        margin-bottom: 10px;
    }
    .meta-table {
        margin-left: auto;
        border-collapse: collapse;
        font-size: 10px;
        min-width: 240px;
    }
    .meta-table td {
        padding: 3px 0;
        border-bottom: 0.75px solid #e5e7eb;
    }
    .meta-table td:first-child {
        color: #6b7280;
        white-space: nowrap;
        padding-right: 14px;
    }
    .meta-table td:last-child {
        font-weight: bold;
        color: #111827;
        text-align: right;
    }
    .status-paid    { color: #059669; }
    .status-partial { color: #b45309; }
    .status-unpaid  { color: #dc2626; }

    /* ===== Parties ===== */
    .parties-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }
    .party-label {
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #9ca3af;
        margin-bottom: 2px;
    }
    .party-value {
        font-size: 11px;
        color: #111827;
    }
    .party-value strong { font-weight: bold; }
    .party-info {
        font-size: 10px;
        color: #6b7280;
        line-height: 1.6;
    }

    /* ===== Detail tables ===== */
    .section-title {
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #6b7280;
        margin: 12px 0 5px 0;
    }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10.5px;
    }
    .items-table thead th {
        text-align: left;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.75px;
        color: #6b7280;
        font-weight: bold;
        padding: 4px 6px;
        border-bottom: 1.25px solid #111827;
    }
    .items-table tbody td {
        padding: 4px 6px;
        border-bottom: 0.75px solid #e5e7eb;
        vertical-align: top;
    }
    .items-table .num {
        text-align: right;
        white-space: nowrap;
    }
    .items-table .ctr {
        text-align: center;
        white-space: nowrap;
    }
    .pet-ref {
        font-style: italic;
        color: #6b7280;
        font-size: 9.5px;
    }
    .subtotal-row td {
        border-bottom: 1.25px solid #111827;
        font-weight: bold;
        text-align: right;
        padding: 6px;
    }
    .subtotal-row td:first-child {
        text-align: left;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #6b7280;
    }

    /* ===== Summary ===== */
    .summary-table {
        margin-left: auto;
        border-collapse: collapse;
        font-size: 11px;
        min-width: 300px;
        margin-top: 14px;
    }
    .summary-table td {
        padding: 4px 0 4px 18px;
        border-bottom: 0.75px solid #e5e7eb;
    }
    .summary-table td:first-child {
        color: #6b7280;
        white-space: nowrap;
    }
    .summary-table td:last-child {
        text-align: right;
        font-weight: bold;
        white-space: nowrap;
    }
    .summary-total td {
        border-top: 1.25px solid #111827;
        border-bottom: none;
        font-weight: bold;
        color: #111827;
        padding-top: 7px;
    }
    .summary-total td:first-child { color: #111827; }
    .summary-due td:last-child { color: #dc2626; }

    /* ===== Footer ===== */
    .footer {
        text-align: center;
        font-style: italic;
        font-size: 10px;
        color: #6b7280;
        margin-top: 26px;
    }
</style>
</head>
<body>
<div class="doc">

    <!-- ===== Header ===== -->
    <table class="header-table">
        <tr>
            <!-- Left: round logo, clinic name, email & phone -->
            <td style="width: 55%; vertical-align: top;">
                <table style="border-collapse: collapse; border: none;">
                    <tr>
                        <?php if (!empty($logoBase64)): ?>
                            <td style="padding-right: 14px; border: none;">
                                <img class="clinic-logo" src="<?= $logoBase64 ?>" alt="Logo">
                            </td>
                        <?php endif; ?>
                        <td style="border: none; vertical-align: middle;">
                            <div class="clinic-name"><?= esc($clinic['name'] ?? 'MyVetPaws') ?></div>
                            <div class="clinic-details">
                                <?= esc($clinic['email'] ?: 'contact@myvetpaws.com') ?><br>
                                <?= esc($clinic['phone'] ?: '—') ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <!-- Right: big INVOICE + meta block with horizontal rules -->
            <td style="width: 45%; vertical-align: top;">
                <div class="invoice-title">INVOICE</div>
                <table class="meta-table">
                    <tr>
                        <td>No. Invoice</td>
                        <td><?= esc($invoice['invoice_number']) ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal Invoice</td>
                        <td><?= date('d/m/Y', strtotime($invoice['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            <?php if ($remainingBalance <= 0 && $totalPaid > 0): ?>
                                <span class="status-paid">Lunas</span>
                            <?php elseif ($invoice['status'] == 3): ?>
                                <span class="status-partial">Kurang Bayar Rp<?= number_format($remainingBalance, 0, ',', '.') ?></span>
                            <?php else: ?>
                                <span class="status-unpaid">Kurang Bayar Rp<?= number_format($remainingBalance, 0, ',', '.') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ===== Parties: client info (left) + user (right) ===== -->
    <table class="parties-table">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 30px;">
                <div class="party-label">Invoice Kepada:</div>
                <div class="party-value"><strong><?= esc($invoice['customer_name']) ?></strong></div>
                <div class="party-info">
                    Alamat: <?= esc($invoice['customer_address'] ?: '—') ?><br>
                    Telepon: <?= esc($invoice['customer_phone'] ?: '—') ?>
                </div>
            </td>
            <td style="width: 45%; vertical-align: top; padding-left: 30px;">
                <div class="party-label">Pasien:</div>
                <div class="party-value">
                    <?php
                    $petLabels = [];
                    foreach ($groupedPets as $gp) {
                        $petLabels[] = '<strong>' . esc($gp['pet_name']) . '</strong>' . (!empty($gp['pet_species']) ? ' (' . esc($gp['pet_species']) . ')' : '');
                    }
                    echo implode(', ', $petLabels);
                    ?>
                </div>
                <div class="party-info">
                    User: <?= esc($groupedPets[0]['doctor_name'] ?? (session()->get('user_name') ?: '—')) ?>
                </div>
            </td>
        </tr>
    </table>

    <?php
    // Flatten rows across pets for the two category tables
    $serviceRows = [];
    $itemRows = [];
    foreach ($groupedPets as $gp) {
        foreach ($gp['services'] as $srv) {
            $serviceRows[] = $srv + ['pet_name' => $gp['pet_name']];
        }
        foreach ($gp['items'] as $itm) {
            $itemRows[] = $itm + ['pet_name' => $gp['pet_name']];
        }
    }
    ?>

    <!-- ===== Layanan ===== -->
    <?php if (!empty($serviceRows)): ?>
        <div class="section-title">Layanan</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 44%;">Deskripsi</th>
                    <th class="ctr" style="width: 10%;">Kuantitas</th>
                    <th class="num" style="width: 14%;">Diskon</th>
                    <th class="num" style="width: 15%;">Harga</th>
                    <th class="num" style="width: 17%;">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php $serviceSubtotal = 0.0; ?>
                <?php foreach ($serviceRows as $srv): ?>
                    <?php
                    $gross    = $srv['price'] * $srv['quantity'];
                    $discount = (float)($srv['discount'] ?? 0);
                    $serviceSubtotal += $gross - $discount;
                    ?>
                    <tr>
                        <td>
                            <?= esc($srv['name']) ?> <span class="pet-ref">(<?= esc($srv['pet_name']) ?>)</span>
                        </td>
                        <td class="ctr"><?= (int)$srv['quantity'] ?></td>
                        <td class="num"><?= $discount > 0 ? number_format($discount, 0, ',', '.') : '—' ?></td>
                        <td class="num"><?= number_format($srv['price'], 0, ',', '.') ?></td>
                        <td class="num"><?= number_format($gross - $discount, 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="subtotal-row">
                    <td colspan="4">Sub Total</td>
                    <td><?= number_format($serviceSubtotal, 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- ===== Item ===== -->
    <?php if (!empty($itemRows)): ?>
        <div class="section-title">Item</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 44%;">Deskripsi</th>
                    <th class="ctr" style="width: 10%;">Kuantitas</th>
                    <th class="num" style="width: 14%;">Diskon</th>
                    <th class="num" style="width: 15%;">Harga</th>
                    <th class="num" style="width: 17%;">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php $itemSubtotal = 0.0; ?>
                <?php foreach ($itemRows as $itm): ?>
                    <?php
                    $gross    = $itm['price'] * $itm['quantity'];
                    $discount = (float)($itm['discount'] ?? 0);
                    $itemSubtotal += $gross - $discount;
                    ?>
                    <tr>
                        <td>
                            <?= esc($itm['name']) ?> <span class="pet-ref">(<?= esc($itm['pet_name']) ?>)</span>
                        </td>
                        <td class="ctr"><?= (int)$itm['quantity'] ?></td>
                        <td class="num"><?= $discount > 0 ? number_format($discount, 0, ',', '.') : '—' ?></td>
                        <td class="num"><?= number_format($itm['price'], 0, ',', '.') ?></td>
                        <td class="num"><?= number_format($gross - $discount, 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="subtotal-row">
                    <td colspan="4">Sub Total</td>
                    <td><?= number_format($itemSubtotal, 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- ===== Ringkasan Tagihan ===== -->
    <table class="summary-table">
        <?php if ($totalDiscount > 0): ?>
            <tr>
                <td>Diskon</td>
                <td>- Rp<?= number_format($totalDiscount, 0, ',', '.') ?></td>
            </tr>
        <?php endif; ?>
        <tr class="summary-total">
            <td>Total</td>
            <td>Rp<?= number_format($totalInvoiceAmount, 0, ',', '.') ?></td>
        </tr>
        <?php if ($totalPaid > 0): ?>
            <tr>
                <td>Bayar</td>
                <td>Rp<?= number_format($totalPaid, 0, ',', '.') ?></td>
            </tr>
            <tr class="summary-due">
                <td>Sisa Tagihan</td>
                <td>Rp<?= number_format($remainingBalance, 0, ',', '.') ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- ===== Footer ===== -->
    <div class="footer">Terima kasih! Semoga sehat selalu.</div>

</div>
</body>
</html>
