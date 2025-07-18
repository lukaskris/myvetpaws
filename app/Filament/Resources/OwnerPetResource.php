<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerPetResource\Pages;
use App\Filament\Resources\OwnerPetResource\RelationManagers;
use App\Models\OwnerPet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OwnerPetResource extends Resource
{
    protected static ?string $model = OwnerPet::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('profile_picture')
                    ->image()
                    ->directory('owner-pets')
                    ->label('Profile Picture')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('title')
                    ->maxLength(50)
                    ->label('Title'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Name'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->label('Email'),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20)
                    ->label('Phone'),
                Forms\Components\Textarea::make('address')
                    ->maxLength(500)
                    ->label('Address'),
                Forms\Components\Repeater::make('pets')
                    ->relationship('pets')
                    ->schema([
                        Forms\Components\TextInput::make('name')->required()->label('Pet Name'),
                        Forms\Components\TextInput::make('age')->numeric()->label('Age'),
                        Forms\Components\Select::make('id')->label('Species')->relationship('species', 'name'),
                        Forms\Components\Select::make('id')->label('Breed')->relationship('breeds', 'name'),
                        Forms\Components\TextInput::make('color')->label('Color'),
                    ])
                    ->label('Pets')
                    ->minItems(1)
                    ->addActionLabel('Add Pet')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwnerPets::route('/'),
            'create' => Pages\CreateOwnerPet::route('/create'),
            'edit' => Pages\EditOwnerPet::route('/{record}/edit'),
        ];
    }
}
