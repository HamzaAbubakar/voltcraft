<?php

namespace Tests\Feature;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_the_investments_page(): void
    {
        $this->get(route('investments.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_the_investments_page(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('investments.index'));

        $response->assertOk()
            ->assertSee('Investment Records')
            ->assertSee('Investments')
            ->assertSee('Record Investment');
    }

    public function test_authenticated_user_can_record_an_investment_and_cash_in_transaction(): void
    {
        $user = User::factory()->create();
        $cashAccount = CashAccount::factory()->create();

        $response = $this->actingAs($user)->post(route('investments.store'), [
            'cash_account_id' => $cashAccount->id,
            'investor_name' => 'Amina Khan',
            'contact_info' => 'amina@example.test',
            'principal_amount' => '25000.00',
            'received_date' => '2026-11-12',
            'maturity_date' => '2026-12-12',
            'profit_share_percentage' => '12.50',
            'notes' => 'Family investment',
            'status' => 'settled',
            'profit_paid' => '5000.00',
        ]);

        $response->assertRedirect(route('investments.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('investments', [
            'investor_name' => 'Amina Khan',
            'contact_info' => 'amina@example.test',
            'principal_amount' => 25000,
            'received_date' => '2026-11-12 00:00:00',
            'maturity_date' => '2026-12-12 00:00:00',
            'profit_share_percentage' => 12.5,
            'notes' => 'Family investment',
            'status' => 'active',
            'profit_paid' => 0,
        ]);
        $this->assertDatabaseHas('cash_transactions', [
            'cash_account_id' => $cashAccount->id,
            'recorded_by_user_id' => $user->id,
            'type' => 'cash_in',
            'amount' => 25000,
            'description' => 'Investment from Amina Khan',
            'transaction_date' => '2026-11-12 00:00:00',
        ]);
        $investment = Investment::query()->where('investor_name', 'Amina Khan')->firstOrFail();
        $this->assertDatabaseHas('cash_transactions', [
            'id' => $investment->initial_cash_transaction_id,
            'description' => 'Investment from Amina Khan',
        ]);
    }

    public function test_authenticated_user_can_edit_an_active_investment_and_its_cash_transaction(): void
    {
        $user = User::factory()->create();
        $investment = $this->createActiveInvestment();
        $replacementAccount = CashAccount::factory()->create();

        $response = $this->actingAs($user)->put(route('investments.update', $investment), [
            'cash_account_id' => $replacementAccount->id,
            'investor_name' => 'Updated Investor',
            'contact_info' => 'updated@example.test',
            'principal_amount' => '18000.25',
            'received_date' => '2026-11-10',
            'maturity_date' => '2026-12-10',
            'profit_share_percentage' => '15.00',
            'notes' => 'Updated terms',
        ]);

        $response->assertRedirect(route('investments.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'investor_name' => 'Updated Investor',
            'principal_amount' => 18000.25,
            'profit_share_percentage' => 15,
            'notes' => 'Updated terms',
        ]);
        $this->assertDatabaseHas('cash_transactions', [
            'id' => $investment->initial_cash_transaction_id,
            'cash_account_id' => $replacementAccount->id,
            'amount' => 18000.25,
            'transaction_date' => '2026-11-10 00:00:00',
        ]);
    }

    public function test_authenticated_user_can_settle_an_investment_and_record_full_cash_out(): void
    {
        $user = User::factory()->create();
        $investment = $this->createActiveInvestment(['principal_amount' => '25000.00']);
        $repaymentAccount = CashAccount::factory()->create();

        $response = $this->actingAs($user)->post(route('investments.settlement.store', $investment), [
            'cash_account_id' => $repaymentAccount->id,
            'profit_paid' => 1250.75,
            'settlement_date' => '2026-12-15',
        ]);

        $response->assertRedirect(route('investments.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'status' => 'settled',
            'profit_paid' => 1250.75,
            'settlement_date' => '2026-12-15 00:00:00',
        ]);
        $this->assertDatabaseHas('cash_transactions', [
            'cash_account_id' => $repaymentAccount->id,
            'recorded_by_user_id' => $user->id,
            'type' => 'cash_out',
            'amount' => 26250.75,
            'description' => 'Investment settlement: Test Investor',
            'transaction_date' => '2026-12-15 00:00:00',
        ]);
        $settledInvestment = $investment->fresh();
        $this->assertNotNull($settledInvestment->settlement_cash_transaction_id);
        $this->assertDatabaseHas('cash_transactions', [
            'id' => $settledInvestment->settlement_cash_transaction_id,
            'description' => 'Investment settlement: Test Investor',
        ]);

        $this->actingAs($user)
            ->get(route('investments.index'))
            ->assertOk()
            ->assertSee('Settled')
            ->assertSee('Profit paid: Rs 1,250.75')
            ->assertDontSee('>Settle</a>', false);
    }

    public function test_a_settled_investment_cannot_be_settled_again(): void
    {
        $user = User::factory()->create();
        $investment = $this->createActiveInvestment();
        $investment->update([
            'status' => 'settled',
            'profit_paid' => '50.00',
            'settlement_date' => '2026-11-12',
        ]);
        $cashAccount = CashAccount::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('investments.index'))
            ->post(route('investments.settlement.store', $investment), [
                'cash_account_id' => $cashAccount->id,
                'profit_paid' => '100.00',
                'settlement_date' => '2026-11-12',
            ]);

        $response->assertSessionHasErrors([
            'investment' => 'This investment has already been settled.',
        ]);
        $this->assertDatabaseCount('cash_transactions', 1);
    }

    public function test_investment_cash_transactions_cannot_be_edited_from_the_cash_module(): void
    {
        $user = User::factory()->create();
        $investment = $this->createActiveInvestment();
        $settlementTransaction = CashTransaction::factory()
            ->for(CashAccount::factory()->create())
            ->create([
                'type' => 'cash_out',
                'amount' => '10050.00',
                'description' => 'Investment settlement: Test Investor',
                'transaction_date' => '2026-12-12',
            ]);
        $investment->update([
            'status' => 'settled',
            'profit_paid' => '50.00',
            'settlement_date' => '2026-12-12',
            'settlement_cash_transaction_id' => $settlementTransaction->id,
        ]);

        $this->actingAs($user)
            ->get(route('cash.transactions.edit', $investment->initial_cash_transaction_id))
            ->assertRedirect(route('investments.index'))
            ->assertSessionHas('warning', 'Investment transactions must be managed through the Investments module.');

        $this->actingAs($user)
            ->put(route('cash.transactions.update', $settlementTransaction), [
                'cash_account_id' => $settlementTransaction->cash_account_id,
                'type' => 'cash_in',
                'amount' => '1.00',
                'description' => 'Tampered settlement',
                'transaction_date' => '2026-12-12',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('cash_transactions', [
            'id' => $settlementTransaction->id,
            'type' => 'cash_out',
            'amount' => 10050,
            'description' => 'Investment settlement: Test Investor',
        ]);
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'status' => 'settled',
            'principal_amount' => 10000,
        ]);
    }

    public function test_investor_details_are_escaped_on_the_investments_page(): void
    {
        $user = User::factory()->create();
        $this->createActiveInvestment([
            'investor_name' => "<script>alert('xss')</script>",
            'contact_info' => '<img src=x onerror=alert(1)>',
        ]);

        $response = $this->actingAs($user)->get(route('investments.index'));

        $response->assertOk()
            ->assertSee('&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee("<script>alert('xss')</script>", false);
    }

    public function test_settlement_rejects_a_total_above_the_cash_transaction_limit_without_writing(): void
    {
        $user = User::factory()->create();
        $investment = $this->createActiveInvestment([
            'principal_amount' => '9999999999.99',
        ]);
        $cashAccount = CashAccount::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('investments.settlement.create', $investment))
            ->post(route('investments.settlement.store', $investment), [
                'cash_account_id' => $cashAccount->id,
                'profit_paid' => '0.01',
                'settlement_date' => '2026-12-15',
            ]);

        $response->assertSessionHasErrors([
            'profit_paid' => 'The principal and profit total exceeds the supported cash transaction amount.',
        ]);
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('cash_transactions', 1);
    }

    public function test_investment_dates_must_not_place_maturity_before_receipt(): void
    {
        $user = User::factory()->create();
        $cashAccount = CashAccount::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('investments.create'))
            ->post(route('investments.store'), [
                'cash_account_id' => $cashAccount->id,
                'investor_name' => 'Invalid Dates',
                'principal_amount' => '500.00',
                'received_date' => '2026-11-12',
                'maturity_date' => '2026-11-11',
                'profit_share_percentage' => '10.00',
            ]);

        $response->assertSessionHasErrors('maturity_date');
        $this->assertDatabaseCount('investments', 0);
        $this->assertDatabaseCount('cash_transactions', 0);
    }

    public function test_settlement_date_cannot_be_before_the_investment_was_received(): void
    {
        $user = User::factory()->create();
        $investment = $this->createActiveInvestment([
            'received_date' => '2026-11-12',
        ]);
        $cashAccount = CashAccount::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('investments.settlement.create', $investment))
            ->post(route('investments.settlement.store', $investment), [
                'cash_account_id' => $cashAccount->id,
                'profit_paid' => '0.00',
                'settlement_date' => '2026-11-11',
            ]);

        $response->assertSessionHasErrors('settlement_date');
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('cash_transactions', 1);
    }

    public function test_dashboard_shows_overdue_and_three_day_investments_but_not_later_or_settled_ones(): void
    {
        $this->travelTo('2026-11-12 10:00:00');
        $user = User::factory()->create();
        $this->createActiveInvestment([
            'investor_name' => 'Overdue Investor',
            'maturity_date' => '2026-11-11',
        ]);
        $this->createActiveInvestment([
            'investor_name' => 'Due Today Investor',
            'maturity_date' => '2026-11-12',
        ]);
        $this->createActiveInvestment([
            'investor_name' => 'Three Day Investor',
            'maturity_date' => '2026-11-15',
        ]);
        $this->createActiveInvestment([
            'investor_name' => 'Later Investor',
            'maturity_date' => '2026-11-16',
        ]);
        $settled = $this->createActiveInvestment([
            'investor_name' => 'Settled Investor',
            'maturity_date' => '2026-11-11',
        ]);
        $settled->update([
            'status' => 'settled',
            'profit_paid' => '10.00',
            'settlement_date' => '2026-11-10',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Investment repayments need attention')
            ->assertSee('Overdue Investor')
            ->assertSee('Due Today Investor')
            ->assertSee('Three Day Investor')
            ->assertViewHas('dueInvestments', function ($dueInvestments): bool {
                return $dueInvestments->pluck('investor_name')->all() === [
                    'Overdue Investor',
                    'Due Today Investor',
                    'Three Day Investor',
                ];
            });
    }

    private function createActiveInvestment(array $attributes = []): Investment
    {
        $cashAccount = CashAccount::factory()->create();
        $principalAmount = $attributes['principal_amount'] ?? '10000.00';
        $investorName = $attributes['investor_name'] ?? 'Test Investor';
        $receivedDate = $attributes['received_date'] ?? '2026-11-12';
        $cashTransaction = CashTransaction::factory()
            ->for($cashAccount)
            ->create([
                'type' => 'cash_in',
                'amount' => $principalAmount,
                'description' => 'Investment from '.$investorName,
                'transaction_date' => $receivedDate,
            ]);

        return Investment::create(array_merge([
            'investor_name' => $investorName,
            'principal_amount' => $principalAmount,
            'received_date' => $receivedDate,
            'maturity_date' => '2026-12-12',
            'profit_share_percentage' => '10.00',
            'initial_cash_transaction_id' => $cashTransaction->id,
            'status' => 'active',
        ], $attributes));
    }
}
