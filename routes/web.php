<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BotController;

// Rutas de API para el bot conversacional
Route::prefix('bot')->group(function () {
    // Mensajes y Conversaciones
    Route::post('messages', [BotController::class, 'storeMessage']);
    Route::get('conversations/{chat_id}', [BotController::class, 'getOrCreateConversation']);
    Route::put('conversations/{conversation_id}', [BotController::class, 'updateConversation']);
    Route::get('messages/{conversation_id}', [BotController::class, 'getMessages']); // Para historial de Gemini

    // Solicitantes Parciales (Proceso de Evaluación)
    Route::post('partial-applicants', [BotController::class, 'createPartialApplicant']);
    Route::get('partial-applicants/{conversation_id}', [BotController::class, 'getPartialApplicant']);
    Route::put('partial-applicants/{partial_applicant_id}', [BotController::class, 'updatePartialApplicant']);

    // Solicitantes Finales y Grupos (Lógica de Negocio Crítica)
    Route::post('applicants/evaluate-and-save', [BotController::class, 'evaluateAndSaveApplicant']);
});
