<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpnameListResource\Pages;
use App\Filament\Resources\OpnameListResource\RelationManagers;
use App\Models\OpnameList;
use App\Models\Customer;
use App\Models\Pet;
use Filament\Forms;
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
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),

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

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('discount')
                            ->label('Discount')
                            ->numeric()
                            ->required(),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Diagnose')
                    ->schema([
                        Forms\Components\Repeater::make('diagnoses')
                            ->relationship('diagnoses')
                            ->addActionLabel('Add Diagnose')
                            ->helperText('Klik Add untuk menambah diagnose lain pada pet yang sama.')
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

                                Forms\Components\Select::make('name')
                                    ->label('Select Diagnose')
                                    ->options([
                                        'Alergi' => 'Alergi',
                                        'Blood Parasitic' => 'Blood Parasitic',
                                        'Clamidia' => 'Clamidia',
                                        'Cystitis' => 'Cystitis',
                                        'Ear mite' => 'Ear mite',
                                        'Endoparasitic' => 'Endoparasitic',
                                    ])
                                    ->placeholder('Pilih Diagnose')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('type')
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
                                    ->default('Fausta')
                                    ->required()
                                    ->inline(),
                            ])
                            ->columns(2)
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

