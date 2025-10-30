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
                            ->relationship('diagnoses')
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

                                    return $detail;
                                }, array_values($data['details'] ?? []));

                                $data['details'] = $details;

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
                                    ]),
                                Forms\Components\Repeater::make('details')
                                    ->label('Detail Items')
                                    ->relationship('details')
                                    ->itemLabel(fn (array $state): string => match ($state['detail_item_sections'] ?? null) {
                                        'diagnose' => 'Diagnose',
                                        'medicine' => 'Medicine',
                                        'service'  => 'Service',
                                        default    => 'Detail',
                                    })
                                    ->createItemButtonLabel('Add Detail')
                                    ->defaultItems(0)
                                    ->minItems(0)
                                    ->collapsed(false)
                                    ->schema([
                                        Forms\Components\Select::make('detail_item_sections')
                                            ->label('Detail Type')
                                            ->options([
                                                'diagnose' => 'Diagnose',
                                                'medicine' => 'Medicine',
                                                'service' => 'Service',
                                            ])
                                            ->placeholder('Pilih tipe detail')
                                            ->required()
                                            ->native(false)
                                            ->reactive()
                                            ->afterStateHydrated(function ($state, Set $set, Get $get) {
                                                // When editing old data, the section may be missing.
                                                // Infer it from existing nested details, or default to 'diagnose'.
                                                if ($state === null || $state === '') {
                                                    $hasMedicine = ! empty($get('medicineDetails'));
                                                    $hasService = ! empty($get('serviceDetails'));

                                                    if ($hasMedicine && ! $hasService) {
                                                        $set('detail_item_sections', 'medicine');
                                                        if (empty($get('medicineDetails'))) {
                                                            $set('medicineDetails', [[]]);
                                                        }
                                                        $set('serviceDetails', []);
                                                    } elseif (! $hasMedicine && $hasService) {
                                                        $set('detail_item_sections', 'service');
                                                        if (empty($get('serviceDetails'))) {
                                                            $set('serviceDetails', [[]]);
                                                        }
                                                        $set('medicineDetails', []);
                                                    } else {
                                                        // Default to diagnose when ambiguous or empty
                                                        $set('detail_item_sections', 'diagnose');
                                                        $set('medicineDetails', []);
                                                        $set('serviceDetails', []);
                                                    }
                                                    return; // Done initializing
                                                }

                                                // Keep nested arrays consistent with the chosen section
                                                if ($state === 'medicine' && empty($get('medicineDetails'))) {
                                                    $set('medicineDetails', [[]]);
                                                    $set('serviceDetails', []);
                                                } elseif ($state === 'service' && empty($get('serviceDetails'))) {
                                                    $set('serviceDetails', [[]]);
                                                    $set('medicineDetails', []);
                                                } elseif ($state === 'diagnose') {
                                                    $set('medicineDetails', []);
                                                    $set('serviceDetails', []);
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state === 'medicine') {
                                                    if (empty($get('medicineDetails'))) {
                                                        $set('medicineDetails', [[]]);
                                                    }
                                                    $set('serviceDetails', []);
                                                } elseif ($state === 'service') {
                                                    if (empty($get('serviceDetails'))) {
                                                        $set('serviceDetails', [[]]);
                                                    }
                                                    $set('medicineDetails', []);
                                                } else {
                                                    $set('medicineDetails', []);
                                                    $set('serviceDetails', []);
                                                }
                                            }),

                                        Forms\Components\Group::make([
                                            Forms\Components\Hidden::make('name')
                                                ->default('Diagnose')
                                                ->dehydrated(),
                                        Forms\Components\Placeholder::make('diagnose_heading')
                                            ->content('Diagnose Details')
                                            ->hidden(),
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
                                                ->visible(fn (Get $get) => self::shouldShowDetailSection($get, 'diagnose'))
                                                ->required(fn (Get $get) => self::shouldShowDetailSection($get, 'diagnose'))
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
                                                ->default('Primary')
                                            ->required(fn (Get $get) => self::shouldShowDetailSection($get, 'diagnose'))
                                            ->visible(fn (Get $get) => self::shouldShowDetailSection($get, 'diagnose')),
                                            Forms\Components\Radio::make('prognose')
                                                ->label('Prognose')
                                                ->options([
                                                    'Fausta' => 'Fausta',
                                                    'Dubius' => 'Dubius',
                                                    'Infausta' => 'Infausta',
                                                ])
                                                ->default('Fausta')
                                                ->inline()
                                            ->required(fn (Get $get) => self::shouldShowDetailSection($get, 'diagnose'))
                                            ->visible(fn (Get $get) => self::shouldShowDetailSection($get, 'diagnose')),
                                            Forms\Components\Textarea::make('notes')
                                                ->label('Appointment Notes')
                                                ->rows(2)
                                                ->visible(fn (Get $get) => self::shouldShowDetailSection($get, 'diagnose')),
                                        ])
                                            ->columns(2)
                                            ->visible(fn (Get $get) => self::shouldShowDetailSection($get, 'diagnose')),
                                        Forms\Components\Group::make([
                                            Forms\Components\Repeater::make('medicineDetails')
                                                ->label('Medicine Details')
                                                ->relationship('medicineDetails')
                                                ->defaultItems(1)
                                                ->minItems(1)
                                                ->maxItems(1)
                                                ->disableItemCreation()
                                                ->disableItemDeletion()
                                                ->reorderable(false)
                                                ->collapsible(false)
                                                ->dehydrated(fn (Get $get) => self::shouldShowDetailSection($get, 'medicine'))
                                                ->columns(2)
                                                ->schema([
                                                    Forms\Components\Select::make('medicine_id')
                                                        ->label('Medicine')
                                                        ->options(fn () => Medicine::query()
                                                            ->orderBy('name')
                                                            ->pluck('name', 'id'))
                                                        ->placeholder('Pilih Obat')
                                                        ->searchable()
                                                        ->preload()
                                                        ->required()
                                                        ->columnSpan(1),
                                                    Forms\Components\Textarea::make('notes')
                                                        ->label('Notes')
                                                        ->rows(3)
                                                        ->columnSpan(1),
                                                ])
                                                ->helperText('Catatan obat untuk detail ini.'),
                                        ])
                                            ->visible(fn (Get $get) => self::shouldShowDetailSection($get, 'medicine')),
                                        Forms\Components\Group::make([
                                            Forms\Components\Repeater::make('serviceDetails')
                                                ->label('Service Details')
                                                ->relationship('serviceDetails')
                                                ->defaultItems(1)
                                                ->minItems(1)
                                                ->maxItems(1)
                                                ->disableItemCreation()
                                                ->disableItemDeletion()
                                                ->reorderable(false)
                                                ->collapsible(false)
                                                ->dehydrated(fn (Get $get) => self::shouldShowDetailSection($get, 'service'))
                                                ->columns(2)
                                                ->schema([
                                                    Forms\Components\Select::make('service_id')
                                                        ->label('Service')
                                                        ->options(fn () => Service::query()
                                                            ->orderBy('name')
                                                            ->pluck('name', 'id'))
                                                        ->placeholder('Pilih Layanan')
                                                        ->native(false)
                                                        ->searchable()
                                                        ->preload()
                                                        // Important: use relative lookup, we're inside serviceDetails.* item
                                                        ->required(fn (Get $get) => ($get('../detail_item_sections') === 'service'))
                                                        ->dehydrated(true)
                                                        ->live()
                                                        ->columnSpan(1),
                                                    Forms\Components\Textarea::make('notes')
                                                        ->label('Notes')
                                                        ->rows(3)
                                                        ->columnSpan(1),
                                                ])
                                                ->helperText('Detail ini berisi layanan yang dibutuhkan.'),
                                        ])
                                            ->visible(fn (Get $get) => self::shouldShowDetailSection($get, 'service')),
                                    ])
                                    ->columns(1),
                            ])
                            ->columns(1)
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function shouldShowDetailSection(Get $get, string $section): bool
    {
        $current = $get('detail_item_sections');
        if ($current === null || $current === '') {
            $current = 'diagnose';
        }
        return $current === $section;
    }

    protected static function normalizeDiagnosePayload(array $data): array
    {
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

            if ($section === 'medicine' && empty($detail['medicineDetails'])) {
                $detail['medicineDetails'] = [[]];
            }

            if ($section === 'service' && empty($detail['serviceDetails'])) {
                $detail['serviceDetails'] = [[]];
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
        }, array_values($data['details'] ?? []));

        $data['details'] = $details;

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
                Tables\Columns\TextColumn::make('pets_count')
                    ->label('Number of Pets')
                    ->counts('pets'),
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
