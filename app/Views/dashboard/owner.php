<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('header') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Welcome Banner -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-white tracking-tight">Welcome back, <?= esc(session()->get('user_name')) ?></h2>
    <p class="text-sm text-slate-400 mt-1">Here is a quick overview of <?= esc(session()->get('clinic_name')) ?> for today.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
    <!-- Today's Visits -->
    <div class="glass-panel p-5 rounded-2xl flex flex-col justify-between hover:border-brand-500/30 hover:shadow-lg hover:shadow-brand-600/5 transition duration-300">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Today's Visits</span>
            <div class="p-1.5 bg-brand-600/10 rounded-lg">
                <i data-lucide="calendar" class="w-4 h-4 text-brand-500"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-3xl font-extrabold text-white tracking-tight"><?= esc($today_visits) ?></span>
            <span class="text-xs text-slate-500 block mt-1 font-medium">Checked in today</span>
        </div>
    </div>

    <!-- Active Customers -->
    <div class="glass-panel p-5 rounded-2xl flex flex-col justify-between hover:border-brand-500/30 hover:shadow-lg hover:shadow-brand-600/5 transition duration-300">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Active Customers</span>
            <div class="p-1.5 bg-brand-600/10 rounded-lg">
                <i data-lucide="users" class="w-4 h-4 text-brand-500"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-3xl font-extrabold text-white tracking-tight"><?= esc($active_customers) ?></span>
            <span class="text-xs text-slate-500 block mt-1 font-medium">Registered owners</span>
        </div>
    </div>

    <!-- Active Pets -->
    <div class="glass-panel p-5 rounded-2xl flex flex-col justify-between hover:border-brand-500/30 hover:shadow-lg hover:shadow-brand-600/5 transition duration-300">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Active Pets</span>
            <div class="p-1.5 bg-brand-600/10 rounded-lg">
                <i data-lucide="paw-print" class="w-4 h-4 text-brand-500"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-3xl font-extrabold text-white tracking-tight"><?= esc($active_pets) ?></span>
            <span class="text-xs text-slate-500 block mt-1 font-medium">Patients in database</span>
        </div>
    </div>

    <!-- Revenue Summary -->
    <div class="glass-panel p-5 rounded-2xl flex flex-col justify-between hover:border-neon-emerald/30 hover:shadow-lg hover:shadow-neon-emerald/5 transition duration-300">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Today's Revenue</span>
            <div class="p-1.5 bg-neon-emerald/10 rounded-lg">
                <i data-lucide="dollar-sign" class="w-4 h-4 text-neon-emerald"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-2xl font-extrabold text-white tracking-tight">Rp<?= number_format($revenue_summary, 0, ',', '.') ?></span>
            <span class="text-xs text-slate-500 block mt-1 font-medium">Settled payments</span>
        </div>
    </div>

    <!-- Outstanding Payments -->
    <div class="glass-panel p-5 rounded-2xl flex flex-col justify-between hover:border-neon-pink/30 hover:shadow-lg hover:shadow-neon-pink/5 transition duration-300">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Unpaid Invoices</span>
            <div class="p-1.5 bg-neon-pink/10 rounded-lg">
                <i data-lucide="alert-circle" class="w-4 h-4 text-neon-pink"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-2xl font-extrabold text-white tracking-tight">Rp<?= number_format($outstanding_payments, 0, ',', '.') ?></span>
            <span class="text-xs text-slate-500 block mt-1 font-medium">Pending payments</span>
        </div>
    </div>
</div>

