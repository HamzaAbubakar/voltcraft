<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeDailyCompensation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WorkforceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $selectedMonth = $filters['month'] ?? now()->format('Y-m');
        $monthStart = $selectedMonth.'-01';
        $monthEnd = Carbon::parse($monthStart)->endOfMonth()->endOfDay()->toDateTimeString();

        $employees = Employee::query()
            ->where(function ($query) use ($selectedMonth, $monthStart, $monthEnd): void {
                $query->where('is_active', true)
                    ->orWhereHas('compensations', fn ($query) => $query->where('month', $selectedMonth))
                    ->orWhereHas('dailyCompensations', fn ($query) => $query->whereBetween('work_date', [$monthStart, $monthEnd]));
            })
            ->with(['compensations' => fn ($query) => $query->where('month', $selectedMonth)])
            ->with(['dailyCompensations' => fn ($query) => $query
                ->whereBetween('work_date', [$monthStart, $monthEnd])
                ->orderByDesc('work_date')
                ->orderByDesc('id')])
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        $monthlyTotals = EmployeeCompensation::query()
            ->where('month', $selectedMonth)
            ->selectRaw(
                'COALESCE(SUM(base_salary), 0) as base_salary, '.
                'COALESCE(SUM(overtime_amount), 0) as overtime_amount, '.
                'COALESCE(SUM(commission_amount), 0) as commission_amount, '.
                'COALESCE(SUM(base_salary + overtime_amount + commission_amount), 0) as gross_pay'
            )
            ->first();
        $dailyTotals = EmployeeDailyCompensation::query()
            ->whereBetween('work_date', [$monthStart, $monthEnd])
            ->selectRaw(
                'COALESCE(SUM(overtime_hours), 0) as overtime_hours, '.
                'COALESCE(SUM(overtime_amount), 0) as overtime_amount, '.
                'COALESCE(SUM(commission_amount), 0) as commission_amount'
            )
            ->first();
        $dailyCompensations = EmployeeDailyCompensation::query()
            ->with('employee:id,name')
            ->whereBetween('work_date', [$monthStart, $monthEnd])
            ->orderByDesc('work_date')
            ->orderBy('employee_id')
            ->orderBy('id')
            ->get();

        return view('workforce.index', [
            'employees' => $employees,
            'dailyCompensations' => $dailyCompensations,
            'selectedMonth' => $selectedMonth,
            'totals' => [
                'base_salary' => (float) $monthlyTotals->base_salary,
                'overtime_amount' => (float) $monthlyTotals->overtime_amount + (float) $dailyTotals->overtime_amount,
                'commission_amount' => (float) $monthlyTotals->commission_amount + (float) $dailyTotals->commission_amount,
                'gross_pay' => (float) $monthlyTotals->gross_pay + (float) $dailyTotals->overtime_amount + (float) $dailyTotals->commission_amount,
            ],
        ]);
    }
}
