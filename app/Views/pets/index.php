<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Patient Directory<?= $this->endSection() ?>

<?= $this->section('header') ?>Pets & Patients<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Inline helper to calculate pet age
function getPatientListAge($birthDate) {
    if (empty($birthDate)) return '—';
    try {
        $dob = new \DateTime($birthDate);
        $diff = $dob->diff(new \DateTime());
        if ($diff->y > 0) {
            return $diff->y . ' yr' . ($diff->y > 1 ? 's' : '') . ($diff->m > 0 ? ' ' . $diff->m . ' mo' . ($diff->m > 1 ? 's' : '') : '');
        }
        if ($diff->m > 0) {
            return $diff->m . ' mo' . ($diff->m > 1 ? 's' : '');
        }
        return $diff->d . ' d';
    } catch (\Exception $e) {
        return '—';
    }
}
?>
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <span class="text-white font-medium">Pets & Patients</span>
    </div>

    <!-- Header block -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-neutral-900 gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Patient Directory</h2>
            <p class="text-xs text-neutral-400 mt-1">Look up pet medical profiles, breeds, age calculations, and owner associations.</p>
        </div>
    </div>

    <!-- Search box -->
    <div class="flex items-center justify-between gap-4">
        <form action="/pets" method="GET" class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-neutral-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" name="q" value="<?= esc($search ?? '') ?>" placeholder="Search pet name, species, owner..."
                class="block w-full rounded-xl bg-neutral-900 border border-neutral-850 pl-10 pr-4 py-2 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-xs transition">
        </form>
    </div>

    <!-- Pets Table Card -->
    <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-neutral-800 bg-neutral-950/40 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                        <th class="px-6 py-4">Patient Name</th>
                        <th class="px-6 py-4">Species & Breed</th>
                        <th class="px-6 py-4">Age (DOB)</th>
                        <th class="px-6 py-4">Gender</th>
                        <th class="px-6 py-4">Owner Name</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/60 text-sm">
                    <?php if (empty($pets)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-neutral-500">
                                <svg class="mx-auto h-12 w-12 text-neutral-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                                <span class="block text-sm font-semibold text-white">No patients found</span>
                                <span class="block text-xs text-neutral-500 mt-1">Try refining your search term or registers.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pets as $pet): ?>
                            <tr class="hover:bg-neutral-850/30 transition duration-150">
                                <td class="px-6 py-4 flex items-center space-x-3">
                                    <?php if (!empty($pet['photo']) && file_exists(FCPATH . $pet['photo'])): ?>
                                        <img src="/<?= esc($pet['photo']) ?>" class="h-9 w-9 object-cover rounded-xl border border-neutral-850" alt="<?= esc($pet['name']) ?>">
                                    <?php else: ?>
                                        <div class="h-9 w-9 bg-neutral-850 border border-neutral-800 text-neutral-350 rounded-xl flex items-center justify-center">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-bold text-white"><?= esc($pet['name']) ?></div>
                                        <span class="text-[9px] bg-neutral-950 px-1.5 py-0.5 border border-neutral-850 text-neutral-400 rounded">ID: #<?= esc($pet['id']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-neutral-950 border border-neutral-850 text-brand-400 capitalize" x-text="service.category"><?= esc($pet['species']) ?></span>
                                        <?php if (!empty($pet['breed'])): ?>
                                            <span class="text-xs text-neutral-400 truncate max-w-[120px]"><?= esc($pet['breed']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-neutral-300 font-medium">
                                    <span><?= getPatientListAge($pet['birth_date']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-neutral-300 capitalize">
                                    <?= esc($pet['gender'] ?: '—') ?>
                                </td>
                                <td class="px-6 py-4 text-neutral-300">
                                    <?php if (!empty($pet['customer_name'])): ?>
                                        <a href="/customers/show/<?= $pet['customer_id'] ?>" class="font-bold text-brand-500 hover:text-brand-400 transition"><?= esc($pet['customer_name']) ?></a>
                                    <?php else: ?>
                                        <span class="text-neutral-600">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right space-x-3">
                                    <a href="/pets/show/<?= $pet['id'] ?>" class="text-xs font-semibold text-brand-500 hover:text-brand-400 transition" id="btn-view-<?= $pet['id'] ?>">Details</a>
                                    <a href="/pets/edit/<?= $pet['id'] ?>" class="text-xs font-semibold text-neutral-400 hover:text-white transition" id="btn-edit-<?= $pet['id'] ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
