<?php

namespace App\Models;

use Database\Factories\CashTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'cash_account_id',
    'recorded_by_user_id',
    'type',
    'amount',
    'description',
    'note',
    'transaction_date',
])]
class CashTransaction extends Model
{
    /** @use HasFactory<CashTransactionFactory> */
    use HasFactory;

    /**
     * Get the cash account associated with the transaction.
     *
     * @return BelongsTo<CashAccount, $this>
     */
    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    /**
     * Get the user who recorded the transaction.
     *
     * @return BelongsTo<User, $this>
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    /**
     * Get the expense linked to the transaction, if any.
     *
     * @return HasOne<Expense, $this>
     */
    public function expense(): HasOne
    {
        return $this->hasOne(Expense::class);
    }

    /**
     * Get the product lines associated with the transaction.
     *
     * @return HasMany<CashTransactionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CashTransactionItem::class);
    }

    /**
     * Get the investment funded by the transaction, if any.
     *
     * @return HasOne<Investment, $this>
     */
    public function investmentPrincipal(): HasOne
    {
        return $this->hasOne(Investment::class, 'initial_cash_transaction_id');
    }

    /**
     * Get the investment settled by the transaction, if any.
     *
     * @return HasOne<Investment, $this>
     */
    public function investmentSettlement(): HasOne
    {
        return $this->hasOne(Investment::class, 'settlement_cash_transaction_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }
}
