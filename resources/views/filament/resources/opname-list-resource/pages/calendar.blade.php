<x-filament::page>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
            <span class="text-3xl">📅</span>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Kalender Janjian</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola dan lihat semua appointment dengan mudah</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-filament::button color="gray" icon="heroicon-o-chevron-left" wire:click="goToPreviousMonth">
                ← Bulan Sebelumnya
            </x-filament::button>
            <x-filament::button color="gray" icon="heroicon-o-calendar" wire:click="goToToday">
                Hari Ini
            </x-filament::button>
            <x-filament::button color="primary" icon="heroicon-o-chevron-right" wire:click="goToNextMonth">
                Bulan Selanjutnya →
            </x-filament::button>
        </div>
    </div>

    <x-filament::section class="mt-4 overflow-hidden">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="text-xl">🗓️</span>
                Kalender Janjian Bulanan
            </div>
        </x-slot>

        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="text-base font-semibold text-gray-800 dark:text-gray-100">
                {{ $calendarMonthLabel }}
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-50 to-indigo-50 px-3 py-1.5 text-blue-700 dark:from-blue-500/20 dark:to-indigo-500/20 dark:text-blue-300 shadow-sm">
                    <span class="h-2.5 w-2.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 shadow-sm"></span>
                    Tanggal Ada Janji
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-emerald-50 to-green-50 px-3 py-1.5 text-emerald-700 dark:from-emerald-500/20 dark:to-green-500/20 dark:text-emerald-300 shadow-sm">
                    <span class="h-2.5 w-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-green-500 shadow-sm"></span>
                    Hari Ini
                </span>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-1.5 rounded-t-lg bg-gradient-to-b from-gray-50 to-white px-3 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:from-gray-800/50 dark:to-gray-800 dark:text-gray-400">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayLabel)
                <div class="text-center">{{ $dayLabel }}</div>
            @endforeach
        </div>

        <div class="space-y-1.5">
            @foreach ($calendarWeeks as $week)
                <div class="grid grid-cols-7 gap-1.5">
                    @foreach ($week as $day)
                        @php
                            $hasAppointments = ! empty($day['appointments']);
                            $appointmentCount = count($day['appointments']);
                        @endphp
                        <div @class([
                            'group relative min-h-[120px] rounded-lg border-2 bg-white p-2.5 text-xs transition-all duration-300 hover:shadow-lg dark:bg-gray-800/80',
                            'border-blue-300 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-500/15 dark:to-indigo-500/15 hover:scale-105' => $hasAppointments,
                            'border-gray-200 opacity-50 dark:border-gray-700' => ! $day['is_current_month'],
                            'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600' => ! $hasAppointments && $day['is_current_month'],
                            'border-emerald-400 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-500/15 dark:to-green-500/15 ring-2 ring-emerald-400 ring-offset-2 dark:ring-offset-gray-900' => $day['is_today'],
                        ])>
                            <div class="flex items-center justify-between">
                                <span @class([
                                    'text-lg font-bold',
                                    'text-gray-400 dark:text-gray-500' => ! $day['is_current_month'],
                                    'text-gray-700 dark:text-gray-200' => $day['is_current_month'] && ! $day['is_today'],
                                    'text-emerald-700 dark:text-emerald-300' => $day['is_today'],
                                ])>{{ $day['day'] }}</span>
                                @if ($appointmentCount > 0)
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 text-[10px] font-bold text-white shadow-md">
                                        {{ $appointmentCount }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 space-y-1.5">
                                @forelse ($day['appointments'] as $appointment)
                                    <div class="group/item relative rounded-md border border-blue-200/50 bg-gradient-to-r from-blue-50 to-indigo-50 px-2 py-1.5 text-[10px] leading-tight text-blue-900 shadow-sm transition-all duration-200 hover:shadow-md hover:scale-[1.02] dark:from-blue-500/10 dark:to-indigo-500/10 dark:border-blue-500/30 dark:text-blue-100">
                                        <div class="flex items-start gap-1.5">
                                            <span class="mt-0.5 text-blue-500">📋</span>
                                            <div class="flex-1 min-w-0">
                                                <p class="truncate font-semibold text-[11px]">{{ $appointment['name'] }}</p>
                                                @if (! empty($appointment['owner']))
                                                    <p class="truncate text-[10px] text-blue-700 dark:text-blue-200/80">
                                                        👤 {{ $appointment['owner'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    @if ($day['is_current_month'])
                                        <p class="text-[10px] italic text-gray-400 dark:text-gray-500 text-center py-2">Tiada janji</p>
                                    @endif
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament::page>

