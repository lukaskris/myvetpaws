<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Medical Records Directory<?= $this->endSection() ?>

<?= $this->section('header') ?>Medical Records<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header with Search -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Clinical Records Directory</h2>
            <p class="text-sm text-neutral-400 mt-1">Review clinical findings, diagnoses, and treatments recorded by clinic veterinarians.</p>
        </div>
        
        <!-- Search bar -->
        <div class="w-full md:w-80">
            <form action="/records" method="GET" class="relative">
                <input type="text" name="q" placeholder="Search by diagnosis, pet, owner..." value="<?= esc($search) ?>"
                       class="w-full bg-neutral-900 border border-neutral-800 focus:border-brand-500 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white focus:outline-none transition duration-150">
                <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-neutral-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <?php if (!empty($search)): ?>
                    <a href="/records" class="absolute inset-y-0 right-3.5 flex items-center text-neutral-400 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Records Table -->
    <?php if (empty($records)): ?>
        <div class="bg-neutral-900 border border-neutral-800 rounded-3xl p-12 text-center max-w-xl mx-auto mt-6">
            <div class="h-12 w-12 bg-neutral-950 border border-neutral-800 text-neutral-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-md font-bold text-white">No medical records found</h3>
            <p class="text-xs text-neutral-500 mt-1 max-w-xs mx-auto">
                <?= !empty($search) ? 'No records match your search criteria. Try a different query.' : 'No examinations have been recorded for checked-in patients yet.' ?>
            </p>
        </div>
    <?php else: ?>
        <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-800">
                    <thead class="bg-neutral-950/60">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Patient & Owner</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Date & Doctor</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Vitals</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Diagnosis</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Treatment Summary</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-neutral-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800 bg-neutral-900/40">
                        <?php foreach ($records as $record): ?>
                            <tr class="hover:bg-neutral-800/20 transition duration-150">
                                <!-- Patient / Owner -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="truncate">
                                        <a href="/pets/show/<?= $record['pet_id'] ?>" class="text-sm font-bold text-white hover:text-brand-400 transition-colors block truncate"><?= esc($record['pet_name']) ?></a>
                                        <span class="text-xs text-neutral-400 block truncate"><?= esc($record['customer_name']) ?></span>
                                    </div>
                                </td>
                                <!-- Date & Doctor -->
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-300">
                                    <?= date('M j, Y', strtotime($record['created_at'])) ?>
                                    <span class="text-[10px] text-neutral-500 block">By: <?= esc($record['doctor_name'] ?: 'Veterinarian') ?></span>
                                </td>
                                <!-- Vitals -->
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-300 space-y-0.5">
                                    <div><span class="text-neutral-500">Wt:</span> <?= $record['weight'] ? esc($record['weight']) . ' kg' : '—' ?></div>
                                    <div><span class="text-neutral-500">T:</span> <?= $record['temperature'] ? esc($record['temperature']) . ' °C' : '—' ?></div>
                                </td>
                                <!-- Diagnosis -->
                                <td class="px-6 py-4 text-xs text-neutral-350 max-w-xs truncate">
                                    <?= esc($record['diagnosis']) ?>
                                </td>
                                <!-- Treatment Summary -->
                                <td class="px-6 py-4 text-xs text-neutral-450 max-w-xs truncate">
                                    <?= esc($record['treatment_plan']) ?>
                                </td>
                                <!-- View button -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                    <a href="/records/show/<?= $record['id'] ?>" class="px-3 py-1.5 bg-neutral-950 border border-neutral-800 text-neutral-300 hover:text-white hover:border-neutral-700 rounded-lg transition inline-block">
                                        View Details
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
