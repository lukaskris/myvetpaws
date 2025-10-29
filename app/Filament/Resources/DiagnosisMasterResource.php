<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiagnosisMasterResource\Pages;
use App\Models\DiagnosisMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DiagnosisMasterResource extends Resource
{
    protected static ?string $model = DiagnosisMaster::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Diagnose';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Diagnose';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Diagnose')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nama diagnose'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Diagnose')
                    ->searchable()
                    ->sortable(),
                // notes column removed
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
                // No filters for now
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiagnosisMasters::route('/'),
            'create' => Pages\CreateDiagnosisMaster::route('/create'),
            'edit' => Pages\EditDiagnosisMaster::route('/{record}/edit'),
        ];
    }
}