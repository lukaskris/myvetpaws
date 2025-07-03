<?php
// app/Filament/Pages/Auth/Register.php
namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(),

                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->required(),

                TextInput::make('password')
                    ->password()
                    ->required()
                    ->confirmed(),

                TextInput::make('password_confirmation')
                    ->password()
                    ->required()
                    ->label('Confirm Password'),
            ]);
    }
}
