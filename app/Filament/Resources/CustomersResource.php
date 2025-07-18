<?php

namespace App\Filament\Resources;

use App\Models\Customer;
use App\Filament\Resources\CustomersResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomersResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationLabel = 'Owner';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Client';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\FileUpload::make('profile_picture')
                    ->label('Profile Picture')
                    ->image()
                    ->directory('profile-pictures')
                    ->imagePreviewHeight('100'),
                Forms\Components\TextInput::make('title')->label('Title'),
                Forms\Components\TextInput::make('email')->email()->label('Email'),
                Forms\Components\TextInput::make('phone')->tel()->label('Phone'),
                Forms\Components\TextInput::make('address')->label('Address'),
                Forms\Components\Section::make('Pets')
                    ->schema([
                        Forms\Components\Repeater::make('pets')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\Select::make('species_id')
                                    ->label('Species')
                                    ->relationship('species', 'name')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')->required(),
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('breed_id')
                                    ->label('Breed')
                                    ->relationship('breed', 'name')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')->required(),
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('gender')
                                ->options([
                                    'Male' => 'male',
                                    'Female' => 'female',
                                ]),
                                Forms\Components\DatePicker::make('birth_date'),
                            ])
                            ->label('Pets')
                            ->createItemButtonLabel('Add Pet'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name'),
                Tables\Columns\TextColumn::make('email')->label('Email'),
                Tables\Columns\TextColumn::make('phone')->label('Phone'),
                Tables\Columns\TextColumn::make('address')->label('Address'),
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

    // public static function getRelations(): array
    // {
    //     return [
    //         \App\Filament\Resources\CustomersResource\RelationManagers\PetRelationManager::class,
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomers::route('/create'),
            'edit' => Pages\EditCustomers::route('/{record}/edit'),
        ];
    }
}
