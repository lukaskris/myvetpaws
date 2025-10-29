<?php

namespace App\Filament\Resources\OpnameListResource\Pages;

use App\Filament\Resources\OpnameListResource;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = OpnameListResource::class;

    protected static string $view = 'filament.resources.opname-list-resource.pages.view-invoice';

    public array $invoiceItems = [];

    public array $invoiceTotals = [];

    public function mount($record): void
    {
        parent::mount($record);

        $this->record->load([
            'customer',
            'diagnoses.pet',
            'diagnoses.details.diagnosisMaster',
            'diagnoses.details.medicineDetails.medicine',
            'diagnoses.details.serviceDetails.service',
        ]);

        $this->prepareInvoiceData();
    }

    protected function prepareInvoiceData(): void
    {
        $items = [];
        $medicineTotal = 0;
        $serviceTotal = 0;

        foreach ($this->record->diagnoses as $diagnose) {
            foreach ($diagnose->details as $detail) {
                // Medicines
                foreach ($detail->medicineDetails as $medicineDetail) {
                    $medicine = $medicineDetail->medicine;
                    if (! $medicine) {
                        continue;
                    }

                    $price = (float) ($medicine->price ?? 0);
                    $medicineTotal += $price;

                    $items[] = [
                        'type' => 'Medicine',
                        'name' => $medicine->name,
                        'notes' => $medicineDetail->notes,
                        'price' => $price,
                    ];
                }

                // Services
                foreach ($detail->serviceDetails as $serviceDetail) {
                    $service = $serviceDetail->service;
                    if (! $service) {
                        continue;
                    }

                    $price = (float) ($service->price ?? 0);
                    $serviceTotal += $price;

                    $items[] = [
                        'type' => 'Service',
                        'name' => $service->name,
                        'notes' => $serviceDetail->notes,
                        'price' => $price,
                    ];
                }
            }
        }

        $subtotal = $medicineTotal + $serviceTotal;
        $discount = (float) ($this->record->discount ?? 0);
        $total = max($subtotal - $discount, 0);

        $this->invoiceItems = $items;
        $this->invoiceTotals = [
            'medicine' => $medicineTotal,
            'service' => $serviceTotal,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
        ];
    }

    public function getInvoiceNumber(): string
    {
        $createdAt = $this->record->created_at;

        return sprintf(
            'INV-%s-%s',
            $createdAt ? $createdAt->format('ymd') : now()->format('ymd'),
            str_pad((string) $this->record->getKey(), 4, '0', STR_PAD_LEFT)
        );
    }
}

