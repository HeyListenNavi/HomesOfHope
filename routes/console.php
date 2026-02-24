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

Schedule::call(function () {
    Applicant::where('process_status', 'in_progress')
        ->chunk(100, function ($applicants) {

            $applicants->load('conversation.latestMessage');

            foreach ($applicants as $applicant) {
                $conversation = $applicant->conversation;

                if (!$conversation || !$conversation->latestMessage) continue;

                $lastMessage = $conversation->latestMessage;

                if ($lastMessage->role !== 'user') continue;

                $minutesSinceLastMessage = $lastMessage->created_at->diffInMinutes(now());

                if ($minutesSinceLastMessage >= 60) {
                    $applicant->update([
                        'process_status' => 'requires_revision',
                    ]);
                }
            }
        });
})->hourly();
