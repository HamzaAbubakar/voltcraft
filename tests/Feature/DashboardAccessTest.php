<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_the_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_the_erp_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Ledger')
            ->assertSee('Inventory Management')
            ->assertSee('Projects')
            ->assertSee('Company Shares')
            ->assertSee('Investments')
            ->assertSee('Soon')
            ->assertSee('Sign out')
            ->assertDontSee('Operations Control');
    }
}
