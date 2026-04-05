<?php

use Illuminate\Support\Facades\Route;

\Lomkit\Rest\Facades\Rest::resource('users', \App\Rest\Controllers\UsersController::class);

// Audio conversation processing route
Route::post('/conversations/process-audio', [\App\Rest\Controllers\ConversationAudioController::class, 'processAudio']);