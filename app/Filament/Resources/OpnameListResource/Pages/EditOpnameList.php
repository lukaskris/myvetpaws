<?php

namespace App\Filament\Resources\OpnameListResource\Pages;

use App\Filament\Resources\OpnameListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOpnameList extends EditRecord
{
    protected static string $resource = OpnameListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
