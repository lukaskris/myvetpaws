<?php

namespace App\Filament\Resources\BreedsResource\Pages;

use App\Filament\Resources\BreedsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBreeds extends EditRecord
{
    protected static string $resource = BreedsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
