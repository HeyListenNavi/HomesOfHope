<?php

use App\Filament\Widgets\ApplicantsOverview;
use App\Filament\Widgets\ApprovedApplicantsChart;
use App\Filament\Widgets\ApprovedPieChart;
use App\Filament\Widgets\InProgressApplicantsChart;
use App\Filament\Widgets\MessageVolumeChart;
use App\Filament\Widgets\MonthlyApplicantsChart;
use App\Filament\Widgets\RejectedApplicantsChart;
use App\Filament\Widgets\RejectedPieChart;
use App\Filament\Widgets\RejectionReasonsChart;
use App\Filament\Widgets\StatusDistributionChart;
use App\Models\User;
use Livewire\Livewire;

it('can render all date period chart widgets without errors', function (string $widgetClass) {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test($widgetClass)
        ->assertSuccessful();
})->with([
    ApplicantsOverview::class,
    ApprovedApplicantsChart::class,
    ApprovedPieChart::class,
    InProgressApplicantsChart::class,
    MessageVolumeChart::class,
    MonthlyApplicantsChart::class,
    RejectedApplicantsChart::class,
    RejectedPieChart::class,
    RejectionReasonsChart::class,
    StatusDistributionChart::class,
]);
