<?php

use App\Http\Controllers\GroupSelectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dates-form');
});

Route::get('/seleccionar-grupo/{applicant:id}', [GroupSelectionController::class, 'showSelectionForm'])
    ->name('group.selection.form');
// ->middleware('signed'); // <-- Middleware que valida la firma de la URL

Route::post('/seleccionar-grupo/{applicant:id}', [GroupSelectionController::class, 'assignToGroup'])
    ->name('group.selection.assign');

Route::get('/seleccion/confirmado', [GroupSelectionController::class, 'showSuccess'])
    ->name('selection.success');

Route::get('/seleccion/enlace-invalido', [GroupSelectionController::class, 'showInvalidLink'])
    ->name('selection.invalid');
