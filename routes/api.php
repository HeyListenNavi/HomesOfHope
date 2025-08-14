<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BotController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas de API para el bot conversacional
Route::prefix('bot')->group(function () {
    // Mensajes y Conversaciones
    Route::post('messages', [BotController::class, 'storeMessage']);
    Route::get('conversations/{chat_id}', [BotController::class, 'getOrCreateConversation']);
    Route::put('conversations/{conversation_id}', [BotController::class, 'updateConversation']);
    Route::get('messages/{conversation_id}', [BotController::class, 'getMessages']); // Para historial de Gemini

    // Endpoints del Flujo de Solicitantes (Unificados y nuevos)
    Route::post('applicants/start', [BotController::class, 'startEvaluation']);
    Route::get('applicants/{chat_id}/next-question', [BotController::class, 'getNextQuestion']);
    Route::post('applicants/{chat_id}/submit-answer', [BotController::class, 'submitAnswer']);
    Route::put('applicants/{applicant_id}/update-manually', [BotController::class, 'updateManually']);
    Route::post('applicants/stage-approval', [BotController::class, 'handleStageApproval']);
    Route::get('applicants/{chat_id}/stage-data', [BotController::class, 'getStageDataForAi']);

    // Solicitantes Finales y Grupos (Lógica de Negocio Crítica)
    Route::post('applicants/evaluate-and-save', [BotController::class, 'evaluateAndSaveApplicant']);
});
