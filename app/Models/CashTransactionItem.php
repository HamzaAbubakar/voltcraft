<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cash_transaction_id',
    'inventory_item_id',
    'item_name',
    'quantity',
])]
class CashTransactionItem extends Model
{
    public const BATTERY_OPTIONS = [
        '48V Type A (Wall Mount)',
        '48V Type B (Server Rack)',
        '24V Heavy Duty Pack',
        '12V Standard Pack',
    ];

    /**
     * Get the cash transaction associated with the line item.
     *
     * @return BelongsTo<CashTransaction, $this>
     */
    public function cashTransaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class);
    }

    /**
     * Get the inventory item associated with a supplier receipt line.
     *
     * @return BelongsTo<InventoryItem, $this>
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
        ];
    }
}
