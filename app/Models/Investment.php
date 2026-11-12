<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'investor_name',
    'contact_info',
    'principal_amount',
    'received_date',
    'maturity_date',
    'profit_share_percentage',
    'notes',
    'status',
    'profit_paid',
    'settlement_date',
    'initial_cash_transaction_id',
    'settlement_cash_transaction_id',
])]
class Investment extends Model
{
    /**
     * Get the cash transaction that recorded the original investment.
     *
     * @return BelongsTo<CashTransaction, $this>
     */
    public function initialCashTransaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class, 'initial_cash_transaction_id');
    }

    /**
     * Get the cash transaction that recorded settlement.
     *
     * @return BelongsTo<CashTransaction, $this>
     */
    public function settlementCashTransaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class, 'settlement_cash_transaction_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'principal_amount' => 'decimal:2',
            'profit_share_percentage' => 'decimal:2',
            'profit_paid' => 'decimal:2',
            'received_date' => 'date',
            'maturity_date' => 'date',
            'settlement_date' => 'date',
        ];
    }
}
