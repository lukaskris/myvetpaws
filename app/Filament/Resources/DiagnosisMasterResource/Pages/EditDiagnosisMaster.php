<?php

namespace App\Filament\Resources\DiagnosisMasterResource\Pages;

use App\Filament\Resources\DiagnosisMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiagnosisMaster extends EditRecord
{
    protected static string $resource = DiagnosisMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}