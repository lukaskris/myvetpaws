<!DOCTYPE html>
<html lang="en" class="h-full bg-obsidian-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | MyVetPaws</title>
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
        button {
            transition: transform 0.1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        button:active {
            transform: scale(0.98) !important;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-obsidian-950 bg-cosmic-glow">
    <div class="w-full max-w-md space-y-8" x-data="{ loading: false }">
        <!-- Logo and Heading -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center p-3 bg-obsidian-900 border border-obsidian-800 rounded-2xl shadow-xl shadow-brand-600/5 mb-5 select-none pointer-events-none">
                <!-- Brand Logo Image -->
                <img class="w-10 h-10 object-contain" src="/images/myveticon.webp" alt="MyVetPaws">
            </div>
            <h2 class="text-3xl font-extrabold tracking-tight text-white">Sign in to your account</h2>
            <p class="mt-2 text-sm text-slate-400">
                Or <a href="/register" class="font-semibold text-brand-500 hover:text-brand-400 transition-colors">register a new clinic workspace</a>
            </p>
        </div>

        <!-- Notification alerts -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="p-4 bg-emerald-950/55 border border-neon-emerald/30 rounded-2xl text-sm text-emerald-300 shadow-lg">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="p-4 bg-red-950/55 border border-neon-pink/30 rounded-2xl text-sm text-rose-300 shadow-lg">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="p-4 bg-red-950/55 border border-neon-pink/30 rounded-2xl text-sm text-rose-300 space-y-1 shadow-lg">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <p>• <?= esc($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Glassmorphism Card Container -->
        <div class="glass-panel p-8 rounded-3xl shadow-2xl">
            <form class="space-y-6" action="/login" method="POST" @submit="loading = true">
                <?= csrf_field() ?>

                <div class="space-y-5">
                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-300">Email Address</label>
                        <div class="mt-1.5 relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required value="<?= old('email') ?>"
                                class="block w-full rounded-xl bg-obsidian-950/60 border border-obsidian-800/80 pl-10 pr-4 py-3 text-white placeholder-slate-500 focus:border-brand-600 focus:ring-1 focus:ring-brand-600 outline-none transition duration-200 text-sm"
                                placeholder="name@clinic.com">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex justify-between items-center">
                            <label for="password" class="block text-sm font-semibold text-slate-300">Password</label>
                            <a href="#" class="text-xs font-medium text-slate-500 hover:text-slate-300 transition-colors">Forgot password?</a>
                        </div>
                        <div class="mt-1.5 relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input :type="show ? 'text' : 'password'" id="password" name="password" required
                                class="block w-full rounded-xl bg-obsidian-950/60 border border-obsidian-800/80 pl-10 pr-10 py-3 text-white placeholder-slate-500 focus:border-brand-600 focus:ring-1 focus:ring-brand-600 outline-none transition duration-200 text-sm"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition">
                                <i x-show="!show" data-lucide="eye" class="w-4 h-4"></i>
                                <i x-show="show" data-lucide="eye-off" class="w-4 h-4" x-cloak></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Remember Me and Options -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 rounded border-obsidian-800 bg-obsidian-950 text-brand-600 focus:ring-brand-600 focus:ring-offset-obsidian-950 transition duration-200">
                        <label for="remember" class="ml-2.5 block text-xs font-medium text-slate-400 select-none cursor-pointer">Remember this device</label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" :disabled="loading"
                        class="w-full flex justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-obsidian-950 focus:ring-brand-600 transition-premium disabled:opacity-50 disabled:cursor-not-allowed items-center shadow-lg shadow-brand-600/20">
                        <span x-show="!loading" class="flex items-center gap-1.5">
                            <span>Sign In</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </span>
                        <span x-show="loading" class="flex items-center" x-cloak>
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Signing In...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
