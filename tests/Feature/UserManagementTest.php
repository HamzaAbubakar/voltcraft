<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_management_pages(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('User Management')
            ->assertSee($admin->email);

        $this->get(route('users.create'))
            ->assertOk()
            ->assertSee('Create user')
            ->assertSee('Page access');
    }

    public function test_admin_can_create_a_user_with_selected_page_access(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Inventory Clerk',
            'email' => 'inventory@example.com',
            'password' => 'secure-password',
            'accessible_pages' => ['inventory', 'cash'],
            'is_admin' => true,
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'inventory@example.com')->sole();

        $this->assertSame(['inventory', 'cash'], $user->accessible_pages);
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('secure-password', $user->password));
    }

    public function test_admin_can_update_a_users_page_access_without_changing_a_blank_password(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->nonAdmin()->create([
            'email' => 'employee@example.com',
            'password' => 'original-password',
            'accessible_pages' => ['inventory'],
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'accessible_pages' => ['projects'],
        ]);

        $response->assertRedirect(route('users.index'));

        $user->refresh();
        $this->assertSame(['projects'], $user->accessible_pages);
        $this->assertTrue(Hash::check('original-password', $user->password));
    }

    public function test_users_can_open_assigned_pages_but_not_unassigned_pages_or_user_management(): void
    {
        $user = User::factory()->nonAdmin()->create([
            'accessible_pages' => ['inventory'],
        ]);

        $this->actingAs($user)
            ->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Inventory Management')
            ->assertDontSee('Cash In and Out')
            ->assertDontSee('User Management');

        $this->get(route('cash.index'))->assertForbidden();
        $this->post(route('cash.accounts.store'), [])->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_admin_cannot_assign_unknown_page_access(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Invalid Access',
                'email' => 'invalid@example.com',
                'password' => 'secure-password',
                'accessible_pages' => ['unknown-page'],
            ]);

        $response->assertSessionHasErrors([
            'accessible_pages.0' => 'The selected accessible_pages.0 is invalid.',
        ]);
        $this->assertDatabaseMissing('users', ['email' => 'invalid@example.com']);
    }
}
