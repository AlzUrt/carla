<?php

namespace App\Livewire;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GenerateTokenHeaderButton extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function generateTokenAction(): Action
    {
        return Action::make('generateToken')
            ->label('Générer un token')
            ->icon('heroicon-o-key')
            ->modalHeading('Générer un token Sanctum')
            ->modalDescription('Renseignez le nom du token et sa date d\'expiration.')
            ->modalSubmitActionLabel('Générer')
            ->form([
                TextInput::make('name')
                    ->label('Nom du token')
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('expires_at')
                    ->label('Date d\'expiration')
                    ->native(false)
                    ->seconds(false)
                    ->minDate(now())
                    ->required(),
            ])
            ->action(function (array $data): void {
                $user = Auth::user();

                if (! $user) {
                    Notification::make()
                        ->title('Utilisateur non authentifié')
                        ->danger()
                        ->send();

                    return;
                }

                $token = $user->createToken(
                    $data['name'],
                    ['*'],
                    Carbon::parse($data['expires_at'])
                );

                Notification::make()
                    ->title('Token généré')
                    ->body($token->plainTextToken)
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    public function render()
    {
        return view('livewire.generate-token-header-button');
    }
}
