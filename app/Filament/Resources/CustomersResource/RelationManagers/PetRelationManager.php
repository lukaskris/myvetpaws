<?php

namespace App\Filament\Resources\CustomersResource\RelationManagers;

use App\Models\Pet;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Database\Eloquent\Relations\Relation;

class PetRelationManager extends RelationManager
{
    protected static string $relationship = 'pets';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('species'),
            Forms\Components\TextInput::make('breed'),
            Forms\Components\Select::make('gender')
            ->options([
                'Male' => 'male',
                'Female' => 'female',
            ]),
            Forms\Components\DatePicker::make('birth_date'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('species')->searchable(),
            Tables\Columns\TextColumn::make('breed')->searchable(),
            Tables\Columns\TextColumn::make('gender')->searchable(),
            Tables\Columns\TextColumn::make('birth_date')->date(),
        ])
        ->filters([
            //
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make(),
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
}