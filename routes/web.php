<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\KnowledgeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/generate', [ChatController::class, 'generate'])->name('chat.generate');
Route::get('/knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');
Route::post('/knowledge', [KnowledgeController::class, 'store'])->name('knowledge.store');