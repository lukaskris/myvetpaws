<!DOCTYPE html>
<html lang="en" class="h-full bg-neutral-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | MyVetPaws</title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#eedeff',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-radial-gradient">
    <div class="w-full max-w-md space-y-8" x-data="{ loading: false }">
        <!-- Logo and Heading -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center p-2.5 bg-neutral-900 border border-neutral-800 rounded-2xl shadow-xl shadow-brand-500/10 mb-4 select-none pointer-events-none">
                <!-- Brand Logo Image -->
                <img class="w-9 h-9 object-contain" src="/images/myveticon.webp" alt="MyVetPaws">
            </div>
            <h2 class="text-3xl font-bold tracking-tight text-white">Create your clinic account</h2>
            <p class="mt-2 text-sm text-neutral-400">
                Or <a href="/login" class="font-medium text-brand-500 hover:text-brand-400 transition-colors">sign in to your existing account</a>
            </p>
        </div>

        <!-- Alert messages -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="p-4 bg-red-900/30 border border-red-500/30 rounded-xl text-sm text-red-400 animate-pulse">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="p-4 bg-red-900/30 border border-red-500/30 rounded-xl text-sm text-red-400 space-y-1">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <p>• <?= esc($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Card Container -->
        <div class="bg-neutral-900/80 backdrop-blur-md border border-neutral-800 p-8 rounded-3xl shadow-2xl">
            <form class="space-y-6" action="/register" method="POST" @submit="loading = true">
                <?= csrf_field() ?>

                <div class="space-y-5">
                    <!-- Clinic Name -->
                    <div>
                        <label for="clinic_name" class="block text-sm font-medium text-neutral-300">Clinic Name</label>
                        <div class="mt-1.5 relative">
                            <input id="clinic_name" name="clinic_name" type="text" required value="<?= old('clinic_name') ?>"
                                class="block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-3 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition duration-200 text-sm"
                                placeholder="e.g. Happy Paws Vet Clinic">
                        </div>
                    </div>

                    <!-- Owner Name -->
                    <div>
                        <label for="owner_name" class="block text-sm font-medium text-neutral-300">Owner Full Name</label>
                        <div class="mt-1.5 relative">
                            <input id="owner_name" name="owner_name" type="text" required value="<?= old('owner_name') ?>"
                                class="block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-3 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition duration-200 text-sm"
                                placeholder="e.g. Dr. Lukas Miller">
                        </div>
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-300">Business Email Address</label>
                        <div class="mt-1.5 relative">
                            <input id="email" name="email" type="email" autocomplete="email" required value="<?= old('email') ?>"
                                class="block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-3 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition duration-200 text-sm"
                                placeholder="name@clinic.com">
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-neutral-300">Phone Number</label>
                        <div class="mt-1.5 relative">
                            <input id="phone" name="phone" type="tel" required value="<?= old('phone') ?>"
                                class="block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-3 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition duration-200 text-sm"
                                placeholder="+1 (555) 000-0000">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-300">Password</label>
                        <div class="mt-1.5 relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" id="password" name="password" required
                                class="block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-3 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition duration-200 text-sm"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-neutral-500 hover:text-neutral-300">
                                <svg class="h-5 w-5" :class="show ? 'hidden' : 'block'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="h-5 w-5" :class="show ? 'block' : 'hidden'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 015.63-5.903m2.783-1.15A9.997 9.997 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-4.225-4.225L3 3m13.225 13.225L12 12m0 0l-1.077-1.077" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" :disabled="loading"
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-neutral-900 focus:ring-brand-500 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed items-center shadow-lg shadow-brand-500/20">
                        <span x-show="!loading">Get Started</span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating Account...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
