<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    private ?string $apiKey;
    private string $model;
    private string $apiBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
    }

    /**
     * Send message to Gemini AI and get response
     */
    public function chat(string $message, ?string $systemPrompt = null): array
    {
        if (blank($this->apiKey)) {
            throw new \Exception('Gemini API error: GEMINI_API_KEY is not configured');
        }

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
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
        ]));

        $response = null;
        $usedModel = null;
        $lastError = null;

        foreach ($candidateModels as $model) {
            try {
                $attempt = Http::timeout(30)->post(
                    "{$this->apiBaseUrl}/{$model}:generateContent?key={$this->apiKey}",
                    $payload,
                );
            } catch (\Throwable $e) {
                $lastError = "Request failed for {$model}: {$e->getMessage()}";
                continue;
            }

            if ($attempt->successful()) {
                $response = $attempt;
                $usedModel = $model;
                break;
            }

            $status = $attempt->status();
            $body = trim((string) $attempt->body());
            $lastError = "HTTP {$status} for {$model}: " . ($body !== '' ? $body : 'Empty response body');

            if (! in_array($status, [400, 404], true)) {
                $response = $attempt;
                $usedModel = $model;
                break;
            }
        }

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        if (! $response || $response->failed()) {
            if (str_contains((string) $lastError, '404')) {
                throw new \Exception('Gemini API error: modèle Gemini introuvable ou non supporté. Vérifiez GEMINI_MODEL (ex: gemini-2.5-flash). Détail: ' . $lastError);
            }

            throw new \Exception('Gemini API error: ' . ($lastError ?? 'No response from Gemini'));
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');

        if (! is_string($text) || trim($text) === '') {
            throw new \Exception('Gemini API error: Gemini returned an empty response');
        }

        return [
            'text' => $text,
            'duration_ms' => $duration,
            'model' => $usedModel,
        ];
    }
}
