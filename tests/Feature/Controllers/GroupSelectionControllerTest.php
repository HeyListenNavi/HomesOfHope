<?php

namespace Tests\Feature\Controllers;

use App\Models\Applicant;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Models\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GroupSelectionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    public function test_show_selection_form_returns_invalid_if_applicant_already_has_group()
    {
        // given an applicant that already has a group assigned
        $applicant = Applicant::factory()->create(['group_id' => Group::factory()->create()->id]);

        // when accessing the selection form
        $url = URL::temporarySignedRoute('group.selection.form', now()->addDay(), ['applicant' => $applicant->id]);
        $response = $this->get($url);

        // then the invalid view is returned
        $response->assertViewIs('selection.invalid');
    }

    public function test_show_success_returns_success_view()
    {
        // given a group with a future date and an applicant assigned to it
        $group = Group::factory()->create(['date_time' => now()->addDays(5), 'capacity' => 10]);
        $applicant = Applicant::factory()->create(['group_id' => $group->id, 'confirmation_status' => 'confirmed']);

        // when accessing the success page
        $url = URL::temporarySignedRoute('selection.success', now()->addDays(3), ['applicant' => $applicant->id]);
        $response = $this->get($url);

        // then the success view is rendered
        $response->assertViewIs('selection.success')->assertViewHas('applicant');
    }

    public function test_show_invalid_link_returns_invalid_view()
    {
        // given no specific state

        // when accessing the invalid link page
        $response = $this->get('/seleccion/enlace-invalido');

        // then the invalid view is returned
        $response->assertViewIs('selection.invalid');
    }

    public function test_assign_to_group_redirects_if_applicant_already_has_group()
    {
        // given an applicant that already has a group assigned
        $group = Group::factory()->create(['capacity' => 10]);
        $applicant = Applicant::factory()->create(['group_id' => $group->id]);

        // when attempting to assign to a group
        $url = URL::temporarySignedRoute('group.selection.assign', now()->addDay(), ['applicant' => $applicant->id]);
        $response = $this->post($url, ['group_id' => $group->id]);

        // then it redirects to invalid link
        $response->assertRedirect(route('selection.invalid'));
    }

    public function test_show_invitation_shows_invalid_if_no_group()
    {
        // given an applicant without a group
        $applicant = Applicant::factory()->create(['group_id' => null]);

        // when accessing the invitation page
        $url = URL::temporarySignedRoute('invitation.show', now()->addDay(), ['applicant' => $applicant->id]);
        $response = $this->get($url);

        // then the invalid view is shown
        $response->assertViewIs('selection.invalid');
    }
}
