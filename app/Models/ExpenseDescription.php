<?php

namespace App\Models;

use Database\Factories\ExpenseDescriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class ExpenseDescription extends Model
{
    /** @use HasFactory<ExpenseDescriptionFactory> */
    use HasFactory;

    /**
     * Get expenses recorded with this description.
     *
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
