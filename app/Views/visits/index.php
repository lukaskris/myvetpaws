<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Visits Queue<?= $this->endSection() ?>

<?= $this->section('header') ?>Visits Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$appointmentLabels = ['vet_checkup' => 'Vet Checkup', 'home_visit' => 'Home Visit', 'grooming' => 'Grooming'];
?>
<div class="space-y-6" x-data="{ tab: 'queue' }">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Clinic Visits</h2>
            <p class="text-sm text-neutral-400 mt-1">Manage today's checked-in patient queue and review historical visits.</p>
        </div>
        <div>
            <a href="/visits/create" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-semibold shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 transition duration-150 inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Check-in Patient</span>
            </a>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-neutral-800 flex space-x-6">
        <button @click="tab = 'queue'" 
                :class="tab === 'queue' ? 'border-brand-500 text-white' : 'border-transparent text-neutral-400 hover:text-neutral-200'"
                class="py-3 px-1 border-b-2 font-semibold text-sm transition duration-150 relative">
            Active Queue
            <?php if (!empty($activeVisits)): ?>
                <span class="ml-2 px-2 py-0.5 text-xs bg-brand-500/20 text-brand-400 border border-brand-500/30 rounded-full font-bold">
                    <?= count($activeVisits) ?>
                </span>
            <?php endif; ?>
        </button>
        <button @click="tab = 'history'"
                :class="tab === 'history' ? 'border-brand-500 text-white' : 'border-transparent text-neutral-400 hover:text-neutral-200'"
                class="py-3 px-1 border-b-2 font-semibold text-sm transition duration-150">
            Historical Visits
        </button>
        <a href="/calendar"
           class="py-3 px-1 border-b-2 border-transparent text-neutral-400 hover:text-neutral-200 font-semibold text-sm transition duration-150 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Calendar View</span>
        </a>
    </div>

    <!-- Active Queue Tab -->
    <div x-show="tab === 'queue'" class="space-y-4">
        <?php if (empty($activeVisits)): ?>
            <div class="bg-neutral-900 border border-neutral-800 rounded-3xl p-12 text-center max-w-xl mx-auto mt-6">
                <div class="h-12 w-12 bg-neutral-950 border border-neutral-800 text-neutral-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-md font-bold text-white">Active queue is empty</h3>
                <p class="text-xs text-neutral-500 mt-1 max-w-xs mx-auto">No patients are currently checked in or waiting for examination.</p>
                <a href="/visits/create" class="mt-4 inline-flex items-center text-xs font-semibold text-brand-500 hover:text-brand-400">
                    Check-in a patient now &rarr;
                </a>
            </div>
        <?php else: ?>
            <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-800">
                        <thead class="bg-neutral-950/60">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Patient & Owner</th>
                                <th scope="col" class="px-6 py-4 class text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Check-in Time</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Vitals</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Chief Complaint</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-neutral-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800 bg-neutral-900/40">
                            <?php foreach ($activeVisits as $visit): ?>
                                <tr class="hover:bg-neutral-800/20 transition duration-150">
                                    <!-- Patient / Owner info -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-10 w-10 bg-neutral-950 rounded-xl flex items-center justify-center border border-neutral-850 overflow-hidden shrink-0">
                                                <?php if (!empty($visit['pet_photo']) && file_exists(FCPATH . $visit['pet_photo'])): ?>
                                                    <img src="/<?= esc($visit['pet_photo']) ?>" class="h-full w-full object-cover">
                                                <?php else: ?>
                                                    <svg class="h-5 w-5 text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="truncate">
                                                <a href="/pets/show/<?= $visit['pet_id'] ?>" class="text-sm font-bold text-white hover:text-brand-400 transition-colors block truncate"><?= esc($visit['pet_name']) ?></a>
                                                <span class="text-xs text-neutral-400 block truncate">
                                                    <?= esc($visit['customer_name']) ?>
                                                    <span class="ml-1 px-1.5 py-0.5 text-[9px] font-bold uppercase rounded bg-neutral-950 text-brand-400 border border-neutral-800"><?= esc($appointmentLabels[$visit['appointment_type']] ?? ucfirst(str_replace('_', ' ', $visit['appointment_type'] ?? ''))) ?></span>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Check-in Time -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-300">
                                        <?= date('H:i', strtotime($visit['checkin_time'])) ?>
                                        <span class="text-xs text-neutral-500 block"><?= date('M j, Y', strtotime($visit['checkin_time'])) ?></span>
                                    </td>
                                    <!-- Vitals -->
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-300 space-y-1">
                                        <div><span class="text-neutral-500">Weight:</span> <?= $visit['weight'] ? esc($visit['weight']) . ' kg' : '—' ?></div>
                                        <div><span class="text-neutral-500">Temp:</span> <?= $visit['temperature'] ? esc($visit['temperature']) . ' °C' : '—' ?></div>
                                    </td>
                                    <!-- Complaints -->
                                    <td class="px-6 py-4 text-xs text-neutral-300 max-w-xs truncate">
                                        <?= esc($visit['complaints'] ?: 'No complaint specified') ?>
                                    </td>
                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($visit['status'] == 1): ?>
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-brand-500/10 text-brand-400 border border-brand-500/20 rounded-full">Queued</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full">In Examination</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold space-x-2">
                                        <?php if (session()->get('user_role') === 'owner' || session()->get('user_role') === 'doctor'): ?>
                                            <a href="/visits/examine/<?= $visit['id'] ?>" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white rounded-lg transition">Examine</a>
                                        <?php endif; ?>
                                        <a href="/visits/cancel/<?= $visit['id'] ?>" onclick="return confirm('Are you sure you want to cancel this visit check-in?');" class="px-3 py-1.5 bg-neutral-950 border border-neutral-800 text-red-400 hover:text-red-300 hover:border-red-500/30 rounded-lg transition">Cancel</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- History Tab -->
    <div x-show="tab === 'history'" class="space-y-4" style="display: none;">
        <?php if (empty($historyVisits)): ?>
            <div class="bg-neutral-900 border border-neutral-800 rounded-3xl p-12 text-center max-w-xl mx-auto mt-6">
                <div class="h-12 w-12 bg-neutral-950 border border-neutral-800 text-neutral-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-md font-bold text-white">No historical records</h3>
                <p class="text-xs text-neutral-500 mt-1 max-w-xs mx-auto">No completed or cancelled visits have been archived yet.</p>
            </div>
        <?php else: ?>
            <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-800">
                        <thead class="bg-neutral-950/60">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Patient & Owner</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Date & Time</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Vitals</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Complaints</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800 bg-neutral-900/40">
                            <?php foreach ($historyVisits as $visit): ?>
                                <tr class="hover:bg-neutral-800/20 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-10 w-10 bg-neutral-950 rounded-xl flex items-center justify-center border border-neutral-850 overflow-hidden shrink-0">
                                                <?php if (!empty($visit['pet_photo']) && file_exists(FCPATH . $visit['pet_photo'])): ?>
                                                    <img src="/<?= esc($visit['pet_photo']) ?>" class="h-full w-full object-cover">
                                                <?php else: ?>
                                                    <svg class="h-5 w-5 text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="truncate">
                                                <a href="/pets/show/<?= $visit['pet_id'] ?>" class="text-sm font-bold text-white hover:text-brand-400 transition-colors block truncate"><?= esc($visit['pet_name']) ?></a>
                                                <span class="text-xs text-neutral-400 block truncate">
                                                    <?= esc($visit['customer_name']) ?>
                                                    <span class="ml-1 px-1.5 py-0.5 text-[9px] font-bold uppercase rounded bg-neutral-950 text-brand-400 border border-neutral-800"><?= esc($appointmentLabels[$visit['appointment_type']] ?? ucfirst(str_replace('_', ' ', $visit['appointment_type'] ?? ''))) ?></span>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-300">
                                        <?= date('M j, Y', strtotime($visit['checkin_time'])) ?>
                                        <span class="text-xs text-neutral-500 block"><?= date('H:i', strtotime($visit['checkin_time'])) ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-300 space-y-1">
                                        <div><span class="text-neutral-500">Weight:</span> <?= $visit['weight'] ? esc($visit['weight']) . ' kg' : '—' ?></div>
                                        <div><span class="text-neutral-500">Temp:</span> <?= $visit['temperature'] ? esc($visit['temperature']) . ' °C' : '—' ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-neutral-300 max-w-xs truncate">
                                        <?= esc($visit['complaints'] ?: '—') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($visit['status'] == 3): ?>
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">Completed</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-neutral-950 text-neutral-500 border border-neutral-800 rounded-full">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
<?= $this->endSection() ?>
