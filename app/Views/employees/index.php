<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Employee Management<?= $this->endSection() ?>

<?= $this->section('header') ?>Staff Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <span class="text-white font-medium">Employees</span>
    </div>

    <!-- Header block -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-neutral-900 gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Staff Directory</h2>
            <p class="text-xs text-neutral-400 mt-1">Manage and provision access roles for your clinic's doctors, receptionists, and finance personnel.</p>
        </div>
        <a href="/employees/create" id="btn-create-employee" class="px-3.5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 transition duration-150 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Add Employee</span>
        </a>
    </div>

    <!-- Staff Table Card -->
    <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-neutral-800 bg-neutral-950/40 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Phone</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/60 text-sm">
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-neutral-500">
                                <svg class="mx-auto h-12 w-12 text-neutral-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="block text-sm font-semibold text-white">No employees found</span>
                                <span class="block text-xs text-neutral-500 mt-1">Get started by adding your first staff member.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $employee): ?>
                            <tr class="hover:bg-neutral-850/30 transition duration-150">
                                <td class="px-6 py-4 flex items-center space-x-3">
                                    <div class="h-9 w-9 bg-neutral-850 border border-neutral-800 text-neutral-300 font-semibold text-xs rounded-full flex items-center justify-center">
                                        <?= substr(esc($employee['name']), 0, 1) ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white"><?= esc($employee['name']) ?></div>
                                        <div class="text-xs text-neutral-400"><?= esc($employee['email']) ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $role = esc($employee['role']);
                                    $badgeClass = '';
                                    switch ($role) {
                                        case 'owner':
                                            $badgeClass = 'bg-rose-950/40 text-rose-400 border-rose-500/10';
                                            break;
                                        case 'doctor':
                                            $badgeClass = 'bg-indigo-950/40 text-indigo-400 border-indigo-500/10';
                                            break;
                                        case 'receptionist':
                                            $badgeClass = 'bg-emerald-950/40 text-emerald-400 border-emerald-500/10';
                                            break;
                                        case 'finance':
                                            $badgeClass = 'bg-amber-950/40 text-amber-400 border-amber-500/10';
                                            break;
                                        default:
                                            $badgeClass = 'bg-neutral-800 text-neutral-400 border-neutral-700/10';
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize <?= $badgeClass ?>">
                                        <?= $role ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-neutral-300 font-medium">
                                    <?= !empty($employee['phone']) ? esc($employee['phone']) : '<span class="text-neutral-600">—</span>' ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($employee['status'] == 1): ?>
                                        <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400 font-semibold">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 text-xs text-neutral-500 font-semibold">
                                            <span class="h-1.5 w-1.5 rounded-full bg-neutral-600"></span>
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="/employees/edit/<?= $employee['id'] ?>" class="text-xs font-semibold text-brand-500 hover:text-brand-400 transition" id="btn-edit-<?= $employee['id'] ?>">Edit</a>
                                    
                                    <?php if ($employee['role'] !== 'owner'): ?>
                                        <a href="/employees/toggle/<?= $employee['id'] ?>" class="text-xs font-semibold <?= $employee['status'] == 1 ? 'text-red-500 hover:text-red-400' : 'text-emerald-500 hover:text-emerald-400' ?> transition" id="btn-toggle-<?= $employee['id'] ?>">
                                            <?= $employee['status'] == 1 ? 'Deactivate' : 'Activate' ?>
                                        </a>
                                    <?php endif; ?>
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
