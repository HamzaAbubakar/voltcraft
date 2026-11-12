<?php

namespace Tests\Feature;

use App\Models\BatteryModel;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatteryModelMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_battery_model_component_mappings(): void
    {
        $user = User::factory()->create();
        $batteryModel = BatteryModel::query()
            ->where('name', '24V Heavy Duty Pack')
            ->firstOrFail();
        $cell = $this->createInventoryItem('INV-000001', '12 Volt Cell', 'pcs');
        $cable = $this->createInventoryItem('INV-000002', 'Cable Kit', 'sets');

        $this->actingAs($user)
            ->get(route('inventory.battery-models.index'))
            ->assertOk()
            ->assertSee('Battery Model Inventory Mapping')
            ->assertSee('24V Heavy Duty Pack');

        $response = $this->actingAs($user)->put(
            route('inventory.battery-models.update', $batteryModel),
            [
                'components' => [
                    ['inventory_item_id' => $cell->id, 'quantity_per_battery' => '2.000'],
                    ['inventory_item_id' => $cable->id, 'quantity_per_battery' => '0.500'],
                ],
            ],
        );

        $response->assertRedirect(route('inventory.battery-models.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('battery_model_inventory_item', [
            'battery_model_id' => $batteryModel->id,
            'inventory_item_id' => $cell->id,
            'quantity_per_battery' => '2.000',
        ]);
        $this->assertDatabaseHas('battery_model_inventory_item', [
            'battery_model_id' => $batteryModel->id,
            'inventory_item_id' => $cable->id,
            'quantity_per_battery' => '0.500',
        ]);
    }

    public function test_battery_model_mapping_rejects_duplicate_inventory_items(): void
    {
        $user = User::factory()->create();
        $batteryModel = BatteryModel::query()
            ->where('name', '12V Standard Pack')
            ->firstOrFail();
        $inventoryItem = $this->createInventoryItem('INV-000001', '12 Volt Cell', 'pcs');

        $response = $this->actingAs($user)
            ->from(route('inventory.battery-models.index'))
            ->put(route('inventory.battery-models.update', $batteryModel), [
                'components' => [
                    ['inventory_item_id' => $inventoryItem->id, 'quantity_per_battery' => '1.000'],
                    ['inventory_item_id' => $inventoryItem->id, 'quantity_per_battery' => '2.000'],
                ],
            ]);

        $response->assertSessionHasErrors('components.1.inventory_item_id');
        $this->assertDatabaseCount('battery_model_inventory_item', 0);
    }

    public function test_guest_cannot_manage_battery_model_mappings(): void
    {
        $batteryModel = BatteryModel::query()
            ->where('name', '12V Standard Pack')
            ->firstOrFail();

        $this->get(route('inventory.battery-models.index'))
            ->assertRedirect(route('login'));
        $this->put(route('inventory.battery-models.update', $batteryModel), [
            'components' => [],
        ])->assertRedirect(route('login'));
    }

    private function createInventoryItem(string $sku, string $name, string $unit): InventoryItem
    {
        return InventoryItem::create([
            'sku' => $sku,
            'name' => $name,
            'category' => 'Cell',
            'quantity_on_hand' => '10.000',
            'unit' => $unit,
            'reorder_point' => '2.000',
            'unit_cost' => '1.00',
        ]);
    }
}
