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
                                            ->options(fn (Get $get) => Pet::query()
                                                ->when($get('../../customer_id'), fn ($q, $owner) => $q->where('customer_id', $owner))
                                                ->orderBy('name')
                                                ->pluck('name', 'id'))
                                            ->placeholder('Pilih Pet')
                                            ->reactive()
                                            ->searchable()
                                            ->preload()
                                            ->required(),
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
                                                ->columns(2)
                                                ->schema([
                                                    Forms\Components\Select::make('service_id')
                                                        ->label('Service')
                                                        ->options(fn () => Service::query()
                                                            ->orderBy('name')
                                                            ->pluck('name', 'id'))
                                                        ->placeholder('Pilih Layanan')
                                                        ->searchable()
                                                        ->preload()
                                                        ->required()
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
        return $current === $section;
    }

    protected static function normalizeDiagnosePayload(array $data): array
    {
        $details = array_map(function (array $detail): array {
            $detail['medicineDetails'] = array_values($detail['medicineDetails'] ?? []);
            $detail['serviceDetails'] = array_values($detail['serviceDetails'] ?? []);

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
}
