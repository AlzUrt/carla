<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ElevenLabsService
{
    private string $apiKey;
    private string $voiceId;
    private string $apiUrl = 'https://api.elevenlabs.io/v1';

    public function __construct()
    {
        $this->apiKey = config('services.elevenlabs.api_key');
        $this->voiceId = config('services.elevenlabs.voice_id', '21m00Tcm4TlvDq8ikWAM');
    }

    /**
     * Convert text to speech using ElevenLabs API
     */
    public function textToSpeech(string $text): array
    {
        $startTime = microtime(true);

        $response = Http::withHeaders([
            'xi-api-key' => $this->apiKey,
        ])->post("{$this->apiUrl}/text-to-speech/{$this->voiceId}", [
            'text' => $text,
            'model_id' => 'eleven_monolingual_v1',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75,
            ],
        ]);

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        if ($response->failed()) {
            throw new \Exception('ElevenLabs API error: ' . $response->body());
        }

        // Save audio file
        $audioPath = 'conversations/audio_answers/' . uniqid('answer_', true) . '.mp3';
        Storage::disk('local')->put($audioPath, $response->body());

        return [
            'path' => $audioPath,
            'duration_ms' => $duration,
        ];
    }
}
