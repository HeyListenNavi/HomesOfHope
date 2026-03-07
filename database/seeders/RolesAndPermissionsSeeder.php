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
        $connection = Role::firstOrCreate(['name' => 'connection']);
        $selection = Role::firstOrCreate(['name' => 'selection']);
        $visit = Role::firstOrCreate(['name' => 'visit']);
        $distribution = Role::firstOrCreate(['name' => 'distribution']);

        $applicantViewAny = Permission::firstOrCreate(['name' => 'applicant.view_any']);
        $applicantView = Permission::firstOrCreate(['name' => 'applicant.view']);
        $applicantCreate = Permission::firstOrCreate(['name' => 'applicant.create']);
        $applicantUpdate = Permission::firstOrCreate(['name' => 'applicant.update']);
        $applicantDelete = Permission::firstOrCreate(['name' => 'applicant.delete']);

        $colonyViewAny = Permission::firstOrCreate(['name' => 'colony.view_any']);
        $colonyView = Permission::firstOrCreate(['name' => 'colony.view']);
        $colonyCreate = Permission::firstOrCreate(['name' => 'colony.create']);
        $colonyUpdate = Permission::firstOrCreate(['name' => 'colony.update']);
        $colonyDelete = Permission::firstOrCreate(['name' => 'colony.delete']);

        $conversationViewAny = Permission::firstOrCreate(['name' => 'conversation.view_any']);
        $conversationView = Permission::firstOrCreate(['name' => 'conversation.view']);
        $conversationCreate = Permission::firstOrCreate(['name' => 'conversation.create']);
        $conversationUpdate = Permission::firstOrCreate(['name' => 'conversation.update']);
        $conversationDelete = Permission::firstOrCreate(['name' => 'conversation.delete']);

        $messageViewAny = Permission::firstOrCreate(['name' => 'message.view_any']);
        $messageView = Permission::firstOrCreate(['name' => 'message.view']);
        $messageCreate = Permission::firstOrCreate(['name' => 'message.create']);
        $messageUpdate = Permission::firstOrCreate(['name' => 'message.update']);
        $messageDelete = Permission::firstOrCreate(['name' => 'message.delete']);

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

        $botSettingViewAny = Permission::firstOrCreate(['name' => 'bot_setting.view_any']);
        $botSettingUpdate = Permission::firstOrCreate(['name' => 'bot_setting.update']);

        $connection->givePermissionTo([
            $applicantViewAny,
            $applicantView,
            $applicantUpdate,
            $applicantCreate,

            $conversationViewAny,
            $conversationView,

            $messageViewAny,
            $messageView,

            $colonyViewAny,
            $colonyView,

            $groupViewAny,
            $groupView,
            $groupUpdate,
            $groupCreate,
            $groupDelete,
        ]);

        $visit->givePermissionTo([
            $applicantViewAny,
            $applicantView,

            $groupViewAny,
            $groupView,

            $conversationViewAny,
            $conversationView,

            $messageViewAny,
            $messageView,

            $colonyViewAny,
            $colonyView,

            $familyMemberViewAny,
            $familyMemberView,

            $familyProfileViewAny,
            $familyProfileView,
        ]);

        $selection->givePermissionTo([
            $applicantViewAny,
            $applicantView,

            $groupViewAny,
            $groupView,

            $conversationViewAny,
            $conversationView,

            $messageViewAny,
            $messageView,

            $colonyViewAny,
            $colonyView,

            $familyMemberViewAny,
            $familyMemberView,
            $familyMemberCreate,
            $familyMemberUpdate,

            $familyProfileViewAny,
            $familyProfileView,
            $familyProfileCreate,
            $familyProfileUpdate,
        ]);

        $distribution->givePermissionTo([
            $applicantViewAny,
            $applicantView,

            $conversationViewAny,
            $conversationView,

            $messageViewAny,
            $messageView,

            $colonyViewAny,
            $colonyView,

            $familyProfileViewAny,
            $familyProfileView,

            $familyMemberViewAny,
            $familyMemberView,
        ]);

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

            $colonyViewAny,
            $colonyView,
            $colonyCreate,
            $colonyUpdate,
            $colonyDelete,

            $messageViewAny,
            $messageView,
            $messageCreate,
            $messageUpdate,
            $messageDelete,

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

            $botSettingViewAny,
            $botSettingUpdate,
        ]);

        $adminEmails = [
            'admin@admin.com',
            'mario.borda@ywamsdb.org',
            'montserrat.gonzalez@ywamsdb.org',
        ];

        $selectionEmails = [
            'pablo.mendez@ywamsdb.org',
            'brian.villaruel@ywamsdb.org',
            'jonas.mayer@ywamsdb.org',
            'mauro.lopez@ywamsdb.org',
        ];

        $visitEmails = [
            'juan.ordonez@ywamsdb.org',
            'javier.salazar@ywamsdb.org',
            'jose.cepeda@ywamsdb.org',
            'hanna.stoltz@ywamsdb.org',
            'ruth.gasca@ywamsdb.org',
            'manuel.ruiz@ywamsdb.org',
        ];

        $connectionEmails = [
            'alfredo.robles@ywamsdb.org',
            'laura.torres@ywamsdb.org',
            'jesus.arreola@ywamsdb.org',
            'yadira.morales@ywamsdb.org',
            'jonathan.garcia@ywamsdb.org',
        ];

        $distributionEmails = [
            'zureida.silva@ywamsdb.org',
        ];

        $adminUsers = User::whereIn('email', $adminEmails)->get();
        $selectionUsers = User::whereIn('email', $selectionEmails)->get();
        $visitUsers = User::whereIn('email', $visitEmails)->get();
        $connectionUsers = User::whereIn('email', $connectionEmails)->get();
        $distributionUsers = User::whereIn('email', $distributionEmails)->get();

        foreach ($adminUsers as $adminUser) {
            $adminUser->assignRole($admin);
        }

        foreach ($selectionUsers as $selectionUser) {
            $selectionUser->assignRole($selection);
        }

        foreach ($visitUsers as $visitUser) {
            $visitUser->assignRole($visit);
        }

        foreach ($connectionUsers as $connectionUser) {
            $connectionUser->assignRole($connection);
        }

        foreach ($distributionUsers as $distributionUser) {
            $distributionUser->assignRole($distribution);
        }
    }
}
