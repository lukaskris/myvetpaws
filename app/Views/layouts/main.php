<!DOCTYPE html>
<html lang="en" class="h-full bg-obsidian-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> | MyVetPaws</title>
    <!-- Prevent FOUC (Flash of Unstyled Content) by setting theme immediately -->
    <meta name="color-scheme" content="light dark">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.classList.add('dark');
                document.querySelector('meta[name="color-scheme"]').content = 'dark';
            } else {
                document.documentElement.classList.remove('dark');
                document.querySelector('meta[name="color-scheme"]').content = 'light';
            }
        })();
    </script>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <!-- Compiled Tailwind CSS -->
    <link rel="stylesheet" href="/css/app.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        
        /* 2026 Tactile click feedback micro-interactions (Desktop + Mobile) */
        button, 
        .btn-tactile, 
        a#btn-quick-visit, 
        a#btn-logout, 
        aside nav a,
        nav.md\:hidden a {
            transition: transform 0.1s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }
        button:active, 
        .btn-tactile:active, 
        a#btn-quick-visit:active, 
        a#btn-logout:active, 
        aside nav a:active,
        nav.md\:hidden a:active {
            transform: scale(0.97) !important;
        }

        /* Premium glowing focus rings for form elements */
        input:focus,
        select:focus,
        textarea:focus {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15) !important;
            border-color: #6366f1 !important;
            outline: none !important;
        }
    </style>
