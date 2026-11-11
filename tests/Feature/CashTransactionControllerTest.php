<?php

namespace Tests\Feature;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_transaction_can_be_recorded_with_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();

        $response = $this->actingAs($user)->post(route('cash.transactions.store'), [
            'cash_account_id' => $account->id,
            'type' => 'cash_in',
            'amount' => '850.25',
            'description' => 'Customer payment',
            'note' => 'Invoice 104',
            'transaction_date' => '2026-11-08',
        ]);

        $response->assertRedirect(route('cash.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cash_transactions', [
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'type' => 'cash_in',
            'amount' => 850.25,
            'description' => 'Customer payment',
            'transaction_date' => '2026-11-08 00:00:00',
        ]);
    }

    public function test_cash_transaction_can_be_edited_and_updates_the_ledger_totals(): void
    {
        $user = User::factory()->create();
        $originalAccount = CashAccount::factory()->create(['name' => 'Original Account']);
        $newAccount = CashAccount::factory()->create(['name' => 'Updated Account']);
        $transaction = CashTransaction::factory()->create([
            'cash_account_id' => $originalAccount->id,
            'recorded_by_user_id' => $user->id,
            'type' => 'cash_in',
            'amount' => 500.00,
            'description' => 'Old description',
            'transaction_date' => '2026-11-07',
        ]);

        $response = $this->actingAs($user)->put(route('cash.transactions.update', $transaction), [
            'cash_account_id' => $newAccount->id,
            'type' => 'cash_out',
            'amount' => '125.50',
            'description' => 'Supplier payment',
            'note' => 'Updated note',
            'transaction_date' => '2026-11-08',
        ]);

        $response->assertRedirect(route('cash.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cash_transactions', [
            'id' => $transaction->id,
            'cash_account_id' => $newAccount->id,
            'recorded_by_user_id' => $user->id,
            'type' => 'cash_out',
            'amount' => 125.50,
            'description' => 'Supplier payment',
            'note' => 'Updated note',
            'transaction_date' => '2026-11-08 00:00:00',
        ]);

        $this->get(route('cash.index'))
            ->assertOk()
            ->assertSee('Supplier payment')
            ->assertSee('Rs 125.50')
            ->assertSee('Rs -125.50');
    }

    public function test_zero_amount_is_rejected_without_recording_a_transaction(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('cash.transactions.create'))
            ->post(route('cash.transactions.store'), [
                'cash_account_id' => $account->id,
                'type' => 'cash_in',
                'amount' => '0',
                'description' => 'Invalid transaction',
                'transaction_date' => '2026-11-08',
            ]);

        $response->assertSessionHasErrors([
            'amount' => 'The amount field must be at least 0.01.',
        ]);
        $this->assertDatabaseMissing('cash_transactions', [
            'description' => 'Invalid transaction',
        ]);
    }

    public function test_inactive_account_cannot_be_used_for_a_new_transaction(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user)
            ->from(route('cash.transactions.create'))
            ->post(route('cash.transactions.store'), [
                'cash_account_id' => $account->id,
                'type' => 'cash_in',
                'amount' => '25.00',
                'description' => 'Inactive account payment',
                'transaction_date' => '2026-11-08',
            ]);

        $response->assertSessionHasErrors('cash_account_id');
        $this->assertDatabaseMissing('cash_transactions', [
            'description' => 'Inactive account payment',
        ]);
    }
}
