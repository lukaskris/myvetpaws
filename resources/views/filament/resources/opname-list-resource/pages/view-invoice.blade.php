@php
    $formatCurrency = fn (float $amount): string => 'Rp ' . number_format($amount, 0, ',', '.');
    $owner = $record->customer;
    $pets = $record->diagnoses
        ->pluck('pet.name')
        ->filter()
        ->unique()
        ->values()
        ->implode(', ');
@endphp

<x-filament::page>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Invoice</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Nomor: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $this->getInvoiceNumber() }}</span>
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Dibuat pada: {{ optional($record->created_at)->format('d M Y H:i') ?? '-' }}
            </p>
        </div>
        <div class="flex gap-2">
            <x-filament::button color="gray" icon="heroicon-o-arrow-left" tag="a" :href="$this->getResource()::getUrl('index')">
                Kembali ke Appointments
            </x-filament::button>
            <x-filament::button color="primary" icon="heroicon-o-printer" x-on:click="window.print()">
                Cetak Invoice
            </x-filament::button>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                Informasi Appointment
            </x-slot>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500 dark:text-gray-400">Judul</span>
                    <span class="font-medium text-gray-900 dark:text-gray-50">{{ $record->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500 dark:text-gray-400">Tanggal</span>
                    <span class="font-medium text-gray-900 dark:text-gray-50">
                        {{ optional($record->date)->format('d M Y') ?? '-' }}
                    </span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500 dark:text-gray-400">Diskon</span>
                    <span class="font-medium text-gray-900 dark:text-gray-50">
                        {{ $formatCurrency((float) ($record->discount ?? 0)) }}
                    </span>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Informasi Pemilik
            </x-slot>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500 dark:text-gray-400">Nama</span>
                    <span class="font-medium text-gray-900 dark:text-gray-50">{{ $owner->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500 dark:text-gray-400">Kontak</span>
                    <span class="font-medium text-gray-900 dark:text-gray-50">
                        {{ $owner->phone ?? $owner->email ?? '-' }}
                    </span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500 dark:text-gray-400">Alamat</span>
                    <span class="text-right font-medium text-gray-900 dark:text-gray-50">
                        {{ $owner->address ?? '-' }}
                    </span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500 dark:text-gray-400">Pets</span>
                    <span class="text-right font-medium text-gray-900 dark:text-gray-50">
                        {{ $pets !== '' ? $pets : '-' }}
                    </span>
                </div>
            </div>
        </x-filament::section>
    </div>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Ringkasan Diagnosa
        </x-slot>

        <div class="space-y-4">
            @forelse ($record->diagnoses as $diagnose)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $diagnose->name ?? 'Diagnose' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Pet: {{ $diagnose->pet->name ?? '-' }} • Tipe: {{ $diagnose->type ?? '-' }} • Prognose: {{ $diagnose->prognose ?? '-' }}
                            </p>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Durasi perawatan: {{ $diagnose->duration_days ?? 0 }} hari
                        </div>
                    </div>

                    @if ($diagnose->details->isNotEmpty())
                        <ul class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            @foreach ($diagnose->details as $detail)
                                <li class="flex flex-col rounded-md border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900/60">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $detail->name ?? $detail->diagnosisMaster->name ?? '-' }}
                                        </span>
                                        <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                            {{ ucfirst($detail->detail_item_sections ?? 'diagnose') }}
                                        </span>
                                    </div>
                                    @if ($detail->notes)
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $detail->notes }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Belum ada data diagnosa untuk appointment ini.
                </p>
            @endforelse
        </div>
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Rincian Biaya
        </x-slot>

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Tipe
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Nama Item
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Catatan
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Harga
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white text-sm dark:divide-gray-800 dark:bg-gray-900/60">
                    @forelse ($this->invoiceItems as $item)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $item['type'] }}
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                {{ $item['name'] }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                {{ $item['notes'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-gray-100">
                                {{ $formatCurrency($item['price']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                Belum ada biaya layanan atau obat yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 max-w-md space-y-2 self-end rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-900/40">
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Total Obat</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $formatCurrency($this->invoiceTotals['medicine'] ?? 0) }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Total Layanan</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $formatCurrency($this->invoiceTotals['service'] ?? 0) }}
                </span>
            </div>
            <div class="flex justify-between border-t border-dashed border-gray-200 pt-2 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $formatCurrency($this->invoiceTotals['subtotal'] ?? 0) }}
                </span>
            </div>
            <div class="flex justify-between text-red-500 dark:text-red-400">
                <span>Diskon</span>
                <span>-{{ $formatCurrency($this->invoiceTotals['discount'] ?? 0) }}</span>
            </div>
            <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-900 dark:border-gray-700 dark:text-gray-100">
                <span>Grand Total</span>
                <span>{{ $formatCurrency($this->invoiceTotals['total'] ?? 0) }}</span>
            </div>
        </div>
    </x-filament::section>
</x-filament::page>
