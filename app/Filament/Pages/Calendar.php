<?php

namespace App\Filament\Pages;

use App\Models\OpnameList;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Calendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Opnames';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.calendar';

    protected static ?string $title = 'Calendar';

    public function getViewData(): array
    {
        $events = OpnameList::query()
            ->select(['id', 'date', 'name'])
            ->get()
            ->map(function ($o) {
                $start = $o->date ? \Carbon\Carbon::parse($o->date)->format('Y-m-d') : null;
                return [
                    'id' => $o->id,
                    'title' => $o->name ?? 'Opname',
                    'start' => $start,
                    'allDay' => true,
                ];
            })
            ->values()
            ->toArray();

        return [
            'events' => $events,
        ];
    }
}