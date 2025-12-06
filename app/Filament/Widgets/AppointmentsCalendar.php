<?php

namespace App\Filament\Widgets;

use App\Models\OpnameList;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class AppointmentsCalendar extends Widget
{
    protected static string $view = 'filament.widgets.appointments-calendar';

    protected static ?int $sort = 1;

    public array $calendarWeeks = [];

    public string $calendarMonthLabel = '';

    public string $activeDate;

    public function mount(): void
    {
        $this->activeDate = now()->toDateString();
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

