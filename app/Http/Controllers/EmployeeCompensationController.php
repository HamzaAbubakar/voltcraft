<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeCompensation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeCompensationController extends Controller
{
    public function create(Request $request): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'employee_id' => [
                'nullable',
                'integer',
                Rule::exists('employees', 'id')->where('is_active', true),
            ],
        ]);

        return view('workforce.compensations.form', [
            'compensation' => null,
            'employees' => Employee::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name', 'position', 'base_salary']),
            'selectedMonth' => $filters['month'] ?? now()->format('Y-m'),
            'selectedEmployeeId' => $filters['employee_id'] ?? null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->compensationRules() + [
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where('is_active', true),
                Rule::unique('employee_compensation', 'employee_id')
                    ->where('month', $request->input('month')),
            ],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $employee = Employee::query()->findOrFail($validated['employee_id']);
        $employee->compensations()->create([
            'month' => $validated['month'],
            'base_salary' => $employee->base_salary,
            'overtime_hours' => $validated['overtime_hours'],
            'overtime_amount' => $validated['overtime_amount'],
            'commission_amount' => $validated['commission_amount'],
        ]);

        return redirect()
            ->route('workforce.index', ['month' => $validated['month']])
            ->with('success', 'Monthly compensation recorded.');
    }

    public function edit(EmployeeCompensation $compensation): View
    {
        $compensation->load('employee:id,name,position');

        return view('workforce.compensations.form', [
            'compensation' => $compensation,
            'employees' => collect(),
            'selectedMonth' => $compensation->month,
            'selectedEmployeeId' => $compensation->employee_id,
        ]);
    }

    public function update(Request $request, EmployeeCompensation $compensation): RedirectResponse
    {
        $compensation->update($request->validate($this->compensationRules()));

        return redirect()
            ->route('workforce.index', ['month' => $compensation->month])
            ->with('success', 'Monthly compensation updated.');
    }

    /**
     * Get the validation rules for manually entered compensation amounts.
     *
     * @return array<string, array<int, mixed>|string>
     */
    private function compensationRules(): array
    {
        return [
            'overtime_hours' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:999999.99'],
            'overtime_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999.99'],
            'commission_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999.99'],
        ];
    }
}
