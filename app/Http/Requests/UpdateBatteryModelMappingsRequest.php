<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBatteryModelMappingsRequest extends FormRequest
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
            'components' => ['required', 'array', 'min:1', 'max:50'],
            'components.*.inventory_item_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('inventory_items', 'id')->whereNull('deleted_at'),
            ],
            'components.*.quantity_per_battery' => [
                'required',
                'numeric',
                'min:0.001',
                'decimal:0,3',
                'max:999999.999',
            ],
        ];
    }
}
