<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Conversation extends Model
{
    protected $fillable = [
        'audio_question_path',
        'text_question',
        'text_answer',
        'audio_answer_path',
        'duration_stt_ms',
        'duration_llm_ms',
        'duration_tts_ms',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'duration_stt_ms' => 'integer',
            'duration_llm_ms' => 'integer',
            'duration_tts_ms' => 'integer',
        ];
    }

    public function getTotalDurationMs(): ?int
    {
        if ($this->duration_stt_ms === null) {
            return null;
        }

        return $this->duration_stt_ms
            + $this->duration_llm_ms
            + $this->duration_tts_ms;
    }

    public function audioQuestionExists(): bool
    {
        return Storage::disk('local')->exists($this->audio_question_path);
    }

    public function audioAnswerExists(): bool
    {
        return $this->audio_answer_path
            && Storage::disk('local')->exists($this->audio_answer_path);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error' => $error,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}