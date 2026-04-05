<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Services\ElevenLabsService;
use App\Services\GeminiService;
use App\Services\WhisperService;
use Illuminate\Console\Command;

class TestVoiceWorkflow extends Command
{
    protected $signature = 'voice:test {audio_path} {--conversation_id=}';

    protected $description = 'Test the complete voice workflow with an audio file';

    public function __construct(
        private WhisperService $whisperService,
        private GeminiService $geminiService,
        private ElevenLabsService $elevenLabsService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $audioPath = $this->argument('audio_path');
        $conversationId = $this->option('conversation_id');

        if (!file_exists($audioPath)) {
            $this->error("Audio file not found: $audioPath");
            return 1;
        }

        try {
            $this->info('Starting voice workflow...');

            // Step 1: Transcribe
            $this->info('Step 1: Transcribing audio with Whisper...');
            $whisperResult = $this->whisperService->transcribe($audioPath);
            $transcribedText = $whisperResult['text'];
            $this->line("📝 Transcribed: $transcribedText");
            $this->line("⏱️  Duration: {$whisperResult['duration_ms']}ms");

            // Step 2: Gemini
            $this->info('Step 2: Getting response from Gemini...');
            $llmResult = $this->geminiService->chat($transcribedText);
            $responseText = $llmResult['text'];
            $this->line("🤖 Response: $responseText");
            $this->line("⏱️  Duration: {$llmResult['duration_ms']}ms");

            // Step 3: ElevenLabs
            $this->info('Step 3: Converting response to speech with ElevenLabs...');
            $ttsResult = $this->elevenLabsService->textToSpeech($responseText);
            $audioAnswerPath = $ttsResult['path'];
            $this->line("🔊 Audio saved to: $audioAnswerPath");
            $this->line("⏱️  Duration: {$ttsResult['duration_ms']}ms");

            // Save conversation
            if ($conversationId) {
                $conversation = Conversation::find($conversationId);
            } else {
                $conversation = new Conversation();
            }

            $conversation->fill([
                'audio_question_path' => $audioPath,
                'text_question' => $transcribedText,
                'text_answer' => $responseText,
                'audio_answer_path' => $audioAnswerPath,
                'duration_stt_ms' => $whisperResult['duration_ms'],
                'duration_llm_ms' => $llmResult['duration_ms'],
                'duration_tts_ms' => $ttsResult['duration_ms'],
                'status' => 'completed',
            ]);
            $conversation->save();

            $this->info("✅ Conversation saved with ID: {$conversation->id}");
            $this->line("Total time: {$conversation->getTotalDurationMs()}ms");

            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            if ($conversationId) {
                $conversation = Conversation::find($conversationId);
                if ($conversation) {
                    $conversation->update([
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            return 1;
        }
    }
}
