<x-filament::widget>
    <div class="min-h-screen w-full flex flex-col bg-gradient-to-br from-slate-50 via-sky-50 to-white dark:from-slate-950 dark:via-slate-900 dark:to-slate-900">
        <!-- Clean, Professional Header -->
        <div class="bg-white/90 backdrop-blur border-b border-slate-200 px-6 py-5 shrink-0 shadow-sm dark:bg-slate-900/90 dark:border-slate-800">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between w-full">
                <div class="flex-1">
                    <h3 class="text-2xl font-semibold text-slate-800 tracking-tight dark:text-slate-100">Kalender Janjian</h3>
                    <p class="text-sm text-slate-500 mt-1 dark:text-slate-300">Kelola dan lihat semua appointment dengan mudah</p>
                </div>
                <div class="flex gap-2">
                    <x-filament::button color="gray" size="sm" icon="heroicon-o-chevron-left" wire:click="goToPreviousMonth">
                        
                    </x-filament::button>
                    <x-filament::button color="primary" size="sm" icon="heroicon-o-calendar" wire:click="goToToday">
                        Hari Ini
                    </x-filament::button>
                    <x-filament::button color="gray" size="sm" icon="heroicon-o-chevron-right" wire:click="goToNextMonth">
                        
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- Main Calendar Content -->
        <div class="flex-1 px-6 pb-6">
            <!-- Month and Legend -->
            <div class="my-6 w-full gap-4 lg:grid lg:grid-cols-[1fr_auto_1fr] lg:items-center">
                <div class="flex items-center justify-center gap-3 lg:col-start-2 lg:justify-self-center">
                    <div class="h-11 w-11 rounded-xl bg-slate-100 flex items-center justify-center text-sky-600 shadow-md dark:bg-slate-800 dark:text-sky-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="text-2xl font-semibold text-slate-800 tracking-tight dark:text-slate-100 text-center">
                        {{ $calendarMonthLabel }}
                    </div>
                </div>
            </div>

            <!-- Day Headers -->
            <div class="grid grid-cols-7 gap-2 rounded-t-xl  px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300 ">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayLabel)
                    <div class="text-center">
                        <span>{{ $dayLabel }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Calendar Grid -->
            <div class="space-y-2 flex-1 overflow-visible">
                @foreach ($calendarWeeks as $week)
                    <div class="grid grid-cols-7 gap-2">
                        @foreach ($week as $day)
                            @php
                                $hasAppointments = ! empty($day['appointments']);
                                $appointmentCount = count($day['appointments']);
                            @endphp
                            <div @class([
                                'group relative min-h-[160px] rounded-xl border bg-white/95 p-3 text-xs transition-shadow duration-200 hover:shadow-md dark:bg-slate-900/85 dark:border-slate-700',
                                'border-sky-300 bg-sky-50/60 hover:border-sky-400 dark:border-sky-500/60 dark:bg-sky-500/15' => $hasAppointments,
                                'border-slate-200 bg-slate-50/70 text-slate-400 opacity-60 dark:bg-slate-900/60 dark:text-slate-500' => ! $day['is_current_month'],
                                'border-slate-200 hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700' => ! $hasAppointments && $day['is_current_month'],
                                'border-emerald-400 bg-emerald-50 ring-2 ring-emerald-200 ring-offset-2 ring-offset-white shadow-sm dark:border-emerald-400/70 dark:bg-emerald-500/10 dark:ring-emerald-400/60 dark:ring-offset-slate-950' => $day['is_today'],
                            ])>
                                <div class="flex items-center justify-between">
                                    <span @class([
                                        'text-lg font-semibold',
                                        'text-slate-400 dark:text-slate-500' => ! $day['is_current_month'],
                                        'text-slate-700 dark:text-slate-200' => $day['is_current_month'] && ! $day['is_today'],
                                        'text-emerald-700 dark:text-emerald-300' => $day['is_today'],
                                    ])>{{ $day['day'] }}</span>
                                    @if ($appointmentCount > 0)
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-600 text-[10px] font-semibold text-white dark:bg-sky-500">
                                            {{ $appointmentCount }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 space-y-2 [content-visibility:auto] [contain-intrinsic-size:240px]">
                                    @forelse ($day['appointments'] as $appointment)
                                        <div class="group/item relative rounded-lg border border-sky-200 bg-white/90 px-2.5 py-2 text-[10px] leading-snug text-slate-700 shadow-sm transition-all duration-200 hover:shadow-md hover:border-sky-300 dark:bg-slate-900 dark:border-sky-500/30 dark:text-slate-200 dark:hover:border-sky-400/60">
                                            <div class="flex items-start gap-2">
                                                <span class="mt-0.5 text-sky-600 dark:text-sky-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                    </svg>
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="truncate font-semibold text-[11px] text-slate-800 dark:text-slate-100">{{ $appointment['name'] }}</p>
                                                    @if (! empty($appointment['owner']))
                                                        <p class="truncate text-[10px] text-slate-500 flex items-center gap-1 mt-0.5 dark:text-slate-300">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                            </svg>
                                                            {{ $appointment['owner'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        @if ($day['is_current_month'])
                                            <div class="flex items-center justify-center py-6">
                                                <p class="text-[10px] italic text-slate-400 flex items-center gap-1.5 dark:text-slate-400">
                                                    <svg class="w-4 h-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                    </svg>
                                                    Tiada janji
                                                </p>
                                            </div>
                                        @endif
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament::widget>
