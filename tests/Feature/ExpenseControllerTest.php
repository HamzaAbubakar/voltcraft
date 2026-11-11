<?php

namespace Tests\Feature;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\ExpenseDescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_expenses(): void
    {
        $this->get(route('expenses.index'))
            ->assertRedirect(route('login'));
    }

    public function test_expense_creates_a_reusable_description_and_linked_cash_out_transaction(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create(['name' => 'Ammar Main Bank']);

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'cash_account_id' => $account->id,
            'description' => 'Petrol',
            'category' => 'Travel',
            'paid_for' => 'Company',
            'amount' => '350.50',
            'transaction_date' => '2026-11-08',
        ]);

        $response->assertRedirect(route('expenses.index', ['period' => 'month', 'month' => '2026-11']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('expense_descriptions', ['name' => 'Petrol']);
        $this->assertDatabaseHas('cash_transactions', [
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'type' => 'cash_out',
            'amount' => 350.50,
            'description' => 'Petrol',
            'transaction_date' => '2026-11-08 00:00:00',
        ]);
        $this->assertDatabaseHas('expenses', [
            'created_by_user_id' => $user->id,
            'category' => 'Travel',
            'paid_for' => 'Company',
        ]);

        $this->get(route('expenses.index', ['period' => 'month', 'month' => '2026-11']))
            ->assertOk()
            ->assertSee('Petrol')
            ->assertSee('Travel')
            ->assertSee('Bank Account')
            ->assertSee('Ammar Main Bank');
        $this->get(route('cash.index'))
            ->assertOk()
            ->assertSee('Edit expense');
    }

    public function test_expense_edit_updates_its_linked_cash_transaction_and_balance(): void
    {
        $user = User::factory()->create();
        $oldAccount = CashAccount::factory()->create(['name' => 'Old account']);
        $newAccount = CashAccount::factory()->create(['name' => 'New account']);
        $description = ExpenseDescription::factory()->create(['name' => 'Old description']);
        $transaction = CashTransaction::factory()->cashOut()->create([
            'cash_account_id' => $oldAccount->id,
            'recorded_by_user_id' => $user->id,
            'amount' => 100,
            'description' => $description->name,
            'transaction_date' => '2026-11-07',
        ]);
        $expense = Expense::factory()->create([
            'expense_description_id' => $description->id,
            'cash_transaction_id' => $transaction->id,
            'created_by_user_id' => $user->id,
            'category' => 'Operations',
            'paid_for' => 'Company',
        ]);

        $response = $this->actingAs($user)->put(route('expenses.update', $expense), [
            'cash_account_id' => $newAccount->id,
            'description' => 'Kids fee',
            'category' => 'Family support',
            'paid_for' => 'Ammar',
            'amount' => '75.25',
            'transaction_date' => '2026-11-08',
        ]);

        $response->assertRedirect(route('expenses.index', ['period' => 'month', 'month' => '2026-11']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('expense_descriptions', ['name' => 'Kids fee']);
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'expense_description_id' => ExpenseDescription::query()->where('name', 'Kids fee')->value('id'),
            'category' => 'Family support',
            'paid_for' => 'Ammar',
        ]);
        $this->assertDatabaseHas('cash_transactions', [
            'id' => $transaction->id,
            'cash_account_id' => $newAccount->id,
            'type' => 'cash_out',
            'amount' => 75.25,
            'description' => 'Kids fee',
            'transaction_date' => '2026-11-08',
        ]);

        $this->get(route('cash.index'))
            ->assertOk()
            ->assertSee('Rs -75.25');
    }

    public function test_cash_transaction_linked_to_expense_can_only_be_edited_through_expenses(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();
        $description = ExpenseDescription::factory()->create();
        $transaction = CashTransaction::factory()->cashOut()->create([
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'amount' => 25,
            'description' => $description->name,
        ]);
        $expense = Expense::factory()->create([
            'expense_description_id' => $description->id,
            'cash_transaction_id' => $transaction->id,
            'created_by_user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('cash.transactions.edit', $transaction))
            ->assertRedirect(route('expenses.edit', $expense));

        $this->actingAs($user)
            ->put(route('cash.transactions.update', $transaction), [])
            ->assertForbidden();

        $this->assertDatabaseHas('cash_transactions', [
            'id' => $transaction->id,
            'amount' => 25,
        ]);
    }

    public function test_expenses_page_filters_records_and_totals_by_selected_day(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();
        $description = ExpenseDescription::factory()->create(['name' => 'Petrol']);
        $todayTransaction = CashTransaction::factory()->cashOut()->create([
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'amount' => 40,
            'description' => $description->name,
            'transaction_date' => '2026-11-08',
        ]);
        Expense::factory()->create([
            'expense_description_id' => $description->id,
            'cash_transaction_id' => $todayTransaction->id,
            'created_by_user_id' => $user->id,
            'category' => 'Travel',
            'paid_for' => 'Company',
        ]);
        $otherDayTransaction = CashTransaction::factory()->cashOut()->create([
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'amount' => 60,
            'description' => 'Kids fee',
            'transaction_date' => '2026-11-07',
        ]);
        Expense::factory()->create([
            'expense_description_id' => ExpenseDescription::factory()->create(['name' => 'Kids fee'])->id,
            'cash_transaction_id' => $otherDayTransaction->id,
            'created_by_user_id' => $user->id,
            'category' => 'Family support',
            'paid_for' => 'Ammar',
        ]);

        $this->actingAs($user)
            ->get(route('expenses.index', ['period' => 'day', 'date' => '2026-11-08']))
            ->assertOk()
            ->assertSee('Petrol')
            ->assertViewHas('expenses', fn ($expenses): bool => $expenses->count() === 1
                && $expenses->first()->id === Expense::query()->where('cash_transaction_id', $todayTransaction->id)->value('id'))
            ->assertViewHas('expenseTotal', fn ($total): bool => (float) $total === 40.0)
            ->assertViewHas('expenseCount', 1)
            ->assertViewHas('expenseAverage', fn ($average): bool => (float) $average === 40.0);

        $this->get(route('expenses.index', ['period' => 'month', 'month' => '2026-11']))
            ->assertOk()
            ->assertViewHas('expenseTotal', fn ($total): bool => (float) $total === 100.0)
            ->assertViewHas('expenseCount', 2)
            ->assertViewHas('expenseAverage', fn ($average): bool => (float) $average === 50.0);
    }

    public function test_inactive_cash_account_cannot_be_used_for_a_new_expense(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user)
            ->from(route('expenses.create'))
            ->post(route('expenses.store'), [
                'cash_account_id' => $account->id,
                'description' => 'Petrol',
                'category' => 'Travel',
                'paid_for' => 'Company',
                'amount' => '10.00',
                'transaction_date' => '2026-11-08',
            ]);

        $response->assertSessionHasErrors('cash_account_id');
        $this->assertDatabaseMissing('expense_descriptions', ['name' => 'Petrol']);
        $this->assertDatabaseMissing('cash_transactions', ['description' => 'Petrol']);
    }
}
