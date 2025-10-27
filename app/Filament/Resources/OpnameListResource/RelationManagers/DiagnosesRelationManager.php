<?php

namespace App\Filament\Resources\OpnameListResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DiagnosesRelationManager extends RelationManager
{
    protected static string $relationship = 'diagnoses';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Diagnosis')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('prognose')
                    ->options([
                        'Fausta' => 'Fausta',
                        'Dubius' => 'Dubius',
                        'Infausta' => 'Infausta',
                    ])
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'Primary' => 'Primary',
                        'Differential' => 'Differential',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Diagnosis')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('prognose')
                    ->colors([
                        'success' => 'Fausta',
                        'warning' => 'Dubius',
                        'danger' => 'Infausta',
                    ]),
                Tables\Columns\TextColumn::make('type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
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
}