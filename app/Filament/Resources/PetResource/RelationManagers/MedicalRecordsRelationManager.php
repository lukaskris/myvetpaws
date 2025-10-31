<?php

namespace App\Filament\Resources\PetResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MedicalRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'medicalRecords';
    protected static ?string $recordTitleAttribute = 'created_at';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            // Read-only context; no form fields required for this report view.
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->latest()->limit(3);
            })
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('diagnosis')
                    ->label('Diagnosis')
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('treatment_plan')
                    ->label('Treatment Plan')
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label('Weight')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('temperature')
                    ->label('Temp')
                    ->toggleable(),
            ])
            ->filters([
                // No filters for the summary view.
            ])
            ->headerActions([
                // Read-only: hide create from this report section.
            ])
            ->actions([
                // Keep actions minimal; allow view/edit if needed later.
            ])
            ->bulkActions([
                // No bulk actions for the report section.
            ]);
    }
}

