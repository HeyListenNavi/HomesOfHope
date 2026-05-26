<?php

use App\Models\Applicant;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendApplicantReminder;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // Nota de Vero real JAJA: Se cargan en chunks de 100 para no quedarse sin memoria
    Applicant::where('process_status', 'in_progress')
        ->select('id')
        ->chunkById(100, function ($applicants) {
            foreach ($applicants as $applicant) {
                SendApplicantReminder::dispatch($applicant->id);
            }
        });
})->hourly();

Schedule::command('app:send-group-reminders')->dailyAt('09:00');
