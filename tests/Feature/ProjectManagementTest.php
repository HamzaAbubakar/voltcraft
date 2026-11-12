<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_projects(): void
    {
        $this->get(route('projects.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_a_project_and_see_its_margin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'North Site Solar',
            'customer_name' => 'Amina Khan',
            'site_location' => 'Lahore',
            'system_size_watts' => 1000,
            'customer_rate_per_watt' => '5.00',
            'outsourced_rate_per_watt' => '4.00',
            'status' => 'active',
            'start_date' => '2026-11-12',
        ]);

        $project = Project::query()->where('name', 'North Site Solar')->firstOrFail();
        $response->assertRedirect(route('projects.show', $project))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'system_size_watts' => 1000,
            'customer_rate_per_watt' => 5,
            'outsourced_rate_per_watt' => 4,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Rs 5,000.00')
            ->assertSee('Rs 4,000.00')
            ->assertSee('Rs 1,000.00');

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertViewHas('grossMarginTotal', 1000.0)
            ->assertSee('Rs 1,000.00');
    }

    public function test_project_site_expenses_reduce_profit_without_creating_company_expense_or_cash_records(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'system_size_watts' => 1000,
            'customer_rate_per_watt' => '5.00',
            'outsourced_rate_per_watt' => '4.00',
        ]);

        $response = $this->actingAs($user)->post(route('projects.expenses.store', $project), [
            'category' => 'food',
            'amount' => '175.50',
            'expense_date' => '2026-11-12',
            'note' => 'Team meals',
        ]);

        $response->assertRedirect(route('projects.show', $project))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('project_expenses', [
            'project_id' => $project->id,
            'recorded_by_user_id' => $user->id,
            'category' => 'food',
            'amount' => 175.5,
            'expense_date' => '2026-11-12 00:00:00',
            'note' => 'Team meals',
        ]);
        $this->assertDatabaseCount('expenses', 0);
        $this->assertDatabaseCount('cash_transactions', 0);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Rs 824.50')
            ->assertSee('Team meals');

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Rs 824.50');
    }

    public function test_authenticated_user_can_update_project_rates_and_status(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'system_size_watts' => 1000,
            'customer_rate_per_watt' => '5.00',
            'outsourced_rate_per_watt' => '4.00',
        ]);

        $response = $this->actingAs($user)->put(route('projects.update', $project), [
            'name' => 'Updated Solar Project',
            'customer_name' => 'Amina Khan',
            'site_location' => 'Lahore',
            'system_size_watts' => 1000,
            'customer_rate_per_watt' => '6.00',
            'outsourced_rate_per_watt' => '4.00',
            'status' => 'completed',
            'start_date' => '2026-11-01',
            'completed_date' => '2026-11-12',
        ]);

        $response->assertRedirect(route('projects.show', $project))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Solar Project',
            'customer_rate_per_watt' => 6,
            'status' => 'completed',
            'completed_date' => '2026-11-12 00:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Rs 2,000.00');
    }

    public function test_active_project_can_be_completed_and_completion_date_is_recorded(): void
    {
        $this->travelTo('2026-11-12');
        $user = User::factory()->create();
        $project = Project::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->post(route('projects.complete', $project));

        $response->assertRedirect(route('projects.show', $project))
            ->assertSessionHas('success', 'Project marked as completed.');
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'completed',
            'completed_date' => '2026-11-12 00:00:00',
        ]);
        $this->assertDatabaseCount('project_expenses', 0);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertDontSee('Complete project');
    }

    public function test_cancelled_project_cannot_be_completed(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'status' => 'cancelled',
            'completed_date' => null,
        ]);

        $response = $this->actingAs($user)
            ->from(route('projects.show', $project))
            ->post(route('projects.complete', $project));

        $response->assertRedirect(route('projects.show', $project))
            ->assertSessionHas('warning', 'Only active projects can be completed.');
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'cancelled',
            'completed_date' => null,
        ]);
    }

    public function test_invalid_per_watt_rates_do_not_create_a_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('projects.create'))
            ->post(route('projects.store'), [
                'name' => 'Invalid Project',
                'system_size_watts' => 1000,
                'customer_rate_per_watt' => '0',
                'outsourced_rate_per_watt' => '4.00',
                'status' => 'active',
                'start_date' => '2026-11-12',
            ]);

        $response->assertRedirect(route('projects.create'))
            ->assertSessionHasErrors(['customer_rate_per_watt' => 'The customer rate per watt field must be at least 0.01.']);
        $this->assertDatabaseMissing('projects', ['name' => 'Invalid Project']);
    }

    public function test_project_expense_requires_a_positive_amount(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('projects.expenses.create', $project))
            ->post(route('projects.expenses.store', $project), [
                'category' => 'travel',
                'amount' => '0',
                'expense_date' => '2026-11-12',
            ]);

        $response->assertRedirect(route('projects.expenses.create', $project))
            ->assertSessionHasErrors(['amount' => 'The amount field must be at least 0.01.']);
        $this->assertDatabaseCount('project_expenses', 0);
        $this->assertDatabaseCount('cash_transactions', 0);
    }
}
