<?php

namespace Tests\Feature;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapitalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_record_a_battery_sale_with_multiple_battery_lines(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();
        $inventoryItem = $this->createInventoryItem();

        $response = $this->actingAs($user)->post(route('dashboard.capital-flows.store'), [
            'cash_account_id' => $account->id,
            'type' => 'cash_in',
            'amount' => '6250.00',
            'transaction_date' => '2026-11-11',
            'battery_items' => [
                ['name' => '48V Type A (Wall Mount)', 'quantity' => 2],
                ['name' => '24V Heavy Duty Pack', 'quantity' => 1],
            ],
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cash_transactions', [
            'cash_account_id' => $account->id,
            'recorded_by_user_id' => $user->id,
            'type' => 'cash_in',
            'amount' => 6250,
            'description' => 'Battery sale',
        ]);
        $this->assertDatabaseHas('cash_transaction_items', [
            'item_name' => '48V Type A (Wall Mount)',
            'quantity' => 2,
            'inventory_item_id' => null,
        ]);
        $this->assertDatabaseHas('cash_transaction_items', [
            'item_name' => '24V Heavy Duty Pack',
            'quantity' => 1,
            'inventory_item_id' => null,
        ]);
        $this->assertSame('4.000', $inventoryItem->fresh()->quantity_on_hand);
        $this->assertDatabaseCount('stock_movements', 0);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Battery sale')
            ->assertSee('48V Type A (Wall Mount)')
            ->assertSee('6,250.00');
    }

    public function test_supplier_debit_records_lines_and_receives_inventory_atomically(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();
        $inventoryItem = $this->createInventoryItem(['quantity_on_hand' => '4.000']);

        $response = $this->actingAs($user)->post(route('dashboard.capital-flows.store'), [
            'cash_account_id' => $account->id,
            'type' => 'cash_out',
            'amount' => '315.50',
            'transaction_date' => '2026-11-11',
            'inventory_items' => [
                ['inventory_item_id' => $inventoryItem->id, 'quantity' => '2.500'],
            ],
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cash_transactions', [
            'cash_account_id' => $account->id,
            'type' => 'cash_out',
            'amount' => 315.5,
            'description' => 'Supplier inventory purchase',
        ]);
        $transaction = CashTransaction::query()->firstOrFail();
        $this->assertDatabaseHas('cash_transaction_items', [
            'cash_transaction_id' => $transaction->id,
            'inventory_item_id' => $inventoryItem->id,
            'item_name' => $inventoryItem->name,
            'quantity' => '2.500',
        ]);
        $this->assertSame('6.500', $inventoryItem->fresh()->quantity_on_hand);
        $this->assertDatabaseHas('stock_movements', [
            'inventory_item_id' => $inventoryItem->id,
            'user_id' => $user->id,
            'type' => 'received',
            'quantity_change' => '2.500',
        ]);
    }

    public function test_supplier_debit_rejects_inventory_items_that_do_not_exist(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('dashboard.capital-flows.store'), [
                'cash_account_id' => $account->id,
                'type' => 'cash_out',
                'amount' => '315.50',
                'transaction_date' => '2026-11-11',
                'inventory_items' => [
                    ['inventory_item_id' => 999, 'quantity' => '2.500'],
                ],
            ]);

        $response->assertSessionHasErrors('inventory_items.0.inventory_item_id');
        $this->assertDatabaseCount('cash_transactions', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_supplier_debit_requires_at_least_one_inventory_line(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('dashboard.capital-flows.store'), [
                'cash_account_id' => $account->id,
                'type' => 'cash_out',
                'amount' => '315.50',
                'transaction_date' => '2026-11-11',
                'inventory_items' => [],
            ]);

        $response->assertSessionHasErrors('inventory_items');
        $this->assertDatabaseCount('cash_transactions', 0);
    }

    public function test_battery_sale_rejects_battery_models_outside_the_available_options(): void
    {
        $user = User::factory()->create();
        $account = CashAccount::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('dashboard.capital-flows.store'), [
                'cash_account_id' => $account->id,
                'type' => 'cash_in',
                'amount' => '100.00',
                'transaction_date' => '2026-11-11',
                'battery_items' => [
                    ['name' => 'Unknown model', 'quantity' => 1],
                ],
            ]);

        $response->assertSessionHasErrors('battery_items.0.name');
        $this->assertDatabaseCount('cash_transactions', 0);
    }

    public function test_guest_cannot_record_a_capital_flow(): void
    {
        $this->post(route('dashboard.capital-flows.store'))
            ->assertRedirect(route('login'));
    }

    public function test_capital_flow_with_item_lines_cannot_be_edited_through_the_cash_form(): void
    {
        $user = User::factory()->create();
        $transaction = CashTransaction::factory()->create(['type' => 'cash_out']);
        $transaction->items()->create([
            'inventory_item_id' => null,
            'item_name' => 'Test item',
            'quantity' => '1.000',
        ]);

        $this->actingAs($user)
            ->get(route('cash.transactions.edit', $transaction))
            ->assertRedirect(route('cash.index'))
            ->assertSessionHas('warning');
    }

    public function test_capital_flow_with_item_lines_cannot_be_updated_through_the_cash_form(): void
    {
        $user = User::factory()->create();
        $transaction = CashTransaction::factory()->create(['type' => 'cash_out']);
        $transaction->items()->create([
            'inventory_item_id' => null,
            'item_name' => 'Test item',
            'quantity' => '1.000',
        ]);

        $this->actingAs($user)->put(route('cash.transactions.update', $transaction), [
            'cash_account_id' => $transaction->cash_account_id,
            'type' => 'cash_in',
            'amount' => '10.00',
            'description' => 'Changed transaction',
            'transaction_date' => '2026-11-11',
        ])->assertForbidden();

        $this->assertDatabaseHas('cash_transactions', [
            'id' => $transaction->id,
            'type' => 'cash_out',
            'description' => $transaction->description,
        ]);
    }

    /**
     * Create inventory data for a capital-flow feature test.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function createInventoryItem(array $attributes = []): InventoryItem
    {
        return InventoryItem::create([
            'sku' => 'INV-000001',
            'name' => 'Prismatic Cell',
            'category' => 'Cell',
            'quantity_on_hand' => '4.000',
            'unit' => 'pcs',
            'reorder_point' => '5.000',
            'unit_cost' => '3.25',
            ...$attributes,
        ]);
    }
}
