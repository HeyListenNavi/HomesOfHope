<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BotConversationController;
use App\Http\Controllers\Api\BotMessageController;
use App\Http\Controllers\Api\BotApplicantController;
use App\Http\Controllers\Api\BotApplicantManualController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas de API para el bot conversacional
Route::prefix('bot')->group(function () {

    // Rutas para mensajes
    Route::post('messages', [BotMessageController::class, 'storeMessage']);
    Route::get('messages/{conversationId}', [BotMessageController::class, 'getMessages']);

    // Rutas para conversaciones
    Route::get('conversations/{chatId}', [BotConversationController::class, 'getOrCreateConversation']);
    Route::put('conversations/{conversationId}', [BotConversationController::class, 'updateConversation']);

    // Rutas para el flujo del solicitante a través del bot
    Route::prefix('applicants')->group(function () {
        Route::post('start', [BotApplicantController::class, 'startEvaluation']);
        Route::get('{chatId}/next-question', [BotApplicantController::class, 'getNextQuestion']);
        Route::post('{chatId}/submit-answer', [BotApplicantController::class, 'submitAnswer']);
        Route::post('stage-approval', [BotApplicantController::class, 'handleStageApproval']);
        Route::get('{chatId}/stage-data', [BotApplicantController::class, 'getStageDataForAi']);

        Route::get("(chatId}/aplicant-current-status", [BotApplicantController::class, "aplicantCurrentStatus"]);
        Route::get("{chatID/current-stage-questions", [BotApplicantController::class, "currentStageQuestions"]);
        Route::post("send-initial-data"), [BotApplicantController::class, "sendInitialData"]);
    });

    // Ruta para actualizaciones manuales (ej. desde un panel de administración)
    Route::put('applicants/{applicantId}/update-manually', [BotApplicantManualController::class, 'updateManually']);
});
