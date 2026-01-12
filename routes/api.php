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
use app\Http\Controllers\Api\TaskController;
use app\Http\Controllers\Api\TestimonyController;



//Ruta para testing
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware(['auth:sanctum'])->group(function () {

    //Routes for FamilyProfile
    Route::prefix('family-profiles')->group(function () {
        Route::get('/', [FamilyProfileController::class, 'index']);        
        Route::post('/', [FamilyProfileController::class, 'store']);
        Route::get('/{id}', [FamilyProfileController::class, 'show']);
        Route::put('/{id}', [FamilyProfileController::class, 'update']);
        Route::delete('/{id}', [FamilyProfileController::class, 'destroy']);
    });

    //Routes for Family Members
    Route::prefix('family-members')->group(function () {
        Route::get('/', [FamilyMemberController::class, 'index']);
        Route::post('/', [FamilyMemberController::class, 'store']);
        Route::get('/{id}', [FamilyMemberController::class, 'show']);
        Route::put('/{id}', [FamilyMemberController::class, 'update']);
        Route::delete('/{id}', [FamilyMemberController::class, 'destroy']);
    });

    //Routes for documents
    Route::prefix('documents')->group(function () {
        Route::post('/', [DocumentController::class, 'store']);
        Route::get('/{id}', [DocumentController::class, 'show']);
        Route::get('/{id}/download', [DocumentController::class, 'download']);
        Route::delete('/{id}', [DocumentController::class, 'destroy']);
    });

    //Routes for notes
    Route::prefix('notes')->group(function () {
        Route::get('/', [NoteController::class, 'index']);
        Route::post('/', [NoteController::class, 'store']);
        Route::get('/{id}', [NoteController::class, 'show']);
        Route::put('/{id}', [NoteController::class, 'update']);
        Route::delete('/{id}', [NoteController::class, 'destroy']);
    });

    //Routes for visits
    Route::prefix('visits')->group(function () {
        Route::get('/', [VisitController::class, 'index']);
        Route::post('/', [VisitController::class, 'store']);
        Route::get('/{id}', [VisitController::class, 'show']);
        Route::put('/{id}', [VisitController::class, 'update']);
        Route::delete('/{id}', [VisitController::class, 'destroy']);
    });

    //Routes for evidence
    Route::prefix('evidence')->group(function () {
        Route::post('/', [EvidenceController::class, 'store']);        
        Route::get('/{id}', [EvidenceController::class, 'show']);        
        Route::delete('/{id}', [EvidenceController::class, 'destroy']);
    });

    //Routes for tasks
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{id}', [TaskController::class, 'show']);
        Route::put('/{id}', [TaskController::class, 'update']);
        Route::delete('/{id}', [TaskController::class, 'destroy']);
    });

    //Routes for testimonies
    Route::prefix('testimonies')->group(function () {
        Route::get('/', [TestimonyController::class, 'index']);
        Route::post('/', [TestimonyController::class, 'store']);
        Route::get('/{id}', [TestimonyController::class, 'show']);
        Route::put('/{id}', [TestimonyController::class, 'update']);
        Route::delete('/{id}', [TestimonyController::class, 'destroy']);
    });

});


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
});
