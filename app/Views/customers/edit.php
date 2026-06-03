<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit Customer<?= $this->endSection() ?>

<?= $this->section('header') ?>Edit Customer<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6" x-data="customerForm">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <a href="/customers" class="hover:text-white transition duration-150">Customers</a>
        <span>/</span>
        <a href="/customers/show/<?= $customer['id'] ?>" class="hover:text-white transition duration-150"><?= esc($customer['name']) ?></a>
        <span>/</span>
        <span class="text-white font-medium">Edit</span>
    </div>

    <!-- Header Block -->
    <div class="pb-6 border-b border-neutral-900">
        <h2 class="text-xl font-bold text-white tracking-tight">Edit Customer Profile</h2>
        <p class="text-xs text-neutral-400 mt-1">Modify information for customer registry #<?= esc($customer['id']) ?>.</p>
    </div>

    <!-- Form block -->
    <form action="/customers/edit/<?= $customer['id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-2xl" @submit="submitting = true">
        <?= csrf_field() ?>

        <!-- Error Panel -->
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="p-4 bg-red-950/40 border border-red-500/20 rounded-2xl text-red-300 text-xs space-y-1">
                <div class="font-semibold text-sm">Please correct the following errors:</div>
                <ul class="list-disc pl-5 space-y-0.5">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl space-y-6 shadow-md">
            <!-- Title & Name -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label for="inp-title" class="block text-xs font-semibold text-neutral-300">Title</label>
                    <select id="inp-title" name="title"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                        <option value="">None</option>
                        <option value="Mr." <?= (old('title') ?? $customer['title']) === 'Mr.' ? 'selected' : '' ?>>Mr.</option>
                        <option value="Mrs." <?= (old('title') ?? $customer['title']) === 'Mrs.' ? 'selected' : '' ?>>Mrs.</option>
                        <option value="Ms." <?= (old('title') ?? $customer['title']) === 'Ms.' ? 'selected' : '' ?>>Ms.</option>
                        <option value="Dr." <?= (old('title') ?? $customer['title']) === 'Dr.' ? 'selected' : '' ?>>Dr.</option>
                        <option value="Prof." <?= (old('title') ?? $customer['title']) === 'Prof.' ? 'selected' : '' ?>>Prof.</option>
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <label for="inp-name" class="block text-xs font-semibold text-neutral-300">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="inp-name" name="name" required value="<?= esc(old('name') ?? $customer['name']) ?>" placeholder="e.g. John Doe"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
            </div>

            <!-- Profile Picture Upload -->
            <div>
                <label class="block text-xs font-semibold text-neutral-300 mb-2">Profile Picture</label>
                <div class="flex items-center space-x-5">
                    <!-- Preview Slot -->
                    <div class="relative h-16 w-16 bg-neutral-950 border border-neutral-800 rounded-full flex items-center justify-center overflow-hidden shrink-0">
                        <template x-if="profilePicPreview">
                            <img :src="profilePicPreview" class="h-full w-full object-cover" alt="Profile preview">
                        </template>
                        <template x-if="!profilePicPreview">
                            <?php if (!empty($customer['profile_picture']) && file_exists(FCPATH . $customer['profile_picture'])): ?>
                                <img src="/<?= esc($customer['profile_picture']) ?>" class="h-full w-full object-cover" alt="<?= esc($customer['name']) ?>">
                            <?php else: ?>
                                <svg class="h-8 w-8 text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            <?php endif; ?>
                        </template>
                    </div>
                    <!-- Upload Input wrapper -->
                    <div class="flex-1">
                        <label for="inp-pic" class="cursor-pointer px-4 py-2 border border-neutral-800 bg-neutral-950 text-neutral-300 hover:text-white rounded-xl text-xs font-semibold transition inline-block">
                            Change Image
                        </label>
                        <input type="file" id="inp-pic" name="profile_picture" accept="image/*" class="sr-only" @change="previewPic">
                        <p class="text-[10px] text-neutral-500 mt-1.5">Supports JPG, PNG, GIF or WEBP. Max 2MB.</p>
                    </div>
                </div>
            </div>

            <hr class="border-neutral-950">

            <!-- Contact Information -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="inp-phone" class="block text-xs font-semibold text-neutral-300">Phone Number</label>
                    <input type="text" id="inp-phone" name="phone" value="<?= esc(old('phone') ?? $customer['phone']) ?>" placeholder="e.g. +628123456789"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
                <div>
                    <label for="inp-email" class="block text-xs font-semibold text-neutral-300">Email Address</label>
                    <input type="email" id="inp-email" name="email" value="<?= esc(old('email') ?? $customer['email']) ?>" placeholder="e.g. customer@example.com"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
            </div>

            <!-- Address -->
            <div>
                <label for="inp-address" class="block text-xs font-semibold text-neutral-300">Residential Address</label>
                <input type="text" id="inp-address" name="address" value="<?= esc(old('address') ?? $customer['address']) ?>" placeholder="e.g. Jl. Kemang Raya No. 45"
                    class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-neutral-900">
            <a href="/customers/show/<?= $customer['id'] ?>" class="px-4 py-2.5 border border-neutral-800 bg-transparent text-neutral-400 hover:text-white rounded-xl text-xs font-semibold transition">
                Discard Changes
            </a>
            <button type="submit" :disabled="submitting"
                class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-semibold transition shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 inline-flex items-center gap-1.5">
                <span x-show="!submitting">Save Profile</span>
                <span x-show="submitting" class="flex items-center">
                    <svg class="animate-spin h-4 w-4 text-white mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Saving...
                </span>
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('customerForm', () => ({
            submitting: false,
            profilePicPreview: null,

            previewPic(event) {
                const file = event.target.files[0];
                if (file) {
                    this.profilePicPreview = URL.createObjectURL(file);
                }
            }
        }));
    });
</script>
<?= $this->endSection() ?>
