<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Patient Check-in<?= $this->endSection() ?>

<?= $this->section('header') ?>Patient Check-in<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2.5 text-xs text-slate-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-60"></i>
        <a href="/visits" class="hover:text-white transition duration-150">Visits</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-60"></i>
        <span class="text-white font-semibold">New Check-in</span>
    </div>

    <!-- Form Container (Glassmorphic) -->
    <div class="glass-panel p-8 rounded-3xl shadow-xl" 
         x-data="{
             customerId: '<?= esc($preselectedCustomerId ?? '') ?>',
             selectedPets: [],
             allPets: <?= htmlspecialchars(json_encode($pets), ENT_QUOTES, 'UTF-8') ?>,
             
             init() {
                 this.resetPets();
                 this.$nextTick(() => { lucide.createIcons(); });
             },
             
             resetPets() {
                 this.selectedPets = [
                     { id: Date.now(), petId: '', weight: '', temperature: '', complaints: '' }
                 ];
                 this.$nextTick(() => { lucide.createIcons(); });
             },
             
             get filteredPets() {
                 if (!this.customerId) return [];
                 return this.allPets.filter(p => p.customer_id == this.customerId);
             },
             
             getAvailablePets(currentPetId) {
                 const selectedIds = this.selectedPets.map(p => p.petId).filter(id => id && id.toString() !== currentPetId.toString());
                 return this.filteredPets.filter(p => !selectedIds.includes(p.id.toString()));
             },
             
             addPet() {
                 if (this.selectedPets.length < this.filteredPets.length) {
                     this.selectedPets.push({
                         id: Date.now() + Math.random(),
                         petId: '',
                         weight: '',
                         temperature: '',
                         complaints: ''
                     });
                     this.$nextTick(() => { lucide.createIcons(); });
                 }
             },
             
             removePet(index) {
                 if (this.selectedPets.length > 1) {
                     this.selectedPets.splice(index, 1);
                 }
             }
         }">
        <h2 class="text-xl font-bold text-white tracking-tight mb-1.5 flex items-center gap-2">
            <i data-lucide="clipboard-list" class="w-5 h-5 text-brand-500"></i>
            <span>Register Patient Check-in</span>
        </h2>
        <p class="text-xs text-slate-400 mb-6">Select a customer, choose their registered pet, and record complaints and initial vitals.</p>

        <!-- Display validation errors -->
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="mb-6 p-4 bg-rose-950/40 border border-neon-pink/30 rounded-2xl text-rose-300 text-xs space-y-1.5 shadow-lg">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <p class="flex items-center gap-2 font-medium">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-neon-pink shrink-0"></i>
                        <span><?= esc($error) ?></span>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="/visits/create" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Check-in Date & Time -->
            <div>
                <label for="checkin_time" class="block text-xs font-bold text-slate-450 uppercase tracking-wider mb-2">Check-in Date & Time</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <input type="datetime-local" name="checkin_time" id="checkin_time" required
                           value="<?= esc($defaultCheckinTime) ?>"
                           class="w-full bg-obsidian-950/60 border border-obsidian-800/80 focus:border-brand-600 rounded-xl pl-10 pr-4 py-3 text-sm text-white focus:outline-none transition duration-200">
                </div>
            </div>

            <!-- Customer Selection -->
            <div>
                <label for="customer_id" class="block text-xs font-bold text-slate-450 uppercase tracking-wider mb-2">Customer / Owner</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <select name="customer_id" id="customer_id" x-model="customerId" @change="resetPets()" required
                            class="w-full bg-obsidian-950/60 border border-obsidian-800/80 focus:border-brand-600 rounded-xl pl-10 pr-4 py-3 text-sm text-white focus:outline-none transition duration-200">
                        <option value="">-- Select Customer --</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>"><?= esc($customer['name']) ?> (<?= esc($customer['phone'] ?: 'No Phone') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Pet Selection dynamic list -->
            <template x-if="customerId && filteredPets.length > 0">
                <div class="space-y-5">
                    <template x-for="(item, index) in selectedPets" :key="item.id">
                        <div class="p-6 border border-obsidian-800/80 bg-obsidian-950/40 rounded-2xl space-y-5 relative">
                            <!-- Header & Remove Button -->
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-brand-400 uppercase tracking-wider" x-text="`Patient #${index + 1}`"></h3>
                                <button type="button" @click="removePet(index)" x-show="selectedPets.length > 1"
                                        class="text-xs font-semibold text-red-500 hover:text-red-400 dark:text-neon-pink dark:hover:text-pink-400 transition cursor-pointer flex items-center gap-1.5 bg-transparent border-0">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Remove</span>
                                </button>
                            </div>

                            <!-- Pet Selection Dropdown -->
                            <div>
                                <label :for="'pet_id_' + index" class="block text-xs font-bold text-slate-450 uppercase tracking-wider mb-2">Pet / Patient</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                        <i data-lucide="paw-print" class="w-4 h-4"></i>
                                    </div>
                                    <select :name="'visits[' + index + '][pet_id]'" :id="'pet_id_' + index" x-model="item.petId" required
                                            class="w-full bg-obsidian-950/60 border border-obsidian-800/80 focus:border-brand-600 rounded-xl pl-10 pr-4 py-3 text-sm text-white focus:outline-none transition duration-200">
                                        <option value="">-- Select Pet --</option>
                                        <template x-for="pet in getAvailablePets(item.petId)" :key="pet.id">
                                            <option :value="pet.id" x-text="`${pet.name} (${pet.species}${pet.breed ? ' - ' + pet.breed : ''})`"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <!-- Vitals Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <!-- Weight -->
                                <div>
                                    <label :for="'weight_' + index" class="block text-xs font-bold text-slate-450 uppercase tracking-wider mb-2">Weight (kg)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                            <i data-lucide="scale" class="w-4 h-4"></i>
                                        </div>
                                        <input type="number" :name="'visits[' + index + '][weight]'" :id="'weight_' + index" step="0.01" min="0" placeholder="0.00" x-model="item.weight"
                                               class="w-full bg-obsidian-950/60 border border-obsidian-800/80 focus:border-brand-600 rounded-xl pl-10 pr-12 py-3 text-sm text-white focus:outline-none transition duration-200">
                                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-500 text-xs font-bold">
                                            kg
                                        </div>
                                    </div>
                                </div>

                                <!-- Temperature -->
                                <div>
                                    <label :for="'temperature_' + index" class="block text-xs font-bold text-slate-450 uppercase tracking-wider mb-2">Temperature (°C)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                            <i data-lucide="thermometer" class="w-4 h-4"></i>
                                        </div>
                                        <input type="number" :name="'visits[' + index + '][temperature]'" :id="'temperature_' + index" step="0.1" min="0" placeholder="0.0" x-model="item.temperature"
                                               class="w-full bg-obsidian-950/60 border border-obsidian-800/80 focus:border-brand-600 rounded-xl pl-10 pr-12 py-3 text-sm text-white focus:outline-none transition duration-200">
                                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-500 text-xs font-bold">
                                            °C
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chief Complaints -->
                            <div>
                                <label :for="'complaints_' + index" class="block text-xs font-bold text-slate-450 uppercase tracking-wider mb-2">Chief Complaints / Symptom Notes</label>
                                <div class="relative">
                                    <textarea :name="'visits[' + index + '][complaints]'" :id="'complaints_' + index" rows="3" placeholder="Briefly describe the symptoms, concerns, or reason for visit..." x-model="item.complaints"
                                              class="w-full bg-obsidian-950/60 border border-obsidian-800/80 focus:border-brand-600 rounded-xl px-4 py-3 text-sm text-white focus:outline-none transition duration-200 resize-none"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- No pets placeholder -->
            <template x-if="customerId && filteredPets.length === 0">
                <div class="p-6 border border-obsidian-800 bg-obsidian-950/40 rounded-2xl text-center space-y-3">
                    <p class="text-xs text-slate-400">No pets are currently registered under this customer.</p>
                    <a :href="`/pets/create/${customerId}`" class="px-4 py-2 border border-brand-500/30 bg-brand-500/10 hover:bg-brand-500/20 text-brand-400 hover:text-brand-300 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 transition-premium">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Register First Pet</span>
                    </a>
                </div>
            </template>

            <!-- Action buttons for patients list -->
            <template x-if="customerId && filteredPets.length > 0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                    <button type="button" @click="addPet" :disabled="selectedPets.length >= filteredPets.length"
                            class="px-4 py-2.5 border border-brand-500/30 bg-brand-500/10 hover:bg-brand-500/20 text-brand-400 hover:text-brand-300 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl text-xs font-bold transition-premium flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add Another Pet to Check-in</span>
                    </button>
                    
                    <a :href="`/pets/create/${customerId}`" class="text-xs text-brand-500 hover:text-brand-400 font-bold transition-colors inline-flex items-center gap-1.5 self-center">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                        <span>Register new pet to system</span>
                    </a>
                </div>
            </template>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 border-t border-obsidian-800/80 pt-6 mt-4">
                <a href="/visits" class="px-5 py-2.5 bg-obsidian-900 border border-obsidian-800 hover:border-obsidian-700 text-slate-300 hover:text-white rounded-xl text-xs font-bold transition-premium">
                    Cancel
                </a>
                <button type="submit" :disabled="!customerId || filteredPets.length === 0" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-600/10 hover:shadow-brand-500/20 hover:scale-[1.02] active:scale-[0.98] transition-premium disabled:opacity-50 disabled:cursor-not-allowed">
                    Complete Check-in
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
<?= $this->endSection() ?>
