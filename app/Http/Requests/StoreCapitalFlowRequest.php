<?php

namespace App\Http\Requests;

use App\Models\BatteryModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'battery_items.*.battery_model_id' => [
                'required',
                'integer',
                Rule::exists('battery_models', 'id'),
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
     * Ensure selected battery models have at least one configured component.
     *
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('type') !== 'cash_in' || $validator->errors()->has('battery_items')) {
                    return;
                }

                foreach ($this->input('battery_items', []) as $index => $item) {
                    $field = "battery_items.{$index}.battery_model_id";

                    if ($validator->errors()->has($field) || ! is_array($item) || ! isset($item['battery_model_id'])) {
                        continue;
                    }

                    $hasComponents = BatteryModel::query()
                        ->whereKey($item['battery_model_id'])
                        ->whereHas('inventoryItems')
                        ->exists();

                    if (! $hasComponents) {
                        $validator->errors()->add($field, 'Map at least one inventory item to this battery model before recording a sale.');
                    }
                }
            },
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
