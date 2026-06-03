<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Examination: <?= esc($visit['pet_name']) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>Veterinary Examination<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Inline helper to calculate pet age
function getDetailedAge($birthDate) {
    if (empty($birthDate)) return 'Unknown age';
    try {
        $dob = new \DateTime($birthDate);
        $diff = $dob->diff(new \DateTime());
        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ($diff->m > 0 ? ' ' . $diff->m . ' month' . ($diff->m > 1 ? 's' : '') : '');
        }
        if ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
        }
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
    } catch (\Exception $e) {
        return 'Unknown age';
    }
}
?>
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <a href="/visits" class="hover:text-white transition duration-150">Visits</a>
        <span>/</span>
        <span class="text-white font-medium">Examine Patient</span>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Side: Patient & Check-in Vitals Summary -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Patient Specs -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg space-y-4">
                <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Patient Specifications</h4>
                
                <div class="flex items-center space-x-3 bg-neutral-950/60 p-4 rounded-2xl border border-neutral-850">
                    <div class="truncate">
                        <div class="text-base font-bold text-white leading-tight truncate"><?= esc($visit['pet_name']) ?></div>
                        <div class="text-xs text-neutral-400 mt-1">Owner: <span class="text-brand-400 font-semibold"><?= esc($visit['customer_name']) ?></span></div>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-neutral-850">
                        <span class="text-neutral-500">Species</span>
                        <span class="text-white font-semibold"><?= esc($visit['pet_species']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-neutral-850">
                        <span class="text-neutral-500">Breed</span>
                        <span class="text-white font-semibold"><?= esc($visit['pet_breed'] ?: '—') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-neutral-850">
                        <span class="text-neutral-500">Gender</span>
                        <span class="text-white font-semibold"><?= esc($visit['pet_gender'] ?: '—') ?></span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-neutral-500">Age</span>
                        <span class="text-white font-semibold"><?= getDetailedAge($visit['pet_birth_date']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Vitals Summary -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg space-y-4">
                <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Checked-in Vitals</h4>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-neutral-950 border border-neutral-850 p-4 rounded-2xl text-center">
                        <span class="text-neutral-500 block text-[10px] uppercase font-bold tracking-wider">Weight</span>
                        <span class="text-lg font-bold text-white mt-1 block">
                            <?= $visit['weight'] ? esc($visit['weight']) . ' <span class="text-xs font-medium text-neutral-400">kg</span>' : '—' ?>
                        </span>
                    </div>
                    <div class="bg-neutral-950 border border-neutral-850 p-4 rounded-2xl text-center">
                        <span class="text-neutral-500 block text-[10px] uppercase font-bold tracking-wider">Temperature</span>
                        <span class="text-lg font-bold text-white mt-1 block">
                            <?= $visit['temperature'] ? esc($visit['temperature']) . ' <span class="text-xs font-medium text-neutral-400">°C</span>' : '—' ?>
                        </span>
                    </div>
                </div>

                <div class="p-4 bg-neutral-950 border border-neutral-850 rounded-2xl">
                    <span class="text-neutral-400 block text-xs font-semibold mb-1">Chief Complaint</span>
                    <p class="text-xs text-neutral-300 leading-relaxed italic">
                        "<?= esc($visit['complaints'] ?: 'No symptoms specified upon check-in') ?>"
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side: Examination Write-up Form -->
        <div class="lg:col-span-2">
            <div class="bg-neutral-900 border border-neutral-800 p-8 rounded-3xl shadow-xl">
                <h2 class="text-xl font-bold text-white tracking-tight mb-2">Examination & Treatment Record</h2>
                <p class="text-xs text-neutral-400 mb-6">Complete clinical findings, prescribe treatment plans, and record services rendered.</p>

                <!-- Display validation errors -->
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="mb-6 p-4 bg-red-950/40 border border-red-500/20 rounded-2xl text-red-300 text-xs space-y-1">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <p><?= esc($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="/visits/examine/<?= $visit['id'] ?>" method="POST" class="space-y-6">
                    <?= csrf_field() ?>

                    <!-- Diagnosis -->
                    <div>
                        <label for="diagnosis" class="block text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-2">Clinical Diagnosis</label>
                        <textarea name="diagnosis" id="diagnosis" rows="4" required placeholder="Write clinical diagnosis findings..."
                                  class="w-full bg-neutral-950 border border-neutral-850 focus:border-brand-500 rounded-xl px-4 py-3 text-sm text-white focus:outline-none transition duration-150 resize-none"><?= old('diagnosis') ?></textarea>
                    </div>

                    <!-- Treatment Plan -->
                    <div>
                        <label for="treatment_plan" class="block text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-2">Treatment Plan / Prescription</label>
                        <textarea name="treatment_plan" id="treatment_plan" rows="4" required placeholder="Write medication plan, prescription details, rest instructions..."
                                  class="w-full bg-neutral-950 border border-neutral-850 focus:border-brand-500 rounded-xl px-4 py-3 text-sm text-white focus:outline-none transition duration-150 resize-none"><?= old('treatment_plan') ?></textarea>
                    </div>

                    <!-- Services Rendered -->
                    <div>
                        <label class="block text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-3">Services Rendered & Billing Items</label>
                        
                        <?php if (empty($services)): ?>
                            <div class="p-4 bg-neutral-950 border border-neutral-850 rounded-2xl text-center">
                                <p class="text-xs text-neutral-500">No services or medical items have been defined in this clinic yet.</p>
                                <a href="/services/create" target="_blank" class="text-xs text-brand-500 font-semibold mt-1 inline-block">Define Catalog Items &rarr;</a>
                            </div>
                        <?php else: ?>
                            <div x-data="{
                                searchQuery: '',
                                activeCategory: 'All',
                                categories: ['All', 'Consultation', 'Vaccination', 'Surgery', 'Grooming', 'Laboratory Test', 'Medicine', 'Supplies'],
                                matches(name, code, category) {
                                    const matchSearch = name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                                        code.toLowerCase().includes(this.searchQuery.toLowerCase());
                                    const matchCategory = this.activeCategory === 'All' || category === this.activeCategory;
                                    return matchSearch && matchCategory;
                                }
                            }" class="space-y-4">
                                
                                <!-- Search and Category Filters -->
                                <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                                    <!-- Category Chips -->
                                    <div class="flex flex-wrap gap-1.5 self-start">
                                        <template x-for="cat in categories">
                                            <button type="button" @click="activeCategory = cat"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition duration-150 cursor-pointer"
                                                :class="activeCategory === cat ? 'bg-neutral-800 border-neutral-750 text-white shadow-inner' : 'bg-transparent border-neutral-850 text-neutral-400 hover:text-white hover:border-neutral-750'">
                                                <span x-text="cat"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <!-- Search Input -->
                                    <div class="relative w-full sm:w-64">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-neutral-500">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </span>
                                        <input type="text" x-model="searchQuery" placeholder="Search item or code..."
                                            class="block w-full rounded-xl bg-neutral-950 border border-neutral-850 pl-9 pr-3 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-xs transition">
                                    </div>
                                </div>

                                <!-- Grid List -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($services as $service): ?>
                                        <div class="p-4 bg-neutral-950 border border-neutral-850 rounded-2xl flex items-center justify-between gap-4" 
                                             x-data="{ checked: false }" 
                                             x-show="matches(<?= esc(json_encode($service['name'])) ?>, <?= esc(json_encode($service['code'])) ?>, <?= esc(json_encode($service['category'])) ?>)"
                                             x-transition.opacity.duration.150ms>
                                            <div class="flex items-center space-x-3 min-w-0">
                                                <input type="checkbox" name="services[]" value="<?= $service['id'] ?>" x-model="checked" id="service-<?= $service['id'] ?>"
                                                       class="h-4.5 w-4.5 rounded border-neutral-800 text-brand-600 bg-neutral-950 focus:ring-brand-500 cursor-pointer">
                                                <label for="service-<?= $service['id'] ?>" class="cursor-pointer select-none min-w-0">
                                                    <span class="text-sm font-semibold text-white block truncate"><?= esc($service['name']) ?></span>
                                                    <span class="text-xs text-neutral-400 block mt-0.5">
                                                        Rp<?= number_format($service['price'], 0, ',', '.') ?> (<?= esc($service['code']) ?>) • <span class="text-brand-400 font-semibold"><?= esc($service['category']) ?></span>
                                                    </span>
                                                </label>
                                            </div>
                                            <!-- Quantity field (visible when checked) -->
                                            <div x-show="checked" x-transition class="flex items-center gap-2 shrink-0">
                                                <label class="text-[10px] uppercase font-bold text-neutral-500">Qty</label>
                                                <input type="number" name="quantities[<?= $service['id'] ?>]" min="1" value="1"
                                                       class="w-16 bg-neutral-900 border border-neutral-800 focus:border-brand-500 rounded-lg px-2 py-1 text-xs text-center focus:outline-none">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Next Visit Date -->
                    <div class="max-w-xs">
                        <label for="next_visit_at" class="block text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-2">Schedule Next Visit (Optional)</label>
                        <input type="date" name="next_visit_at" id="next_visit_at" value="<?= old('next_visit_at') ?>" min="<?= date('Y-m-d') ?>"
                               class="w-full bg-neutral-950 border border-neutral-850 focus:border-brand-500 rounded-xl px-4 py-3 text-sm text-white focus:outline-none transition duration-150">
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 border-t border-neutral-800 pt-6 mt-4">
                        <a href="/visits" class="px-5 py-2.5 bg-neutral-950 border border-neutral-800 hover:border-neutral-700 text-neutral-300 hover:text-white rounded-xl text-sm font-semibold transition">
                            Back to Queue
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-semibold shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 transition">
                            Save Examination Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
