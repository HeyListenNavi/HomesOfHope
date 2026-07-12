<?php

namespace Tests\Feature\Permission;

use App\Filament\Resources\ApplicantResource;
use App\Filament\Resources\ApplicantResource\Pages\CreateApplicant;
use App\Filament\Resources\ApplicantResource\Pages\EditApplicant;
use App\Filament\Resources\ApplicantResource\Pages\ListApplicants;
use App\Filament\Resources\ColonyResource;
use App\Filament\Resources\ColonyResource\Pages\CreateColony;
use App\Filament\Resources\ColonyResource\Pages\EditColony;
use App\Filament\Resources\ColonyResource\Pages\ListColonies;
use App\Filament\Resources\ConversationResource;
use App\Filament\Resources\ConversationResource\Pages\CreateConversation;
use App\Filament\Resources\ConversationResource\Pages\EditConversation;
use App\Filament\Resources\ConversationResource\Pages\ListConversations;
use App\Filament\Resources\FamilyMemberResource;
use App\Filament\Resources\FamilyMemberResource\Pages\CreateFamilyMember;
use App\Filament\Resources\FamilyMemberResource\Pages\EditFamilyMember;
use App\Filament\Resources\FamilyMemberResource\Pages\ListFamilyMembers;
use App\Filament\Resources\FamilyProfileResource;
use App\Filament\Resources\FamilyProfileResource\Pages\CreateFamilyProfile;
use App\Filament\Resources\FamilyProfileResource\Pages\EditFamilyProfile;
use App\Filament\Resources\FamilyProfileResource\Pages\ListFamilyProfiles;
use App\Filament\Resources\GroupResource;
use App\Filament\Resources\GroupResource\Pages\CreateGroup;
use App\Filament\Resources\GroupResource\Pages\EditGroup;
use App\Filament\Resources\GroupResource\Pages\ListGroups;
use App\Filament\Resources\MessageResource;
use App\Filament\Resources\MessageResource\Pages\ListMessages;
use App\Filament\Resources\MessageResource\Pages\ViewMessage;
use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuestionResource\Pages\CreateQuestion;
use App\Filament\Resources\QuestionResource\Pages\EditQuestion;
use App\Filament\Resources\QuestionResource\Pages\ListQuestions;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Resources\StageResource;
use App\Filament\Resources\StageResource\Pages\CreateStage;
use App\Filament\Resources\StageResource\Pages\EditStage;
use App\Filament\Resources\StageResource\Pages\ListStages;
use App\Filament\Resources\TagResource;
use App\Filament\Resources\TagResource\Pages\ManageTags;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\VisitResource;
use App\Filament\Resources\VisitResource\Pages\CreateVisit;
use App\Filament\Resources\VisitResource\Pages\EditVisit;
use App\Filament\Resources\VisitResource\Pages\ListVisits;
use App\Models\Applicant;
use App\Models\Colony;
use App\Models\Conversation;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use App\Models\Group;
use App\Models\Message;
use App\Models\Question;
use App\Models\Stage;
use App\Models\Tag;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResourcePermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $userWithoutPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->userWithoutPermissions = User::factory()->create();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_colonies()
    {
        // given an admin user and a colony
        $colony = Colony::factory()->create();

        // when accessing the list page
        // then it loads successfully
        Livewire::actingAs($this->admin)
            ->test(ListColonies::class)
            ->assertSuccessful();

        // when creating a colony
        Livewire::actingAs($this->admin)
            ->test(CreateColony::class)
            ->fillForm([
                'city' => 'Test City',
                'name' => 'New Colony',
            ])
            ->call('create')
            ->assertNotified();

        // when editing a colony
        Livewire::actingAs($this->admin)
            ->test(EditColony::class, ['record' => $colony->id])
            ->fillForm([
                'name' => 'Updated Colony',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting a colony
        Livewire::actingAs($this->admin)
            ->test(EditColony::class, ['record' => $colony->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_colonies()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(ColonyResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_manage_tags()
    {
        // given an admin user
        // when accessing the manage tags page
        Livewire::actingAs($this->admin)
            ->test(ManageTags::class)
            ->assertSuccessful();

        // when creating a tag
        Livewire::actingAs($this->admin)
            ->test(ManageTags::class)
            ->callAction('create', data: [
                'name' => 'Test Tag',
                'color' => '#ff0000',
            ])
            ->assertNotified();

        // when editing and deleting a tag 
        $tag = Tag::create(['name' => 'Seed Tag', 'color' => '#0000ff']);

        Livewire::actingAs($this->admin)
            ->test(ManageTags::class)
            ->assertTableActionExists('edit')
            ->callTableAction('edit', $tag->id, data: [
                'name' => 'Updated Tag',
                'color' => '#00ff00',
            ])
            ->assertNotified();

        Livewire::actingAs($this->admin)
            ->test(ManageTags::class)
            ->assertTableActionExists('delete')
            ->callTableAction('delete', $tag->id)
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_tags()
    {
        // given a user without any permissions
        // when accessing the manage tags page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(TagResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_groups()
    {
        // given an admin user and a group
        $group = Group::factory()->create();

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListGroups::class)
            ->assertSuccessful();

        // when creating a group
        Livewire::actingAs($this->admin)
            ->test(CreateGroup::class)
            ->fillForm([
                'name' => 'Test Group',
                'date_time' => now()->addDay()->format('Y-m-d H:i:00'),
                'location' => 'Test Location',
                'location_link' => 'https://maps.example.com',
                'capacity' => 10,
            ])
            ->call('create')
            ->assertNotified();

        // when editing a group
        Livewire::actingAs($this->admin)
            ->test(EditGroup::class, ['record' => $group->id])
            ->fillForm([
                'name' => 'Updated Group',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting a group
        Livewire::actingAs($this->admin)
            ->test(EditGroup::class, ['record' => $group->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_groups()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(GroupResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_stages()
    {
        // given an admin user and a stage
        $stage = Stage::factory()->create();

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListStages::class)
            ->assertSuccessful();

        // when creating a stage
        Livewire::actingAs($this->admin)
            ->test(CreateStage::class)
            ->fillForm([
                'name' => 'Test Stage',
            ])
            ->call('create')
            ->assertNotified();

        // when editing a stage
        Livewire::actingAs($this->admin)
            ->test(EditStage::class, ['record' => $stage->id])
            ->fillForm([
                'name' => 'Updated Stage',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting a stage
        Livewire::actingAs($this->admin)
            ->test(EditStage::class, ['record' => $stage->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_stages()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(StageResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_questions()
    {
        // given an admin user, a stage, and a question
        $stage = Stage::factory()->create();
        $question = Question::factory()->create(['stage_id' => $stage->id]);

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListQuestions::class)
            ->assertSuccessful();

        // when creating a question
        Livewire::actingAs($this->admin)
            ->test(CreateQuestion::class)
            ->fillForm([
                'stage_id' => $stage->id,
                'question_text' => 'Test question?',
            ])
            ->call('create')
            ->assertNotified();

        // when editing a question
        Livewire::actingAs($this->admin)
            ->test(EditQuestion::class, ['record' => $question->id])
            ->fillForm([
                'question_text' => 'Updated question?',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting a question
        Livewire::actingAs($this->admin)
            ->test(EditQuestion::class, ['record' => $question->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_questions()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(QuestionResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_users()
    {
        // given an admin user and a role
        $role = Role::where('name', 'connection')->first();
        $targetUser = User::factory()->create();

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->assertSuccessful();

        // when creating a user
        Livewire::actingAs($this->admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'roles' => [$role->id],
            ])
            ->call('create')
            ->assertNotified();

        // when editing a user
        Livewire::actingAs($this->admin)
            ->test(EditUser::class, ['record' => $targetUser->id])
            ->fillForm([
                'name' => 'Updated User',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting a user
        Livewire::actingAs($this->admin)
            ->test(EditUser::class, ['record' => $targetUser->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_users()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(UserResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_roles()
    {
        // given an admin user and a role

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListRoles::class)
            ->assertSuccessful();

        // when creating a role
        $roleName = 'test-role-'.uniqid();
        Livewire::actingAs($this->admin)
            ->test(CreateRole::class)
            ->fillForm([
                'name' => $roleName,
            ])
            ->call('create')
            ->assertNotified();

        // when editing a role
        $createdRole = Role::where('name', $roleName)->first();
        Livewire::actingAs($this->admin)
            ->test(EditRole::class, ['record' => $createdRole->id])
            ->fillForm([
                'name' => $roleName.'-updated',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting a role
        $deleteRole = Role::create(['name' => 'delete-role-'.uniqid()]);
        Livewire::actingAs($this->admin)
            ->test(EditRole::class, ['record' => $deleteRole->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_roles()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(RoleResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_applicants()
    {
        // given an admin user and an applicant
        $applicant = Applicant::factory()->create();

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListApplicants::class)
            ->assertSuccessful();

        // when creating an applicant
        Livewire::actingAs($this->admin)
            ->test(CreateApplicant::class)
            ->fillForm([
                'chat_id' => '521999999999',
                'applicant_name' => 'Test Applicant',
            ])
            ->call('create')
            ->assertNotified();

        // when editing an applicant
        Livewire::actingAs($this->admin)
            ->test(EditApplicant::class, ['record' => $applicant->id])
            ->fillForm([
                'applicant_name' => 'Updated Applicant',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting an applicant
        Livewire::actingAs($this->admin)
            ->test(EditApplicant::class, ['record' => $applicant->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_applicants()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(ApplicantResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_conversations()
    {
        // given an admin user and a conversation
        $conversation = Conversation::factory()->create();

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListConversations::class)
            ->assertSuccessful();

        // when creating a conversation
        Livewire::actingAs($this->admin)
            ->test(CreateConversation::class)
            ->fillForm([
                'chat_id' => '521000000000',
                'user_name' => 'Test User',
            ])
            ->call('create')
            ->assertNotified();

        // when editing a conversation
        Livewire::actingAs($this->admin)
            ->test(EditConversation::class, ['record' => $conversation->id])
            ->fillForm([
                'user_name' => 'Updated User',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting a conversation
        Livewire::actingAs($this->admin)
            ->test(EditConversation::class, ['record' => $conversation->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_conversations()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(ConversationResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_view_messages_but_not_create_or_edit_or_delete()
    {
        // given an admin user, a conversation, and a message
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $conversation->id]);

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListMessages::class)
            ->assertSuccessful();

        // when viewing a message
        Livewire::actingAs($this->admin)
            ->test(ViewMessage::class, ['record' => $message->id])
            ->assertSuccessful();

        // then there is no create action in the header
        Livewire::actingAs($this->admin)
            ->test(ListMessages::class)
            ->assertActionDoesNotExist('create');
    }

    public function test_user_without_permissions_cannot_access_messages()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(MessageResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_family_profiles()
    {
        // given an admin user and a family profile
        $familyProfile = FamilyProfile::factory()->create();

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListFamilyProfiles::class)
            ->assertSuccessful();

        // when creating a family profile
        Livewire::actingAs($this->admin)
            ->test(CreateFamilyProfile::class)
            ->fillForm([
                'family_name' => 'Test Family',
            ])
            ->call('create')
            ->assertNotified();

        // when editing a family profile
        Livewire::actingAs($this->admin)
            ->test(EditFamilyProfile::class, ['record' => $familyProfile->id])
            ->fillForm([
                'family_name' => 'Updated Family Name',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting a family profile
        Livewire::actingAs($this->admin)
            ->test(EditFamilyProfile::class, ['record' => $familyProfile->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_family_profiles()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(FamilyProfileResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_family_members()
    {
        // given an admin user, a family profile, and a family member
        $familyProfile = FamilyProfile::factory()->create();
        $familyMember = FamilyMember::factory()->create(['family_profile_id' => $familyProfile->id]);

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListFamilyMembers::class)
            ->assertSuccessful();

        // when creating a family member
        Livewire::actingAs($this->admin)
            ->test(CreateFamilyMember::class)
            ->fillForm([
                'family_profile_id' => $familyProfile->id,
                'name' => 'Juan',
                'paternal_surname' => 'Pérez',
                'birth_date' => '1990-01-15',
                'relationship' => 'padre',
            ])
            ->call('create')
            ->assertNotified();

        // when editing a family member
        Livewire::actingAs($this->admin)
            ->test(EditFamilyMember::class, ['record' => $familyMember->id])
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting a family member
        Livewire::actingAs($this->admin)
            ->test(EditFamilyMember::class, ['record' => $familyMember->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_family_members()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(FamilyMemberResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }

    public function test_admin_can_list_and_create_and_edit_and_delete_visits()
    {
        // given an admin user, a family profile, and a visit
        $familyProfile = FamilyProfile::factory()->create();
        $visit = Visit::factory()->create([
            'family_profile_id' => $familyProfile->id,
            'location_type' => 'home',
            'scheduled_at' => now(),
        ]);

        // when accessing the list page
        Livewire::actingAs($this->admin)
            ->test(ListVisits::class)
            ->assertSuccessful();

        // when creating a visit
        Livewire::actingAs($this->admin)
            ->test(CreateVisit::class)
            ->fillForm([
                'family_profile_id' => $familyProfile->id,
                'location_type' => 'home',
                'scheduled_at' => now()->format('Y-m-d'),
                'visit_date' => now()->format('Y-m-d'),
            ])
            ->call('create')
            ->assertNotified();

        // when editing a visit
        Livewire::actingAs($this->admin)
            ->test(EditVisit::class, ['record' => $visit->id])
            ->fillForm([
                'outcome_summary' => 'Updated summary',
            ])
            ->call('save')
            ->assertNotified();

        // when deleting a visit
        Livewire::actingAs($this->admin)
            ->test(EditVisit::class, ['record' => $visit->id])
            ->callAction('delete')
            ->assertNotified();
    }

    public function test_user_without_permissions_cannot_access_visits()
    {
        // given a user without any permissions
        // when accessing the list page
        $response = $this->actingAs($this->userWithoutPermissions)
            ->get(VisitResource::getUrl('index'));

        // then access is denied
        $response->assertForbidden();
    }
}
