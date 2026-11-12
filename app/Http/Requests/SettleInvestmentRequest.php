<?php

namespace App\Http\Requests;

use App\Models\Investment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettleInvestmentRequest extends FormRequest
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
        $settlementDateRules = ['required', 'date_format:Y-m-d'];

        if ($investment instanceof Investment) {
            $settlementDateRules[] = 'after_or_equal:'.$investment->received_date->toDateString();
        }

        return [
            'cash_account_id' => [
                'required',
                'integer',
                Rule::exists('cash_accounts', 'id')->where('is_active', true),
            ],
            'profit_paid' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
                'max:9999999999.99',
            ],
            'settlement_date' => $settlementDateRules,
        ];
    }
}
