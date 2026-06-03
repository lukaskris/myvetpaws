<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($customer['name']) ?> | Profile<?= $this->endSection() ?>

<?= $this->section('header') ?>Customer Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Inline helper to calculate pet age
function getPetAge($birthDate) {
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
        <span class="text-white font-medium"><?= esc($customer['name']) ?></span>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Side: Customer Summary Card -->
        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl space-y-6 shadow-lg lg:col-span-1">
            <div class="flex flex-col items-center text-center">
                <!-- Avatar -->
                <div class="relative h-24 w-24 bg-neutral-950 border-2 border-neutral-800 rounded-full flex items-center justify-center overflow-hidden mb-4 shadow-inner">
                    <?php if (!empty($customer['profile_picture']) && file_exists(FCPATH . $customer['profile_picture'])): ?>
                        <img src="/<?= esc($customer['profile_picture']) ?>" class="h-full w-full object-cover" alt="<?= esc($customer['name']) ?>">
                    <?php else: ?>
                        <span class="text-neutral-400 font-bold text-2xl uppercase">
                            <?= substr(esc($customer['name']), 0, 2) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h3 class="text-lg font-bold text-white leading-tight">
                    <?= !empty($customer['title']) ? '<span class="text-neutral-400 text-xs font-semibold mr-1">' . esc($customer['title']) . '</span>' : '' ?><?= esc($customer['name']) ?>
                </h3>
                <span class="text-xs text-neutral-500 font-semibold mt-1">Customer Registry #<?= esc($customer['id']) ?></span>

                <!-- Quick actions -->
                <div class="flex items-center space-x-2 mt-5 w-full">
                    <a href="/customers/edit/<?= $customer['id'] ?>" class="flex-1 text-center py-2 px-3 border border-neutral-800 bg-neutral-950 text-neutral-300 hover:text-white hover:border-neutral-700 rounded-xl text-xs font-semibold transition" id="btn-edit-profile">
                        Edit Profile
                    </a>
                    
                    <form action="/customers/delete/<?= $customer['id'] ?>" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this customer? This will also soft delete all linked pet records.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="w-full py-2 px-3 border border-red-900/30 bg-red-950/20 text-red-400 hover:bg-red-950/40 hover:text-red-300 rounded-xl text-xs font-semibold transition cursor-pointer" id="btn-delete-profile">
                            Delete Profile
                        </button>
                    </form>
                </div>
            </div>

            <hr class="border-neutral-950">

            <!-- Detail Info -->
            <div class="space-y-4">
                <div>
                    <span class="block text-[10px] uppercase font-bold text-neutral-500 tracking-wider">Phone Number</span>
                    <span class="text-sm font-semibold text-white mt-0.5 block">
                        <?= !empty($customer['phone']) ? esc($customer['phone']) : '<span class="text-neutral-600">—</span>' ?>
                    </span>
                </div>
                <div>
                    <span class="block text-[10px] uppercase font-bold text-neutral-500 tracking-wider">Email Address</span>
                    <span class="text-sm font-semibold text-white mt-0.5 block">
                        <?= !empty($customer['email']) ? esc($customer['email']) : '<span class="text-neutral-600">—</span>' ?>
                    </span>
                </div>
                <div>
                    <span class="block text-[10px] uppercase font-bold text-neutral-500 tracking-wider">Residential Address</span>
                    <span class="text-sm font-semibold text-neutral-300 mt-0.5 block">
                        <?= !empty($customer['address']) ? esc($customer['address']) : '<span class="text-neutral-600">—</span>' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Side: Registered Pets -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Header Block -->
            <div class="bg-neutral-900 border border-neutral-800 p-5 rounded-3xl flex items-center justify-between shadow-md">
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider text-brand-500">Registered Pets</h3>
                    <p class="text-xs text-neutral-400 mt-1">Associate and manage multiple patient listings for this client.</p>
                </div>
                <a href="/pets/create/<?= $customer['id'] ?>" id="btn-register-pet" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 transition duration-150 inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Pet</span>
                </a>
            </div>

            <!-- Pets List -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (empty($pets)): ?>
                    <div class="bg-neutral-900 border border-neutral-800 p-8 rounded-3xl text-center text-neutral-500 md:col-span-2 shadow-md">
                        <svg class="mx-auto h-12 w-12 text-neutral-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <h4 class="text-sm font-bold text-white">No pets registered</h4>
                        <p class="text-xs text-neutral-500 max-w-xs mx-auto mt-1">There are no patient records linked to this customer profile. Register a pet to get started.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pets as $pet): ?>
                        <div class="bg-neutral-900 border border-neutral-800 p-5 rounded-3xl flex flex-col justify-between hover:border-neutral-700 transition duration-200 shadow-md">
                            <!-- Summary -->
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <!-- Pet Image -->
                                    <div class="h-12 w-12 bg-neutral-950 border border-neutral-850 rounded-2xl flex items-center justify-center overflow-hidden shrink-0">
                                        <?php if (!empty($pet['photo']) && file_exists(FCPATH . $pet['photo'])): ?>
                                            <img src="/<?= esc($pet['photo']) ?>" class="h-full w-full object-cover" alt="<?= esc($pet['name']) ?>">
                                        <?php else: ?>
                                            <svg class="h-6 w-6 text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm"><?= esc($pet['name']) ?></h4>
                                        <div class="flex items-center space-x-1.5 mt-0.5">
                                            <span class="text-[10px] bg-neutral-950 px-1.5 py-0.5 border border-neutral-850 rounded text-neutral-400 font-semibold tracking-wide capitalize"><?= esc($pet['species']) ?></span>
                                            <?php if (!empty($pet['breed'])): ?>
                                                <span class="text-[10px] text-neutral-500 max-w-[100px] truncate"><?= esc($pet['breed']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-[10px] bg-neutral-950 text-neutral-500 border border-neutral-850 px-1.5 py-0.5 rounded font-mono">#<?= esc($pet['id']) ?></span>
                            </div>

                            <!-- Specs info -->
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs border-t border-neutral-950 pt-3">
                                <div>
                                    <span class="text-neutral-500 block text-[9px] uppercase font-bold tracking-wider">Age</span>
                                    <span class="text-white font-medium truncate block"><?= getPetAge($pet['birth_date']) ?></span>
                                </div>
                                <div>
                                    <span class="text-neutral-500 block text-[9px] uppercase font-bold tracking-wider">Gender</span>
                                    <span class="text-white font-medium capitalize truncate block"><?= esc($pet['gender'] ?: 'Unknown') ?></span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-5 flex items-center justify-end space-x-3 text-xs border-t border-neutral-950 pt-3">
                                <form action="/pets/delete/<?= $pet['id'] ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this pet profile?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-red-500 hover:text-red-400 font-semibold cursor-pointer" id="btn-delete-pet-<?= $pet['id'] ?>">Delete</button>
                                </form>
                                <a href="/pets/edit/<?= $pet['id'] ?>" class="text-neutral-400 hover:text-white font-semibold" id="btn-edit-pet-<?= $pet['id'] ?>">Edit</a>
                                <a href="/pets/show/<?= $pet['id'] ?>" class="text-brand-500 hover:text-brand-400 font-bold" id="btn-view-pet-<?= $pet['id'] ?>">Details →</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
