<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Customer Management<?= $this->endSection() ?>

<?= $this->section('header') ?>Customers<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <span class="text-white font-medium">Customers</span>
    </div>

    <!-- Header block -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-neutral-900 gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Customer Directory</h2>
            <p class="text-xs text-neutral-400 mt-1">Search, register, and manage pet owners registered in your clinic workspace.</p>
        </div>
        <a href="/customers/create" id="btn-create-customer" class="px-3.5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 transition duration-150 inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Register Customer</span>
        </a>
    </div>

    <!-- Search box -->
    <div class="flex items-center justify-between gap-4">
        <form action="/customers" method="GET" class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-neutral-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" name="q" value="<?= esc($search ?? '') ?>" placeholder="Search name, phone, or email..."
                class="block w-full rounded-xl bg-neutral-900 border border-neutral-850 pl-10 pr-4 py-2 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-xs transition">
        </form>
    </div>

    <!-- Customers Table Card -->
    <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-neutral-800 bg-neutral-950/40 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Phone</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Address</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/60 text-sm">
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-neutral-500">
                                <svg class="mx-auto h-12 w-12 text-neutral-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="block text-sm font-semibold text-white">No customers found</span>
                                <span class="block text-xs text-neutral-500 mt-1">Try refining your search term or add a new customer registry.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr class="hover:bg-neutral-850/30 transition duration-150">
                                <td class="px-6 py-4 flex items-center space-x-3">
                                    <?php if (!empty($customer['profile_picture']) && file_exists(FCPATH . $customer['profile_picture'])): ?>
                                        <img src="/<?= esc($customer['profile_picture']) ?>" class="h-9 w-9 object-cover rounded-full border border-neutral-850" alt="<?= esc($customer['name']) ?>">
                                    <?php else: ?>
                                        <div class="h-9 w-9 bg-neutral-850 border border-neutral-800 text-neutral-300 font-semibold text-xs rounded-full flex items-center justify-center">
                                            <?= substr(esc($customer['name']), 0, 2) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-bold text-white">
                                            <?= !empty($customer['title']) ? '<span class="text-neutral-400 text-xs font-semibold mr-1">' . esc($customer['title']) . '</span>' : '' ?><?= esc($customer['name']) ?>
                                        </div>
                                        <span class="text-[10px] bg-neutral-950 px-1.5 py-0.5 border border-neutral-850 text-neutral-400 rounded">ID: #<?= esc($customer['id']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-neutral-300 font-medium">
                                    <?= !empty($customer['phone']) ? esc($customer['phone']) : '<span class="text-neutral-600">—</span>' ?>
                                </td>
                                <td class="px-6 py-4 text-neutral-300">
                                    <?= !empty($customer['email']) ? esc($customer['email']) : '<span class="text-neutral-600">—</span>' ?>
                                </td>
                                <td class="px-6 py-4 text-neutral-400 max-w-xs truncate">
                                    <?= !empty($customer['address']) ? esc($customer['address']) : '<span class="text-neutral-600">—</span>' ?>
                                </td>
                                <td class="px-6 py-4 text-right space-x-3">
                                    <a href="/customers/show/<?= $customer['id'] ?>" class="text-xs font-semibold text-brand-500 hover:text-brand-400 transition" id="btn-view-<?= $customer['id'] ?>">View Details</a>
                                    <a href="/customers/edit/<?= $customer['id'] ?>" class="text-xs font-semibold text-neutral-400 hover:text-white transition" id="btn-edit-<?= $customer['id'] ?>">Edit</a>
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
