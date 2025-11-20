<x-filament::widget>
    <x-filament::card>
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Kalender Janjian</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Lihat semua appointment per bulan.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-filament::button color="gray" size="sm" icon="heroicon-o-chevron-left" wire:click="goToPreviousMonth">
                    Sebelumnya
                </x-filament::button>
                <x-filament::button color="gray" size="sm" icon="heroicon-o-home" wire:click="goToToday">
                    Bulan Ini
                </x-filament::button>
                <x-filament::button color="primary" size="sm" icon="heroicon-o-chevron-right" wire:click="goToNextMonth">
                    Selanjutnya
                </x-filament::button>
            </div>
        </div>

        <div class="mt-4 mb-3 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Menampilkan bulan {{ $calendarMonthLabel }}.
            </div>
            <div class="flex flex-wrap items-center gap-2 text-[11px] text-gray-600 dark:text-gray-300">
                <span class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1 text-primary-800 dark:bg-primary-500/10 dark:text-primary-200">
                    <span class="h-2 w-2 rounded-full bg-primary-500"></span>
                    Tanggal ada janji
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Hari ini
                </span>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayLabel)
                <div class="text-center">{{ $dayLabel }}</div>
            @endforeach
        </div>

        <div class="mt-2 space-y-2">
            @foreach ($calendarWeeks as $week)
                <div class="grid grid-cols-7 gap-2">
                    @foreach ($week as $day)
                        @php
                            $hasAppointments = ! empty($day['appointments']);
                        @endphp
                        <div @class([
                            'min-h-[100px] rounded-lg border bg-white p-2 text-xs shadow-sm transition dark:bg-gray-900/60',
                            'border-primary-200 ring-1 ring-primary-100/60 dark:ring-primary-500/30' => $hasAppointments,
                            'border-gray-200 opacity-60 dark:border-gray-700' => ! $day['is_current_month'],
                            'border-gray-200 dark:border-gray-700' => ! $hasAppointments && $day['is_current_month'],
                            'ring-2 ring-emerald-300/70' => $day['is_today'],
                        ])>
                            <div class="flex items-center justify-between text-[11px] font-semibold text-gray-700 dark:text-gray-200">
                                <span>{{ $day['day'] }}</span>
                                @if ($day['is_today'])
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200">
                                        Hari ini
                                    </span>
                                @endif
                            </div>

                            <div class="mt-1 space-y-1">
                                @forelse ($day['appointments'] as $appointment)
                                    <div class="rounded-md border border-primary-100 bg-primary-50 px-2 py-1 text-[11px] leading-tight text-primary-900 dark:border-primary-500/40 dark:bg-primary-500/10 dark:text-primary-100">
                                        <p class="truncate font-semibold">{{ $appointment['name'] }}</p>
                                        @if (! empty($appointment['owner']))
                                            <p class="truncate text-[10px] text-primary-700 dark:text-primary-200/80">
                                                {{ $appointment['owner'] }}
                                            </p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-[11px] italic text-gray-400 dark:text-gray-500">Tidak ada janji</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </x-filament::card>
</x-filament::widget>

