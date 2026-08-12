<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BotApplicantController;
use App\Http\Controllers\Api\BotApplicantManualController;
use App\Http\Controllers\Api\BotConversationController;
use App\Http\Controllers\Api\BotMessageController;
use App\Http\Controllers\Api\ColonyController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\EvidenceController;
use App\Http\Controllers\Api\FamilyMemberController;
use App\Http\Controllers\Api\FamilyProfileController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\HandleMessageController;
use App\Http\Controllers\Api\MetaWebhookVerificationController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TestimonyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VisitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Handle Messages to n8n
Route::post('/handle-message', [HandleMessageController::class, 'handle']);
Route::get('/handle-message', [MetaWebhookVerificationController::class, 'verify']);

// Ruta para testing
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Login Route
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);

    // Routes for FamilyProfile
    Route::prefix('family-profiles')->group(function () {
        Route::get('/', [FamilyProfileController::class, 'index']);
        Route::post('/', [FamilyProfileController::class, 'store']);
        Route::get('/{id}', [FamilyProfileController::class, 'show']);
        Route::put('/{id}', [FamilyProfileController::class, 'update']);
        Route::delete('/{id}', [FamilyProfileController::class, 'destroy']);
    });

    // Routes for Family Members
    Route::prefix('family-members')->group(function () {
        Route::get('/', [FamilyMemberController::class, 'index']);
        Route::post('/', [FamilyMemberController::class, 'store']);
        Route::get('/{id}', [FamilyMemberController::class, 'show']);
        Route::put('/{id}', [FamilyMemberController::class, 'update']);
        Route::delete('/{id}', [FamilyMemberController::class, 'destroy']);
    });

    // Routes for documents
    Route::prefix('documents')->group(function () {
        Route::post('/', [DocumentController::class, 'store']);
        Route::get('/{id}', [DocumentController::class, 'show']);
        Route::get('/{id}/download', [DocumentController::class, 'download']);
        Route::delete('/{id}', [DocumentController::class, 'destroy']);
    });

    // Routes for notes
    Route::prefix('notes')->group(function () {
        Route::get('/', [NoteController::class, 'index']);
        Route::post('/', [NoteController::class, 'store']);
        Route::get('/{id}', [NoteController::class, 'show']);
        Route::put('/{id}', [NoteController::class, 'update']);
        Route::delete('/{id}', [NoteController::class, 'destroy']);
    });

    // Routes for visits
    Route::prefix('visits')->group(function () {
        Route::get('/', [VisitController::class, 'index']);
        Route::post('/', [VisitController::class, 'store']);
        Route::get('/{id}', [VisitController::class, 'show']);
        Route::put('/{id}', [VisitController::class, 'update']);
        Route::delete('/{id}', [VisitController::class, 'destroy']);
    });

    // Routes for evidence
    Route::prefix('evidence')->group(function () {
        Route::post('/', [EvidenceController::class, 'store']);
        Route::get('/{id}', [EvidenceController::class, 'show']);
        Route::delete('/{id}', [EvidenceController::class, 'destroy']);
    });

    // Routes for tasks
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{id}', [TaskController::class, 'show']);
        Route::put('/{id}', [TaskController::class, 'update']);
        Route::delete('/{id}', [TaskController::class, 'destroy']);
    });

    // Routes for testimonies
    Route::prefix('testimonies')->group(function () {
        Route::get('/', [TestimonyController::class, 'index']);
        Route::post('/', [TestimonyController::class, 'store']);
        Route::get('/{id}', [TestimonyController::class, 'show']);
        Route::put('/{id}', [TestimonyController::class, 'update']);
        Route::delete('/{id}', [TestimonyController::class, 'destroy']);
    });

    // Routes for Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });

    // Routes for Groups and Applicants
    Route::prefix('groups')->group(function () {
        Route::get('/', [GroupController::class, 'index']);
        Route::get('/{id}/applicants', [GroupController::class, 'applicants']);
    });

    // Routes for Attendance
    Route::prefix('attendance')->group(function () {
        Route::post('/scan', [AttendanceController::class, 'scan']);
        Route::put('/{id}', [AttendanceController::class, 'update']);
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
        Route::get('{chatId}/stage-data', [BotApplicantController::class, 'getStageDataForAi']);

        // Nuevos endpoints
        Route::get('applicant-status/{chatId}', [BotApplicantController::class, 'applicantCurrentStatus']);
        Route::get('current-stage-questions/{stageId}', [BotApplicantController::class, 'currentStageQuestions']);
        Route::put('update-answer', [BotApplicantController::class, 'updateAnswer']);
        Route::post('send-initial-data', [BotApplicantController::class, 'sendInitialData']);
        Route::post('{chatId}/submit-answer', [BotApplicantController::class, 'submitAnswer']);
        Route::post('stage-approval', [BotApplicantController::class, 'handleStageApproval']);

    });

    Route::put('applicants/{applicantId}/update-manually', [BotApplicantManualController::class, 'updateManually']);

    Route::post('reschedule/{chatId}', [BotApplicantController::class, 'reschedule']);

    // Routes for Colonies
    Route::get('/colonies', [ColonyController::class, 'index']);
});
