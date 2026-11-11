<?php

namespace Tests\Feature;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_page_calculates_totals_from_transaction_records(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create(['name' => 'Sales Rep']);

        CashTransaction::factory()->create([
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'type' => 'cash_in',
            'amount' => 250.00,
            'transaction_date' => '2026-11-08',
        ]);
        CashTransaction::factory()->cashOut()->create([
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'amount' => 75.00,
            'transaction_date' => '2026-11-08',
        ]);
        CashTransaction::factory()->create([
            'cash_account_id' => CashAccount::factory()->create()->id,
            'recorded_by_user_id' => $user->id,
            'type' => 'cash_in',
            'amount' => 50.00,
            'transaction_date' => '2026-11-08',
        ]);

        $response = $this->actingAs($user)->get(route('cash.index'));

        $response->assertOk()
            ->assertSee('Sales Rep')
            ->assertSee('Rs 300.00')
            ->assertSee('Rs 75.00')
            ->assertSee('Rs 225.00')
            ->assertViewHas('cashHeldTotal', fn ($total): bool => (float) $total === 225.0);
    }

    public function test_cash_page_search_filters_transactions_and_keeps_totals(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();

        CashTransaction::factory()->count(2)->create([
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'description' => 'Plain transaction',
            'transaction_date' => '2026-11-08',
        ]);
        CashTransaction::factory()->create([
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'description' => 'Customer solar payment',
            'amount' => 500.00,
            'transaction_date' => '2026-11-08',
        ]);

        $response = $this->actingAs($user)->get(route('cash.index', ['search' => 'solar']));

        $response->assertOk()
            ->assertSee('Customer solar payment')
            ->assertViewHas('transactions', fn ($transactions): bool => $transactions->total() === 1)
            ->assertViewHas('cashInTotal', fn ($total): bool => (float) $total > 0);
    }

    public function test_cash_page_paginates_transactions_and_preserves_search(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();

        CashTransaction::factory()->count(11)->create([
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'description' => 'Target transaction',
            'transaction_date' => '2026-11-08',
        ]);

        $response = $this->actingAs($user)->get(route('cash.index', [
            'search' => 'Target',
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertViewHas('transactions', function ($transactions): bool {
                return $transactions->total() === 11
                    && $transactions->currentPage() === 2;
            });
        $this->assertStringContainsString('search=Target', $response->getContent());
    }

    public function test_cash_page_escapes_account_and_transaction_text(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create([
            'name' => '<script>alert(1)</script>',
            'role' => '<em>unsafe role</em>',
        ]);
        CashTransaction::factory()->create([
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'description' => '<img src=x onerror=alert(1)>',
            'note' => '<b>unsafe note</b>',
            'transaction_date' => '2026-11-08',
        ]);

        $response = $this->actingAs($user)->get(route('cash.index'));

        $response->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;em&gt;unsafe role&lt;/em&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertSee('&lt;b&gt;unsafe note&lt;/b&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }
}
