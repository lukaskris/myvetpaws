<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Visits Queue<?= $this->endSection() ?>

<?= $this->section('header') ?>Visits Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6" 
     x-data="{ 
         tab: 'queue',
         currentYear: new Date().getFullYear(),
         currentMonth: new Date().getMonth(),
         monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
         daysOfWeek: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
         events: <?= htmlspecialchars(json_encode($calendarEvents), ENT_QUOTES, 'UTF-8') ?>,
         selectedDate: new Date().toISOString().split('T')[0],

         get daysInMonth() {
             return new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
         },

         get startDayOffset() {
             return new Date(this.currentYear, this.currentMonth, 1).getDay();
         },

         get calendarWeeks() {
             let days = [];
             const prevMonthDays = new Date(this.currentYear, this.currentMonth, 0).getDate();
             for (let i = this.startDayOffset - 1; i >= 0; i--) {
                 days.push({ day: prevMonthDays - i, isCurrentMonth: false, dateStr: null });
             }
             for (let d = 1; d <= this.daysInMonth; d++) {
                 const dateStr = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                 days.push({ day: d, isCurrentMonth: true, dateStr: dateStr });
             }
             const totalCells = Math.ceil(days.length / 7) * 7;
             const nextMonthPadding = totalCells - days.length;
             for (let n = 1; n <= nextMonthPadding; n++) {
                 days.push({ day: n, isCurrentMonth: false, dateStr: null });
             }
             return days;
         },

         getEventsForDate(dateStr) {
             if (!dateStr) return [];
             return this.events.filter(e => e.date === dateStr);
         },

         prevMonth() {
             if (this.currentMonth === 0) {
                 this.currentMonth = 11;
                 this.currentYear--;
             } else {
                 this.currentMonth--;
             }
             this.$nextTick(() => { lucide.createIcons(); });
         },

         nextMonth() {
             if (this.currentMonth === 11) {
                 this.currentMonth = 0;
                 this.currentYear++;
             } else {
                 this.currentMonth++;
             }
             this.$nextTick(() => { lucide.createIcons(); });
         },

         getStatusColor(status) {
             switch(status) {
                 case 1: return 'bg-brand-650/15 text-brand-350 border-brand-600/30 hover:bg-brand-600/25'; 
                 case 2: return 'bg-amber-500/10 text-amber-400 border-amber-500/25 hover:bg-amber-500/20'; 
                 case 3: return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25 hover:bg-emerald-500/20'; 
                 case 4: return 'bg-neutral-850/45 text-neutral-500 border-neutral-800/60 hover:bg-neutral-800/60'; 
                 default: return 'bg-slate-500/10 text-slate-400 border-slate-500/25';
             }
         },

         getStatusText(status) {
             switch(status) {
                 case 1: return 'Queued';
                 case 2: return 'Examining';
                 case 3: return 'Completed';
                 case 4: return 'Cancelled';
                 default: return '';
             }
         },

         dateParamFormatted(dateStr) {
             if (!dateStr) return '';
             const parts = dateStr.split('-');
             if (parts.length !== 3) return dateStr;
             const year = parts[0];
             const monthIndex = parseInt(parts[1], 10) - 1;
             const day = parseInt(parts[2], 10);
             const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
             return `${day} ${months[monthIndex]} ${year}`;
         }
     }">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Clinic Visits</h2>
            <p class="text-sm text-neutral-400 mt-1">Manage today's checked-in patient queue and review historical visits.</p>
        </div>
        <div>
            <a href="/visits/create" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-semibold shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 transition duration-150 inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Check-in Patient</span>
            </a>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-neutral-800 flex space-x-6">
        <button @click="tab = 'queue'" 
                :class="tab === 'queue' ? 'border-brand-500 text-white' : 'border-transparent text-neutral-400 hover:text-neutral-200'"
                class="py-3 px-1 border-b-2 font-semibold text-sm transition duration-150 relative">
            Active Queue
            <?php if (!empty($activeVisits)): ?>
                <span class="ml-2 px-2 py-0.5 text-xs bg-brand-500/20 text-brand-400 border border-brand-500/30 rounded-full font-bold">
                    <?= count($activeVisits) ?>
                </span>
            <?php endif; ?>
        </button>
        <button @click="tab = 'history'" 
                :class="tab === 'history' ? 'border-brand-500 text-white' : 'border-transparent text-neutral-400 hover:text-neutral-200'"
                class="py-3 px-1 border-b-2 font-semibold text-sm transition duration-150">
            Historical Visits
        </button>
        <button @click="tab = 'calendar'; $nextTick(() => { lucide.createIcons(); })" 
                :class="tab === 'calendar' ? 'border-brand-500 text-white' : 'border-transparent text-neutral-400 hover:text-neutral-200'"
                class="py-3 px-1 border-b-2 font-semibold text-sm transition duration-150 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Calendar View</span>
        </button>
    </div>

    <!-- Active Queue Tab -->
    <div x-show="tab === 'queue'" class="space-y-4">
        <?php if (empty($activeVisits)): ?>
            <div class="bg-neutral-900 border border-neutral-800 rounded-3xl p-12 text-center max-w-xl mx-auto mt-6">
                <div class="h-12 w-12 bg-neutral-950 border border-neutral-800 text-neutral-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-md font-bold text-white">Active queue is empty</h3>
                <p class="text-xs text-neutral-500 mt-1 max-w-xs mx-auto">No patients are currently checked in or waiting for examination.</p>
                <a href="/visits/create" class="mt-4 inline-flex items-center text-xs font-semibold text-brand-500 hover:text-brand-400">
                    Check-in a patient now &rarr;
                </a>
            </div>
        <?php else: ?>
            <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-800">
                        <thead class="bg-neutral-950/60">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Patient & Owner</th>
                                <th scope="col" class="px-6 py-4 class text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Check-in Time</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Vitals</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Chief Complaint</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-neutral-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800 bg-neutral-900/40">
                            <?php foreach ($activeVisits as $visit): ?>
                                <tr class="hover:bg-neutral-800/20 transition duration-150">
                                    <!-- Patient / Owner info -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-10 w-10 bg-neutral-950 rounded-xl flex items-center justify-center border border-neutral-850 overflow-hidden shrink-0">
                                                <?php if (!empty($visit['pet_photo']) && file_exists(FCPATH . $visit['pet_photo'])): ?>
                                                    <img src="/<?= esc($visit['pet_photo']) ?>" class="h-full w-full object-cover">
                                                <?php else: ?>
                                                    <svg class="h-5 w-5 text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="truncate">
                                                <a href="/pets/show/<?= $visit['pet_id'] ?>" class="text-sm font-bold text-white hover:text-brand-400 transition-colors block truncate"><?= esc($visit['pet_name']) ?></a>
                                                <span class="text-xs text-neutral-400 block truncate"><?= esc($visit['customer_name']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Check-in Time -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-300">
                                        <?= date('H:i', strtotime($visit['checkin_time'])) ?>
                                        <span class="text-xs text-neutral-500 block"><?= date('M j, Y', strtotime($visit['checkin_time'])) ?></span>
                                    </td>
                                    <!-- Vitals -->
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-300 space-y-1">
                                        <div><span class="text-neutral-500">Weight:</span> <?= $visit['weight'] ? esc($visit['weight']) . ' kg' : '—' ?></div>
                                        <div><span class="text-neutral-500">Temp:</span> <?= $visit['temperature'] ? esc($visit['temperature']) . ' °C' : '—' ?></div>
                                    </td>
                                    <!-- Complaints -->
                                    <td class="px-6 py-4 text-xs text-neutral-300 max-w-xs truncate">
                                        <?= esc($visit['complaints'] ?: 'No complaint specified') ?>
                                    </td>
                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($visit['status'] == 1): ?>
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-brand-500/10 text-brand-400 border border-brand-500/20 rounded-full">Queued</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full">In Examination</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold space-x-2">
                                        <?php if (session()->get('user_role') === 'owner' || session()->get('user_role') === 'doctor'): ?>
                                            <a href="/visits/examine/<?= $visit['id'] ?>" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white rounded-lg transition">Examine</a>
                                        <?php endif; ?>
                                        <a href="/visits/cancel/<?= $visit['id'] ?>" onclick="return confirm('Are you sure you want to cancel this visit check-in?');" class="px-3 py-1.5 bg-neutral-950 border border-neutral-800 text-red-400 hover:text-red-300 hover:border-red-500/30 rounded-lg transition">Cancel</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- History Tab -->
    <div x-show="tab === 'history'" class="space-y-4" style="display: none;">
        <?php if (empty($historyVisits)): ?>
            <div class="bg-neutral-900 border border-neutral-800 rounded-3xl p-12 text-center max-w-xl mx-auto mt-6">
                <div class="h-12 w-12 bg-neutral-950 border border-neutral-800 text-neutral-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-md font-bold text-white">No historical records</h3>
                <p class="text-xs text-neutral-500 mt-1 max-w-xs mx-auto">No completed or cancelled visits have been archived yet.</p>
            </div>
        <?php else: ?>
            <div class="bg-neutral-900 border border-neutral-800 rounded-3xl overflow-hidden shadow-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-800">
                        <thead class="bg-neutral-950/60">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Patient & Owner</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Date & Time</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Vitals</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Complaints</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-neutral-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800 bg-neutral-900/40">
                            <?php foreach ($historyVisits as $visit): ?>
                                <tr class="hover:bg-neutral-800/20 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-10 w-10 bg-neutral-950 rounded-xl flex items-center justify-center border border-neutral-850 overflow-hidden shrink-0">
                                                <?php if (!empty($visit['pet_photo']) && file_exists(FCPATH . $visit['pet_photo'])): ?>
                                                    <img src="/<?= esc($visit['pet_photo']) ?>" class="h-full w-full object-cover">
                                                <?php else: ?>
                                                    <svg class="h-5 w-5 text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="truncate">
                                                <a href="/pets/show/<?= $visit['pet_id'] ?>" class="text-sm font-bold text-white hover:text-brand-400 transition-colors block truncate"><?= esc($visit['pet_name']) ?></a>
                                                <span class="text-xs text-neutral-400 block truncate"><?= esc($visit['customer_name']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-300">
                                        <?= date('M j, Y', strtotime($visit['checkin_time'])) ?>
                                        <span class="text-xs text-neutral-500 block"><?= date('H:i', strtotime($visit['checkin_time'])) ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-300 space-y-1">
                                        <div><span class="text-neutral-500">Weight:</span> <?= $visit['weight'] ? esc($visit['weight']) . ' kg' : '—' ?></div>
                                        <div><span class="text-neutral-500">Temp:</span> <?= $visit['temperature'] ? esc($visit['temperature']) . ' °C' : '—' ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-neutral-300 max-w-xs truncate">
                                        <?= esc($visit['complaints'] ?: '—') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($visit['status'] == 3): ?>
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">Completed</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-neutral-950 text-neutral-500 border border-neutral-800 rounded-full">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Calendar View Tab -->
    <div x-show="tab === 'calendar'" class="space-y-6" style="display: none;">
        <!-- Calendar Card (Glassmorphic) -->
        <div class="glass-panel p-6 rounded-3xl shadow-xl">
            <!-- Calendar Navigation Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <h3 class="text-lg font-bold text-white tracking-tight" x-text="`${monthNames[currentMonth]} ${currentYear}`"></h3>
                    <div class="flex items-center space-x-1.5 bg-obsidian-950/80 border border-obsidian-850/60 rounded-xl p-0.5 shadow-sm">
                        <button @click="prevMonth()" class="p-1.5 hover:text-neutral-50 dark:hover:text-white text-slate-400 hover:bg-obsidian-900 rounded-lg transition cursor-pointer">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </button>
                        <button @click="currentYear = new Date().getFullYear(); currentMonth = new Date().getMonth(); $nextTick(() => { lucide.createIcons(); })" 
                                class="px-2 py-1 text-[10px] font-bold text-brand-400 hover:text-neutral-50 dark:hover:text-white rounded-lg transition hover:bg-obsidian-900 cursor-pointer">
                            Today
                        </button>
                        <button @click="nextMonth()" class="p-1.5 hover:text-neutral-50 dark:hover:text-white text-slate-400 hover:bg-obsidian-900 rounded-lg transition cursor-pointer">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Event Legend -->
                <div class="hidden sm:flex items-center space-x-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-brand-500"></span>Queued</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span>Examining</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Completed</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-neutral-600"></span>Cancelled</span>
                </div>
            </div>

            <!-- Calendar Days of Week Header -->
            <div class="grid grid-cols-7 gap-px text-center mb-1">
                <template x-for="dayName in daysOfWeek" :key="dayName">
                    <div class="py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-obsidian-950/40 rounded-lg" x-text="dayName"></div>
                </template>
            </div>

            <!-- Calendar Grid -->
            <div class="grid grid-cols-7 gap-px bg-obsidian-850/40 border border-obsidian-800/80 rounded-2xl overflow-hidden shadow-inner">
                <template x-for="cell in calendarWeeks" :key="cell.day + '-' + cell.isCurrentMonth + '-' + Math.random()">
                    <div @click="if(cell.isCurrentMonth && cell.dateStr) { selectedDate = cell.dateStr; }"
                         class="min-h-[100px] p-2 bg-neutral-900/30 border-r border-b border-obsidian-800/60 relative group hover:bg-obsidian-900/50 transition duration-150 flex flex-col justify-between cursor-pointer"
                         :class="[
                            cell.isCurrentMonth ? 'bg-neutral-900/20' : 'opacity-20 bg-neutral-950/10 pointer-events-none',
                            selectedDate === cell.dateStr ? 'ring-1 ring-inset ring-brand-500/80 bg-brand-950/5' : ''
                         ]">
                        
                        <div class="flex items-center justify-between">
                            <!-- Date Number -->
                            <span class="text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center transition" 
                                  :class="[
                                    cell.dateStr === new Date().toISOString().split('T')[0] 
                                        ? 'bg-brand-600 text-white' 
                                        : (selectedDate === cell.dateStr ? 'text-brand-400 font-extrabold' : 'text-slate-400 group-hover:text-white')
                                  ]"
                                  x-text="cell.day">
                            </span>
                            
                            <!-- Hover Quick Plus Add -->
                            <template x-if="cell.isCurrentMonth && cell.dateStr">
                                <a :href="`/visits/create?date=${cell.dateStr}`" 
                                   @click.stop
                                   class="opacity-0 group-hover:opacity-100 p-1 bg-brand-500/10 border border-brand-500/30 text-brand-400 hover:text-white hover:bg-brand-600 rounded-lg transition duration-150 flex items-center justify-center shadow-md cursor-pointer"
                                   title="Schedule patient check-in for this day">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                </a>
                            </template>
                        </div>
                        
                        <!-- Event Lists (Desktop) -->
                        <div class="hidden sm:block flex-1 mt-2 space-y-1.5 overflow-y-auto max-h-[70px] custom-scrollbar">
                            <template x-for="event in getEventsForDate(cell.dateStr)" :key="event.id">
                                <div class="px-1.5 py-0.5 text-[9px] font-bold border rounded-lg flex flex-col truncate leading-normal select-none"
                                     :class="getStatusColor(event.status)"
                                     :title="`${event.pet_name} - ${event.customer_name}\nTime: ${event.time}\nComplaint: ${event.complaints}`">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="truncate" x-text="event.pet_name"></span>
                                        <span class="opacity-75" x-text="event.time"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Dots indicator (Mobile / Tablet) -->
                        <div class="sm:hidden flex items-center justify-center gap-1 mt-2">
                            <template x-for="event in getEventsForDate(cell.dateStr)" :key="event.id">
                                <span class="w-1.5 h-1.5 rounded-full" 
                                      :class="[
                                          event.status === 1 ? 'bg-brand-500' : '',
                                          event.status === 2 ? 'bg-amber-500' : '',
                                          event.status === 3 ? 'bg-emerald-500' : '',
                                          event.status === 4 ? 'bg-neutral-600' : ''
                                      ]">
                                </span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Selected Date Details Panel (Glassmorphic) -->
        <div class="glass-panel p-6 rounded-3xl shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-obsidian-850/60 pb-4">
                <div>
                    <h3 class="text-md font-bold text-white tracking-tight flex items-center gap-2">
                        <svg class="text-brand-500 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Schedule Details for <span x-text="dateParamFormatted(selectedDate)"></span></span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Review active and completed patient check-ins for the selected day.</p>
                </div>
                <div>
                    <a :href="`/visits/create?date=${selectedDate}`"
                       class="px-4 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-600/10 hover:shadow-brand-500/20 transition duration-150 inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Check-in Patient for this Day</span>
                    </a>
                </div>
            </div>

            <!-- List of events on Selected Date -->
            <div class="space-y-3">
                <template x-if="getEventsForDate(selectedDate).length === 0">
                    <div class="p-8 text-center bg-obsidian-950/40 border border-obsidian-850/60 rounded-2xl max-w-md mx-auto">
                        <div class="w-10 h-10 bg-obsidian-900 border border-obsidian-850 text-slate-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-xs font-bold text-white">No visits scheduled</h4>
                        <p class="text-[10px] text-slate-400 mt-1">There are no patient records registered on this calendar date.</p>
                    </div>
                </template>

                <template x-if="getEventsForDate(selectedDate).length > 0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="event in getEventsForDate(selectedDate)" :key="event.id">
                            <div class="p-4 bg-obsidian-950/40 border border-obsidian-850/60 hover:border-obsidian-750 rounded-2xl flex items-start justify-between gap-4 transition duration-150">
                                <div class="flex items-start gap-3">
                                    <div class="h-10 w-10 bg-brand-600/10 border border-brand-500/20 text-brand-400 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-white" x-text="event.pet_name"></span>
                                            <span class="px-1.5 py-0.5 text-[8px] font-extrabold uppercase rounded bg-obsidian-900 text-slate-400 border border-obsidian-800" x-text="event.pet_species"></span>
                                        </div>
                                        <p class="text-[10px] text-slate-400 font-semibold" x-text="`Owner: ${event.customer_name}`"></p>
                                        <p class="text-xs text-slate-300 italic" x-text="event.complaints ? `“${event.complaints}”` : 'No complaint specified'"></p>
                                    </div>
                                </div>
                                <div class="text-right space-y-2 shrink-0">
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="text-xs font-bold text-white flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span x-text="event.time"></span>
                                        </span>
                                        <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-full border"
                                              :class="[
                                                  event.status === 1 ? 'bg-brand-500/10 text-brand-400 border-brand-500/20' : '',
                                                  event.status === 2 ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : '',
                                                  event.status === 3 ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : '',
                                                  event.status === 4 ? 'bg-neutral-850/40 text-neutral-500 border-neutral-800/60' : ''
                                              ]"
                                              x-text="getStatusText(event.status)">
                                        </span>
                                    </div>
                                    
                                    <!-- Action buttons -->
                                    <div class="flex justify-end gap-1.5 pt-1">
                                        <template x-if="event.status === 1 || event.status === 2">
                                            <a :href="`/visits/examine/${event.id}`" 
                                               class="px-2.5 py-1 bg-brand-600 hover:bg-brand-500 text-white text-[10px] font-bold rounded-lg transition shadow">
                                                Examine
                                            </a>
                                        </template>
                                        <template x-if="event.status === 3">
                                            <span class="text-[10px] text-emerald-400 flex items-center gap-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Closed</span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
