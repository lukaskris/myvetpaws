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

                Forms\Components\TextInput::make('pivot.duration_days')
                    ->label('Duration (days)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(7)
                    ->default(0),
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

                Tables\Columns\TextColumn::make('pivot.duration_days')
                    ->label('Duration (days)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('Remaining')
                    ->getStateUsing(function ($record) {
                        $duration = (int) ($record->pivot->duration_days ?? 0);
                        $owner = $this->getOwnerRecord();
                        $start = optional($owner)->date; // count from appointment date
                        $diff = $start ? now()->diffInDays($start) : 0;
                        return max($duration - $diff, 0);
                    }),

                Tables\Columns\IconColumn::make('pivot.is_done')
                    ->label('Done')
                    ->boolean(),

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
                        Forms\Components\TextInput::make('duration_days')
                            ->label('Duration (days)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(7)
                            ->default(0),
                        Forms\Components\Textarea::make('medical_notes')
                            ->label('Medical Notes')
                            ->columnSpanFull(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\Action::make('done')
                    ->label('Done')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function ($record) {
                        $owner = $this->getOwnerRecord();
                        $start = optional($owner)->date;
                        $diff = $start ? now()->diffInDays($start) : 0;
                        $remaining = max((int)($record->pivot->duration_days ?? 0) - $diff, 0);
                        return (int)($record->pivot->is_done ?? 0) === 0 && $remaining === 0;
                    })
                    ->action(function ($record) {
                        $record->pivot->update(['is_done' => true]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}

