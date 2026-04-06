<?php

namespace App\Filament\Resources\PersonalAccessTokens\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PersonalAccessTokenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations du token')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom du token')
                            ->required()
                            ->maxLength(255),

                        TagsInput::make('abilities')
                            ->label('Permissions')
                            ->placeholder('Ajouter une permission')
                            ->helperText('Exemple: * pour accès complet, ou read / write')
                            ->required(),

                        DateTimePicker::make('expires_at')
                            ->label('Date d\'expiration')
                            ->native(false)
                            ->seconds(false),
                    ]),
            ]);
    }
}
