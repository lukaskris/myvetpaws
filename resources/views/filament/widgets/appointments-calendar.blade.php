<x-filament::widget>
    <x-filament::card class="overflow-hidden shadow-2xl">
        <div class="relative bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 px-6 py-5 overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-30"></div>
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative z-10 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-white drop-shadow-lg">📅 Kalender Janjian</h3>
                    <p class="text-sm text-blue-100/90 backdrop-blur-sm">Kelola dan lihat semua appointment dengan mudah</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button color="white" size="sm" icon="heroicon-o-chevron-left" wire:click="goToPreviousMonth" class="shadow-lg hover:shadow-xl transition-shadow duration-300">
                        ← Sebelumnya
                    </x-filament::button>
                    <x-filament::button color="white" size="sm" icon="heroicon-o-calendar" wire:click="goToToday" class="shadow-lg hover:shadow-xl transition-shadow duration-300">
                        Hari Ini
                    </x-filament::button>
                    <x-filament::button color="white" size="sm" icon="heroicon-o-chevron-right" wire:click="goToNextMonth" class="shadow-lg hover:shadow-xl transition-shadow duration-300">
                        Selanjutnya →
                    </x-filament::button>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-2">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white shadow-lg">
                        🗓️
                    </div>
                    <div class="text-xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent dark:from-gray-100 dark:to-gray-300">
                        {{ $calendarMonthLabel }}
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 px-4 py-2 text-blue-700 dark:from-blue-500/20 dark:via-indigo-500/20 dark:to-purple-500/20 dark:text-blue-300 shadow-md border border-blue-200/50 dark:border-blue-500/30">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-gradient-to-r from-blue-500 to-indigo-500"></span>
                        </span>
                        Tanggal Ada Janji
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-emerald-50 via-green-50 to-teal-50 px-4 py-2 text-emerald-700 dark:from-emerald-500/20 dark:via-green-500/20 dark:to-teal-500/20 dark:text-emerald-300 shadow-md border border-emerald-200/50 dark:border-emerald-500/30">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-gradient-to-r from-emerald-500 to-green-500"></span>
                        </span>
                        Hari Ini
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-1.5 rounded-t-xl bg-gradient-to-b from-gray-100 via-gray-50 to-white px-3 py-3 text-xs font-bold uppercase tracking-wider text-gray-600 dark:from-gray-800 dark:via-gray-800/50 dark:to-gray-800 dark:text-gray-400 shadow-sm">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayLabel)
                    <div class="text-center group cursor-default">
                        <span class="inline-block transition-transform duration-200 group-hover:scale-110">{{ $dayLabel }}</span>
                    </div>
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
                                'group relative min-h-[130px] rounded-xl border-2 bg-white p-2.5 text-xs transition-all duration-300 hover:shadow-xl dark:bg-gray-800/80 backdrop-blur-sm',
                                'border-blue-300 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-blue-500/15 dark:via-indigo-500/15 dark:to-purple-500/15 hover:scale-[1.02] hover:shadow-2xl' => $hasAppointments,
                                'border-gray-200 opacity-40 dark:border-gray-700' => ! $day['is_current_month'],
                                'border-gray-200 hover:border-gray-300 hover:shadow-lg dark:border-gray-700 dark:hover:border-gray-600' => ! $hasAppointments && $day['is_current_month'],
                                'border-emerald-400 bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 dark:from-emerald-500/15 dark:via-green-500/15 dark:to-teal-500/15 ring-2 ring-emerald-400 ring-offset-2 dark:ring-offset-gray-900 shadow-lg' => $day['is_today'],
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
                                        <div class="group/item relative rounded-lg border border-blue-200/60 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 px-2 py-1.5 text-[10px] leading-tight text-blue-900 shadow-md transition-all duration-300 hover:shadow-xl hover:scale-105 hover:border-blue-400 dark:from-blue-500/10 dark:via-indigo-500/10 dark:to-purple-500/10 dark:border-blue-500/40 dark:text-blue-100">
                                            <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative flex items-start gap-1.5">
                                                <span class="mt-0.5 text-blue-500 drop-shadow-sm">📋</span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="truncate font-bold text-[11px] text-blue-800 dark:text-blue-100">{{ $appointment['name'] }}</p>
                                                    @if (! empty($appointment['owner']))
                                                        <p class="truncate text-[10px] text-blue-700/80 dark:text-blue-200/80 flex items-center gap-1">
                                                            <span>👤</span>
                                                            {{ $appointment['owner'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        @if ($day['is_current_month'])
                                            <div class="flex items-center justify-center py-3">
                                                <p class="text-[10px] italic text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                                                    <span class="text-gray-300 dark:text-gray-600">📭</span>
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
    </x-filament::card>
</x-filament::widget>

