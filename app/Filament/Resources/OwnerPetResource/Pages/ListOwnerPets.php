<?php

namespace App\Filament\Resources\OwnerPetResource\Pages;

use App\Filament\Resources\OwnerPetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOwnerPets extends ListRecords
{
    protected static string $resource = OwnerPetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
