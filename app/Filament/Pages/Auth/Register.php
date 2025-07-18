<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                // Select::make('role')
                //     ->options([
                //         1 => 'Owner',
                //         2 => 'Admin',
                //         3 => 'Customer'
                //     ])
                //     ->required()
                //     ->native(false)
            ]);
    }
}