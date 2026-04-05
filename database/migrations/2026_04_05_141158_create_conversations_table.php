<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('audio_question_path');
            $table->text('text_question')->nullable();
            $table->text('text_answer')->nullable();
            $table->string('audio_answer_path')->nullable();
            $table->unsignedInteger('duration_stt_ms')->nullable();
            $table->unsignedInteger('duration_llm_ms')->nullable();
            $table->unsignedInteger('duration_tts_ms')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
