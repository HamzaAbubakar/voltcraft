<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return view('workforce.employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('workforce.employees.form', ['employee' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Employee::create($request->validate($this->employeeRules()));

        return redirect()
            ->route('workforce.employees.index')
            ->with('success', 'Employee added.');
    }

    public function edit(Employee $employee): View
    {
        return view('workforce.employees.form', compact('employee'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validate($this->employeeRules()));

        return redirect()
            ->route('workforce.employees.index')
            ->with('success', 'Employee updated.');
    }

    /**
     * Get the validation rules for employee records.
     *
     * @return array<string, array<int, mixed>|string>
     */
    private function employeeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'base_salary' => ['required', 'numeric', 'min:0.01', 'decimal:0,2', 'max:9999999999.99'],
            'overtime_hourly_rate' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999.99'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
