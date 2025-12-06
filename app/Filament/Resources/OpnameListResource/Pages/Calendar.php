<?php

namespace App\Filament\Resources\OpnameListResource\Pages;

use App\Filament\Resources\OpnameListResource;
use App\Models\OpnameList;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;

class Calendar extends Page
{
    protected static string $resource = OpnameListResource::class;

    protected static string $view = 'filament.resources.opname-list-resource.pages.calendar';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Calendar';

    protected static ?string $navigationGroup = 'Appointments';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = true;

    public array $calendarWeeks = [];

    public string $calendarMonthLabel = '';

    public string $activeDate;

    public function mount(): void
    {
        $this->activeDate = request()->query('month', now()->toDateString());
        $this->buildAppointmentCalendar();
    }

    public function goToPreviousMonth(): void
    {
        $this->activeDate = $this->resolveFocusDate()->subMonth()->toDateString();
        $this->buildAppointmentCalendar();
    }

    public function goToNextMonth(): void
    {
        $this->activeDate = $this->resolveFocusDate()->addMonth()->toDateString();
        $this->buildAppointmentCalendar();
    }

    public function goToToday(): void
    {
        $this->activeDate = now()->toDateString();
        $this->buildAppointmentCalendar();
    }

    protected function buildAppointmentCalendar(): void
    {
        $focusDate = $this->resolveFocusDate();
        $startOfMonth = $focusDate->copy()->startOfMonth();
        $endOfMonth = $focusDate->copy()->endOfMonth();

        $appointments = OpnameList::query()
            ->select('id', 'name', 'customer_id', 'date')
            ->with('customer:id,name')
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('date')
            ->get()
            ->groupBy(fn (OpnameList $appointment) => optional($appointment->date)->format('Y-m-d'));

        $appointmentsByDate = [];
        foreach ($appointments as $date => $items) {
            $appointmentsByDate[$date] = $items->map(function (OpnameList $appointment): array {
                return [
                    'id' => $appointment->id,
                    'name' => $appointment->name ?? 'Appointment',
                    'owner' => optional($appointment->customer)->name,
                ];
            })->values()->all();
        }

        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        $cursor = $calendarStart->copy();

        while ($cursor->lte($calendarEnd)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $dateString = $cursor->format('Y-m-d');

                $week[] = [
                    'day' => $cursor->day,
                    'date' => $dateString,
                    'is_current_month' => $cursor->month === $startOfMonth->month,
                    'is_today' => $cursor->isToday(),
                    'appointments' => $appointmentsByDate[$dateString] ?? [],
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        $this->calendarWeeks = $weeks;
        $this->calendarMonthLabel = $startOfMonth->translatedFormat('F Y');
    }

    protected function resolveFocusDate(): Carbon
    {
        try {
            return Carbon::parse($this->activeDate)->startOfDay();
        } catch (\Throwable $exception) {
            return now()->startOfDay();
        }
    }
}
