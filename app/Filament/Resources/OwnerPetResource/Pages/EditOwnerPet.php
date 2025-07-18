<?php

namespace App\Filament\Resources\OwnerPetResource\Pages;

use App\Filament\Resources\OwnerPetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOwnerPet extends EditRecord
{
    protected static string $resource = OwnerPetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
