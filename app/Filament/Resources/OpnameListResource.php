<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpnameListResource\Pages;
use App\Filament\Resources\OpnameListResource\RelationManagers;
use App\Models\OpnameList;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Pet;
use App\Models\Service;
use App\Models\DiagnosisMaster;
use App\Models\Diagnose;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Throwable;

class OpnameListResource extends Resource
{
    protected static ?string $model = OpnameList::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Appointments';
    protected static ?string $navigationLabel = 'Appointments';

    public static function getModelLabel(): string
    {
        return 'Appointment';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Appointments';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        // Internal reactive flag to force pet selects to refresh across rows
                        Forms\Components\Hidden::make('pets_version')
                            ->default(0)
                            ->dehydrated(false),
                        Forms\Components\Select::make('customer_id')
                            ->label('Owner')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateHydrated(function ($state, Set $set): void {
                                $set('form_owner_id', $state);
                            })
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $diagnoses = $get('diagnoses') ?? [];
                                foreach (array_keys($diagnoses) as $index) {
                                    $set("diagnoses.$index.pet_id", null);
                                }
                                $set('form_owner_id', $state);
                            }),
                        Forms\Components\Hidden::make('form_owner_id')
                            ->default(fn (Get $get) => $get('customer_id'))
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('discount')
                            ->label('Discount')
                            ->numeric()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Detail Information')
                    ->schema([
                        Forms\Components\Repeater::make('diagnoses')
                            ->relationship('diagnoses', modifyQueryUsing: function (Builder $query): Builder {
                                return $query->with(['details', 'details.medicineDetails', 'details.serviceDetails']);
                            })
                            ->afterStateHydrated(function (Forms\Components\Repeater $component): void {
                                $records = $component->getCachedExistingRecords();
                                if ($records->isEmpty()) {
                                    return;
                                }

                                $items = [];

                                foreach ($records as $key => $record) {
                                    $itemData = $record->attributesToArray();

                                    $diagnoseItems = [];
                                    $medicineItems = [];
                                    $serviceItems = [];

                                    foreach ($record->details ?? [] as $detail) {
                                        $section = $detail->detail_item_sections ?: 'diagnose';
                                        $medicineDetail = $detail->medicineDetails->first();
                                        $serviceDetail = $detail->serviceDetails->first();

                                        $detailPayload = [
                                            'detail_item_sections' => $section,
                                            'name' => $detail->name,
                                            'diagnosis_master_id' => $detail->diagnosis_master_id,
                                            'type' => $detail->type,
                                            'prognose' => $detail->prognose,
                                            'notes' => $detail->notes,
                                            'medicine_id' => $medicineDetail?->medicine_id,
                                            'medicine_notes' => $medicineDetail?->notes,
                                            'service_id' => $serviceDetail?->service_id,
                                            'service_notes' => $serviceDetail?->notes,
                                        ];

                                        if ($section === 'medicine') {
                                            $medicineItems[] = $detailPayload;
                                        } elseif ($section === 'service') {
                                            $serviceItems[] = $detailPayload;
                                        } else {
                                            $diagnoseItems[] = $detailPayload;
                                        }
                                    }

                                    $itemData['diagnose_items'] = $diagnoseItems;
                                    $itemData['medicine_items'] = $medicineItems;
                                    $itemData['service_items'] = $serviceItems;

                                    $items[$key] = $itemData;
                                }

                                $component->state($items);
                            })
                            ->addActionLabel('Add Pet Detail')
                            ->addAction(function (Action $action) {
                                return $action
                                    ->modalHeading('Tambah Pet Detail')
                                    ->modalDescription('Pilih satu atau beberapa pet untuk ditambahkan ke appointment ini.')
                                    ->modalWidth('lg')
                                    ->mountUsing(function (ComponentContainer $form, Get $get) {
                                        $form->fill([
                                            'owner_id' => static::resolveOwnerId($get),
                                        ]);
                                    })
                                    ->form([
                                        Forms\Components\Hidden::make('owner_id'),
                                        Forms\Components\Select::make('pet_ids')
                                            ->label('Pet')
                                            ->options(function (Get $get) {
                                                $ownerId = $get('owner_id');

                                                if (! $ownerId) {
                                                    return [];
                                                }

                                                return Pet::query()
                                                    ->where('customer_id', $ownerId)
                                                    ->orderBy('name')
                                                    ->pluck('name', 'id');
                                            })
                                            ->multiple()
                                            ->placeholder(fn (Get $get) => $get('owner_id')
                                                ? 'Pilih Pet'
                                                : 'Pilih owner terlebih dahulu')
                                            ->disabled(fn (Get $get) => ! $get('owner_id'))
                                            ->required(fn (Get $get) => (bool) $get('owner_id'))
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\Select::make('duration_days')
                                            ->label('Default Duration (days)')
                                            ->options(array_combine(range(0, 7), range(0, 7)))
                                            ->default(0)
                                            ->required(),
                                    ])
                                    ->action(function (array $data, Forms\Components\Repeater $component): void {
                                        $items = $component->getState() ?? [];
                                        $selected = collect($data['pet_ids'] ?? [])
                                            ->filter()
                                            ->unique()
                                            ->values();

                                        $existingPetIds = collect($items)->pluck('pet_id')->filter()->all();

                                        foreach ($selected as $petId) {
                                            if (in_array($petId, $existingPetIds, true)) {
                                                continue; // skip duplicates already added
                                            }

                                            $uuid = $component->generateUuid();
                                            $newItem = [
                                                'pet_id' => $petId,
                                                'duration_days' => $data['duration_days'] ?? 0,
                                                'details' => [],
                                            ];

                                            if ($uuid) {
                                                $items[$uuid] = $newItem;
                                            } else {
                                                $items[] = $newItem;
                                            }
                                        }

                                        $component->state($items);
                                        $component->collapsed(false, shouldMakeComponentCollapsible: false);
                                        $component->callAfterStateUpdated();
                                    });
                            })
                            ->helperText('Klik Add untuk menambah detail lain pada pet yang sama atau pilih banyak pet sekaligus.')
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                $diagnoseItems = [];
                                $medicineItems = [];
                                $serviceItems = [];

                                $details = array_map(function (array $detail): array {
                                    $detail['medicineDetails'] = array_values($detail['medicineDetails'] ?? []);
                                    $detail['serviceDetails'] = array_values($detail['serviceDetails'] ?? []);

                                    // Normalize previous array-based state into a single string value
                                    $section = $detail['detail_item_sections'] ?? null;
                                    if (is_array($section)) {
                                        // pick a priority: diagnose > medicine > service
                                        if (in_array('diagnose', $section, true)) {
                                            $section = 'diagnose';
                                        } elseif (in_array('medicine', $section, true)) {
                                            $section = 'medicine';
                                        } elseif (in_array('service', $section, true)) {
                                            $section = 'service';
                                        } else {
                                            $section = null;
                                        }
                                    }

                                    if (! $section) {
                                        if (! empty($detail['diagnosis_master_id']) || ! empty($detail['name']) || ! empty($detail['type']) || ! empty($detail['prognose'])) {
                                            $section = 'diagnose';
                                        } elseif (! empty($detail['medicineDetails'])) {
                                            $section = 'medicine';
                                        } elseif (! empty($detail['serviceDetails'])) {
                                            $section = 'service';
                                        }
                                    }

                                    $detail['detail_item_sections'] = $section;

                                    if ($section === 'medicine') {
                                        $medicineDetail = $detail['medicineDetails'][0] ?? [];
                                        $detail['medicine_id'] = $medicineDetail['medicine_id'] ?? null;
                                        $detail['medicine_notes'] = $medicineDetail['notes'] ?? null;
                                    }

                                    if ($section === 'service') {
                                        $serviceDetail = $detail['serviceDetails'][0] ?? [];
                                        $detail['service_id'] = $serviceDetail['service_id'] ?? null;
                                        $detail['service_notes'] = $serviceDetail['notes'] ?? null;
                                    }

                                    return $detail;
                                }, array_values($data['details'] ?? []));

                                foreach ($details as $detail) {
                                    $section = $detail['detail_item_sections'] ?? 'diagnose';
                                    if ($section === 'medicine') {
                                        $medicineItems[] = $detail;
                                    } elseif ($section === 'service') {
                                        $serviceItems[] = $detail;
                                    } else {
                                        $diagnoseItems[] = $detail;
                                    }
                                }

                                $data['diagnose_items'] = $diagnoseItems;
                                $data['medicine_items'] = $medicineItems;
                                $data['service_items'] = $serviceItems;
                                unset($data['details']);

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => static::normalizeDiagnosePayload($data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => static::normalizeDiagnosePayload($data))
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('pet_id')
                                            ->label('Pet')
                                            ->options(function (Get $get) {
                                                // Touch the version flag so Filament tracks dependency between rows
                                                $version = $get('../../pets_version');

                                                $query = Pet::query()
                                                    ->when($get('../../customer_id'), fn ($q, $owner) => $q->where('customer_id', $owner));

                                                // Exclude pets already selected in other rows, but allow current value
                                                $diagnoses = $get('../../diagnoses') ?? [];
                                                $current = $get('pet_id');
                                                $selectedIds = collect($diagnoses)
                                                    ->pluck('pet_id')
                                                    ->filter()
                                                    ->unique()
                                                    ->values()
                                                    ->all();
                                                $exclude = array_values(array_diff($selectedIds, $current ? [$current] : []));
                                                if (! empty($exclude)) {
                                                    $query->whereNotIn('id', $exclude);
                                                }

                                                return $query->orderBy('name')->pluck('name', 'id');
                                            })
                                            ->placeholder('Pilih Pet')
                                            ->reactive()
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->rules(['required', 'distinct'])
                                            ->validationAttribute('Pet')
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                // Bump a version to refresh all sibling selects and enforce uniqueness
                                                $version = (int) ($get('../../pets_version') ?? 0);
                                                $set('../../pets_version', $version + 1);
                                            }),
                                        Forms\Components\TextInput::make('duration_days')
                                            ->label('Duration (days)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(7)
                                            ->default(0),
                                Forms\Components\Placeholder::make('pet_history_preview')
                                    ->label('Riwayat Pet (3 Terakhir)')
                                    ->columnSpan(2)
                                    ->visible(fn (Get $get) => filled($get('pet_id')))
                                    ->content(fn (Get $get) => static::renderPetHistory($get('pet_id'))),
                            ]),
                        Forms\Components\Section::make('Diagnose')
                            ->schema([
                                Forms\Components\Repeater::make('diagnose_items')
                                    ->label('')
                                    ->defaultItems(0)
                                    ->minItems(0)
                                    ->addable(false)
                                    ->itemLabel('Diagnose')
                                    ->itemNumbers()
                                    ->schema([
                                        Forms\Components\Hidden::make('detail_item_sections')
                                            ->default('diagnose')
                                            ->dehydrated(),
                                        Forms\Components\Hidden::make('name')
                                            ->default('Diagnose')
                                            ->dehydrated(),
                                        Forms\Components\Select::make('diagnosis_master_id')
                                            ->label('Diagnose')
                                            ->options(fn () => DiagnosisMaster::query()
                                                ->orderBy('name')
                                                ->pluck('name', 'id'))
                                            ->placeholder('Pilih Diagnose')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nama Diagnose')
                                                    ->required(),
                                                Forms\Components\Textarea::make('notes')
                                                    ->label('Catatan')
                                                    ->rows(3),
                                            ])
                                            ->createOptionUsing(function (array $data): int {
                                                $diagnosis = DiagnosisMaster::create([
                                                    'name' => $data['name'],
                                                    'notes' => $data['notes'] ?? null,
                                                ]);

                                                return $diagnosis->getKey();
                                            })
                                            ->createOptionAction(function (Action $action) {
                                                return $action
                                                    ->modalHeading('Tambah Diagnose')
                                                    ->modalSubmitActionLabel('Simpan Diagnose')
                                                    ->modalCancelActionLabel('Batal');
                                            })
                                            ->reactive()
                                            ->afterStateHydrated(function (?int $state, Set $set): void {
                                                if ($state) {
                                                    $set('name', optional(DiagnosisMaster::find($state))->name);
                                                }
                                            })
                                            ->afterStateUpdated(function (?int $state, Set $set): void {
                                                $set('name', $state ? optional(DiagnosisMaster::find($state))->name : null);
                                            }),

                                        Forms\Components\Select::make('type')
                                            ->label('Type')
                                            ->options([
                                                'Primary' => 'Primary',
                                                'Differential' => 'Differential',
                                            ])
                                            ->default('Primary'),
                                        Forms\Components\Radio::make('prognose')
                                            ->label('Prognose')
                                            ->options([
                                                'Fausta' => 'Fausta',
                                                'Dubius' => 'Dubius',
                                                'Infausta' => 'Infausta',
                                            ])
                                            ->default('Fausta')
                                            ->inline(),
                                        Forms\Components\Textarea::make('notes')
                                            ->label('Appointment Notes')
                                            ->rows(2),
                                    ])
                                    ->columns(2),
                            ])
                            ->headerActions([
                                Action::make('addDiagnoseItem')
                                    ->icon('heroicon-o-plus-circle')
                                    ->label('')
                                    ->action(fn (Get $get, Set $set) => static::appendSectionItem($get, $set, 'diagnose_items', 'diagnose')),
                            ])
                            ->extraAttributes([
                                'class' => 'detail-section detail-section--dark',
                            ]),
                        Forms\Components\Section::make('Medicine')
                            ->schema([
                                Forms\Components\Repeater::make('medicine_items')
                                    ->label('')
                                    ->defaultItems(0)
                                    ->minItems(0)
                                    ->addable(false)
                                    ->itemLabel('Medicine')
                                    ->itemNumbers()
                                    ->schema([
                                        Forms\Components\Hidden::make('detail_item_sections')
                                            ->default('medicine')
                                            ->dehydrated(),
                                        Forms\Components\Hidden::make('name')
                                            ->default('Medicine Detail')
                                            ->dehydrated(),
                                        Forms\Components\Select::make('medicine_id')
                                            ->label('Medicine')
                                            ->options(fn () => Medicine::query()
                                                ->orderBy('name')
                                                ->pluck('name', 'id'))
                                            ->placeholder('Pilih Obat')
                                            ->reactive()
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpan(1),
                                        Forms\Components\Placeholder::make('medicine_price_display')
                                            ->label('Price')
                                            ->columnSpan(1)
                                            ->content(function (Get $get) {
                                                $medicineId = $get('medicine_id');
                                                if (! $medicineId) {
                                                    return static::formatCurrency(null);
                                                }

                                                $price = optional(Medicine::find($medicineId))->price;

                                                return static::formatCurrency($price);
                                            })
                                            ->visible(fn (Get $get) => filled($get('medicine_id'))),
                                        Forms\Components\Textarea::make('medicine_notes')
                                            ->label('Notes')
                                            ->rows(3)
                                            ->columnSpan(1)
                                            ->helperText('Catatan obat untuk detail ini.'),
                                    ])
                                    ->columns(2),
                            ])
                            ->headerActions([
                                Action::make('addMedicineItem')
                                    ->icon('heroicon-o-plus-circle')
                                    ->label('')
                                    ->action(fn (Get $get, Set $set) => static::appendSectionItem($get, $set, 'medicine_items', 'medicine')),
                            ])
                            ->extraAttributes([
                                'class' => 'detail-section detail-section--dark',
                            ]),
                        Forms\Components\Section::make('Service')
                            ->schema([
                                Forms\Components\Repeater::make('service_items')
                                    ->label('')
                                    ->defaultItems(0)
                                    ->minItems(0)
                                    ->addable(false)
                                    ->itemLabel('Service')
                                    ->itemNumbers()
                                    ->schema([
                                        Forms\Components\Hidden::make('detail_item_sections')
                                            ->default('service')
                                            ->dehydrated(),
                                        Forms\Components\Hidden::make('name')
                                            ->default('Service Detail')
                                            ->dehydrated(),
                                        Forms\Components\Select::make('service_id')
                                            ->label('Service')
                                            ->options(fn () => Service::query()
                                                ->orderBy('name')
                                                ->pluck('name', 'id'))
                                            ->placeholder('Pilih Layanan')
                                            ->reactive()
                                            ->native(false)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->dehydrated(true)
                                            ->live()
                                            ->columnSpan(1),
                                        Forms\Components\Placeholder::make('service_price_display')
                                            ->label('Price')
                                            ->columnSpan(1)
                                            ->content(function (Get $get) {
                                                $serviceId = $get('service_id');
                                                if (! $serviceId) {
                                                    return static::formatCurrency(null);
                                                }

                                                $price = optional(Service::find($serviceId))->price;

                                                return static::formatCurrency($price);
                                            })
                                            ->visible(fn (Get $get) => filled($get('service_id'))),
                                        Forms\Components\Textarea::make('service_notes')
                                            ->label('Notes')
                                            ->rows(3)
                                            ->columnSpan(1)
                                            ->helperText('Detail ini berisi layanan yang dibutuhkan.'),
                                    ])
                                    ->columns(2),
                            ])
                            ->headerActions([
                                Action::make('addServiceItem')
                                    ->icon('heroicon-o-plus-circle')
                                    ->label('')
                                    ->action(fn (Get $get, Set $set) => static::appendSectionItem($get, $set, 'service_items', 'service')),
                            ])
                            ->extraAttributes([
                                'class' => 'detail-section detail-section--dark',
                            ]),
                        Forms\Components\Repeater::make('details')
                            ->relationship('details')
                            ->schema([])
                            ->visible(false),
                            ])
                            ->columns(1)
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function renderPetHistory(?int $petId): HtmlString
    {
        if (! $petId) {
            return new HtmlString('');
        }

        $history = Diagnose::query()
            ->with([
                'opnameList:id,date',
                'details' => fn ($query) => $query->select('id', 'diagnose_id', 'name', 'notes', 'diagnosis_master_id', 'detail_item_sections'),
                'details.diagnosisMaster:id,name',
            ])
            ->where('pet_id', $petId)
            ->latest('created_at')
            ->limit(3)
            ->get();

        if ($history->isEmpty()) {
            return new HtmlString('<span class="text-sm text-gray-500">Belum ada history untuk pet ini.</span>');
        }

        $items = $history->map(function (Diagnose $diagnose): string {
            $date = optional($diagnose->opnameList)->date ?? $diagnose->created_at;
            $dateLabel = $date
                ? ($date instanceof Carbon ? $date->format('d M Y') : Carbon::parse($date)->format('d M Y'))
                : 'Tanggal tidak tersedia';

            $detailSummary = $diagnose->details
                ->map(function ($detail) {
                    $title = $detail->name ?: optional($detail->diagnosisMaster)->name;
                    $notes = $detail->notes;
                    $parts = collect([$title, $notes])->filter()->all();
                    return trim(implode(': ', $parts));
                })
                ->filter()
                ->implode('; ');

            if ($detailSummary === '') {
                $detailSummary = 'Tidak ada catatan detail.';
            }

            $metaParts = collect([$dateLabel, $diagnose->type, $diagnose->prognose])
                ->filter()
                ->implode(' · ');

            $contentParts = array_filter([
                '<span class="font-medium">' . e($diagnose->name ?? 'Diagnose') . '</span>',
                $metaParts ? '<span class="text-xs text-gray-500">' . e($metaParts) . '</span>' : null,
                '<span class="text-sm text-gray-600">' . e($detailSummary) . '</span>',
            ]);

            return '<li class="space-y-1">' . implode('<br>', $contentParts) . '</li>';
        })->implode('');

        return new HtmlString('<ul class="list-disc list-inside space-y-2">' . $items . '</ul>');
    }

    protected static function appendSectionItem(Get $get, Set $set, string $statePath, string $section): void
    {
        $items = $get($statePath) ?? [];
        $items[] = [
            'detail_item_sections' => $section,
            'medicine_id' => null,
            'medicine_notes' => null,
            'service_id' => null,
            'service_notes' => null,
        ];
        $set($statePath, $items);
    }

    protected static function formatCurrency($value): string
    {
        if ($value === null) {
            return 'Rp 0';
        }

        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    protected static function normalizeDiagnosePayload(array $data): array
    {
        $rawDetails = array_merge(
            array_values($data['diagnose_items'] ?? []),
            array_values($data['medicine_items'] ?? []),
            array_values($data['service_items'] ?? []),
            array_values($data['details'] ?? [])
        );

        $details = array_map(function (array $detail): array {
            $detail['medicineDetails'] = collect($detail['medicineDetails'] ?? [])
                ->filter(fn ($row) => ! empty(data_get($row, 'medicine_id')))
                ->values()
                ->all();
            $detail['serviceDetails'] = collect($detail['serviceDetails'] ?? [])
                ->filter(fn ($row) => ! empty(data_get($row, 'service_id')))
                ->values()
                ->all();

            // Ensure detail_item_sections is a string (diagnose|medicine|service)
            $section = $detail['detail_item_sections'] ?? null;
            if (is_array($section)) {
                // Backward compatibility – choose a single section
                if (in_array('diagnose', $section, true)) {
                    $section = 'diagnose';
                } elseif (in_array('medicine', $section, true)) {
                    $section = 'medicine';
                } elseif (in_array('service', $section, true)) {
                    $section = 'service';
                } else {
                    $section = null;
                }
            }
            $detail['detail_item_sections'] = $section;

            if ($section === 'medicine') {
                if (! empty($detail['medicine_id'])) {
                    $detail['medicineDetails'] = [[
                        'medicine_id' => $detail['medicine_id'],
                        'notes' => $detail['medicine_notes'] ?? null,
                    ]];
                }
            }

            if ($section === 'service') {
                if (! empty($detail['service_id'])) {
                    $detail['serviceDetails'] = [[
                        'service_id' => $detail['service_id'],
                        'notes' => $detail['service_notes'] ?? null,
                    ]];
                }
            }

            // Ensure unrelated arrays are empty to prevent accidental saves
            if ($section !== 'medicine') {
                $detail['medicineDetails'] = [];
            }
            if ($section !== 'service') {
                $detail['serviceDetails'] = [];
            }

            if (! empty($detail['diagnosis_master_id'])) {
                $master = DiagnosisMaster::find($detail['diagnosis_master_id']);
                if ($master) {
                    $detail['name'] = $master->name;
                    $detail['notes'] = $detail['notes'] ?? null;
                }
            }

            $hasDiagnose = ($section === 'diagnose')
                || ! empty($detail['diagnosis_master_id'])
                || ! empty($detail['name'])
                || ! empty($detail['type'])
                || ! empty($detail['prognose']);

            if (! $hasDiagnose) {
                if (! empty($detail['medicineDetails']) && empty($detail['serviceDetails'])) {
                    $detail['name'] = 'Medicine Detail';
                } elseif (empty($detail['medicineDetails']) && ! empty($detail['serviceDetails'])) {
                    $detail['name'] = 'Service Detail';
                } elseif (! empty($detail['medicineDetails']) && ! empty($detail['serviceDetails'])) {
                    $detail['name'] = 'Medicine & Service Detail';
                }
            }

            if (empty($detail['name'])) {
                $detail['name'] = match ($section) {
                    'diagnose' => 'Diagnose',
                    'medicine' => 'Medicine Detail',
                    'service' => 'Service Detail',
                    default => 'General',
                };
            }

            $detail['name'] = $detail['name'] ?? 'General';
            if ($section === 'diagnose') {
                $detail['type'] = $detail['type'] ?? 'Primary';
                $detail['prognose'] = $detail['prognose'] ?? 'Fausta';
            } else {
                // clear diagnose-only fields when not diagnose
                $detail['diagnosis_master_id'] = null;
                $detail['type'] = null;
                $detail['prognose'] = null;
            }
            $detail['notes'] = $detail['notes'] ?? null;

            return $detail;
        }, $rawDetails);

        $data['details'] = $details;
        unset($data['diagnose_items'], $data['medicine_items'], $data['service_items']);

        $firstDetail = $details[0] ?? null;

        $data['name'] = $firstDetail['name'] ?? $data['name'] ?? 'General';
        $data['type'] = $firstDetail['type'] ?? $data['type'] ?? 'Primary';
        $data['prognose'] = $firstDetail['prognose'] ?? $data['prognose'] ?? 'Fausta';

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Owner')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Discount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (OpnameList $record): string => static::getUrl('invoice', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PetsRelationManager::class,
            RelationManagers\DiagnosesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'calendar' => Pages\Calendar::route('/calendar'),
            'index' => Pages\ListOpnameLists::route('/'),
            'create' => Pages\CreateOpnameList::route('/create'),
            'edit' => Pages\EditOpnameList::route('/{record}/edit'),
            'invoice' => Pages\ViewInvoice::route('/{record}/invoice'),
        ];
    }

    protected static function resolveOwnerId(Get $get): ?int
    {
        $paths = [
            '../../customer_id',
            '../../../customer_id',
            '../../../../customer_id',
            '../../current_owner_id',
            '../../../current_owner_id',
            '../../../../current_owner_id',
            '../../form_owner_id',
            '../../../form_owner_id',
            '../../../../form_owner_id',
            'current_owner_id',
            'customer_id',
            'form_owner_id',
            'data.current_owner_id',
            'data.customer_id',
            'data.form_owner_id',
            'record.customer_id',
            'record.customer.id',
        ];

        foreach ($paths as $path) {
            try {
                $value = $get($path);
            } catch (Throwable $exception) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $value = data_get($value, 'id');
            }

            if ($value === null || $value === '') {
                continue;
            }

            return (int) $value;
        }

        if (method_exists($get, 'getLivewire')) {
            $livewire = $get->getLivewire();

            if ($livewire) {
                    $fallbackPaths = [
                        'data.current_owner_id',
                        'data.customer_id',
                        'data.form_owner_id',
                        'form.data.current_owner_id',
                        'form.data.customer_id',
                        'form.data.form_owner_id',
                        'record.customer_id',
                        'record.customer.id',
                    ];

                foreach ($fallbackPaths as $path) {
                    $value = data_get($livewire, $path);

                    if ($value === null || $value === '') {
                        continue;
                    }

                    return (int) $value;
                }
            }
        }

        return null;
    }
}
