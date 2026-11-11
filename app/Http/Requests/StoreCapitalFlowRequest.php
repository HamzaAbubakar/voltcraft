<?php

namespace App\Http\Requests;

use App\Models\CashTransactionItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCapitalFlowRequest extends FormRequest
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
        return [
            'cash_account_id' => [
                'required',
                'integer',
                Rule::exists('cash_accounts', 'id')->where('is_active', true),
            ],
            'type' => ['required', Rule::in(['cash_in', 'cash_out'])],
            'amount' => ['required', 'numeric', 'min:0.01', 'decimal:0,2', 'max:9999999999.99'],
            'transaction_date' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:1000'],
            'battery_items' => [
                'exclude_unless:type,cash_in',
                'required',
                'array',
                'min:1',
                'max:20',
            ],
            'battery_items.*.name' => [
                'required',
                'string',
                Rule::in(CashTransactionItem::BATTERY_OPTIONS),
            ],
            'battery_items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:999999',
            ],
            'inventory_items' => [
                'exclude_unless:type,cash_out',
                'required',
                'array',
                'min:1',
                'max:50',
            ],
            'inventory_items.*.inventory_item_id' => [
                'required',
                'integer',
                Rule::exists('inventory_items', 'id')->whereNull('deleted_at'),
            ],
            'inventory_items.*.quantity' => [
                'required',
                'numeric',
                'min:0.001',
                'decimal:0,3',
                'max:999999999.999',
            ],
        ];
    }

    /**
     * Get custom validation messages for item selection.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'battery_items.min' => 'Select at least one battery model.',
            'inventory_items.min' => 'Select at least one inventory item.',
        ];
    }
}