</head>
<body class="h-full flex flex-col md:flex-row overflow-hidden bg-obsidian-950 bg-cosmic-glow select-none" x-data="toastManager">

    <!-- Global Toast Notifications -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-[-20px] opacity-0 scale-95"
                 x-transition:enter-end="translate-y-0 opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-y-0 opacity-100 scale-100"
                 x-transition:leave-end="translate-y-[-20px] opacity-0 scale-95"
                 class="p-4 rounded-2xl border backdrop-blur-xl shadow-2xl flex items-start gap-3 pointer-events-auto"
                 :class="{
                     'bg-emerald-950/70 border-neon-emerald/30 text-emerald-200': toast.type === 'success',
                     'bg-red-950/70 border-neon-pink/30 text-rose-200': toast.type === 'error',
                     'bg-obsidian-900/80 border-obsidian-700/60 text-slate-200': toast.type === 'info'
                 }">
                <!-- Icon -->
                <div class="mt-0.5 shrink-0">
                    <template x-if="toast.type === 'success'">
                        <i data-lucide="check-circle" class="w-5 h-5 text-neon-emerald"></i>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-neon-pink"></i>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <i data-lucide="info" class="w-5 h-5 text-brand-400"></i>
                    </template>
                </div>
                <!-- Content -->
                <div class="flex-1">
                    <p class="text-sm font-bold text-white tracking-tight" x-text="toast.title"></p>
                    <p class="text-xs opacity-90 mt-0.5 font-medium leading-relaxed" x-text="toast.message"></p>
                </div>
                <!-- Close Button -->
                <button @click="dismiss(toast.id)" class="text-slate-400 hover:text-white transition duration-150 shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </template>
    </div>

    <!-- Desktop Sidebar Layout (Glassmorphism 2.0) -->
    <aside class="hidden md:flex flex-col w-64 bg-obsidian-900/40 backdrop-blur-xl border-r border-obsidian-850/50 shrink-0 h-full p-5 justify-between">
        <div class="space-y-6">
            <!-- Brand Logo -->
            <div class="flex items-center space-x-3 px-2 select-none pointer-events-none">
                <img class="h-8 w-8 object-contain rounded-xl shadow-md shadow-brand-600/10" src="/images/myveticon.webp" alt="MyVetPaws">
                <span class="text-base font-extrabold text-white tracking-wider">My<span class="text-brand-500">Vet</span>Paws</span>
            </div>

            <!-- Clinic Selector/Header -->
            <div class="flex items-center space-x-3 px-3 py-2 border border-obsidian-800/80 rounded-2xl bg-obsidian-950/60 shadow-sm">
                <div class="h-9 w-9 bg-gradient-to-br from-brand-600 to-brand-700 rounded-xl flex items-center justify-center font-bold text-white shadow-lg shadow-brand-600/20">
                    <?= substr(session()->get('clinic_name') ?? 'M', 0, 1) ?>
                </div>
                <div class="truncate">
                    <div class="text-sm font-bold text-white leading-tight truncate"><?= esc(session()->get('clinic_name') ?? 'MyVetPaws') ?></div>
                    <div class="text-[10px] text-slate-400 font-semibold tracking-wider uppercase mt-0.5">Workspace</div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1.5">
                <a href="/dashboard" id="nav-dashboard" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-premium <?= (current_url() == base_url('dashboard')) ? 'bg-brand-600/15 text-brand-300 border-l-2 border-brand-600 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-obsidian-800/40' ?>">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/customers" id="nav-customers" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-premium <?= (strpos(current_url(), base_url('customers')) !== false) ? 'bg-brand-600/15 text-brand-300 border-l-2 border-brand-600 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-obsidian-800/40' ?>">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Customers</span>
                </a>
                <a href="/pets" id="nav-pets" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-premium <?= (strpos(current_url(), base_url('pets')) !== false) ? 'bg-brand-600/15 text-brand-300 border-l-2 border-brand-600 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-obsidian-800/40' ?>">
                    <i data-lucide="paw-print" class="w-4 h-4"></i>
                    <span>Pets & Patients</span>
                </a>
                <a href="/visits" id="nav-visits" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-premium <?= (strpos(current_url(), base_url('visits')) !== false) ? 'bg-brand-600/15 text-brand-300 border-l-2 border-brand-600 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-obsidian-800/40' ?>">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <span>Visits</span>
                </a>
                <a href="/records" id="nav-records" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-premium <?= (strpos(current_url(), base_url('records')) !== false) ? 'bg-brand-600/15 text-brand-300 border-l-2 border-brand-600 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-obsidian-800/40' ?>">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Medical Records</span>
                </a>
                <a href="/invoices" id="nav-invoices" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-premium <?= (strpos(current_url(), base_url('invoices')) !== false) ? 'bg-brand-600/15 text-brand-300 border-l-2 border-brand-600 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-obsidian-800/40' ?>">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>Invoices</span>
                </a>
                <a href="/reports" id="nav-reports" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-premium <?= (strpos(current_url(), base_url('reports')) !== false) ? 'bg-brand-600/15 text-brand-300 border-l-2 border-brand-600 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-obsidian-800/40' ?>">
                    <i data-lucide="line-chart" class="w-4 h-4"></i>
                    <span>Analytics & Reports</span>
                </a>
                <a href="/services" id="nav-services" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-premium <?= (strpos(current_url(), base_url('services')) !== false) ? 'bg-brand-600/15 text-brand-300 border-l-2 border-brand-600 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-obsidian-800/40' ?>">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>Services</span>
                </a>
                
                <?php if (session()->get('user_role') === 'owner'): ?>
                <a href="/employees" id="nav-staff" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-premium <?= (strpos(current_url(), base_url('employees')) !== false) ? 'bg-brand-600/15 text-brand-300 border-l-2 border-brand-600 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-obsidian-800/40' ?>">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                    <span>Staff Management</span>
                </a>
                
                <a href="/profile" id="nav-settings" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-premium <?= (strpos(current_url(), base_url('profile')) !== false) ? 'bg-brand-600/15 text-brand-300 border-l-2 border-brand-600 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-obsidian-800/40' ?>">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Clinic Settings</span>
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- User Information & Logout -->
        <div class="border-t border-obsidian-800/60 pt-4 flex flex-col gap-2">
            <div class="flex items-center space-x-3 px-2">
                <div class="h-9 w-9 bg-obsidian-800 border border-obsidian-700/80 rounded-full flex items-center justify-center text-xs font-bold text-slate-300">
                    <?= substr(session()->get('user_name') ?? 'U', 0, 1) ?>
                </div>
                <div class="truncate">
                    <div class="text-xs font-bold text-white leading-tight truncate"><?= esc(session()->get('user_name') ?? 'User') ?></div>
                    <div class="text-[10px] text-slate-400 font-semibold uppercase mt-0.5 tracking-wider"><?= esc(session()->get('user_role') ?? 'Staff') ?></div>
                </div>
            </div>
            <a href="/logout" id="btn-logout" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-bold text-rose-400 hover:bg-rose-950/20 hover:text-rose-300 transition duration-150 mt-1">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Mobile Bottom Navigation Layout (Glassmorphism 2.0) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-obsidian-900/80 backdrop-blur-xl border-t border-obsidian-850/50 flex justify-around py-3 px-4 shadow-xl">
        <a href="/dashboard" class="flex flex-col items-center gap-1 text-[10px] font-semibold transition <?= (current_url() == base_url('dashboard')) ? 'text-brand-500 font-bold' : 'text-slate-500 hover:text-slate-300' ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span>Dashboard</span>
        </a>
        <a href="/customers" class="flex flex-col items-center gap-1 text-[10px] font-semibold transition <?= (strpos(current_url(), base_url('customers')) !== false) ? 'text-brand-500 font-bold' : 'text-slate-500 hover:text-slate-300' ?>">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span>Customers</span>
        </a>
        <a href="/pets" class="flex flex-col items-center gap-1 text-[10px] font-semibold transition <?= (strpos(current_url(), base_url('pets')) !== false) ? 'text-brand-500 font-bold' : 'text-slate-500 hover:text-slate-300' ?>">
            <i data-lucide="paw-print" class="w-5 h-5"></i>
            <span>Pets</span>
        </a>
        <a href="/visits" class="flex flex-col items-center gap-1 text-[10px] font-semibold transition <?= (strpos(current_url(), base_url('visits')) !== false) ? 'text-brand-500 font-bold' : 'text-slate-500 hover:text-slate-300' ?>">
            <i data-lucide="calendar" class="w-5 h-5"></i>
            <span>Visits</span>
        </a>
        <a href="/invoices" class="flex flex-col items-center gap-1 text-[10px] font-semibold transition <?= (strpos(current_url(), base_url('invoices')) !== false) ? 'text-brand-500 font-bold' : 'text-slate-500 hover:text-slate-300' ?>">
            <i data-lucide="receipt" class="w-5 h-5"></i>
            <span>Invoices</span>
        </a>
        <a href="/logout" class="flex flex-col items-center gap-1 text-[10px] font-semibold text-rose-400 hover:text-rose-300">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span>Logout</span>
        </a>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto pb-20 md:pb-0 h-full bg-transparent">
        <!-- Top Toolbar Header -->
        <header class="border-b border-obsidian-850/40 bg-obsidian-950/40 backdrop-blur-xl sticky top-0 z-20 px-6 py-4.5 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-xl font-extrabold tracking-tight text-white"><?= $this->renderSection('header') ?></h1>
            </div>
            <!-- Quick Actions -->
            <div class="flex items-center space-x-3.5">
                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="p-2 bg-obsidian-900/40 border border-obsidian-800 hover:border-obsidian-750 text-slate-400 hover:text-white rounded-xl transition duration-150 inline-flex items-center justify-center cursor-pointer" aria-label="Toggle Theme">
                    <i data-lucide="sun" class="w-4 h-4 dark:hidden"></i>
                    <i data-lucide="moon" class="w-4 h-4 hidden dark:block text-brand-400"></i>
                </button>
                <span class="text-xs text-slate-400 hidden sm:inline-block font-semibold tracking-wider"><?= date('F j, Y') ?></span>
                <a href="/visits/create" id="btn-quick-visit" class="px-4 py-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-600/10 hover:shadow-brand-500/20 hover:scale-[1.02] active:scale-[0.98] transition-premium inline-flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Quick Visit</span>
                </a>
            </div>
        </header>

        <!-- Content Body -->
        <section class="flex-1 p-6 overflow-y-auto max-w-7xl w-full mx-auto">
            <?= $this->renderSection('content') ?>
        </section>
    </main>

    <!-- Global scripts and flash message handlers -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('toastManager', () => ({
                toasts: [],
                init() {
                    // Check for CI flash messages on load
                    <?php if (session()->getFlashdata('success')): ?>
                        this.pushToast('Success', '<?= esc(session()->getFlashdata('success'), "js") ?>', 'success');
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        this.pushToast('Error', '<?= esc(session()->getFlashdata('error'), "js") ?>', 'error');
                    <?php endif; ?>
                },
                pushToast(title, message, type = 'info') {
                    const id = Date.now();
                    this.toasts.push({
                        id: id,
                        title: title,
                        message: message,
                        type: type,
                        visible: true
                    });
                    // Refresh Lucide icons in dynamically inserted toast content
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                    // Auto-dismiss after 4 seconds
                    setTimeout(() => {
                        this.dismiss(id);
                    }, 4000);
                },
                dismiss(id) {
                    const toast = this.toasts.find(t => t.id === id);
                    if (toast) {
                        toast.visible = false;
                        // Remove from DOM after transition completes
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                }
            }));
        });
        
        // Initialize Lucide Icons and Theme Toggle
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            
            const themeToggleBtn = document.getElementById('theme-toggle');
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', () => {
                    const isDark = document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    
                    const metaScheme = document.querySelector('meta[name="color-scheme"]');
                    if (metaScheme) {
                        metaScheme.content = isDark ? 'dark' : 'light';
                    }
                });
            }
        });
    </script>
</body>
</html>
