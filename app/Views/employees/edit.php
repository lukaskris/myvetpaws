<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit Staff Details<?= $this->endSection() ?>

<?= $this->section('header') ?>Staff Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6" x-data="{ submitting: false }">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <a href="/employees" class="hover:text-white transition duration-150">Employees</a>
        <span>/</span>
        <span class="text-white font-medium">Edit Details</span>
    </div>

    <!-- Header block -->
    <div class="pb-6 border-b border-neutral-900">
        <h2 class="text-xl font-bold text-white tracking-tight">Edit Staff Member</h2>
        <p class="text-xs text-neutral-400 mt-1">Modify account details, phone extensions, or reset passwords for <?= esc($employee['name']) ?>.</p>
    </div>

    <!-- Alert notifications -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="p-4 bg-red-950/40 border border-red-500/30 rounded-2xl text-xs text-red-400 space-y-1 shadow-md">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <p>• <?= esc($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg">
        <form action="/employees/edit/<?= $employee['id'] ?>" method="POST" class="space-y-6" @submit="submitting = true">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Name -->
                <div class="sm:col-span-2">
                    <label for="inp-name" class="block text-xs font-semibold text-neutral-300">Full Name</label>
                    <input type="text" id="inp-name" name="name" required value="<?= old('name', $employee['name']) ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>

                <!-- Email -->
                <div>
                    <label for="inp-email" class="block text-xs font-semibold text-neutral-300">Email Address</label>
                    <input type="email" id="inp-email" name="email" required value="<?= old('email', $employee['email']) ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>

                <!-- Phone -->
                <div>
                    <label for="inp-phone" class="block text-xs font-semibold text-neutral-300">Phone Number</label>
                    <input type="tel" id="inp-phone" name="phone" value="<?= old('phone', $employee['phone']) ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition"
                        placeholder="e.g. +1 (555) 123-4567">
                </div>

                <!-- Role Selection -->
                <div>
                    <label for="inp-role" class="block text-xs font-semibold text-neutral-300">Designated Access Role</label>
                    <?php if ($employee['role'] === 'owner'): ?>
                        <!-- Lock primary owner role to prevent locking yourself out -->
                        <div class="mt-1.5 block w-full rounded-xl bg-neutral-950/60 border border-neutral-800 px-4 py-2.5 text-neutral-500 text-sm font-semibold cursor-not-allowed select-none">
                            Primary Owner (Locked)
                        </div>
                        <input type="hidden" name="role" value="owner">
                    <?php else: ?>
                        <select id="inp-role" name="role" required
                            class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                            <option value="doctor" <?= old('role', $employee['role']) == 'doctor' ? 'selected' : '' ?>>Doctor (Clinical Operations)</option>
                            <option value="receptionist" <?= old('role', $employee['role']) == 'receptionist' ? 'selected' : '' ?>>Receptionist (Front Desk)</option>
                            <option value="finance" <?= old('role', $employee['role']) == 'finance' ? 'selected' : '' ?>>Finance (Billing & Reports)</option>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div>
                    <label for="inp-password" class="block text-xs font-semibold text-neutral-300">Change Password</label>
                    <div class="mt-1.5 relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" id="inp-password" name="password"
                            class="block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition"
                            placeholder="Leave blank to keep current">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-neutral-500 hover:text-neutral-300">
                            <svg class="h-4 w-4" :class="show ? 'hidden' : 'block'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="h-4 w-4" :class="show ? 'block' : 'hidden'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 015.63-5.903m2.783-1.15A9.997 9.997 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-4.225-4.225L3 3m13.225 13.225L12 12m0 0l-1.077-1.077" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit Button Panel -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-neutral-800">
                <a href="/employees" class="px-4 py-2.5 border border-neutral-800 bg-transparent text-neutral-400 hover:text-white rounded-xl text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" :disabled="submitting"
                    class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-semibold transition shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 inline-flex items-center gap-1.5">
                    <span x-show="!submitting">Update Details</span>
                    <span x-show="submitting" class="flex items-center">
                        <svg class="animate-spin h-4 w-4 text-white mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Updating...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
