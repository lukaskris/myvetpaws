<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Inventory Management<?= $this->endSection() ?>

<?= $this->section('header') ?>Inventory (Obat & Alat Medis)<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6" x-data="itemManager">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <span class="text-white font-medium">Inventory</span>
    </div>

    <!-- Header Block -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-neutral-900 gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Medicine & Supplies Catalog</h2>
            <p class="text-xs text-neutral-400 mt-1">Manage single-use medicines, medical equipment, purchase and selling prices, and track active stock levels.</p>
        </div>
        <?php if (session()->get('user_role') === 'owner'): ?>
        <a href="/items/create" id="btn-create-item" class="px-3.5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 transition duration-150 inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Add Item</span>
        </a>
        <?php endif; ?>
    </div>

    <!-- Filters and Search Toolbar -->
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- Category Filter Chips -->
        <div class="flex flex-wrap gap-1.5 self-start">
            <template x-for="cat in categories">
                <button @click="activeCategory = cat"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition duration-150"
                    :class="activeCategory === cat ? 'bg-neutral-800 border-neutral-700 text-white shadow-inner' : 'bg-transparent border-neutral-900 text-neutral-400 hover:text-neutral-50 dark:hover:text-white hover:border-neutral-800'">
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
            <input type="text" x-model="searchQuery" placeholder="Search item name or code..."
                class="block w-full rounded-xl bg-neutral-900 border border-neutral-850 pl-10 pr-4 py-2 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-xs transition">
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-neutral-800 bg-neutral-950/40 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Item Details</th>
                        <th class="px-6 py-4">Pricing (Buy / Sell)</th>
                        <th class="px-6 py-4">Margin</th>
                        <th class="px-6 py-4">Stock Levels</th>
                        <th class="px-6 py-4">Status</th>
                        <?php if (session()->get('user_role') === 'owner'): ?>
                        <th class="px-6 py-4 text-right">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/60 text-sm">
                    <template x-for="item in items" :key="item.id">
                        <tr x-show="matches(item)" x-transition.opacity.duration.150ms class="hover:bg-neutral-850/30 transition duration-150">
                            <!-- Code -->
                            <td class="px-6 py-4 font-mono text-xs font-bold text-neutral-400">
                                <span class="bg-neutral-950 px-2 py-1 border border-neutral-850 rounded-lg" x-text="item.code"></span>
                            </td>
                            <!-- Item Details -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-white" x-text="item.name"></div>
                                <div class="text-xs text-neutral-500 truncate max-w-xs mt-0.5" x-text="item.description || 'No description.'"></div>
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-neutral-950 border border-neutral-850 text-neutral-400 mt-1" x-text="item.category"></span>
                            </td>
                            <!-- Pricing -->
                            <td class="px-6 py-4 text-xs font-semibold">
                                <div class="text-neutral-400">Buy: <span class="text-neutral-300 font-bold" x-text="'Rp' + formatPrice(item.buy_price)"></span></div>
                                <div class="text-brand-400 mt-0.5">Sell: <span class="text-white font-bold" x-text="'Rp' + formatPrice(item.sell_price)"></span></div>
                            </td>
                            <!-- Margin -->
                            <td class="px-6 py-4 text-xs font-bold text-emerald-400">
                                <span x-text="calculateMarginPercent(item)"></span>%
                                <div class="text-[10px] text-neutral-500 font-medium" x-text="'+Rp' + formatPrice(item.sell_price - item.buy_price)"></div>
                            </td>
                            <!-- Stock levels -->
                            <td class="px-6 py-4">
                                <template x-if="parseInt(item.stock) <= 0">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-red-950/40 text-red-400 border border-red-500/10"
                                          x-text="'Out of Stock (' + item.stock + ')'"></span>
                                </template>
                                <template x-if="parseInt(item.stock) > 0 && parseInt(item.stock) <= parseInt(item.min_stock)">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-950/40 text-amber-400 border border-amber-500/10"
                                          x-text="'Low (' + item.stock + ' / min ' + item.min_stock + ')'"></span>
                                </template>
                                <template x-if="parseInt(item.stock) > parseInt(item.min_stock)">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-950/40 text-emerald-400 border border-emerald-500/10"
                                          x-text="item.stock + ' Available'"></span>
                                </template>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <template x-if="item.status == 1">
                                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400 font-semibold">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        Active
                                    </span>
                                </template>
                                <template x-if="item.status == 0">
                                    <span class="inline-flex items-center gap-1.5 text-xs text-neutral-500 font-semibold">
                                        <span class="h-1.5 w-1.5 rounded-full bg-neutral-600"></span>
                                        Inactive
                                    </span>
                                </template>
                            </td>
                            <!-- Actions -->
                            <?php if (session()->get('user_role') === 'owner'): ?>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a :href="'/items/edit/' + item.id" class="text-xs font-semibold text-brand-500 hover:text-brand-400 transition" :id="'btn-edit-item-' + item.id">Edit</a>
                                <form :action="'/items/delete/' + item.id" method="POST" class="inline-block" @submit="confirmDelete(event)">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-400 transition cursor-pointer" :id="'btn-delete-item-' + item.id">Delete</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    </template>

                    <!-- Empty Table State -->
                    <tr x-show="totalVisible() === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-neutral-500">
                            <svg class="mx-auto h-12 w-12 text-neutral-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="block text-sm font-semibold text-white">No items found</span>
                            <span class="block text-xs text-neutral-500 mt-1">Try refining your search query or add a new medicine / medical supply item.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('itemManager', () => ({
            searchQuery: '',
            activeCategory: 'All',
            categories: ['All', 'Medicine', 'Supplies', 'Equipment'],
            items: <?= json_encode($items) ?>,

            matches(item) {
                const matchSearch = item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                    item.code.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchCategory = this.activeCategory === 'All' || item.category === this.activeCategory;
                return matchSearch && matchCategory;
            },
            totalVisible() {
                return this.items.filter(i => this.matches(i)).length;
            },
            formatPrice(price) {
                return parseFloat(price).toLocaleString('id-ID');
            },
            calculateMarginPercent(item) {
                const buy = parseFloat(item.buy_price);
                const sell = parseFloat(item.sell_price);
                if (buy <= 0) return 100;
                const percent = ((sell - buy) / buy) * 100;
                return Math.round(percent);
            },
            confirmDelete(event) {
                if (!confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
                    event.preventDefault();
                }
            }
        }));
    });
</script>
<?= $this->endSection() ?>
