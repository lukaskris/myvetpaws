<?php
// app/Filament/Pages/SetupClinic.php
namespace App\Filament\Pages;

use App\Models\Clinic;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class SetupClinic extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static string $view = 'filament.pages.setup-clinic';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $layout = 'layouts.filament.setup-layout';


    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }
    protected function getFormSchema(): array
    {
        return [
            Fieldset::make('Clinic Information')
                ->schema([
                    TextInput::make('data.name') // 👈 bind to $data['name']
                        ->label('Clinic Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('data.address')
                        ->required()
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),

            Fieldset::make('Operational Hours')
                ->schema([
                    TimePicker::make('data.opening_time')
                        ->label('Opening Time')
                        ->required()
                        ->seconds(false)
                        ->displayFormat('h:i A'),

                    TimePicker::make('data.closing_time')
                        ->label('Closing Time')
                        ->required()
                        ->seconds(false)
                        ->displayFormat('h:i A'),

                    TextInput::make('data.operational_days')
                        ->label('Operational Days')
                        ->required()
                        ->placeholder('e.g., Monday–Friday'),
                ])->columns(3),

            Textarea::make('data.description')
                ->label('Additional Information (Optional)')
                ->columnSpanFull(),
        ];
    }

    public function create()
    {
        $state = $this->form->getState();     // ← state['data'] exists
        $data = $state['data'];               // ← extract nested array

        $clinic = Clinic::create([
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => Auth::user()->phone,
            'open_days' => $data['operational_days'],
            'open_time' => $data['opening_time'],
            'close_time' => $data['closing_time'],
            'description' => $data['description'] ?? null,
            'subscription_status' => 'trial',
            'subscription_ends_at' => now()->addDays(30),
            'user_id' => Auth::id(),
        ]);

        Auth::user()->update(['clinic_id' => $clinic->id]);


        return redirect()->to(filament()->getUrl());
    }
}
