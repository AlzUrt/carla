<?php

namespace App\Filament\Resources\Conversations\Schemas;

use App\Filament\Components\VoiceRecorderInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enregistrement Vocal')
                    ->description('Enregistrez votre question et obtenez une réponse audio instantanée')
                    ->schema([
                        VoiceRecorderInput::make(),
                    ])
                    ->collapsible(),

                Section::make('Question et Réponse')
                    ->schema([
                        Textarea::make('text_question')
                            ->label('Question (texte)')
                            ->disabled()
                            ->required(false),

                        Textarea::make('text_answer')
                            ->label('Réponse (texte)')
                            ->disabled()
                            ->required(false),
                    ])
                    ->collapsible(),

                Section::make('Fichiers Audio')
                    ->schema([
                        TextInput::make('audio_question_path')
                            ->label('Chemin du fichier audio - Question')
                            ->disabled()
                            ->required(false),

                        TextInput::make('audio_answer_path')
                            ->label('Chemin du fichier audio - Réponse')
                            ->disabled()
                            ->required(false),
                    ])
                    ->collapsible(),

                Section::make('Performance')
                    ->schema([
                        TextInput::make('duration_stt_ms')
                            ->label('Temps STT (ms)')
                            ->numeric()
                            ->disabled()
                            ->required(false),

                        TextInput::make('duration_llm_ms')
                            ->label('Temps LLM (ms)')
                            ->numeric()
                            ->disabled()
                            ->required(false),

                        TextInput::make('duration_tts_ms')
                            ->label('Temps TTS (ms)')
                            ->numeric()
                            ->disabled()
                            ->required(false),
                    ])
                    ->collapsible(),

                TextInput::make('status')
                    ->label('Statut')
                    ->disabled()
                    ->required(false),

                Textarea::make('error')
                    ->label('Erreur')
                    ->disabled()
                    ->required(false),
            ]);
    }
}
