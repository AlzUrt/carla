<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $apiBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Send message to Gemini AI and get response
     */
    public function chat(string $message, ?string $systemPrompt = null): array
    {
        $startTime = microtime(true);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $message],
                    ],
                ],
            ],
        ];

        if ($systemPrompt) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ];
        }

        $candidateModels = array_values(array_unique([
            $this->model,
            'gemini-2.0-flash',
            'gemini-2.0-flash-lite',
            'gemini-1.5-flash-latest',
        ]));

        $response = null;
        $usedModel = null;

        foreach ($candidateModels as $model) {
            $attempt = Http::post(
                "{$this->apiBaseUrl}/{$model}:generateContent?key={$this->apiKey}",
                $payload,
            );

            if ($attempt->successful()) {
                $response = $attempt;
                $usedModel = $model;
                break;
            }

            $status = $attempt->status();
            if (! in_array($status, [400, 404], true)) {
                $response = $attempt;
                break;
            }
        }

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        if (! $response || $response->failed()) {
            throw new \Exception('Gemini API error: ' . ($response?->body() ?? 'No response'));
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');

        return [
            'text' => $text,
            'duration_ms' => $duration,
            'model' => $usedModel,
        ];
    }
}
