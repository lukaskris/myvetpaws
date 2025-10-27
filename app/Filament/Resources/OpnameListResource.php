<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpnameListResource\Pages;
use App\Models\Customer;
use App\Models\DiagnosisOption;
use App\Models\OpnameList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OpnameListResource extends Resource
{
    protected static ?string $model = OpnameList::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Opnames';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Opname Details')
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('name')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('customer_id')
                            ->label('Owner')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set) => $set('pets', [])),
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\Select::make('pets')
                            ->label('Pets')
                            ->relationship(
                                name: 'pets',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Get $get): void {
                                    $customerId = $get('customer_id');

                                    if (! $customerId) {
                                        $query->whereRaw('1 = 0');

                                        return;
                                    }

                                    $query->where('customer_id', $customerId);
                                },
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->disabled(fn (Get $get): bool => blank($get('customer_id')))
                            ->helperText('Select owner first to choose pets.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(65535),
                        Forms\Components\Textarea::make('medical_notes')
                            ->label('Medical Notes')
                            ->rows(4)
                            ->maxLength(65535),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                Forms\Components\Section::make('Diagnoses')
                    ->schema([
                        Forms\Components\Repeater::make('diagnoses')
                            ->relationship()
                            ->label('Diagnoses')
                            ->schema([
                                Forms\Components\Select::make('name')
                                    ->label('Select Diagnose')
                                    ->options(function (): array {
                                        $custom = DiagnosisOption::query()
                                            ->orderBy('name')
                                            ->pluck('name')
                                            ->all();

                                        $customOptions = collect($custom)
                                            ->mapWithKeys(fn (string $name) => [$name => $name])
                                            ->all();

                                        $defaults = [
                                            'Alergi' => 'Alergi',
                                            'Blood Parasitic' => 'Blood Parasitic',
                                            'Clamidia' => 'Clamidia',
                                            'Cystitis' => 'Cystitis',
                                            'Ear mite' => 'Ear mite',
                                            'Endoparasitic' => 'Endoparasitic',
                                        ];

                                        return $customOptions + $defaults;
                                    })
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Diagnose Name')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->createOptionUsing(function (array $data): string {
                                        $option = DiagnosisOption::firstOrCreate([
                                            'name' => $data['name'],
                                        ]);

                                        return $option->name;
                                    })
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'Primary' => 'Primary',
                                        'Differential' => 'Differential',
                                    ])
                                    ->default('Primary')
                                    ->required(),
                                Forms\Components\Radio::make('prognose')
                                    ->label('Prognose')
                                    ->options([
                                        'Fausta' => 'Fausta',
                                        'Dubius' => 'Dubius',
                                        'Infausta' => 'Infausta',
                                    ])
                                    ->inline()
                                    ->default('Fausta')
                                    ->required()
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->minItems(0)
                            ->columnSpanFull()
                            ->createItemButtonLabel('Add diagnosis'),
                    ])
                    ->columnSpanFull(),
            ]);
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
                
                Tables\Columns\TextColumn::make('price')
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
                
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
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
        return [];
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
