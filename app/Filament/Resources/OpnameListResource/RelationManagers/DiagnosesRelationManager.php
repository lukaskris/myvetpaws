<?php

namespace App\Filament\Resources\OpnameListResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DiagnosesRelationManager extends RelationManager
{
    protected static string $relationship = 'diagnoses';
    
    protected static ?string $title = 'Diagnose';
    
    protected static ?string $modelLabel = 'Diagnose';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Diagnose')
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
                    ->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('prognose')
                    ->label('Prognose')
                    ->formatStateUsing(function ($state) {
                        return view('filament.components.prognose-radio', [
                            'state' => $state,
                        ]);
                    }),
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Primary' => 'primary',
                        'Differential' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->formatStateUsing(function ($record) {
                        return view('filament.components.delete-button', [
                            'record' => $record,
                        ]);
                    }),
            ])
            ->filters([
                //
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