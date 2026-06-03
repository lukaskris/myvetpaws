<!DOCTYPE html>
<html lang="en" class="h-full bg-neutral-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email | MyVetPaws</title>
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
<body class="h-full flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
        <!-- Heading -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center p-3 bg-neutral-900 border border-neutral-800 rounded-2xl shadow-xl shadow-brand-500/10 mb-4">
                <!-- Inbox Icon -->
                <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l8-5.333a2 2 0 012.22 0l8 5.333A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold tracking-tight text-white">Check your email</h2>
            <p class="mt-2 text-sm text-neutral-400">
                We've sent a verification link to <span class="text-brand-100 font-semibold"><?= esc($email) ?></span>
            </p>
        </div>

        <!-- Simulated Sandbox Inbox Card -->
        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-2xl">
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-brand-500 mb-3">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                </span>
                <span>Sandbox Simulated Mailbox</span>
            </div>
            
            <p class="text-xs text-neutral-400 mb-4 leading-relaxed">
                In a production environment, an email is sent. For local sandbox testing, click the verification button below to activate the account.
            </p>

            <div class="bg-neutral-950 border border-neutral-800 rounded-2xl p-4 space-y-3">
                <div class="flex justify-between text-xs text-neutral-500">
                    <span>From: accounts@myvetpaws.com</span>
                    <span>Just now</span>
                </div>
                <div class="text-sm font-semibold text-white">
                    Confirm your registration on MyVetPaws
                </div>
                <div class="text-xs text-neutral-400 leading-relaxed border-t border-neutral-900 pt-3">
                    Hi there, thank you for registering your clinic with MyVetPaws. Please click the button below to verify your email address and set up your clinic workspace.
                </div>
                <div class="pt-2">
                    <a href="/register/verify?token=<?= esc($token) ?>" 
                       class="inline-flex w-full justify-center items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl transition duration-150 shadow-md shadow-brand-500/10">
                        Verify Email Address
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="/login" class="text-xs font-medium text-neutral-500 hover:text-neutral-300 transition-colors">
                Back to Sign In
            </a>
        </div>
    </div>
</body>
</html>
