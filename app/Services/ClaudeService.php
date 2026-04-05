<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClaudeService
{
    private string $apiKey;
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';
    private string $apiVersion = '2024-06-01';

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    /**
     * Send message to Claude AI and get response
     */
    public function chat(string $message, string $systemPrompt = null): array
    {
        $startTime = microtime(true);

        $payload = [
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $message,
                ],
            ],
        ];

        if ($systemPrompt) {
            $payload['system'] = $systemPrompt;
        }

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
        ])->post($this->apiUrl, $payload);

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        if ($response->failed()) {
            throw new \Exception('Claude API error: ' . $response->body());
        }

        $content = $response->json('content');
        $text = $content[0]['text'] ?? '';

        return [
            'text' => $text,
            'duration_ms' => $duration,
        ];
    }
}
