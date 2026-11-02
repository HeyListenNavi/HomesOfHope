<?php

namespace App\Console\Commands;

use App\Enums\FamilyStatus;
use App\Models\FamilyProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateProgrammedFamiliesStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-programmed-families-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update programmed family profiles to built status when their building start date has passed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Log::info('Running UpdateProgrammedFamiliesStatusCommand...');

        $updatedCount = FamilyProfile::where('status', FamilyStatus::Programmed)
            ->whereNotNull('building_start_date')
            ->where('building_start_date', '<', today())
            ->update(['status' => FamilyStatus::Built]);

        $this->info("Updated {$updatedCount} family profile(s) from Programmed to Built.");
        Log::info("UpdateProgrammedFamiliesStatusCommand completed. Updated: {$updatedCount}.");

        return self::SUCCESS;
    }
}
