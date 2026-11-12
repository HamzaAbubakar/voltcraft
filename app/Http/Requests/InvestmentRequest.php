<?php

namespace App\Http\Requests;

use App\Models\Investment;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvestmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        $investment = $this->route('investment');
        $cashAccountRule = Rule::exists('cash_accounts', 'id')
            ->where(function (Builder $query) use ($investment): void {
                $query->where('is_active', true);

                if ($investment instanceof Investment) {
                    $currentAccountId = $investment->initialCashTransaction()
                        ->value('cash_account_id');

                    if ($currentAccountId !== null) {
                        $query->orWhere('id', $currentAccountId);
                    }
                }
            });

        return [
            'cash_account_id' => ['required', 'integer', $cashAccountRule],
            'investor_name' => ['required', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'principal_amount' => [
                'required',
                'numeric',
                'min:0.01',
                'decimal:0,2',
                'max:9999999999.99',
            ],
            'received_date' => ['required', 'date_format:Y-m-d'],
            'maturity_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:received_date',
            ],
            'profit_share_percentage' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                'decimal:0,2',
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