<!-- Grid Layout for Modules -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Operations Checklist -->
    <div class="glass-panel p-6 rounded-3xl shadow-xl lg:col-span-2">
        <h3 class="text-lg font-extrabold text-white mb-1.5">Clinic Setup Checklist</h3>
        <p class="text-xs text-slate-400 mb-6">Complete these initial setup tasks to begin managing daily veterinary operations.</p>
        
        <div class="space-y-4">
            <!-- Clinic Registered -->
            <div class="flex items-center justify-between p-4 bg-obsidian-950/40 border border-obsidian-800/80 rounded-2xl">
                <div class="flex items-center space-x-3.5">
                    <div class="h-8 w-8 bg-neon-emerald/10 border border-neon-emerald/20 text-neon-emerald rounded-full flex items-center justify-center">
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white">Register Clinic Workspace</div>
                        <div class="text-xs text-slate-400 mt-0.5">Clinic account created and verified</div>
                    </div>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-neon-emerald bg-neon-emerald/10 px-3 py-1 rounded-full border border-neon-emerald/15">Completed</span>
            </div>

            <!-- Define Services -->
            <div class="flex items-center justify-between p-4 bg-obsidian-950/40 border border-obsidian-800/80 rounded-2xl hover:border-brand-500/20 hover:bg-obsidian-900/30 transition duration-200">
                <div class="flex items-center space-x-3.5">
                    <div class="h-8 w-8 bg-obsidian-800 border border-obsidian-700 text-slate-300 rounded-full flex items-center justify-center font-bold text-xs shadow-inner">
                        2
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white">Manage Clinic Services</div>
                        <div class="text-xs text-slate-400 mt-0.5">Add medical consultations, grooming, surgeries</div>
                    </div>
                </div>
                <a href="/services" class="text-xs font-bold text-brand-500 hover:text-brand-400 transition-colors inline-flex items-center gap-1">
                    <span>Start Setup</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Register Customers & Pets -->
            <div class="flex items-center justify-between p-4 bg-obsidian-950/40 border border-obsidian-800/80 rounded-2xl hover:border-brand-500/20 hover:bg-obsidian-900/30 transition duration-200">
                <div class="flex items-center space-x-3.5">
                    <div class="h-8 w-8 bg-obsidian-800 border border-obsidian-700 text-slate-300 rounded-full flex items-center justify-center font-bold text-xs shadow-inner">
                        3
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white">Add Customers & Pets</div>
                        <div class="text-xs text-slate-400 mt-0.5">Populate the owner records and pet timelines</div>
                    </div>
                </div>
                <a href="/customers" class="text-xs font-bold text-brand-500 hover:text-brand-400 transition-colors inline-flex items-center gap-1">
                    <span>Register</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Create First Visit -->
            <div class="flex items-center justify-between p-4 bg-obsidian-950/40 border border-obsidian-800/80 rounded-2xl hover:border-brand-500/20 hover:bg-obsidian-900/30 transition duration-200">
                <div class="flex items-center space-x-3.5">
                    <div class="h-8 w-8 bg-obsidian-800 border border-obsidian-700 text-slate-300 rounded-full flex items-center justify-center font-bold text-xs shadow-inner">
                        4
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white">Launch Clinic Visit Workflow</div>
                        <div class="text-xs text-slate-400 mt-0.5">Check-in patients and write medical records</div>
                    </div>
                </div>
                <a href="/visits/create" class="text-xs font-bold text-brand-500 hover:text-brand-400 transition-colors inline-flex items-center gap-1">
                    <span>Check-in</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column: Queue/Activity & Alerts -->
    <div class="space-y-6">
        <!-- Low Stock Alerts -->
        <?php if (!empty($lowStockItems)): ?>
            <div class="glass-panel p-6 rounded-3xl shadow-xl border-l-4 border-amber-500/80 bg-amber-500/5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 animate-pulse"></i>
                        <span>Restock Required</span>
                    </h3>
                    <span class="px-2 py-0.5 text-[9px] bg-amber-500/20 text-amber-400 border border-amber-500/25 rounded font-bold">
                        <?= count($lowStockItems) ?> <?= count($lowStockItems) > 1 ? 'items' : 'item' ?>
                    </span>
                </div>
                <div class="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                    <?php foreach ($lowStockItems as $lowItem): ?>
                        <div class="flex justify-between items-center text-xs py-1 border-b border-obsidian-850/60 last:border-0">
                            <div class="min-w-0">
                                <span class="font-bold text-white block truncate"><?= esc($lowItem['name']) ?></span>
                                <span class="text-[9px] text-slate-500 block font-semibold"><?= esc($lowItem['category']) ?></span>
                            </div>
                            <div class="text-right shrink-0">
                                <?php if ($lowItem['stock'] <= 0): ?>
                                    <span class="text-[10px] font-bold text-neon-pink bg-neon-pink/10 border border-neon-pink/15 px-2 py-0.5 rounded-full">Out of Stock</span>
                                <?php else: ?>
                                    <span class="text-[10px] font-bold text-amber-500 bg-amber-500/10 border border-amber-500/15 px-2 py-0.5 rounded-full"><?= esc($lowItem['stock']) ?> left (min <?= esc($lowItem['min_stock']) ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="/items" class="text-xs font-bold text-brand-500 hover:text-brand-400 mt-4 inline-flex items-center gap-1.5 transition">
                    <span>Manage Inventory</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        <?php endif; ?>

        <!-- Patient Queue -->
        <div class="glass-panel p-6 rounded-3xl shadow-xl">
            <div class="flex items-center justify-between mb-1.5">
                <h3 class="text-lg font-extrabold text-white">Patient Queue</h3>
                <?php if (!empty($activeQueue)): ?>
                    <span class="px-2.5 py-0.5 text-[10px] bg-brand-600/20 text-brand-300 border border-brand-500/25 rounded-full font-bold">
                        <?= count($activeQueue) ?> active
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-xs text-slate-400 mb-6">Real-time check-ins waiting for medical inspection.</p>

            <?php if (empty($activeQueue)): ?>
                <!-- Empty Queue State -->
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="p-3 bg-obsidian-950 border border-obsidian-850 text-slate-600 rounded-2xl mb-3 shadow-inner">
                        <i data-lucide="hourglass" class="w-8 h-8 text-slate-500 animate-pulse"></i>
                    </div>
                    <h4 class="text-sm font-bold text-white">Queue is empty</h4>
                    <p class="text-xs text-slate-500 max-w-xs mt-1.5 leading-relaxed">No checked-in patients in the clinic right now. Create a visit to start.</p>
                </div>
            <?php else: ?>
                <!-- Active Queue List -->
                <div class="space-y-3.5">
                    <?php foreach ($activeQueue as $queuedVisit): ?>
                        <div class="p-4 bg-obsidian-950/45 border border-obsidian-850 rounded-2xl flex items-center justify-between gap-3 hover:border-brand-500/20 transition duration-150 shadow-sm">
                            <div class="truncate">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="/pets/show/<?= $queuedVisit['pet_id'] ?>" class="text-sm font-bold text-white hover:text-brand-400 transition-colors truncate">
                                        <?= esc($queuedVisit['pet_name']) ?>
                                    </a>
                                    <span class="text-[9px] px-2 py-0.5 rounded font-bold uppercase tracking-wider <?= $queuedVisit['status'] == 2 ? 'bg-neon-amber/15 text-neon-amber border border-neon-amber/15' : 'bg-brand-600/15 text-brand-300 border border-brand-500/15' ?>">
                                        <?= $queuedVisit['status'] == 2 ? 'Exam' : 'Queued' ?>
                                    </span>
                                </div>
                                <span class="text-xs text-slate-400 block mt-1 truncate">Owner: <?= esc($queuedVisit['customer_name']) ?></span>
                                <?php if (!empty($queuedVisit['complaints'])): ?>
                                    <span class="text-[11px] text-slate-500 block italic mt-1.5 truncate">"<?= esc($queuedVisit['complaints']) ?>"</span>
                                <?php endif; ?>
                                <span class="text-[10px] text-slate-500 block mt-2 font-medium">Checked in at <?= date('H:i', strtotime($queuedVisit['checkin_time'])) ?></span>
                            </div>
                            
                            <div class="flex flex-col gap-2 shrink-0 items-end">
                                <?php if (session()->get('user_role') === 'owner' || session()->get('user_role') === 'doctor'): ?>
                                    <a href="/visits/examine/<?= $queuedVisit['id'] ?>" class="px-3 py-1.5 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white text-[11px] font-bold rounded-lg transition-premium text-center shadow-md shadow-brand-600/10 hover:shadow-brand-500/20 hover:scale-[1.02] active:scale-[0.98]">
                                        Examine
                                    </a>
                                <?php endif; ?>
                                <a href="/visits/cancel/<?= $queuedVisit['id'] ?>" onclick="return confirm('Are you sure you want to cancel this check-in?');" class="text-[10px] font-bold text-neon-pink hover:text-rose-300 px-1 py-0.5 transition">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
