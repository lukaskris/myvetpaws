<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Invoice: <?= esc($invoice['invoice_number']) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>Invoice Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6" x-data="{ showPaymentModal: false }">
    <!-- Action buttons -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center space-x-2.5 text-xs text-slate-400">
            <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-60"></i>
            <a href="/invoices" class="hover:text-white transition duration-150">Invoices</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-60"></i>
            <span class="text-white font-semibold"><?= esc($invoice['invoice_number']) ?></span>
        </div>
        <div class="flex items-center gap-2">
            <a href="/invoices" class="px-3.5 py-2 bg-obsidian-900 hover:bg-obsidian-800 text-slate-300 hover:text-neutral-50 dark:hover:text-white rounded-xl text-xs font-bold border border-obsidian-800 transition-premium inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to List</span>
            </a>
            
            <button onclick="window.print()" class="px-3.5 py-2 bg-obsidian-900 hover:bg-obsidian-800 text-slate-300 hover:text-neutral-50 dark:hover:text-white rounded-xl text-xs font-bold border border-obsidian-800 transition-premium inline-flex items-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Print Invoice</span>
            </button>

            <!-- Download PDF Button for sharing -->
            <a href="/invoices/download/<?= $invoice['id'] ?>" class="px-3.5 py-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-600/10 hover:shadow-brand-500/20 hover:scale-[1.02] active:scale-[0.98] transition-premium inline-flex items-center gap-1.5">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Download PDF</span>
            </a>

            <?php if ($remainingBalance > 0): ?>
                <button @click="showPaymentModal = true" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/10 hover:shadow-emerald-500/20 hover:scale-[1.02] active:scale-[0.98] transition-premium inline-flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Record Payment</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Columns: Invoice Slip -->
        <div class="lg:col-span-2 space-y-6" id="printable-slip">
            <div class="glass-panel p-8 rounded-3xl shadow-xl relative overflow-hidden">
                <!-- Decorative brand color overlay in corner -->
                <div class="absolute -right-16 -top-16 text-slate-800/10 select-none pointer-events-none transform rotate-12">
                    <i data-lucide="receipt" class="w-64 h-64 opacity-5"></i>
                </div>

                <!-- Slip Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-obsidian-800 pb-6 mb-6">
                    <div>
                        <!-- Clinic Name -->
                        <h3 class="text-xl font-extrabold text-white leading-tight flex items-center gap-2">
                            <span class="h-7.5 w-7.5 bg-gradient-to-br from-brand-600 to-brand-700 rounded-lg flex items-center justify-center font-extrabold text-white text-xs select-none shadow">
                                <?= substr(session()->get('clinic_name') ?? 'M', 0, 1) ?>
                            </span>
                            <span><?= esc(session()->get('clinic_name') ?? 'MyVetPaws') ?></span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-2 max-w-xs leading-normal">
                            <span class="flex items-center gap-1"><i data-lucide="mail" class="w-3.5 h-3.5 text-slate-500"></i> <?= esc(session()->get('clinic_email') ?: 'contact@' . (session()->get('clinic_slug') ?: 'myvetpaws') . '.com') ?></span>
                            <span class="flex items-center gap-1 mt-1"><i data-lucide="phone" class="w-3.5 h-3.5 text-slate-500"></i> <?= esc(session()->get('clinic_phone') ?: 'Clinic Hotline') ?></span>
                        </p>
                    </div>
                    <div class="sm:text-right">
                        <h2 class="text-base font-extrabold tracking-tight text-white uppercase">Tax Invoice</h2>
                        <div class="text-xs text-slate-400 mt-1.5">Invoice No: <span class="text-white font-bold"><?= esc($invoice['invoice_number']) ?></span></div>
                        <div class="text-[11px] text-slate-500 mt-1">Date: <?= date('F j, Y, H:i A', strtotime($invoice['created_at'])) ?></div>
                        
                        <div class="mt-3">
                            <?php if ($invoice['status'] == 1): ?>
                                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider bg-neon-pink/15 text-neon-pink border border-neon-pink/15 rounded-full inline-block">
                                    Unpaid
                                </span>
                            <?php elseif ($invoice['status'] == 2): ?>
                                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider bg-neon-emerald/15 text-neon-emerald border border-neon-emerald/15 rounded-full inline-block">
                                    Fully Paid
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider bg-neon-amber/15 text-neon-amber border border-neon-amber/15 rounded-full inline-block">
                                    Partially Paid
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Client and Patient Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-obsidian-800 pb-6 mb-6">
                    <div>
                        <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Billed To (Owner)</h4>
                        <div class="text-sm font-bold text-white"><?= esc($invoice['customer_name']) ?></div>
                        <div class="text-xs text-slate-400 mt-1.5 leading-relaxed space-y-0.5">
                            <div><?= esc($invoice['customer_email'] ?: 'No email registered') ?></div>
                            <div><?= esc($invoice['customer_phone'] ?: 'No phone registered') ?></div>
                            <div class="italic text-slate-500 mt-1"><i data-lucide="map-pin" class="w-3 h-3 inline mr-0.5"></i> <?= esc($invoice['customer_address'] ?: 'No billing address provided') ?></div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Patient Details</h4>
                        <?php foreach ($groupedPets as $idx => $gp): ?>
                            <div class="<?= $idx > 0 ? 'mt-3 border-t border-obsidian-850 pt-2' : '' ?>">
                                <div class="text-sm font-bold text-white"><?= esc($gp['pet_name']) ?></div>
                                <div class="text-xs text-slate-400 mt-1 leading-relaxed space-y-0.5">
                                    <div>Species: <span class="text-white font-semibold"><?= esc($gp['pet_species']) ?></span></div>
                                    <div>Breed: <span class="text-white font-semibold"><?= esc($gp['pet_breed'] ?: '—') ?></span></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Medical Diagnoses Summary -->
                <div class="bg-obsidian-950/60 border border-obsidian-800 p-4.5 rounded-2xl mb-6 space-y-3">
                    <h4 class="text-[10px] font-bold text-brand-500 uppercase tracking-wider">Veterinary Case Summary</h4>
                    <?php foreach ($groupedPets as $idx => $gp): ?>
                        <div class="<?= $idx > 0 ? 'border-t border-obsidian-850 pt-2.5' : '' ?>">
                            <div class="text-xs font-bold text-white mb-1"><?= esc($gp['pet_name']) ?>:</div>
                            <div class="text-xs text-slate-300">
                                <span class="text-slate-500 font-semibold">Attending Vet:</span> <span class="text-white font-semibold"><?= esc($gp['doctor_name'] ?: 'Veterinarian') ?></span>
                            </div>
                            <div class="text-xs text-slate-300 mt-1">
                                <span class="text-slate-500 font-semibold">Diagnosis:</span> <span class="text-slate-200"><?= esc($gp['diagnosis']) ?></span>
                            </div>
                            <div class="text-xs text-slate-350 mt-1">
                                <span class="text-slate-500 font-semibold">Treatment Plan:</span> <span class="text-slate-300"><?= esc($gp['treatment_plan'] ?: 'General Consultation') ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Services Rendered Billing Table -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Line Items</h4>
                    <div class="overflow-hidden border border-obsidian-800 rounded-2xl">
                        <table class="min-w-full divide-y divide-obsidian-800">
                            <thead class="bg-obsidian-950/80">
                                <tr>
                                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Service Description</th>
                                    <th scope="col" class="px-4 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Unit Price</th>
                                    <th scope="col" class="px-4 py-3.5 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                                    <th scope="col" class="px-4 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-obsidian-800 bg-obsidian-950/20 text-xs">
                                <?php if (empty($groupedPets)): ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                            No clinical services were billed.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($groupedPets as $petIdx => $gp): ?>
                                        <!-- Pet Group Sub-header -->
                                        <tr class="bg-obsidian-900/40">
                                            <td colspan="4" class="px-4 py-2 text-xs font-bold text-slate-300">
                                                <?= ($petIdx + 1) ?>. <?= esc($gp['pet_name']) ?> (<?= esc($gp['pet_species']) ?>):
                                            </td>
                                        </tr>
                                        <!-- Services -->
                                        <tr class="bg-obsidian-950/45">
                                            <td colspan="4" class="px-6 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                Services & Procedures Rendered:
                                            </td>
                                        </tr>
                                        <?php if (empty($gp['services'])): ?>
                                            <tr>
                                                <td colspan="4" class="px-8 py-2 text-xs text-slate-500 italic">
                                                    No services billed.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($gp['services'] as $srv): 
                                                $amount = $srv['price'] * $srv['quantity'];
                                            ?>
                                                <tr class="hover:bg-obsidian-900/10">
                                                    <td class="px-8 py-2.5">
                                                        <span class="text-white font-medium block text-xs"><?= esc($srv['name']) ?></span>
                                                        <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider block mt-0.5"><?= esc($srv['code']) ?></span>
                                                    </td>
                                                    <td class="px-4 py-2.5 text-right text-slate-350">
                                                        Rp<?= number_format($srv['price'], 0, ',', '.') ?>
                                                    </td>
                                                    <td class="px-4 py-2.5 text-center text-slate-350 font-semibold">
                                                        <?= esc($srv['quantity']) ?>
                                                    </td>
                                                    <td class="px-4 py-2.5 text-right text-white font-bold">
                                                        Rp<?= number_format($amount, 0, ',', '.') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <!-- Medicines & Supplies -->
                                        <tr class="bg-obsidian-950/45">
                                            <td colspan="4" class="px-6 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                Medicines & Disposable Supplies Used:
                                            </td>
                                        </tr>
                                        <?php if (empty($gp['items'])): ?>
                                            <tr>
                                                <td colspan="4" class="px-8 py-2 text-xs text-slate-500 italic">
                                                    No medicines or supplies used.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($gp['items'] as $itm): 
                                                $amount = $itm['price'] * $itm['quantity'];
                                            ?>
                                                <tr class="hover:bg-obsidian-900/10">
                                                    <td class="px-8 py-2.5">
                                                        <span class="text-white font-medium block text-xs"><?= esc($itm['name']) ?></span>
                                                        <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider block mt-0.5"><?= esc($itm['code']) ?></span>
                                                    </td>
                                                    <td class="px-4 py-2.5 text-right text-slate-350">
                                                        Rp<?= number_format($itm['price'], 0, ',', '.') ?>
                                                    </td>
                                                    <td class="px-4 py-2.5 text-center text-slate-350 font-semibold">
                                                        <?= esc($itm['quantity']) ?>
                                                    </td>
                                                    <td class="px-4 py-2.5 text-right text-white font-bold">
                                                        Rp<?= number_format($amount, 0, ',', '.') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="bg-obsidian-950/80 border-t border-obsidian-800 text-xs font-bold">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-slate-450 uppercase tracking-wider">Total Invoice Amount:</td>
                                    <td class="px-4 py-3 text-right text-white">
                                        Rp<?= number_format($totalInvoiceAmount, 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-slate-450 uppercase tracking-wider">Settled Payments:</td>
                                    <td class="px-4 py-3 text-right text-neon-emerald">
                                        Rp<?= number_format($totalPaid, 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <tr class="bg-obsidian-900/60 border-t border-obsidian-800">
                                    <td colspan="3" class="px-4 py-4 text-right font-extrabold text-white uppercase tracking-wider">Outstanding Balance Due:</td>
                                    <td class="px-4 py-4 text-right font-black text-brand-400 text-sm">
                                        Rp<?= number_format($remainingBalance, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 1 Column: Payments History -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-panel p-6 rounded-3xl shadow-xl space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-obsidian-800/80 pb-3 flex items-center gap-1.5">
                    <i data-lucide="receipt" class="w-4 h-4 text-brand-500"></i>
                    <span>Payment Log</span>
                </h3>
                
                <?php if (empty($payments)): ?>
                    <div class="p-6 bg-obsidian-950/60 border border-obsidian-850 rounded-2xl text-center shadow-inner">
                        <p class="text-xs text-slate-500">No payment records exist for this invoice yet.</p>
                    </div>
                <?php else: ?>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <?php foreach ($payments as $idx => $pay): ?>
                                <li>
                                    <div class="relative pb-8">
                                        <!-- Connecting vertical line -->
                                        <?php if ($idx < count($payments) - 1): ?>
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-obsidian-800" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-neon-emerald/10 border border-neon-emerald/20 text-neon-emerald flex items-center justify-center">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0 pt-1.5">
                                                <p class="text-xs font-bold text-white">
                                                    Rp<?= number_format($pay['amount'], 0, ',', '.') ?>
                                                </p>
                                                <div class="text-[10px] text-slate-450 mt-0.5">
                                                    Via <?= esc($pay['payment_method']) ?> • <?= date('M j, Y, H:i', strtotime($pay['payment_date'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alpine.js Record Payment Modal -->
    <div x-show="showPaymentModal" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-obsidian-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak>
        <div class="relative bg-obsidian-900 border border-obsidian-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-6" @click.away="showPaymentModal = false">
            <!-- Modal Header -->
            <div class="flex justify-between items-center">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-5 h-5 text-brand-500"></i>
                    <span>Record Payment Transaction</span>
                </h3>
                <button @click="showPaymentModal = false" class="text-slate-500 hover:text-neutral-50 dark:hover:text-white transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="/invoices/pay/<?= $invoice['id'] ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div class="bg-obsidian-950/80 p-4 border border-obsidian-800 rounded-2xl space-y-1 shadow-inner">
                    <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Remaining Outstanding Balance</div>
                    <div class="text-lg font-extrabold text-brand-400">Rp<?= number_format($remainingBalance, 0, ',', '.') ?></div>
                </div>

                <!-- Amount Field -->
                <div>
                    <label for="amount" class="text-xs font-bold text-slate-400 block mb-1.5">Payment Amount (Rp)</label>
                    <input type="number" name="amount" id="amount" value="<?= (float)$remainingBalance ?>" min="1" max="<?= (float)$remainingBalance ?>" step="0.01" required
                           class="w-full bg-obsidian-950 border border-obsidian-800 focus:border-brand-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition">
                </div>

                <!-- Payment Method Field -->
                <div>
                    <label for="payment_method" class="text-xs font-bold text-slate-400 block mb-1.5">Payment Method</label>
                    <select name="payment_method" id="payment_method" required
                            class="w-full bg-obsidian-950 border border-obsidian-800 focus:border-brand-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none transition">
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Card">Card</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showPaymentModal = false"
                            class="px-4 py-2 bg-obsidian-800 hover:bg-obsidian-750 text-slate-300 hover:text-white rounded-xl text-xs font-bold border border-obsidian-700 transition-premium">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-500/10 hover:shadow-emerald-500/20 transition-premium">
                        Submit Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-slip, #printable-slip * {
            visibility: visible;
        }
        #printable-slip {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none !important;
            background: white !important;
            color: black !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
        #printable-slip h2, #printable-slip h3, #printable-slip h4, #printable-slip span, #printable-slip td, #printable-slip th, #printable-slip p {
            color: black !important;
        }
        #printable-slip .border, #printable-slip .border-b, #printable-slip .border-t {
            border-color: #ddd !important;
        }
        #printable-slip .bg-neutral-950\/40, #printable-slip .bg-neutral-950\/20, #printable-slip .bg-neutral-950\/80 {
            background-color: #f5f5f5 !important;
        }
    }
</style>
<?= $this->endSection() ?>
