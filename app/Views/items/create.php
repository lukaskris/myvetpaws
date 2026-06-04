<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Add Inventory Item<?= $this->endSection() ?>

<?= $this->section('header') ?>Inventory (Obat & Alat Medis)<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6" x-data="{ submitting: false }">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2 text-xs text-neutral-400">
        <a href="/dashboard" class="hover:text-white transition duration-150">Workspace</a>
        <span>/</span>
        <a href="/items" class="hover:text-white transition duration-150">Inventory</a>
        <span>/</span>
        <span class="text-white font-medium">Add Item</span>
    </div>

    <!-- Header Block -->
    <div class="pb-6 border-b border-neutral-900">
        <h2 class="text-xl font-bold text-white tracking-tight">Add Inventory Item</h2>
        <p class="text-xs text-neutral-400 mt-1">Add medicines or medical items to your catalog, with purchase price, selling price, and initial stock level.</p>
    </div>

    <!-- Alert notifications -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="p-4 bg-red-950/40 border border-red-500/30 rounded-2xl text-xs text-red-400 space-y-1 shadow-md">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <p>• <?= esc($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="p-4 bg-red-950/40 border border-red-500/30 rounded-2xl text-xs text-red-400 shadow-md">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-3xl shadow-lg">
        <form action="/items/create" method="POST" class="space-y-6" @submit="submitting = true">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Item Code -->
                <div>
                    <label for="inp-code" class="block text-xs font-semibold text-neutral-300">Item Code</label>
                    <input type="text" id="inp-code" name="code" required value="<?= old('code') ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition uppercase"
                        placeholder="e.g. OBT-AMOX-250">
                </div>

                <!-- Item Name -->
                <div>
                    <label for="inp-name" class="block text-xs font-semibold text-neutral-300">Item Name</label>
                    <input type="text" id="inp-name" name="name" required value="<?= old('name') ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition"
                        placeholder="e.g. Amoxicillin 250mg">
                </div>

                <!-- Category -->
                <div>
                    <label for="inp-category" class="block text-xs font-semibold text-neutral-300">Category</label>
                    <select id="inp-category" name="category" required
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition">
                        <option value="Medicine" <?= old('category') == 'Medicine' ? 'selected' : '' ?>>Medicine (Obat)</option>
                        <option value="Supplies" <?= old('category') == 'Supplies' ? 'selected' : '' ?>>Supplies (Alat Medis/BHP)</option>
                        <option value="Equipment" <?= old('category') == 'Equipment' ? 'selected' : '' ?>>Equipment (Peralatan)</option>
                    </select>
                </div>

                <!-- Empty space for layout balance -->
                <div></div>

                <!-- Buy Price -->
                <div>
                    <label for="inp-buy-price" class="block text-xs font-semibold text-neutral-300">Purchase Price / Buy Price (IDR)</label>
                    <div class="mt-1.5 flex rounded-xl bg-neutral-950 border border-neutral-800 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 overflow-hidden">
                        <span class="bg-neutral-900 text-neutral-400 text-xs px-3 py-2.5 flex items-center border-r border-neutral-800 font-semibold select-none">
                            Rp
                        </span>
                        <input type="number" id="inp-buy-price" name="buy_price" required step="0.01" min="0" value="<?= old('buy_price') ?>"
                            class="flex-1 bg-transparent px-4 py-2.5 text-white placeholder-neutral-600 outline-none text-sm"
                            placeholder="e.g. 5000">
                    </div>
                </div>

                <!-- Sell Price -->
                <div>
                    <label for="inp-sell-price" class="block text-xs font-semibold text-neutral-300">Selling Price / Sell Price (IDR)</label>
                    <div class="mt-1.5 flex rounded-xl bg-neutral-950 border border-neutral-800 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 overflow-hidden">
                        <span class="bg-neutral-900 text-neutral-400 text-xs px-3 py-2.5 flex items-center border-r border-neutral-800 font-semibold select-none">
                            Rp
                        </span>
                        <input type="number" id="inp-sell-price" name="sell_price" required step="0.01" min="0" value="<?= old('sell_price') ?>"
                            class="flex-1 bg-transparent px-4 py-2.5 text-white placeholder-neutral-600 outline-none text-sm"
                            placeholder="e.g. 12000">
                    </div>
                </div>

                <!-- Initial Stock -->
                <div>
                    <label for="inp-stock" class="block text-xs font-semibold text-neutral-300">Initial Stock</label>
                    <input type="number" id="inp-stock" name="stock" required min="0" value="<?= old('stock', 0) ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition"
                        placeholder="e.g. 50">
                </div>

                <!-- Min Stock -->
                <div>
                    <label for="inp-min-stock" class="block text-xs font-semibold text-neutral-300">Minimum Stock Warning Level</label>
                    <input type="number" id="inp-min-stock" name="min_stock" required min="0" value="<?= old('min_stock', 5) ?>"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition"
                        placeholder="e.g. 5">
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label for="inp-desc" class="block text-xs font-semibold text-neutral-300">Description</label>
                    <textarea id="inp-desc" name="description" rows="3"
                        class="mt-1.5 block w-full rounded-xl bg-neutral-950 border border-neutral-800 px-4 py-2.5 text-white placeholder-neutral-500 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none text-sm transition"
                        placeholder="Detail the item usage, packaging size, active substances, etc..."><?= old('description') ?></textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-neutral-800">
                <a href="/items" class="px-4 py-2.5 border border-neutral-800 bg-transparent text-neutral-400 hover:text-white rounded-xl text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" :disabled="submitting"
                    class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-semibold transition shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 inline-flex items-center gap-1.5">
                    <span x-show="!submitting">Add Item</span>
                    <span x-show="submitting" class="flex items-center">
                        <svg class="animate-spin h-4 w-4 text-white mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
