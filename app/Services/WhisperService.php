<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhisperService
{
    private string $apiKey;
    private string $apiUrl = 'https://api.openai.com/v1/audio/transcriptions';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    /**
     * Transcribe audio file using OpenAI Whisper API
     */
    public function transcribe(string $audioPath): array
    {
        $startTime = microtime(true);

        $response = Http::withToken($this->apiKey)
            ->attach('file', fopen($audioPath, 'r'), basename($audioPath))
            ->post($this->apiUrl, [
                'model' => 'whisper-1',
                'language' => 'fr',
            ]);

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        if ($response->failed()) {
            throw new \Exception('Whisper API error: ' . $response->body());
        }

        return [
            'text' => $response->json('text'),
            'duration_ms' => $duration,
        ];
    }
}
