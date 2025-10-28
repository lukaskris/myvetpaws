<?php

namespace App\Filament\Resources\OpnameListResource\Pages;

use App\Filament\Resources\OpnameListResource;
use App\Models\Customer;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditOpnameList extends EditRecord
{
    protected static string $resource = OpnameListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name'] = $this->makeGeneratedName($data);

        return $data;
    }

    private function makeGeneratedName(array $data): string
    {
        $ownerName = null;

        if (! empty($data['customer_id'])) {
            $ownerName = Customer::query()
                ->whereKey($data['customer_id'])
                ->value('name');
        }

        $date = isset($data['date'])
            ? Carbon::parse($data['date'])->format('d M Y')
            : Carbon::now()->format('d M Y');

        return trim(($ownerName ?: 'Appointment') . ' - ' . $date);
    }
}
