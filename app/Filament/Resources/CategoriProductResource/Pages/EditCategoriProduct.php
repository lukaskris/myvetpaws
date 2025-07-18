<?php

namespace App\Filament\Resources\CategoriProductResource\Pages;

use App\Filament\Resources\CategoriProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoriProduct extends EditRecord
{
    protected static string $resource = CategoriProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
