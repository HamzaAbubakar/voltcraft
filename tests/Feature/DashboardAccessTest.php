<?php

namespace Tests\Feature;

use App\Models\BatteryModel;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_every_erp_page(): void
    {
        foreach (['dashboard', 'inventory.index', 'cash.index', 'workforce.index', 'expenses.index'] as $routeName) {
            $this->get(route($routeName))
                ->assertRedirect(route('login'));
        }
    }

    public function test_authenticated_users_can_view_each_erp_page(): void
    {
        $this->actingAs(User::factory()->create());

        $pages = [
            'dashboard' => 'Critical Low Stock Inventory Watchlist',
            'inventory.index' => 'Stock Items',
            'cash.index' => 'Cash In and Out',
            'workforce.index' => 'Workforce Compensation',
            'expenses.index' => 'Multi-Channel Expense Ledger',
        ];

        foreach ($pages as $routeName => $content) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee($content)
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

    public function test_dashboard_metrics_reflect_current_inventory_sales_and_cash_balances(): void
    {
        $user = User::factory()->create();
        $cashAccount = CashAccount::factory()->create([
            'name' => 'Demo Sales Account',
            'role' => 'Retail Sales',
        ]);
        $currentSale = CashTransaction::factory()->for($cashAccount)->create([
            'type' => 'cash_in',
            'amount' => '1500.00',
            'transaction_date' => now()->toDateString(),
        ]);
        $currentSale->items()->create([
            'item_name' => '12V Standard Pack',
            'quantity' => '2.000',
        ]);

        $previousSale = CashTransaction::factory()->for($cashAccount)->create([
            'type' => 'cash_in',
            'amount' => '500.00',
            'transaction_date' => now()->subMonthNoOverflow()->toDateString(),
        ]);
        $previousSale->items()->create([
            'item_name' => '12V Standard Pack',
            'quantity' => '5.000',
        ]);

        $supplierPurchase = CashTransaction::factory()->for($cashAccount)->cashOut()->create([
            'amount' => '250.00',
            'transaction_date' => now()->toDateString(),
        ]);
        $supplierPurchase->items()->create([
            'item_name' => '12V Standard Pack',
            'quantity' => '9.000',
        ]);

        InventoryItem::create([
            'sku' => 'INV-LOW-001',
            'name' => 'Low stock cells',
            'category' => 'Battery Cells',
            'quantity_on_hand' => '2.000',
            'unit' => 'pcs',
            'reorder_point' => '5.000',
            'unit_cost' => '10.00',
        ]);
        InventoryItem::create([
            'sku' => 'INV-OK-001',
            'name' => 'Available cells',
            'category' => 'Battery Cells',
            'quantity_on_hand' => '8.000',
            'unit' => 'pcs',
            'reorder_point' => '5.000',
            'unit_cost' => '10.00',
        ]);
        BatteryModel::query()
            ->where('name', '12V Standard Pack')
            ->firstOrFail()
            ->inventoryItems()
            ->attach(
                InventoryItem::query()->where('sku', 'INV-OK-001')->firstOrFail(),
                ['quantity_per_battery' => '1.000'],
            );

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Demo Sales Account')
            ->assertSee('Rs 1,750.00')
            ->assertSee('Rs 2,000.00')
            ->assertSee('Rs 250.00')
            ->assertSee('INV-LOW-001')
            ->assertDontSee('Asif Khan')
            ->assertViewHas('lowStockItems', function ($lowStockItems): bool {
                return $lowStockItems->pluck('sku')->all() === ['INV-LOW-001'];
            })
            ->assertViewHas('batterySales', function ($batterySales): bool {
                return $batterySales->firstWhere('name', '12V Standard Pack')['quantity'] === 2.0;
            });
    }
}
