<?php

namespace App\Filament\Resources\OpnameListResource\RelationManagers;

use App\Models\Medicine;
use App\Models\Service;
use App\Models\DiagnosisMaster;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DiagnosesRelationManager extends RelationManager
{
    protected static string $relationship = 'diagnoses';
    
    protected static ?string $title = 'Diagnose';
    
    protected static ?string $modelLabel = 'Diagnose';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('pet_id')
                                    ->label('Pet')
                                    ->relationship('pet', 'name', modifyQueryUsing: function ($query) {
                                        $ownerId = optional($this->getOwnerRecord())->customer_id;
                                        return $ownerId ? $query->where('customer_id', $ownerId) : $query;
                                    })
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
                            ->addActionLabel('Tambah Detail')
                            ->itemLabel(fn (array $state): string => match ($state['detail_item_sections'] ?? null) {
                                'diagnose' => 'Diagnose',
                                'medicine' => 'Medicine',
                                'service'  => 'Service',
                                default    => 'Detail',
                            })
                            ->addAction(function (Action $action) {
                                return $action
                                    ->modalHeading('Tambah Detail')
                                    ->modalDescription('Pilih tipe detail yang ingin ditambahkan')
                                    ->modalWidth('lg')
                                    ->form([
                                        Forms\Components\ToggleButtons::make('detail_item_section_choice')
                                            ->label('Detail Type')
                                            ->options([
                                                'diagnose' => 'Diagnose',
                                                'medicine' => 'Medicine',
                                                'service' => 'Service',
                                            ])
                                            ->icons([
                                                'diagnose' => 'heroicon-o-clipboard-document-check',
                                                'medicine' => 'heroicon-o-beaker',
                                                'service'  => 'heroicon-o-wrench-screwdriver',
                                            ])
                                            ->colors([
                                                'diagnose' => 'primary',
                                                'medicine' => 'success',
                                                'service'  => 'warning',
                                            ])
                                            ->required()
                                            ->inline()
                                            ->columns(3),
                                    ])
                                    ->action(function (array $data, Forms\Components\Repeater $component): void {
                                        $newUuid = $component->generateUuid();
                                        $items = $component->getState() ?? [];

                                       $section = $data['detail_item_section_choice'] ?? 'diagnose';
 
                                        $newItem = [
                                            'detail_item_sections' => $section,
                                            'medicineDetails' => $section === 'medicine' ? [[]] : [],
                                            'serviceDetails' => $section === 'service' ? [[]] : [],
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
                                            $childComponent->state($section);
                                        }
                                        $childContainer->fill();
                                        $component->collapsed(false, shouldMakeComponentCollapsible: false);
                                        $component->callAfterStateUpdated();
                                    });
                            })
                            ->defaultItems(0)
                            ->minItems(0)
                            ->collapsed(false)
                            ->schema([
                                Forms\Components\Hidden::make('detail_item_sections')
                                    ->default('diagnose')
                                    ->dehydrated()
                                    ->reactive()
                                    ->afterStateHydrated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if ($state === 'medicine' && empty($get('medicineDetails'))) {
                                            $set('medicineDetails', [[]]);
                                            $set('serviceDetails', []);
                                        } elseif ($state === 'service' && empty($get('serviceDetails'))) {
                                            $set('serviceDetails', [[]]);
                                            $set('medicineDetails', []);
                                        } elseif ($state === 'diagnose') {
                                            // Ensure diagnose-only item does not carry other arrays
                                            $set('medicineDetails', []);
                                            $set('serviceDetails', []);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
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
                                            // diagnose or other -> clear both arrays
                                            $set('medicineDetails', []);
                                            $set('serviceDetails', []);
                                        }
                                    }),
                                Forms\Components\Group::make([
                                    Forms\Components\Hidden::make('name')->dehydrated(),
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
                                    ->columns(2)
                                    ->visible(fn (Forms\Get $get) => $this->shouldShowDetailSection($get, 'diagnose')),
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
                                        ->schema([
                                            Forms\Components\Select::make('medicine_id')
                                                ->label('Medicine')
                                                ->options(fn () => Medicine::query()
                                                    ->orderBy('name')
                                                    ->pluck('name', 'id'))
                                                ->placeholder('Pilih Obat')
                                                ->searchable()
                                                ->preload()
                                                ->required(),
                                            Forms\Components\Textarea::make('notes')
                                                ->label('Notes')
                                                ->rows(2),
                                        ])
                                        ->columns(12)
                                        ->helperText('Catatan obat untuk detail ini.'),
                                ])
                                    ->visible(fn (Forms\Get $get) => $this->shouldShowDetailSection($get, 'medicine')),
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
                                        ->schema([
                                            Forms\Components\Select::make('service_id')
                                                ->label('Service')
                                                ->options(fn () => Service::query()
                                                    ->orderBy('name')
                                                    ->pluck('name', 'id'))
                                                ->placeholder('Pilih Layanan')
                                                ->searchable()
                                                ->preload()
                                                ->required(),
                                            Forms\Components\Textarea::make('notes')
                                                ->label('Notes')
                                                ->rows(2),
                                        ])
                                        ->columns(12)
                                        ->helperText('Detail ini berisi layanan yang dibutuhkan.'),
                                ])
                                    ->visible(fn (Forms\Get $get) => $this->shouldShowDetailSection($get, 'service')),
                            ])
                            ->columns(1),
                    ])
                    ->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('prognose')
                    ->label('Prognose')
                    ->formatStateUsing(function ($state) {
                        return view('filament.components.prognose-radio', [
                            'state' => $state,
                        ]);
                    }),
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Primary' => 'primary',
                        'Differential' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->formatStateUsing(function ($record) {
                        return view('filament.components.delete-button', [
                            'record' => $record,
                        ]);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
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
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeDiagnosePayload($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalizeDiagnosePayload($data);
    }

    protected function shouldShowDetailSection(Forms\Get $get, string $section): bool
    {
        $current = $get('detail_item_sections');
        if ($current === null || $current === '') {
            $current = 'diagnose';
        }
        return $current === $section;
    }

    protected function normalizeDiagnosePayload(array $data): array
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
}