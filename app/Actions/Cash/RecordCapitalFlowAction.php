<?php

namespace App\Actions\Cash;

use App\Models\CashTransaction;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
     *     battery_items?: array<int, array{name: string, quantity: int}>,
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

            foreach ($items as $item) {
                if ($data['type'] === 'cash_in') {
                    $transaction->items()->create([
                        'item_name' => $item['name'],
                        'quantity' => $item['quantity'],
                    ]);

                    continue;
                }

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

            return $transaction;
        });
    }
}
