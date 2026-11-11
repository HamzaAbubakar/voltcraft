<?php

namespace Tests\Feature;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_cash_account_form(): void
    {
        $this->get(route('cash.accounts.create'))
            ->assertRedirect(route('login'));
    }

    public function test_cash_account_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('cash.accounts.store'), [
            'name' => 'Sam Taylor',
            'role' => 'Sales Representative',
        ]);

        $response->assertRedirect(route('cash.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cash_accounts', [
            'name' => 'Sam Taylor',
            'role' => 'Sales Representative',
            'is_active' => true,
        ]);
    }

    public function test_cash_account_can_be_deactivated_without_deleting_its_transactions(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();
        $transaction = CashTransaction::factory()->create([
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'transaction_date' => '2026-11-08',
        ]);

        $response = $this->actingAs($user)->put(route('cash.accounts.update', $account), [
            'name' => 'Former Sales Rep',
            'role' => 'Former Representative',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('cash.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cash_accounts', [
            'id' => $account->id,
            'name' => 'Former Sales Rep',
            'is_active' => false,
        ]);
        $this->assertModelExists($transaction);
    }
}
