<?php

namespace App\Filament\Resources\OwnerPetResource\Pages;

use App\Filament\Resources\OwnerPetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOwnerPet extends CreateRecord
{
    protected static string $resource = OwnerPetResource::class;
}
