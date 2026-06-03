<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Record Detail: <?= esc($record['pet_name']) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>Medical Record Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Inline helper to calculate pet age
function getDetailedAge($birthDate) {
    if (empty($birthDate)) return 'Unknown age';
    try {
        $dob = new \DateTime($birthDate);
        $diff = $dob->diff(new \DateTime());
        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ($diff->m > 0 ? ' ' . $diff->m . ' month' . ($diff->m > 1 ? 's' : '') : '');
        }
        if ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
        }
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
    } catch (\Exception $e) {
        return 'Unknown age';
    }
}
?>
<div class="space-y-6">
    <!-- Breadcrumbs & Back -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xs text-neutral-400">
            <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
            <span>/</span>
            <a href="/records" class="hover:text-white transition duration-150">Medical Records</a>
            <span>/</span>
            <span class="text-white font-medium">Record #<?= $record['id'] ?></span>
        </div>
        <a href="/records" class="px-3.5 py-2 bg-neutral-900 hover:bg-neutral-800 text-neutral-300 hover:text-white rounded-xl text-xs font-semibold border border-neutral-800 transition duration-150 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Directory</span>
        </a>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Side: Patient & Clinic Metadata Card -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Patient specifications -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg space-y-4">
                <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Patient & Owner</h4>
                
                <div class="flex items-center space-x-3 bg-neutral-950/60 p-4 rounded-2xl border border-neutral-850">
                    <div>
                        <a href="/pets/show/<?= $record['pet_id'] ?>" class="text-base font-bold text-white hover:text-brand-400 leading-tight transition-colors block"><?= esc($record['pet_name']) ?></a>
                        <div class="text-xs text-neutral-400 mt-1">Owner: <span class="text-brand-400 font-semibold"><?= esc($record['customer_name']) ?></span></div>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-neutral-850">
                        <span class="text-neutral-500">Species</span>
                        <span class="text-white font-semibold"><?= esc($record['pet_species']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-neutral-850">
                        <span class="text-neutral-500">Breed</span>
                        <span class="text-white font-semibold"><?= esc($record['pet_breed'] ?: '—') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-neutral-850">
                        <span class="text-neutral-500">Gender</span>
                        <span class="text-white font-semibold"><?= esc($record['pet_gender'] ?: '—') ?></span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-neutral-500">Age at Exam</span>
                        <span class="text-white font-semibold"><?= getDetailedAge($record['pet_birth_date']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Examination Metadata -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg space-y-4">
                <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Examination Summary</h4>
                
                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-neutral-500 block">Date of Exam</span>
                        <span class="text-white font-bold block mt-0.5"><?= date('F j, Y', strtotime($record['created_at'])) ?></span>
                        <span class="text-[10px] text-neutral-400 font-medium"><?= date('H:i A', strtotime($record['created_at'])) ?></span>
                    </div>
                    <div class="border-t border-neutral-850 pt-2.5">
                        <span class="text-neutral-500 block">Examining Veterinarian</span>
                        <span class="text-white font-bold mt-0.5 block"><?= esc($record['doctor_name'] ?: 'Veterinarian') ?></span>
                    </div>
                    <?php if (!empty($record['next_visit_at'])): ?>
                        <div class="border-t border-neutral-850 pt-2.5">
                            <span class="text-neutral-500 block">Scheduled Next Visit</span>
                            <span class="text-brand-400 font-bold mt-0.5 block"><?= date('F j, Y', strtotime($record['next_visit_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Side: Clinical Case Record -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Vitals and complaints -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg space-y-4">
                <h4 class="text-sm font-bold text-white uppercase tracking-wider border-b border-neutral-800 pb-3">Intake Vitals & Complaints</h4>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="bg-neutral-950 border border-neutral-850 p-4 rounded-2xl text-center">
                        <span class="text-neutral-500 block text-[10px] uppercase font-bold tracking-wider">Weight</span>
                        <span class="text-lg font-bold text-white mt-1 block">
                            <?= $record['weight'] ? esc($record['weight']) . ' <span class="text-xs font-medium text-neutral-400">kg</span>' : '—' ?>
                        </span>
                    </div>
                    <div class="bg-neutral-950 border border-neutral-850 p-4 rounded-2xl text-center">
                        <span class="text-neutral-500 block text-[10px] uppercase font-bold tracking-wider">Temperature</span>
                        <span class="text-lg font-bold text-white mt-1 block">
                            <?= $record['temperature'] ? esc($record['temperature']) . ' <span class="text-xs font-medium text-neutral-400">°C</span>' : '—' ?>
                        </span>
                    </div>
                    <div class="bg-neutral-950 border border-neutral-850 p-4 rounded-2xl text-center col-span-2 sm:col-span-1">
                        <span class="text-neutral-500 block text-[10px] uppercase font-bold tracking-wider">Status</span>
                        <span class="text-emerald-400 font-bold block mt-1 uppercase tracking-wider text-xs bg-emerald-950/40 px-2 py-0.5 border border-emerald-500/20 rounded-full w-fit mx-auto">
                            Exam Closed
                        </span>
                    </div>
                </div>

                <div class="p-4 bg-neutral-950 border border-neutral-850 rounded-2xl">
                    <span class="text-neutral-400 block text-xs font-semibold mb-1">Owner Intake Complaint</span>
                    <p class="text-xs text-neutral-300 leading-relaxed italic">
                        "<?= esc($record['complaints'] ?: 'No symptoms specified upon check-in') ?>"
                    </p>
                </div>
            </div>

            <!-- Case Write-up -->
            <div class="bg-neutral-900 border border-neutral-800 p-8 rounded-3xl shadow-lg space-y-6">
                <h4 class="text-sm font-bold text-white uppercase tracking-wider border-b border-neutral-800 pb-3">Clinical Finding & Diagnosis</h4>
                
                <div>
                    <span class="text-xs text-neutral-450 uppercase font-bold tracking-wider block mb-2">Diagnosis</span>
                    <p class="text-sm text-neutral-200 bg-neutral-950/60 border border-neutral-850 rounded-2xl p-4 whitespace-pre-line leading-relaxed">
                        <?= esc($record['diagnosis']) ?>
                    </p>
                </div>

                <div>
                    <span class="text-xs text-neutral-450 uppercase font-bold tracking-wider block mb-2">Treatment Plan / Prescription</span>
                    <p class="text-sm text-neutral-200 bg-neutral-950/60 border border-neutral-850 rounded-2xl p-4 whitespace-pre-line leading-relaxed">
                        <?= esc($record['treatment_plan']) ?>
                    </p>
                </div>
            </div>

            <!-- Services Summary -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg">
                <h4 class="text-sm font-bold text-white uppercase tracking-wider border-b border-neutral-800 pb-3 mb-4">Services Rendered & Billing Summary</h4>

                <?php if (empty($services)): ?>
                    <div class="p-6 bg-neutral-950 border border-neutral-850 rounded-2xl text-center">
                        <p class="text-xs text-neutral-500">No clinical billing services were recorded for this examination.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-hidden border border-neutral-850 rounded-2xl">
                        <table class="min-w-full divide-y divide-neutral-800">
                            <thead class="bg-neutral-950/80">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase">Service Description</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase">Unit Price</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-neutral-400 uppercase">Qty</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-800 bg-neutral-950/20 text-xs">
                                <?php 
                                $totalBilling = 0;
                                foreach ($services as $srv): 
                                    $amount = $srv['price'] * $srv['quantity'];
                                    $totalBilling += $amount;
                                ?>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="text-white font-medium block"><?= esc($srv['name']) ?></span>
                                            <span class="text-[10px] text-neutral-500 font-semibold uppercase"><?= esc($srv['code']) ?></span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-neutral-300">
                                            Rp<?= number_format($srv['price'], 0, ',', '.') ?>
                                        </td>
                                        <td class="px-4 py-3 text-center text-neutral-300">
                                            <?= esc($srv['quantity']) ?>
                                        </td>
                                        <td class="px-4 py-3 text-right text-white font-semibold">
                                            Rp<?= number_format($amount, 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-neutral-950/80 border-t border-neutral-800 text-xs">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-bold text-neutral-400 uppercase">Subtotal Services:</td>
                                    <td class="px-4 py-3 text-right font-bold text-brand-400 text-sm">
                                        Rp<?= number_format($totalBilling, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
