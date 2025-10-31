<?php

namespace App\Filament\Resources\PetResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DiagnosesRelationManager extends RelationManager
{
    protected static string $relationship = 'diagnoses';
    protected static ?string $title = 'Last 3 Visits';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->with(['details', 'opnameList'])
                    ->latest()
                    ->limit(3);
            })
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Diagnosis')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Primary' => 'primary',
                        'Differential' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('prognose')
                    ->label('Prognose'),
                Tables\Columns\TextColumn::make('details_summary')
                    ->label('Ringkasan')
                    ->state(function ($record) {
                        $names = $record->details?->pluck('name')->filter()->take(5)->implode(', ');
                        return $names ?: '-';
                    })
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}

