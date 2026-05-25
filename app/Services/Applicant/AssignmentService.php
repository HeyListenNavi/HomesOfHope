<?php

namespace App\Services\Applicant;

use App\Models\Applicant;
use App\Models\Group;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    public function assignToGroup(Applicant $applicant): Group
    {
        return DB::transaction(function () use ($applicant) {
            $group = Group::where('current_members_count', '<', DB::raw('capacity'))
                        ->whereNotNull('date_time')
                        ->orderBy('current_members_count', 'asc')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->first();

            if (!$group) {
                $group = Group::create([
                    'name' => 'Grupo ' . (Group::count() + 1),
                    'capacity' => 25,
                    'is_active' => true,
                ]);
            }

            $applicant->group_id = $group->id;
            $applicant->confirmation_status = 'pending';
            
            return $group;
        });
    }
}
