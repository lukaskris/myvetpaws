<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($pet['name']) ?> | Patient Profile<?= $this->endSection() ?>

<?= $this->section('header') ?>Patient Profile<?= $this->endSection() ?>

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
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <a href="/customers" class="hover:text-white transition duration-150">Customers</a>
        <span>/</span>
        <a href="/customers/show/<?= $pet['customer_id'] ?>" class="hover:text-white transition duration-150"><?= esc($pet['customer_name']) ?></a>
        <span>/</span>
        <span class="text-white font-medium"><?= esc($pet['name']) ?></span>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Side: Pet Profile & Owner Summary -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Pet Card -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl flex flex-col items-center text-center shadow-lg">
                <!-- Photo -->
                <div class="relative h-28 w-28 bg-neutral-950 border-2 border-neutral-800 rounded-3xl flex items-center justify-center overflow-hidden mb-4 shadow-inner">
                    <?php if (!empty($pet['photo']) && file_exists(FCPATH . $pet['photo'])): ?>
                        <img src="/<?= esc($pet['photo']) ?>" class="h-full w-full object-cover" alt="<?= esc($pet['name']) ?>">
                    <?php else: ?>
                        <svg class="h-12 w-12 text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    <?php endif; ?>
                </div>

                <h3 class="text-lg font-bold text-white leading-tight"><?= esc($pet['name']) ?></h3>
                
                <div class="flex items-center space-x-1.5 mt-2">
                    <span class="text-xs bg-neutral-950 px-2 py-0.5 border border-neutral-850 rounded-lg text-brand-400 font-semibold tracking-wider uppercase"><?= esc($pet['species']) ?></span>
                    <?php if (!empty($pet['breed'])): ?>
                        <span class="text-xs text-neutral-400 font-medium"><?= esc($pet['breed']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="flex items-center space-x-2 mt-5 w-full">
                    <a href="/pets/edit/<?= $pet['id'] ?>" class="flex-1 text-center py-2 px-3 border border-neutral-800 bg-neutral-950 text-neutral-300 hover:text-white hover:border-neutral-700 rounded-xl text-xs font-semibold transition" id="btn-edit-pet">
                        Edit Patient Profile
                    </a>
                </div>
            </div>

            <!-- Owner Card -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg space-y-4">
                <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Owner Contact</h4>
                
                <div class="flex items-center space-x-3 bg-neutral-950/60 p-3 rounded-2xl border border-neutral-850">
                    <div class="h-9 w-9 bg-neutral-900 border border-neutral-800 text-neutral-300 font-semibold text-xs rounded-full flex items-center justify-center">
                        <?= substr(esc($pet['customer_name']), 0, 2) ?>
                    </div>
                    <div class="truncate">
                        <div class="text-sm font-bold text-white leading-tight truncate"><?= esc($pet['customer_name']) ?></div>
                        <a href="/customers/show/<?= $pet['customer_id'] ?>" class="text-[10px] text-brand-500 hover:text-brand-400 font-semibold transition-colors mt-0.5 block">View Directory Profile →</a>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-neutral-500 block">Phone</span>
                        <span class="text-white font-medium"><?= !empty($pet['customer_phone']) ? esc($pet['customer_phone']) : '—' ?></span>
                    </div>
                    <div>
                        <span class="text-neutral-500 block">Email</span>
                        <span class="text-white font-medium"><?= !empty($pet['customer_email']) ? esc($pet['customer_email']) : '—' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Details & Timeline -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Patient Specifications -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg">
                <h4 class="text-sm font-bold text-white uppercase tracking-wider border-b border-neutral-800 pb-3 mb-4">Patient Specifications</h4>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                    <div>
                        <span class="text-neutral-500 block text-xs">Age (DOB)</span>
                        <span class="text-white font-bold block mt-1"><?= getDetailedAge($pet['birth_date']) ?></span>
                        <span class="text-[10px] text-neutral-400 font-medium"><?= !empty($pet['birth_date']) ? date('M j, Y', strtotime($pet['birth_date'])) : '—' ?></span>
                    </div>
                    <div>
                        <span class="text-neutral-500 block text-xs">Gender</span>
                        <span class="text-white font-bold block mt-1 capitalize"><?= esc($pet['gender'] ?: 'Unknown') ?></span>
                    </div>
                    <div>
                        <span class="text-neutral-500 block text-xs">Color / Markings</span>
                        <span class="text-white font-bold block mt-1"><?= esc($pet['color'] ?: '—') ?></span>
                    </div>
                    <div>
                        <span class="text-neutral-500 block text-xs">Last Vaccinated</span>
                        <span class="text-white font-bold block mt-1"><?= !empty($pet['vaccinated_at']) ? date('M j, Y', strtotime($pet['vaccinated_at'])) : 'Never' ?></span>
                    </div>
                </div>

                <?php if (!empty($pet['notes'])): ?>
                    <div class="mt-6 p-4 bg-neutral-950/60 border border-neutral-850 rounded-2xl">
                        <span class="text-neutral-400 block text-xs font-semibold mb-1">Medical Remarks</span>
                        <p class="text-xs text-neutral-300 whitespace-pre-line leading-relaxed"><?= esc($pet['notes']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Medical History Timeline -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3 mb-6">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Medical History & Visits</h4>
                    <a href="/visits/create?customer_id=<?= $pet['customer_id'] ?>" class="text-xs font-semibold text-brand-500 hover:text-brand-400 transition-colors">
                        + Check-in Patient
                    </a>
                </div>

                <?php if (empty($records)): ?>
                    <!-- Empty State -->
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="p-3 bg-neutral-950 border border-neutral-800 text-neutral-600 rounded-2xl mb-3">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h5 class="text-sm font-bold text-white">No medical records found</h5>
                        <p class="text-xs text-neutral-500 max-w-sm mt-1">No medical examinations have been recorded for this pet yet. Check-in this pet to start.</p>
                    </div>
                <?php else: ?>
                    <!-- Timeline Wrapper -->
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <?php foreach ($records as $index => $rec): ?>
                                <li>
                                    <div class="relative pb-8">
                                        <!-- Vertical Line -->
                                        <?php if ($index !== count($records) - 1): ?>
                                            <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-neutral-800" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        
                                        <div class="relative flex items-start space-x-4">
                                            <!-- Timeline Circle Indicator -->
                                            <div class="relative flex-shrink-0">
                                                <div class="h-10 w-10 rounded-xl bg-brand-950/40 border border-brand-500/30 flex items-center justify-center text-brand-400 font-bold shadow-md shadow-brand-500/5">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Content Block -->
                                            <div class="min-w-0 flex-1 bg-neutral-950/40 border border-neutral-850 p-5 rounded-2xl space-y-3 hover:border-neutral-750 transition duration-150">
                                                <!-- Date, Doctor, Action -->
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <span class="text-xs font-bold text-white block">Examination Record</span>
                                                        <span class="text-[10px] text-neutral-500 font-medium mt-0.5 block">
                                                            <?= date('F j, Y \a\t H:i', strtotime($rec['created_at'])) ?> &bull; By <?= esc($rec['doctor_name'] ?: 'Veterinarian') ?>
                                                        </span>
                                                    </div>
                                                    <a href="/records/show/<?= $rec['id'] ?>" class="text-[10px] font-bold text-brand-500 hover:text-brand-400 transition uppercase tracking-wider shrink-0 bg-brand-500/10 px-2.5 py-1 rounded-lg border border-brand-500/10">
                                                        View Full Details
                                                    </a>
                                                </div>

                                                <!-- Intake Vitals / Complaints -->
                                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3 bg-neutral-900/60 rounded-xl text-[11px] border border-neutral-900">
                                                    <div>
                                                        <span class="text-neutral-550 block font-semibold">Weight</span>
                                                        <span class="text-white font-bold block mt-0.5"><?= $rec['weight'] ? esc($rec['weight']) . ' kg' : '—' ?></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-neutral-550 block font-semibold">Temp</span>
                                                        <span class="text-white font-bold block mt-0.5"><?= $rec['temperature'] ? esc($rec['temperature']) . ' °C' : '—' ?></span>
                                                    </div>
                                                    <div class="col-span-2 sm:col-span-1">
                                                        <span class="text-neutral-550 block font-semibold">Intake Complaint</span>
                                                        <span class="text-neutral-450 block truncate mt-0.5" title="<?= esc($rec['complaints']) ?>">
                                                            "<?= esc($rec['complaints'] ?: 'No symptoms specified') ?>"
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Diagnosis -->
                                                <div class="text-xs">
                                                    <span class="text-neutral-500 font-bold uppercase tracking-wider block text-[9px] mb-1">Diagnosis</span>
                                                    <p class="text-neutral-350 leading-relaxed"><?= esc($rec['diagnosis']) ?></p>
                                                </div>

                                                <!-- Treatment -->
                                                <div class="text-xs border-t border-neutral-850/50 pt-2.5">
                                                    <span class="text-neutral-500 font-bold uppercase tracking-wider block text-[9px] mb-1">Treatment Plan</span>
                                                    <p class="text-neutral-350 leading-relaxed"><?= esc($rec['treatment_plan']) ?></p>
                                                </div>

                                                <!-- Services Rendered List -->
                                                <?php if (!empty($servicesRendered[$rec['id']])): ?>
                                                    <div class="border-t border-neutral-850/50 pt-2.5">
                                                        <span class="text-neutral-500 font-bold uppercase tracking-wider block text-[9px] mb-1.5">Services Provided</span>
                                                        <div class="flex flex-wrap gap-1.5">
                                                            <?php foreach ($servicesRendered[$rec['id']] as $srv): ?>
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-neutral-900 border border-neutral-800 text-[10px] font-medium text-neutral-300">
                                                                    <?= esc($srv['name']) ?> 
                                                                    <?php if ($srv['quantity'] > 1): ?>
                                                                        <span class="text-brand-500 font-semibold ml-1">x<?= esc($srv['quantity']) ?></span>
                                                                    <?php endif; ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Next Visit Notification -->
                                                <?php if (!empty($rec['next_visit_at'])): ?>
                                                    <div class="mt-2 text-[10px] text-amber-500 font-semibold flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        <span>Scheduled follow-up: <?= date('F j, Y', strtotime($rec['next_visit_at'])) ?></span>
                                                    </div>
                                                <?php endif; ?>
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
</div>
<?= $this->endSection() ?>
