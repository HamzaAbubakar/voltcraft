<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeDailyCompensation;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeDailyCompensationController extends Controller
{
    public function create(Request $request): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'work_date' => ['nullable', 'date_format:Y-m-d'],
            'employee_id' => [
                'nullable',
                'integer',
                Rule::exists('employees', 'id')->where('is_active', true),
            ],
        ]);
        $selectedMonth = $filters['month'] ?? now()->format('Y-m');
        $selectedDate = $filters['work_date'] ?? $selectedMonth.'-'.(
            $selectedMonth === now()->format('Y-m') ? now()->format('d') : '01'
        );

        return view('workforce.daily-compensations.form', [
            'dailyCompensation' => null,
            'employees' => Employee::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name', 'position', 'overtime_hourly_rate']),
            'selectedDate' => $selectedDate,
            'selectedEmployeeId' => $filters['employee_id'] ?? null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where('is_active', true),
                Rule::unique('employee_daily_compensation', 'employee_id')
                    ->where(fn (Builder $query) => $query->whereDate('work_date', $request->input('work_date'))),
            ],
            'work_date' => ['required', 'date_format:Y-m-d'],
            'overtime_hours' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:999999.99'],
            'commission_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999.99'],
        ]);

        $employee = Employee::query()->findOrFail($validated['employee_id']);
        $workDate = $validated['work_date'];
        $month = substr($workDate, 0, 7);
        $overtimeRate = (float) $employee->overtime_hourly_rate;
        $overtimeHours = (float) $validated['overtime_hours'];

        DB::transaction(function () use ($employee, $month, $workDate, $validated, $overtimeRate, $overtimeHours): void {
            EmployeeCompensation::query()->firstOrCreate(
                ['employee_id' => $employee->id, 'month' => $month],
                ['base_salary' => $employee->base_salary],
            );

            $employee->dailyCompensations()->create([
                'work_date' => $workDate,
                'overtime_hours' => $validated['overtime_hours'],
                'overtime_hourly_rate' => $overtimeRate,
                'overtime_amount' => round($overtimeHours * $overtimeRate, 2),
                'commission_amount' => $validated['commission_amount'],
            ]);
        });

        return redirect()
            ->route('workforce.index', ['month' => $month])
            ->with('success', 'Daily overtime and commission recorded.');
    }

    public function edit(EmployeeDailyCompensation $dailyCompensation): View
    {
        $dailyCompensation->load('employee:id,name,position');

        return view('workforce.daily-compensations.form', [
            'dailyCompensation' => $dailyCompensation,
            'employees' => collect(),
            'selectedDate' => $dailyCompensation->work_date->toDateString(),
            'selectedEmployeeId' => $dailyCompensation->employee_id,
        ]);
    }

    public function update(Request $request, EmployeeDailyCompensation $dailyCompensation): RedirectResponse
    {
        $validated = $request->validate([
            'overtime_hours' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:999999.99'],
            'commission_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999.99'],
        ]);
        $overtimeHours = (float) $validated['overtime_hours'];
        $overtimeRate = (float) $dailyCompensation->overtime_hourly_rate;

        $dailyCompensation->update([
            ...$validated,
            'overtime_amount' => round($overtimeHours * $overtimeRate, 2),
        ]);

        return redirect()
            ->route('workforce.index', ['month' => $dailyCompensation->work_date->format('Y-m')])
            ->with('success', 'Daily overtime and commission updated.');
    }
}
