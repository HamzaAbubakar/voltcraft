<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'name',
])]
class BatteryModel extends Model
{
    /**
     * Get the inventory components required to build this battery model.
     *
     * @return BelongsToMany<InventoryItem, $this>
     */
    public function inventoryItems(): BelongsToMany
    {
        return $this->belongsToMany(InventoryItem::class)
            ->withPivot('quantity_per_battery')
            ->withTimestamps();
    }
}
