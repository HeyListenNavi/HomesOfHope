<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BotConversationController;
use App\Http\Controllers\Api\BotMessageController;
use App\Http\Controllers\Api\BotApplicantController;
use App\Http\Controllers\Api\BotApplicantManualController;
use App\Http\Controllers\Api\BotController;
use App\Http\Controllers\Api\FamilyProfileController;
use App\Http\Controllers\Api\FamilyMemberController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\EvidenceController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TestimonyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ColonyController;


//Ruta para testing
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Login Route
Route::post('/login', [UserController::class, 'login']);

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
        //Route::post('{chatId}/submit-answer', [BotApplicantController::class, 'submitAnswer']); Estos endpoints ya existian Vero, pero
        //Route::post('stage-approval', [BotApplicantController::class, 'handleStageApproval']);  los puse abajo para que recuerdes que tienen algunas modificaciones, revisalas
        Route::get('{chatId}/stage-data', [BotApplicantController::class, 'getStageDataForAi']);

        //Nuevos endpoints
        Route::get("applicant-status/{chatId}", [BotApplicantController::class, "applicantCurrentStatus"]);
        Route::get("current-stage-questions/{stageId}", [BotApplicantController::class, "currentStageQuestions"]);
        Route::put("update-answer", [BotApplicantController::class, "updateAnswer"]);
        Route::post("send-initial-data", [BotApplicantController::class, "sendInitialData"]);
        Route::post('{chatId}/submit-answer', [BotApplicantController::class, 'submitAnswer']);
        Route::post('stage-approval', [BotApplicantController::class, 'handleStageApproval']);

    });

    // Ruta para actualizaciones manuales (ej. desde un panel de administración)
    Route::put('applicants/{applicantId}/update-manually', [BotApplicantManualController::class, 'updateManually']);

    //Routes for Colonies
    Route::get('/colonies', [ColonyController::class, 'index']);    
});
