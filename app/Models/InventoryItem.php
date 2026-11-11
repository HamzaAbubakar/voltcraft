<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'sku',
    'name',
    'category',
    'quantity_on_hand',
    'unit',
    'reorder_point',
    'unit_cost',
])]
class InventoryItem extends Model
{
    use SoftDeletes;

    /**
     * Get the stock movements for the inventory item.
     *
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:3',
            'reorder_point' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }
}
