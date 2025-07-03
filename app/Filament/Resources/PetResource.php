<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PetResource\Pages;
use App\Filament\Resources\PetResource\RelationManagers;
use App\Models\Pet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PetResource extends Resource
{
    protected static ?string $model = Pet::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name', function ($query) {
                        return $query->whereHas('clinic', function ($q) {
                            $q->where('user_id', auth()->id());
                        });
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        $customer = \App\Models\Customer::find($state);
                        if ($customer && $customer->clinic) {
                            $set('clinic_name', $customer->clinic->name);
                            $set('clinic_id', $customer->clinic_id);
                        } else {
                            $set('clinic_name', null);
                            $set('clinic_id', null);
                        }
                    }),
                Forms\Components\TextInput::make('clinic_name')
                    ->label('Clinic')
                    ->disabled()
                    ->afterStateHydrated(function ($component, $state, $set, $get) {
                        $customerId = $get('customer_id');
                        if ($customerId) {
                            $customer = \App\Models\Customer::find($customerId);
                            if ($customer && $customer->clinic) {
                                $set('clinic_name', $customer->clinic->name);
                            }
                        }
                    })
                    ->dehydrated(false),
                Forms\Components\Hidden::make('clinic_id')
                    ->afterStateHydrated(function ($component, $state, $set, $get) {
                        $customerId = $get('customer_id');
                        if ($customerId) {
                            $customer = \App\Models\Customer::find($customerId);
                            if ($customer) {
                                $set('clinic_id', $customer->clinic_id);
                            }
                        }
                    })
                    ->dehydrated(),
                // Forms\Components\Select::make('clinic_id')
                //     ->label('Clinic')
                //     ->options(function ($get) {
                //         $customerId = $get('customer_id');
                //         if (!$customerId) return [];
                //         $customer = \App\Models\Customer::find($customerId);
                //         if (!$customer) return [];
                //         return [
                //             $customer->clinic_id => optional($customer->clinic)->name
                //         ];
                //     })
                //     ->required()
                //     ->disabled(fn ($get) => ! $get('customer_id'))
                //     ->dehydrated(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('species'),
                Forms\Components\TextInput::make('breed'),
                Forms\Components\TextInput::make('gender'),
                Forms\Components\DatePicker::make('birth_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clinic_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('species')
                    ->searchable(),
                Tables\Columns\TextColumn::make('breed')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
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
            'index' => Pages\ListPets::route('/'),
            'create' => Pages\CreatePet::route('/create'),
            'edit' => Pages\EditPet::route('/{record}/edit'),
        ];
    }
}
