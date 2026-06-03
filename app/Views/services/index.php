<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Service Management<?= $this->endSection() ?>

<?= $this->section('header') ?>Services<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6" x-data="serviceManager">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <span class="text-white font-medium">Services</span>
    </div>

    <!-- Header Block -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-neutral-900 gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Clinic Services</h2>
            <p class="text-xs text-neutral-400 mt-1">Configure and manage clinical services, pricing templates, and operation codes.</p>
        </div>
        <a href="/services/create" id="btn-create-service" class="px-3.5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 transition duration-150 inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Add Service</span>
        </a>
    </div>

    <!-- Filters and Search Toolbar -->
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- Category Filter Chips -->
        <div class="flex flex-wrap gap-1.5 self-start">
            <template x-for="cat in categories">
                <button @click="activeCategory = cat"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition duration-150"
                    :class="activeCategory === cat ? 'bg-neutral-800 border-neutral-700 text-white shadow-inner' : 'bg-transparent border-neutral-900 text-neutral-400 hover:text-white hover:border-neutral-800'">
                    <span x-text="cat"></span>
                </button>
            </template>
        </div>

        <!-- Search box -->
        <div class="relative w-full md:w-72">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-neutral-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" x-model="searchQuery" placeholder="Search service name or code..."
                class="block w-full rounded-xl bg-neutral-900 border border-neutral-850 pl-10 pr-4 py-2 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-xs transition">
        </div>
    </div>

    <!-- Services Table Card -->
    <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-neutral-800 bg-neutral-950/40 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Service Name</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/60 text-sm">
                    <!-- Dynamic rendering in Alpine JS -->
                    <template x-for="service in services" :key="service.id">
                        <tr x-show="matches(service)" x-transition.opacity.duration.150ms class="hover:bg-neutral-850/30 transition duration-150">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-neutral-400">
                                <span class="bg-neutral-950 px-2 py-1 border border-neutral-850 rounded-lg" x-text="service.code"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-white" x-text="service.name"></div>
                                <div class="text-xs text-neutral-500 truncate max-w-xs mt-0.5" x-text="service.description || 'No description provided.'"></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-neutral-950 border border-neutral-850 text-neutral-300" x-text="service.category"></span>
                            </td>
                            <td class="px-6 py-4 font-bold text-white">
                                Rp<span x-text="formatPrice(service.price)"></span>
                            </td>
                            <td class="px-6 py-4">
                                <template x-if="service.status == 1">
                                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400 font-semibold">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        Active
                                    </span>
                                </template>
                                <template x-if="service.status == 0">
                                    <span class="inline-flex items-center gap-1.5 text-xs text-neutral-500 font-semibold">
                                        <span class="h-1.5 w-1.5 rounded-full bg-neutral-600"></span>
                                        Inactive
                                    </span>
                                </template>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a :href="'/services/edit/' + service.id" class="text-xs font-semibold text-brand-500 hover:text-brand-400 transition" :id="'btn-edit-service-' + service.id">Edit</a>
                                <form :action="'/services/delete/' + service.id" method="POST" class="inline-block" @submit="confirmDelete(event)">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-400 transition cursor-pointer" :id="'btn-delete-service-' + service.id">Delete</button>
                                </form>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty Table State -->
                    <tr x-show="totalVisible() === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-neutral-500">
                            <svg class="mx-auto h-12 w-12 text-neutral-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="block text-sm font-semibold text-white">No services found</span>
                            <span class="block text-xs text-neutral-500 mt-1">Try refining your search query or add a new service code.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('serviceManager', () => ({
            searchQuery: '',
            activeCategory: 'All',
            categories: ['All', 'Consultation', 'Vaccination', 'Surgery', 'Grooming', 'Laboratory Test', 'Medicine', 'Supplies'],
            services: <?= json_encode($services) ?>,

            matches(service) {
                const matchSearch = service.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                    service.code.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchCategory = this.activeCategory === 'All' || service.category === this.activeCategory;
                return matchSearch && matchCategory;
            },
            totalVisible() {
                return this.services.filter(s => this.matches(s)).length;
            },
            formatPrice(price) {
                return parseFloat(price).toLocaleString('id-ID');
            },
            confirmDelete(event) {
                if (!confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
                    event.preventDefault();
                }
            }
        }));
    });
</script>
<?= $this->endSection() ?>
