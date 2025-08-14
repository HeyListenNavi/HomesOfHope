<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BotController;
use App\Http\Controllers\ConfirmationController;


Route::get('/', function() {
    return view('dates-form');
});


// Rutas para la confirmación
Route::get('/confirm/{applicant}', [ConfirmationController::class, 'showForm'])->name('confirmation.form');
Route::post('/confirm/{applicant}', [ConfirmationController::class, 'confirmGroup'])->name('confirmation.confirm');
Route::get('/confirmation/success', [ConfirmationController::class, 'showSuccess'])->name('confirmation.success');