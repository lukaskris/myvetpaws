<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpnameListResource\Pages;
use App\Filament\Resources\OpnameListResource\RelationManagers;
use App\Models\OpnameList;
use App\Models\Customer;
use App\Models\Diagnose;
use App\Models\Pet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535),
                        
                        Forms\Components\Textarea::make('medical_notes')
                            ->label('Medical Notes')
                            ->maxLength(65535),
                    ])
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Diagnose')
                    ->schema([
                        Forms\Components\Repeater::make('diagnoses')
                            ->relationship('diagnoses')
                            ->schema([
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
                            ->columns(1)
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
        return [
            // RelationManagers\PetsRelationManager::class,
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
