<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Register Pet | <?= esc($customer['name']) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>Add New Pet<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6" x-data="petForm">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <a href="/customers" class="hover:text-white transition duration-150">Customers</a>
        <span>/</span>
        <a href="/customers/show/<?= $customer['id'] ?>" class="hover:text-white transition duration-150"><?= esc($customer['name']) ?></a>
        <span>/</span>
        <span class="text-white font-medium">Add Pet</span>
    </div>

    <!-- Header Block -->
    <div class="pb-6 border-b border-neutral-900">
        <h2 class="text-xl font-bold text-white tracking-tight">Register Pet for <?= esc($customer['name']) ?></h2>
        <p class="text-xs text-neutral-400 mt-1">Add a new patient record linked to customer registry #<?= esc($customer['id']) ?>.</p>
    </div>

    <!-- Form block -->
    <form action="/pets/create" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-2xl" @submit="submitting = true">
        <?= csrf_field() ?>
        <input type="hidden" name="customer_id" value="<?= esc($customer['id']) ?>">

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
            
            <!-- Pet Photo Upload -->
            <div>
                <label class="block text-xs font-semibold text-neutral-300 mb-2">Pet Photo (Optional)</label>
                <div class="flex items-center space-x-5">
                    <!-- Preview Slot -->
                    <div class="relative h-16 w-16 bg-neutral-950 border border-neutral-800 rounded-2xl flex items-center justify-center overflow-hidden shrink-0">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" class="h-full w-full object-cover" alt="Pet preview">
                        </template>
                        <template x-if="!photoPreview">
                            <svg class="h-8 w-8 text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </template>
                    </div>
                    <!-- Upload Input wrapper -->
                    <div class="flex-1">
                        <label for="inp-photo" class="cursor-pointer px-4 py-2 border border-neutral-800 bg-neutral-950 text-neutral-300 hover:text-white rounded-xl text-xs font-semibold transition inline-block">
                            Choose Photo
                        </label>
                        <input type="file" id="inp-photo" name="photo" accept="image/*" class="sr-only" @change="previewPhoto">
                        <p class="text-[10px] text-neutral-500 mt-1.5">Supports JPG, PNG, GIF or WEBP. Max 2MB.</p>
                    </div>
                </div>
            </div>

            <hr class="border-neutral-950">

            <!-- Pet Basic Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="inp-name" class="block text-xs font-semibold text-neutral-300">Pet Name <span class="text-red-500">*</span></label>
                    <input type="text" id="inp-name" name="name" required value="<?= old('name') ?>" placeholder="e.g. Fluffy"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
                <div>
                    <label for="inp-gender" class="block text-xs font-semibold text-neutral-300">Gender</label>
                    <select id="inp-gender" name="gender"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                        <option value="Unknown" <?= old('gender') === 'Unknown' ? 'selected' : '' ?>>Unknown</option>
                        <option value="Male" <?= old('gender') === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= old('gender') === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Neutered Male" <?= old('gender') === 'Neutered Male' ? 'selected' : '' ?>>Neutered Male</option>
                        <option value="Spayed Female" <?= old('gender') === 'Spayed Female' ? 'selected' : '' ?>>Spayed Female</option>
                    </select>
                </div>
            </div>

            <!-- Species & Breed -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="inp-species" class="block text-xs font-semibold text-neutral-300">Species <span class="text-red-500">*</span></label>
                    <select id="inp-species" name="species" required
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                        <option value="" disabled selected>Select species</option>
                        <option value="Dog" <?= old('species') === 'Dog' ? 'selected' : '' ?>>Dog</option>
                        <option value="Cat" <?= old('species') === 'Cat' ? 'selected' : '' ?>>Cat</option>
                        <option value="Bird" <?= old('species') === 'Bird' ? 'selected' : '' ?>>Bird</option>
                        <option value="Rabbit" <?= old('species') === 'Rabbit' ? 'selected' : '' ?>>Rabbit</option>
                        <option value="Reptile" <?= old('species') === 'Reptile' ? 'selected' : '' ?>>Reptile</option>
                        <option value="Other" <?= old('species') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label for="inp-breed" class="block text-xs font-semibold text-neutral-300">Breed / Variety</label>
                    <input type="text" id="inp-breed" name="breed" value="<?= old('breed') ?>" placeholder="e.g. Persian or Golden Retriever"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <label for="inp-color" class="block text-xs font-semibold text-neutral-300">Color / Markings</label>
                    <input type="text" id="inp-color" name="color" value="<?= old('color') ?>" placeholder="e.g. Black & White"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
                <div>
                    <label for="inp-birth" class="block text-xs font-semibold text-neutral-300">Date of Birth</label>
                    <input type="date" id="inp-birth" name="birth_date" value="<?= old('birth_date') ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
                <div>
                    <label for="inp-vac" class="block text-xs font-semibold text-neutral-300">Last Vaccinated Date</label>
                    <input type="date" id="inp-vac" name="vaccinated_at" value="<?= old('vaccinated_at') ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="inp-notes" class="block text-xs font-semibold text-neutral-300">Medical Notes / Allergies / Remarks</label>
                <textarea id="inp-notes" name="notes" rows="3" placeholder="e.g. Allergic to penicillin. Prefers wet food."
                    class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition"><?= old('notes') ?></textarea>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-neutral-900">
            <a href="/customers/show/<?= $customer['id'] ?>" class="px-4 py-2.5 border border-neutral-800 bg-transparent text-neutral-400 hover:text-white rounded-xl text-xs font-semibold transition">
                Cancel
            </a>
            <button type="submit" :disabled="submitting"
                class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-semibold transition shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 inline-flex items-center gap-1.5">
                <span x-show="!submitting">Add Pet</span>
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
        Alpine.data('petForm', () => ({
            submitting: false,
            photoPreview: null,

            previewPhoto(event) {
                const file = event.target.files[0];
                if (file) {
                    this.photoPreview = URL.createObjectURL(file);
                }
            }
        }));
    });
</script>
<?= $this->endSection() ?>
