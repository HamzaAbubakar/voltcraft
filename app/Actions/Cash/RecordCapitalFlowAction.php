<?php

namespace App\Actions\Cash;

use App\Models\BatteryModel;
use App\Models\CashTransaction;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordCapitalFlowAction
{
    /**
     * Record a cash transaction and its associated battery or inventory lines.
     *
     * @param  array{
     *     cash_account_id: int,
     *     type: 'cash_in'|'cash_out',
     *     amount: numeric-string,
     *     transaction_date: string,
     *     note?: string|null,
     *     battery_items?: array<int, array{battery_model_id: int, quantity: int}>,
     *     inventory_items?: array<int, array{inventory_item_id: int, quantity: numeric-string}>
     * }  $data
     */
    public function handle(array $data, User $user): CashTransaction
    {
        return DB::transaction(function () use ($data, $user): CashTransaction {
            $items = $data['type'] === 'cash_in'
                ? $data['battery_items']
                : $data['inventory_items'];

            $transaction = CashTransaction::create([
                'cash_account_id' => $data['cash_account_id'],
                'recorded_by_user_id' => $user->id,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'description' => $data['type'] === 'cash_in'
                    ? 'Battery sale'
                    : 'Supplier inventory purchase',
                'note' => $data['note'] ?? null,
                'transaction_date' => $data['transaction_date'],
            ]);

            if ($data['type'] === 'cash_in') {
                $this->recordBatterySale($transaction, $items, $user);
            } else {
                foreach ($items as $item) {
                    $inventoryItem = InventoryItem::query()
                        ->whereKey($item['inventory_item_id'])
                        ->lockForUpdate()
                        ->firstOrFail();
                    $quantity = (float) $item['quantity'];

                    $transaction->items()->create([
                        'inventory_item_id' => $inventoryItem->id,
                        'item_name' => $inventoryItem->name,
                        'quantity' => $item['quantity'],
                    ]);

                    $inventoryItem->increment('quantity_on_hand', $quantity);
                    $inventoryItem->stockMovements()->create([
                        'user_id' => $user->id,
                        'type' => 'received',
                        'quantity_change' => $item['quantity'],
                        'note' => 'Supplier receipt for cash transaction #'.$transaction->id,
                    ]);
                }
            }

            return $transaction;
        });
    }

    /**
     * Record battery lines and deduct their mapped components from inventory.
     *
     * @param  array<int, array{battery_model_id: int, quantity: int}>  $items
     */
    private function recordBatterySale(CashTransaction $transaction, array $items, User $user): void
    {
        $batteryModels = BatteryModel::query()
            ->whereIn('id', array_column($items, 'battery_model_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->load('inventoryItems')
            ->keyBy('id');

        $usageByInventoryItem = [];
        $movementLines = [];

        foreach ($items as $index => $item) {
            $batteryModel = $batteryModels->get($item['battery_model_id']);

            if ($batteryModel === null || $batteryModel->inventoryItems->isEmpty()) {
                throw ValidationException::withMessages([
                    "battery_items.{$index}.battery_model_id" => 'Map at least one inventory item to this battery model before recording a sale.',
                ]);
            }

            $transaction->items()->create([
                'item_name' => $batteryModel->name,
                'quantity' => $item['quantity'],
            ]);

            foreach ($batteryModel->inventoryItems as $inventoryItem) {
                $componentQuantityInThousandths = (int) round((float) $inventoryItem->pivot->quantity_per_battery * 1000)
                    * $item['quantity'];
                $inventoryItemId = $inventoryItem->id;

                $usageByInventoryItem[$inventoryItemId] = ($usageByInventoryItem[$inventoryItemId] ?? 0)
                    + $componentQuantityInThousandths;
                $movementLines[] = [
                    'inventory_item_id' => $inventoryItemId,
                    'quantity_change' => $this->formatThousandths(-$componentQuantityInThousandths),
                    'note' => 'Battery sale #'.$transaction->id.': '.$batteryModel->name.' x '.$item['quantity'],
                ];
            }
        }

        $inventoryItems = InventoryItem::query()
            ->whereIn('id', array_keys($usageByInventoryItem))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($usageByInventoryItem as $inventoryItemId => $quantityInThousandths) {
            $inventoryItem = $inventoryItems->get($inventoryItemId);

            if (
                $inventoryItem === null
                || $quantityInThousandths > 999_999_999_999
                || (int) round((float) $inventoryItem->quantity_on_hand * 1000) - $quantityInThousandths < -999_999_999_999
            ) {
                throw ValidationException::withMessages([
                    'battery_items' => 'The mapped component quantities exceed the supported inventory range.',
                ]);
            }

            $inventoryItem->decrement(
                'quantity_on_hand',
                $this->formatThousandths($quantityInThousandths),
            );
        }

        foreach ($movementLines as $movementLine) {
            $inventoryItems->get($movementLine['inventory_item_id'])
                ->stockMovements()
                ->create([
                    'user_id' => $user->id,
                    'type' => 'battery_sale',
                    'quantity_change' => $movementLine['quantity_change'],
                    'note' => $movementLine['note'],
                ]);
        }
    }

    private function formatThousandths(int $quantity): string
    {
        $sign = $quantity < 0 ? '-' : '';
        $quantity = abs($quantity);

        return $sign.intdiv($quantity, 1000).'.'.str_pad((string) ($quantity % 1000), 3, '0', STR_PAD_LEFT);
    }
}
