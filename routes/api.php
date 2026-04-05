<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AudioFileController;

\Lomkit\Rest\Facades\Rest::resource('users', \App\Rest\Controllers\UsersController::class);

// Audio conversation processing route
Route::post('/conversations/process-audio', [\App\Rest\Controllers\ConversationAudioController::class, 'processAudio']);

// Serve audio files (public, no authentication)
Route::get('/storage/{path}', [AudioFileController::class, 'serve'])
    ->where('path', '.*')
    ->name('audio.serve');