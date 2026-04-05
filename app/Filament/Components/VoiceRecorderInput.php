<?php

namespace App\Filament\Components;

use Filament\Schemas\Components\View;

class VoiceRecorderInput extends View
{
    protected string $conversationId = '';

    public static function make(string $view = 'filament.components.voice-recorder-input'): static
    {
        return app(static::class, ['view' => $view]);
    }

    public function conversationId(string $conversationId): static
    {
        $this->conversationId = $conversationId;

        return $this;
    }

    public function getConversationId(): string
    {
        return $this->conversationId;
    }
}
