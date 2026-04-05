<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\ElevenLabsService;
use App\Services\GeminiService;
use App\Services\WhisperService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessConversationAudio implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Conversation $conversation,
        public string $audioPath,
    ) {}

    public function handle(
        WhisperService $whisperService,
        GeminiService $geminiService,
        ElevenLabsService $elevenLabsService,
    ): void {
        try {
            $this->conversation->update(['status' => 'processing']);

            // Step 1: Transcribe
            $whisperResult = $whisperService->transcribe(Storage::path($this->audioPath));

            // Step 2: Get Gemini response
            $llmResult = $geminiService->chat($whisperResult['text']);

            // Step 3: Generate audio
            $ttsResult = $elevenLabsService->textToSpeech($llmResult['text']);

            // Save results
            $this->conversation->update([
                'audio_question_path' => $this->audioPath,
                'text_question' => $whisperResult['text'],
                'text_answer' => $llmResult['text'],
                'audio_answer_path' => $ttsResult['path'],
                'duration_stt_ms' => $whisperResult['duration_ms'],
                'duration_llm_ms' => $llmResult['duration_ms'],
                'duration_tts_ms' => $ttsResult['duration_ms'],
                'status' => 'completed',
            ]);
        } catch (\Exception $e) {
            $this->conversation->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
