<?php

namespace App\Filament\Resources\OpnameListResource\Pages;

use App\Filament\Resources\OpnameListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOpnameLists extends ListRecords
{
    protected static string $resource = OpnameListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
