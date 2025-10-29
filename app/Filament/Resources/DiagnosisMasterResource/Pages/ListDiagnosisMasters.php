<?php

namespace App\Filament\Resources\DiagnosisMasterResource\Pages;

use App\Filament\Resources\DiagnosisMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiagnosisMasters extends ListRecords
{
    protected static string $resource = DiagnosisMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}