<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Invoices Directory<?= $this->endSection() ?>

<?= $this->section('header') ?>Invoices & Billing<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header with Search & Status Filter -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Billing & Invoices</h2>
            <p class="text-sm text-neutral-400 mt-1">Manage invoice lifecycle, print slips, and track payments recorded across client accounts.</p>
        </div>
        
        <!-- Search and Filter Bar -->
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <!-- Status Filter -->
            <div class="w-full sm:w-44">
                <form id="filterForm" action="/invoices" method="GET" class="flex gap-2 w-full">
                    <?php if (!empty($search)): ?>
                        <input type="hidden" name="q" value="<?= esc($search) ?>">
                    <?php endif; ?>
                    <select name="status" onchange="this.form.submit()"
                            class="w-full bg-neutral-900 border border-neutral-800 focus:border-brand-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition">
                        <option value="">All Statuses</option>
                        <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Unpaid</option>
                        <option value="2" <?= $status === '2' ? 'selected' : '' ?>>Paid</option>
                        <option value="3" <?= $status === '3' ? 'selected' : '' ?>>Partially Paid</option>
                    </select>
                </form>
            </div>

            <!-- Search bar -->
            <div class="w-full sm:w-64">
                <form action="/invoices" method="GET" class="relative w-full">
                    <?php if (!empty($status)): ?>
                        <input type="hidden" name="status" value="<?= esc($status) ?>">
                    <?php endif; ?>
                    <input type="text" name="q" placeholder="INV#, owner, patient..." value="<?= esc($search) ?>"
                           class="w-full bg-neutral-900 border border-neutral-800 focus:border-brand-500 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white focus:outline-none transition duration-150">
                    <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-neutral-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <?php if (!empty($search)): ?>
                        <a href="/invoices?<?= !empty($status) ? 'status='.esc($status) : '' ?>" class="absolute inset-y-0 right-3.5 flex items-center text-neutral-400 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <?php if (empty($invoices)): ?>
        <div class="bg-neutral-900 border border-neutral-800 rounded-3xl p-12 text-center max-w-xl mx-auto mt-6">
            <div class="h-12 w-12 bg-neutral-950 border border-neutral-800 text-neutral-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-md font-bold text-white">No invoices found</h3>
            <p class="text-xs text-neutral-500 mt-1 max-w-xs mx-auto">
                <?= (!empty($search) || !empty($status)) ? 'No invoices match your search or filter criteria. Try resetting filters.' : 'No invoices have been generated yet.' ?>
            </p>
        </div>
    <?php else: ?>
        <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-800">
                    <thead class="bg-neutral-950/60">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Invoice Number</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Patient & Owner</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Invoice Date</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-neutral-400 uppercase tracking-wider">Total Billing</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-neutral-400 uppercase tracking-wider">Paid Amount</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-neutral-400 uppercase tracking-wider">Remaining Balance</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-neutral-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-neutral-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800 bg-neutral-900/40">
                        <?php foreach ($invoices as $invoice): 
                            $balance = $invoice['total_amount'] - $invoice['total_paid'];
                            if ($balance < 0.01) $balance = 0.00;
                        ?>
                            <tr class="hover:bg-neutral-800/20 transition duration-150">
                                <!-- Invoice Number -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-white"><?= esc($invoice['invoice_number']) ?></span>
                                    <span class="text-[10px] text-neutral-500 block">Record ID: #<?= $invoice['medical_record_id'] ?></span>
                                </td>
                                <!-- Patient / Owner -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="truncate">
                                        <span class="text-xs font-bold text-white block"><?= esc($invoice['pet_name']) ?></span>
                                        <span class="text-[11px] text-neutral-400 block">Owner: <?= esc($invoice['customer_name']) ?></span>
                                    </div>
                                </td>
                                <!-- Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-300">
                                    <?= date('M j, Y', strtotime($invoice['created_at'])) ?>
                                    <span class="text-[10px] text-neutral-500 block"><?= date('H:i A', strtotime($invoice['created_at'])) ?></span>
                                </td>
                                <!-- Total Billing -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold text-neutral-300">
                                    Rp<?= number_format($invoice['total_amount'], 0, ',', '.') ?>
                                </td>
                                <!-- Paid Amount -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold text-neutral-300">
                                    Rp<?= number_format($invoice['total_paid'], 0, ',', '.') ?>
                                </td>
                                <!-- Remaining Balance -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold <?= $balance > 0 ? 'text-red-400' : 'text-neutral-400' ?>">
                                    Rp<?= number_format($balance, 0, ',', '.') ?>
                                </td>
                                <!-- Status Pill -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if ($invoice['status'] == 1): ?>
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-red-950/40 text-red-400 border border-red-500/20 rounded-full">
                                            Unpaid
                                        </span>
                                    <?php elseif ($invoice['status'] == 2): ?>
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-emerald-950/40 text-emerald-400 border border-emerald-500/20 rounded-full">
                                            Paid
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-amber-950/40 text-amber-400 border border-amber-500/20 rounded-full">
                                            Partial
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <!-- View button -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                    <a href="/invoices/show/<?= $invoice['id'] ?>" class="px-3 py-1.5 bg-neutral-950 border border-neutral-800 text-neutral-300 hover:text-white hover:border-neutral-700 rounded-lg transition inline-block">
                                        View Invoice
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
