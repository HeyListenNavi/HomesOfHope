<?php

use App\Http\Controllers\GroupSelectionController;
use App\Livewire\AttendancePage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dates-form');
});

Route::get('/seleccionar-grupo/{applicant:id}', [GroupSelectionController::class, 'showSelectionForm'])
    ->middleware('signed')
    ->name('group.selection.form');

Route::post('/seleccionar-grupo/{applicant:id}', [GroupSelectionController::class, 'assignToGroup'])
    ->middleware('signed')
    ->name('group.selection.assign');

Route::get('/seleccion/invitacion/{applicant:id}', [GroupSelectionController::class, 'downloadInvitation'])
    ->middleware('signed')
    ->name('selection.invitation.download');

Route::get('/seleccion/confirmado/{applicant:id}', [GroupSelectionController::class, 'showSuccess'])
    ->middleware('signed')
    ->name('selection.success');

Route::get('/invitacion/{applicant:id}', [GroupSelectionController::class, 'showInvitation'])
    ->middleware('signed')
    ->name('invitation.show');

Route::get('/seleccion/enlace-invalido', [GroupSelectionController::class, 'showInvalidLink'])
    ->name('selection.invalid');

Route::middleware(['auth'])->group(function () {
    Route::get('/asistencia/{group}', AttendancePage::class)->name('attendance.page');
});
