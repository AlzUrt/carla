<?php

namespace App\Rest\Controllers;

use App\Models\Conversation;
use App\Services\ElevenLabsService;
use App\Services\GeminiService;
use App\Services\WhisperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConversationAudioController extends Controller
{
    public function __construct(
        private WhisperService $whisperService,
        private GeminiService $geminiService,
        private ElevenLabsService $elevenLabsService,
    ) {}

    /**
     * Process audio through the complete workflow:
    * Audio → Whisper (STT) → Gemini (LLM) → ElevenLabs (TTS) → Audio response
     */
    public function processAudio(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'audio' => 'required|file|mimes:webm,weba,mp3,wav,m4a,mp4,ogg|max:25000',
                'conversation_id' => 'nullable|exists:conversations,id',
            ]);

            // Save uploaded audio file
            $audioFile = $request->file('audio');
            $audioPath = 'conversations/audio_questions/' . uniqid('question_', true) . '.' . $audioFile->getClientOriginalExtension();
            Storage::disk('public')->put($audioPath, file_get_contents($audioFile));

            // Step 1: Transcribe audio with Whisper
            $whisperResult = $this->whisperService->transcribe(Storage::disk('public')->path($audioPath));
            $transcribedText = $whisperResult['text'];
            $sttDuration = $whisperResult['duration_ms'];

            // Step 2: Send text to Gemini
            $llmResult = $this->geminiService->chat(
                $transcribedText,
                'You are a helpful assistant. Respond concisely in the same language as the user.'
            );
            $llmResponse = $llmResult['text'];
            $llmDuration = $llmResult['duration_ms'];

            // Step 3: Convert Gemini response to speech with ElevenLabs
            $ttsResult = $this->elevenLabsService->textToSpeech($llmResponse);
            $audioAnswerPath = $ttsResult['path'];
            $ttsDuration = $ttsResult['duration_ms'];

            // Step 4: Save conversation record
            $conversation = null;
            if ($request->has('conversation_id')) {
                $conversation = Conversation::find($request->input('conversation_id'));
            } else {
                $conversation = new Conversation();
            }

            $conversation->fill([
                'audio_question_path' => $audioPath,
                'text_question' => $transcribedText,
                'text_answer' => $llmResponse,
                'audio_answer_path' => $audioAnswerPath,
                'duration_stt_ms' => $sttDuration,
                'duration_llm_ms' => $llmDuration,
                'duration_tts_ms' => $ttsDuration,
                'status' => 'completed',
            ]);
            $conversation->save();

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id,
                'text_question' => $transcribedText,
                'text_answer' => $llmResponse,
                'audio_answer_path' => $audioAnswerPath,
                'audio_answer_url' => route('audio.serve', ['path' => $audioAnswerPath]),
                'durations' => [
                    'stt_ms' => $sttDuration,
                    'llm_ms' => $llmDuration,
                    'tts_ms' => $ttsDuration,
                    'total_ms' => $sttDuration + $llmDuration + $ttsDuration,
                ],
            ]);
        } catch (\Exception $e) {
            $conversation = null;
            if ($request->has('conversation_id')) {
                $conversation = Conversation::find($request->input('conversation_id'));
                if ($conversation) {
                    $conversation->update([
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
