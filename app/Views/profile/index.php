<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Clinic Profile Settings<?= $this->endSection() ?>

<?= $this->section('header') ?>Clinic Settings<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Leaflet.js Assets for Interactive Pin Point Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div class="max-w-4xl mx-auto space-y-6" x-data="profileForm" x-init="initMap()">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <span class="text-white font-medium">Settings</span>
    </div>

    <!-- Page Title Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-neutral-900 gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Clinic Profile</h2>
            <p class="text-xs text-neutral-400 mt-1">Configure your clinic's business coordinates, branding assets, and marketplace presence.</p>
        </div>
    </div>

    <!-- Alert notifications -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="p-4 bg-red-950/40 border border-red-500/30 rounded-2xl text-xs text-red-400 space-y-1 shadow-md">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <p>• <?= esc($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Profile Form -->
    <form action="/profile" method="POST" enctype="multipart/form-data" class="space-y-8" @submit="submitting = true">
        <?= csrf_field() ?>

        <!-- Section 1: Branding Assets -->
        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl space-y-6 shadow-md">
            <div>
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-brand-500">Clinic Branding</h3>
                <p class="text-xs text-neutral-400 mt-1">Upload files for your workspace logo and public listing banner.</p>
            </div>

            <!-- Banner Upload slot -->
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-neutral-300">Listing Banner Image</label>
                <div class="relative h-48 w-full rounded-2xl border border-neutral-800 bg-neutral-950 overflow-hidden flex items-center justify-center group">
                    <template x-if="bannerPreview">
                        <img :src="bannerPreview" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" alt="Banner Preview">
                    </template>
                    <template x-if="!bannerPreview && hasBanner">
                        <img :src="bannerUrl" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" alt="Current Banner">
                    </template>
                    <template x-if="!bannerPreview && !hasBanner">
                        <div class="text-center p-4">
                            <svg class="mx-auto h-8 w-8 text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="mt-2 block text-[10px] text-neutral-500">Recommend 1200x400px (Max 5MB)</span>
                        </div>
                    </template>

                    <!-- File input overlays -->
                    <div class="absolute inset-0 bg-neutral-950/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition duration-200">
                        <label for="inp-banner" class="cursor-pointer px-4 py-2 bg-neutral-900 border border-neutral-800 hover:border-neutral-700 text-white rounded-xl text-xs font-semibold shadow-md">
                            Change Banner
                        </label>
                        <input type="file" id="inp-banner" name="banner" accept="image/*" class="hidden" @change="previewBanner">
                    </div>
                </div>
            </div>

            <!-- Logo Upload and General Row -->
            <div class="flex flex-col md:flex-row gap-6 items-start">
                <!-- Logo Upload Box -->
                <div class="space-y-2 shrink-0">
                    <label class="block text-xs font-semibold text-neutral-300">Clinic Logo</label>
                    <div class="relative h-28 w-28 rounded-2xl border border-neutral-800 bg-neutral-950 overflow-hidden flex items-center justify-center group">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" class="h-full w-full object-cover" alt="Logo Preview">
                        </template>
                        <template x-if="!logoPreview && hasLogo">
                            <img :src="logoUrl" class="h-full w-full object-cover" alt="Current Logo">
                        </template>
                        <template x-if="!logoPreview && !hasLogo">
                            <svg class="h-8 w-8 text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </template>

                        <div class="absolute inset-0 bg-neutral-950/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition duration-200">
                            <label for="inp-logo" class="cursor-pointer p-1.5 bg-neutral-900 border border-neutral-800 rounded-lg text-white">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </label>
                            <input type="file" id="inp-logo" name="logo" accept="image/*" class="hidden" @change="previewLogo">
                        </div>
                    </div>
                </div>

                <!-- Basic Profile Information -->
                <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="inp-name" class="block text-xs font-semibold text-neutral-300">Clinic Name</label>
                        <input type="text" id="inp-name" name="name" required value="<?= old('name', $clinic['name']) ?>"
                            class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                    </div>
                    <div>
                        <label for="inp-phone" class="block text-xs font-semibold text-neutral-300">Contact Phone</label>
                        <input type="text" id="inp-phone" name="phone" required value="<?= old('phone', $clinic['phone']) ?>"
                            class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                    </div>
                    <div>
                        <label for="inp-email" class="block text-xs font-semibold text-neutral-300">Contact Email</label>
                        <input type="email" id="inp-email" name="email" required value="<?= old('email', $clinic['email']) ?>"
                            class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="inp-desc" class="block text-xs font-semibold text-neutral-300">Description</label>
                <textarea id="inp-desc" name="description" rows="3"
                    class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition"
                    placeholder="Provide a brief summary detailing your clinic, opening hours, or specializations."><?= old('description', $clinic['description']) ?></textarea>
            </div>
        </div>

        <!-- Section 2: Location Details -->
        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl space-y-5 shadow-md">
            <div>
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-brand-500">Location Settings</h3>
                <p class="text-xs text-neutral-400 mt-1">Specify address coordinates for listing displays and map lookup.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-3">
                    <label for="inp-address" class="block text-xs font-semibold text-neutral-300">Street Address</label>
                    <input type="text" id="inp-address" name="address" value="<?= old('address', $clinic['address']) ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
                <div>
                    <label for="inp-city" class="block text-xs font-semibold text-neutral-300">City</label>
                    <input type="text" id="inp-city" name="city" value="<?= old('city', $clinic['city']) ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
                <div>
                    <label for="inp-province" class="block text-xs font-semibold text-neutral-300">Province / Region</label>
                    <input type="text" id="inp-province" name="province" value="<?= old('province', $clinic['province']) ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                </div>
                <div class="grid grid-cols-2 gap-2 sm:col-span-1">
                    <div>
                        <label for="inp-lat" class="block text-[10px] font-semibold text-neutral-400">Latitude</label>
                        <input type="text" id="inp-lat" name="latitude" placeholder="e.g. -6.2000" value="<?= old('latitude', $clinic['latitude']) ?>"
                            class="mt-1 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-3 py-2 text-white placeholder-neutral-600 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-xs transition">
                    </div>
                    <div>
                        <label for="inp-lng" class="block text-[10px] font-semibold text-neutral-400">Longitude</label>
                        <input type="text" id="inp-lng" name="longitude" placeholder="e.g. 106.8166" value="<?= old('longitude', $clinic['longitude']) ?>"
                            class="mt-1 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-3 py-2 text-white placeholder-neutral-600 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-xs transition">
                    </div>
                </div>
            </div>

            <!-- Interactive Map Selector -->
            <div class="space-y-3 pt-4 border-t border-neutral-800">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-300">Map Location (Pin Point)</label>
                        <p class="text-[10px] text-neutral-500">Search address below or drag the map pin to update coordinates automatically.</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <input type="text" id="map-search" placeholder="Type address (e.g. Monas, Jakarta) and press search..." @keydown.enter.prevent="searchMapAddress()"
                           class="flex-1 rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-xs transition">
                    <button type="button" @click="searchMapAddress()" 
                            class="px-4 py-2.5 bg-neutral-800 hover:bg-neutral-700 text-white rounded-xl text-xs font-semibold transition border border-neutral-700 inline-flex items-center gap-1.5 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Search</span>
                    </button>
                </div>
                <!-- Map Container -->
                <div id="clinic-map" class="h-64 w-full rounded-2xl border border-neutral-800 bg-neutral-950 overflow-hidden relative z-10"></div>
            </div>
        </div>

        <!-- Section 3: Marketplace & Visibility -->
        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl space-y-5 shadow-md">
            <div>
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-brand-500">Marketplace Settings</h3>
                <p class="text-xs text-neutral-400 mt-1">Configure parameters for how your clinic is listed publicly.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 items-end">
                <div class="sm:col-span-2">
                    <label for="inp-slug" class="block text-xs font-semibold text-neutral-300">Public Page Slug</label>
                    <div class="mt-1.5 flex rounded-xl bg-neutral-950 border border-neutral-800 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 overflow-hidden">
                        <span class="bg-neutral-900 text-neutral-500 text-xs px-3 py-2.5 flex items-center border-r border-neutral-800 font-medium">
                            myvetpaws.com/clinic/
                        </span>
                        <input type="text" id="inp-slug" name="slug" required value="<?= old('slug', $clinic['slug']) ?>"
                            class="flex-1 bg-transparent px-4 py-2.5 text-white placeholder-neutral-600 outline-none text-sm">
                    </div>
                </div>

                <!-- Visibility Toggle Switch -->
                <div class="p-3 border border-neutral-800 bg-neutral-950/40 rounded-xl flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-white">Public Visibility</div>
                        <div class="text-[10px] text-neutral-500">List clinic in directory search</div>
                    </div>
                    <label for="inp-visibility" class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" id="inp-visibility" name="public_visibility" value="1" class="sr-only peer" <?= old('public_visibility', $clinic['public_visibility']) == 1 ? 'checked' : '' ?>>
                        <div class="w-9 h-5 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-neutral-400 after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600 peer-checked:after:bg-white peer-checked:after:border-brand-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Submit Panel -->
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-neutral-900">
            <a href="/dashboard" class="px-4 py-2.5 border border-neutral-800 bg-transparent text-neutral-400 hover:text-white rounded-xl text-xs font-semibold transition">
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
        Alpine.data('profileForm', () => ({
            submitting: false,
            logoPreview: null,
            bannerPreview: null,
            hasBanner: <?= !empty($clinic['banner']) ? 'true' : 'false' ?>,
            hasLogo: <?= !empty($clinic['logo']) ? 'true' : 'false' ?>,
            bannerUrl: '<?= !empty($clinic['banner']) ? base_url(esc($clinic['banner'])) : '' ?>',
            logoUrl: '<?= !empty($clinic['logo']) ? base_url(esc($clinic['logo'])) : '' ?>',
            map: null,
            marker: null,

            previewLogo(event) {
                const file = event.target.files[0];
                if (file) {
                    this.logoPreview = URL.createObjectURL(file);
                }
            },
            previewBanner(event) {
                const file = event.target.files[0];
                if (file) {
                    this.bannerPreview = URL.createObjectURL(file);
                }
            },

            initMap() {
                // Fix default Leaflet icon paths (prevents 404 errors for images)
                delete L.Icon.Default.prototype._getIconUrl;
                L.Icon.Default.mergeOptions({
                    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
                    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                });

                let latInput = document.getElementById('inp-lat');
                let lngInput = document.getElementById('inp-lng');

                let latVal = parseFloat(latInput.value) || -6.2000;
                let lngVal = parseFloat(lngInput.value) || 106.8166;

                // Initialize Leaflet map
                this.map = L.map('clinic-map').setView([latVal, lngVal], 13);

                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(this.map);

                // Add draggable marker
                this.marker = L.marker([latVal, lngVal], { draggable: true }).addTo(this.map);

                // Marker drag event -> update inputs
                this.marker.on('dragend', () => {
                    const position = this.marker.getLatLng();
                    latInput.value = position.lat.toFixed(6);
                    lngInput.value = position.lng.toFixed(6);
                });

                // Sync manual coordinates inputs -> updates map marker
                const syncInputsToMap = () => {
                    let lat = parseFloat(latInput.value);
                    let lng = parseFloat(lngInput.value);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const newLatLng = new L.LatLng(lat, lng);
                        this.marker.setLatLng(newLatLng);
                        this.map.panTo(newLatLng);
                    }
                };

                latInput.addEventListener('input', syncInputsToMap);
                lngInput.addEventListener('input', syncInputsToMap);
            },

            searchMapAddress() {
                const searchInput = document.getElementById('map-search');
                const query = searchInput.value.trim();
                if (!query) return;

                fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const result = data[0];
                            const lat = parseFloat(result.lat);
                            const lng = parseFloat(result.lon);

                            // Update input fields
                            document.getElementById('inp-lat').value = lat.toFixed(6);
                            document.getElementById('inp-lng').value = lng.toFixed(6);

                            // Relocate map view and marker
                            const newLatLng = new L.LatLng(lat, lng);
                            this.marker.setLatLng(newLatLng);
                            this.map.setView(newLatLng, 16);
                        } else {
                            alert('Location not found. Please try another address name.');
                        }
                    })
                    .catch(err => {
                        console.error('Nominatim search error:', err);
                        alert('Could not search the address. Please check your internet connection.');
                    });
            }
        }));
    });
</script>
<?= $this->endSection() ?>
