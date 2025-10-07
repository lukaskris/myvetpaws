<?php

namespace App\Filament\Resources\OpnameListResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PetsRelationManager extends RelationManager
{
    protected static string $relationship = 'pets';

    protected static ?string $title = 'Pets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pet_id')
                    ->relationship('pet', 'name')
                    ->label('Pet')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                    ]),
                
                Forms\Components\Textarea::make('pivot.medical_notes')
                    ->label('Medical Notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('species.name')
                    ->label('Species'),
                
                Tables\Columns\TextColumn::make('breed')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('pivot.medical_notes')
                    ->label('Medical Notes')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Pet')
                            ->required(),
                        Forms\Components\Textarea::make('medical_notes')
                            ->label('Medical Notes')
                            ->columnSpanFull(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}