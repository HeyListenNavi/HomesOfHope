<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $visitator = Role::firstOrCreate(['name' => 'visitator']);

        $applicantViewAny = Permission::firstOrCreate(['name' => 'applicant.view_any']);
        $applicantView = Permission::firstOrCreate(['name' => 'applicant.view']);
        $applicantCreate = Permission::firstOrCreate(['name' => 'applicant.create']);
        $applicantUpdate = Permission::firstOrCreate(['name' => 'applicant.update']);
        $applicantDelete = Permission::firstOrCreate(['name' => 'applicant.delete']);

        $conversationViewAny = Permission::firstOrCreate(['name' => 'conversation.view_any']);
        $conversationView = Permission::firstOrCreate(['name' => 'conversation.view']);
        $conversationCreate = Permission::firstOrCreate(['name' => 'conversation.create']);
        $conversationUpdate = Permission::firstOrCreate(['name' => 'conversation.update']);
        $conversationDelete = Permission::firstOrCreate(['name' => 'conversation.delete']);

        $familyMemberViewAny = Permission::firstOrCreate(['name' => 'family_member.view_any']);
        $familyMemberView = Permission::firstOrCreate(['name' => 'family_member.view']);
        $familyMemberCreate = Permission::firstOrCreate(['name' => 'family_member.create']);
        $familyMemberUpdate = Permission::firstOrCreate(['name' => 'family_member.update']);
        $familyMemberDelete = Permission::firstOrCreate(['name' => 'family_member.delete']);

        $familyProfileViewAny = Permission::firstOrCreate(['name' => 'family_profile.view_any']);
        $familyProfileView = Permission::firstOrCreate(['name' => 'family_profile.view']);
        $familyProfileCreate = Permission::firstOrCreate(['name' => 'family_profile.create']);
        $familyProfileUpdate = Permission::firstOrCreate(['name' => 'family_profile.update']);
        $familyProfileDelete = Permission::firstOrCreate(['name' => 'family_profile.delete']);

        $groupViewAny = Permission::firstOrCreate(['name' => 'group.view_any']);
        $groupView = Permission::firstOrCreate(['name' => 'group.view']);
        $groupCreate = Permission::firstOrCreate(['name' => 'group.create']);
        $groupUpdate = Permission::firstOrCreate(['name' => 'group.update']);
        $groupDelete = Permission::firstOrCreate(['name' => 'group.delete']);

        $questionViewAny = Permission::firstOrCreate(['name' => 'question.view_any']);
        $questionView = Permission::firstOrCreate(['name' => 'question.view']);
        $questionCreate = Permission::firstOrCreate(['name' => 'question.create']);
        $questionUpdate = Permission::firstOrCreate(['name' => 'question.update']);
        $questionDelete = Permission::firstOrCreate(['name' => 'question.delete']);

        $stageViewAny = Permission::firstOrCreate(['name' => 'stage.view_any']);
        $stageView = Permission::firstOrCreate(['name' => 'stage.view']);
        $stageCreate = Permission::firstOrCreate(['name' => 'stage.create']);
        $stageUpdate = Permission::firstOrCreate(['name' => 'stage.update']);
        $stageDelete = Permission::firstOrCreate(['name' => 'stage.delete']);

        $userViewAny = Permission::firstOrCreate(['name' => 'user.view_any']);
        $userView = Permission::firstOrCreate(['name' => 'user.view']);
        $userCreate = Permission::firstOrCreate(['name' => 'user.create']);
        $userUpdate = Permission::firstOrCreate(['name' => 'user.update']);
        $userDelete = Permission::firstOrCreate(['name' => 'user.delete']);

        $admin->givePermissionTo([
            $applicantViewAny,
            $applicantView,
            $applicantCreate,
            $applicantUpdate,
            $applicantDelete,

            $conversationViewAny,
            $conversationView,
            $conversationCreate,
            $conversationUpdate,
            $conversationDelete,

            $familyMemberViewAny,
            $familyMemberView,
            $familyMemberCreate,
            $familyMemberUpdate,
            $familyMemberDelete,

            $familyProfileViewAny,
            $familyProfileView,
            $familyProfileCreate,
            $familyProfileUpdate,
            $familyProfileDelete,

            $groupViewAny,
            $groupView,
            $groupCreate,
            $groupUpdate,
            $groupDelete,

            $questionViewAny,
            $questionView,
            $questionCreate,
            $questionUpdate,
            $questionDelete,

            $stageViewAny,
            $stageView,
            $stageCreate,
            $stageUpdate,
            $stageDelete,

            $userViewAny,
            $userView,
            $userCreate,
            $userUpdate,
            $userDelete,
        ]);

        $adminEmails = [
            "admin@admin.com",
            "mario.borda@ywamsdb.org",
            "montserrat.gonzalez@ywamsdb.org"
        ];

        $users = User::whereIn("email", $adminEmails )->get();

        foreach( $users as $user ){
            $user->assignRole($admin);
        }
    }
}