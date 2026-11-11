<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'expense_description_id',
    'cash_transaction_id',
    'created_by_user_id',
    'category',
    'paid_for',
])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    /**
     * Get the reusable description for the expense.
     *
     * @return BelongsTo<ExpenseDescription, $this>
     */
    public function description(): BelongsTo
    {
        return $this->belongsTo(ExpenseDescription::class, 'expense_description_id');
    }

    /**
     * Get the cash transaction associated with the expense.
     *
     * @return BelongsTo<CashTransaction, $this>
     */
    public function cashTransaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class);
    }

    /**
     * Get the user who recorded the expense.
     *
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
