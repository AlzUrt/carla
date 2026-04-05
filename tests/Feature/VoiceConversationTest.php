<?php

namespace Tests\Feature;

use App\Models\Conversation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VoiceConversationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_process_audio_endpoint_requires_audio(): void
    {
        $response = $this->postJson('/api/conversations/process-audio');

        $response->assertStatus(422);
    }

    public function test_process_audio_with_valid_file(): void
    {
        // Mock API responses
        Http::fake([
            'api.openai.com/*' => Http::response([
                'text' => 'Quel est la capitale de la France?',
            ]),
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => 'La capitale de la France est Paris.'],
                ],
            ]),
            'api.elevenlabs.io/*' => Http::response('audio_data'),
        ]);

        // Create a fake audio file
        $audioFile = UploadedFile::fake()->create('test.webm', 100, 'audio/webm');

        $response = $this->postJson('/api/conversations/process-audio', [
            'audio' => $audioFile,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'conversation_id',
            'text_question',
            'text_answer',
            'audio_answer_url',
            'durations',
        ]);

        // Verify conversation was created
        $this->assertDatabaseHas('conversations', [
            'status' => 'completed',
        ]);
    }

    public function test_conversation_model_calculates_total_duration(): void
    {
        $conversation = Conversation::create([
            'text_question' => 'Test',
            'text_answer' => 'Answer',
            'duration_stt_ms' => 1000,
            'duration_llm_ms' => 2000,
            'duration_tts_ms' => 3000,
            'status' => 'completed',
        ]);

        $this->assertEquals(6000, $conversation->getTotalDurationMs());
    }
}
