<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'site_location' => ['nullable', 'string', 'max:255'],
            'system_size_watts' => ['required', 'integer', 'min:1', 'max:100000000'],
            'customer_rate_per_watt' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'outsourced_rate_per_watt' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'assigned_employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')],
            'status' => ['required', Rule::in(['active', 'completed', 'cancelled'])],
            'start_date' => ['required', 'date'],
            'completed_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
