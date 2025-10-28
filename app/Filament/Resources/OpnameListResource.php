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
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                        Forms\Components\Select::make('customer_id')
                            ->label('Owner')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $diagnoses = $get('diagnoses') ?? [];
                                foreach (array_keys($diagnoses) as $index) {
                                    $set("diagnoses.$index.pet_id", null);
                                }
                            }),

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
                            ->helperText('Klik Add untuk menambah detail lain pada pet yang sama.')
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                $details = array_map(function (array $detail): array {
                                    $detail['medicineDetails'] = array_values($detail['medicineDetails'] ?? []);
                                    $detail['serviceDetails'] = array_values($detail['serviceDetails'] ?? []);

                                    $sections = array_values($detail['detail_item_sections'] ?? []);

                                    if (empty($sections)) {
                                        if (! empty($detail['diagnosis_master_id']) || ! empty($detail['name']) || ! empty($detail['type']) || ! empty($detail['prognose'])) {
                                            $sections[] = 'diagnose';
                                        }

                                        if (! empty($detail['medicineDetails'])) {
                                            $sections[] = 'medicine';
                                        }

                                        if (! empty($detail['serviceDetails'])) {
                                            $sections[] = 'service';
                                        }
                                    }

                                    $detail['detail_item_sections'] = array_values(array_unique($sections));

                                    if (in_array('medicine', $detail['detail_item_sections'], true) && empty($detail['medicineDetails'])) {
                                        $detail['medicineDetails'] = [[]];
                                    }

                                    if (in_array('service', $detail['detail_item_sections'], true) && empty($detail['serviceDetails'])) {
                                        $detail['serviceDetails'] = [[]];
                                    }

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
                                            ->options(fn (Get $get) => Pet::query()
                                                ->when($get('../../customer_id'), fn ($q, $owner) => $q->where('customer_id', $owner))
                                                ->orderBy('name')
                                                ->pluck('name', 'id'))
                                            ->placeholder('Pilih Pet')
                                            ->reactive()
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->disabled(fn (Get $get) => empty($get('../../customer_id'))),
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
                                    ->addActionLabel('Add Detail')
                                    ->addAction(function (Action $action) {
                                        return $action
                                            ->modalHeading('Pilih Detail')
                                            ->modalWidth('lg')
                                            ->form([
                                                Forms\Components\ToggleButtons::make('detail_item_sections')
                                                    ->label('Detail Type')
                                                    ->options([
                                                        'diagnose' => 'Diagnose',
                                                        'medicine' => 'Medicine',
                                                        'service' => 'Service',
                                                    ])
                                                    ->multiple()
                                                    ->required()
                                                    ->rules(['required', 'array', 'min:1'])
                                                    ->columns(3),
                                            ])
                                            ->action(function (array $data, Forms\Components\Repeater $component): void {
                                                $newUuid = $component->generateUuid();
                                                $items = $component->getState() ?? [];

                                                $selectedSections = $data['detail_item_sections'];
                                                $newItem = [
                                                    'detail_item_sections' => $selectedSections,
                                                    'medicineDetails' => in_array('medicine', $selectedSections) ? [[]] : [],
                                                    'serviceDetails' => in_array('service', $selectedSections) ? [[]] : [],
                                                ];

                                                if ($newUuid) {
                                                    $items[$newUuid] = $newItem;
                                                } else {
                                                    $items[] = $newItem;
                                                    $newUuid = array_key_last($items);
                                                }

                                                $component->state($items);

                                                $childContainer = $component->getChildComponentContainer($newUuid);
                                                if ($childComponent = $childContainer->getComponent('detail_item_sections')) {
                                                    $childComponent->state($data['detail_item_sections']);
                                                }
                                                $childContainer->fill();
                                                $component->collapsed(false, shouldMakeComponentCollapsible: false);
                                                $component->callAfterStateUpdated();
                                            });
                                    })
                                    ->defaultItems(0)
                                    ->minItems(0)
                                    ->schema([
                                        Forms\Components\Hidden::make('detail_item_sections')
                                            ->default([])
                                            ->dehydrated(false)
                                            ->reactive()
                                            ->afterStateHydrated(function (Forms\Components\Component $component, $state): void {
                                                if (is_null($state)) {
                                                    $component->state([]);
                                                }
                                            }),
                                        Forms\Components\Placeholder::make('detail_item_sections_display')
                                            ->content(fn (Get $get) => null)
                                            ->hidden(),
                                        Forms\Components\Group::make([
                                            Forms\Components\Hidden::make('name')->dehydrated(),
                                        Forms\Components\Placeholder::make('diagnose_heading')
                                            ->content('Diagnose Details')
                                            ->hidden(),
                                            Forms\Components\Select::make('diagnosis_master_id')
                                                ->label('Diagnose')
                                                ->options(fn () => DiagnosisMaster::query()->orderBy('name')->pluck('name', 'id'))
                                                ->placeholder('Pilih Diagnose')
                                                ->searchable()
                                                ->preload()
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
                                            Forms\Components\Placeholder::make('diagnosis_master_notes')
                                                ->label('Diagnosis Reference Notes')
                                                ->content(fn (Get $get) => optional(DiagnosisMaster::find($get('diagnosis_master_id')))->notes ?? 'Tidak ada catatan tambahan.')
                                            ->visible(fn (Get $get) => self::shouldShowDetailSection($get, 'diagnose'))
                                                ->columnSpanFull(),
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
                                                ->visible(fn (Get $get) => in_array('diagnose', (array) ($get('detail_item_sections') ?? []))),
                                        ])
                                            ->columns(2)
                                            ->visible(fn (Get $get) => self::shouldShowDetailSection($get, 'diagnose')),
                                        Forms\Components\Repeater::make('medicineDetails')
                                            ->label('Medicine Details')
                                            ->relationship('medicineDetails')
                                            ->defaultItems(1)
                                            ->minItems(1)
                                            ->maxItems(1)
                                            ->schema([
                                                Forms\Components\Select::make('medicine_id')
                                                    ->label('Medicine')
                                                    ->options(fn () => Medicine::query()
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                                Forms\Components\Textarea::make('notes')
                                                    ->label('Notes')
                                                    ->rows(2),
                                            ])
                                            ->columns(12)
                                            ->visible(fn (Get $get) => self::shouldShowDetailSection($get, 'medicine'))
                                            ->helperText('Catatan obat untuk detail ini.'),
                                        Forms\Components\Repeater::make('serviceDetails')
                                            ->label('Service Details')
                                            ->relationship('serviceDetails')
                                            ->addActionLabel('Add Service')
                                            ->defaultItems(0)
                                            ->minItems(0)
                                            ->helperText('Detail ini berisi layanan yang dibutuhkan.')
                                            ->schema([
                                                Forms\Components\Select::make('service_id')
                                                    ->label('Service')
                                                    ->options(fn () => Service::query()
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                                Forms\Components\Textarea::make('notes')
                                                    ->label('Notes')
                                                    ->rows(2),
                                            ])
                                            ->columns(12)
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
        $sections = array_values((array) ($get('detail_item_sections') ?? []));

        if (empty($sections)) {
            if ($get('diagnosis_master_id') || $get('name') || $get('type') || $get('prognose')) {
                $sections[] = 'diagnose';
            }

            if (! empty($get('medicineDetails'))) {
                $sections[] = 'medicine';
            }

            if (! empty($get('serviceDetails'))) {
                $sections[] = 'service';
            }
        }

        return in_array($section, array_unique($sections), true);
    }

    protected static function normalizeDiagnosePayload(array $data): array
    {
        $details = array_map(function (array $detail): array {
            $detail['medicineDetails'] = array_values($detail['medicineDetails'] ?? []);
            $detail['serviceDetails'] = array_values($detail['serviceDetails'] ?? []);

            $sections = array_values($detail['detail_item_sections'] ?? []);
            $detail['detail_item_sections'] = $sections;

            if (! empty($detail['diagnosis_master_id'])) {
                $master = DiagnosisMaster::find($detail['diagnosis_master_id']);
                if ($master) {
                    $detail['name'] = $master->name;
                    $detail['notes'] = $detail['notes'] ?? null;
                }
            }

            $hasDiagnose = in_array('diagnose', $sections, true)
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

            $detail['name'] = $detail['name'] ?? 'General';
            $detail['type'] = $detail['type'] ?? 'Primary';
            $detail['prognose'] = $detail['prognose'] ?? 'Fausta';
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
        ];
    }
}
