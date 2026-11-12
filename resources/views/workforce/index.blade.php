@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Workforce & HR</h2>
            <p class="mt-1 text-sm text-gray-400">Track employee salaries, overtime, and commissions by month.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('workforce.employees.index') }}" class="rounded-lg border border-gray-700 px-4 py-2 text-xs font-semibold text-gray-200 transition hover:bg-gray-800">Manage employees</a>
            <a href="{{ route('workforce.daily-compensations.create', ['month' => $selectedMonth]) }}" class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-2 text-xs font-semibold text-amber-300 transition hover:bg-amber-500/20">
                <i class="fas fa-clock mr-2"></i>Add daily pay
            </a>
            <a href="{{ route('workforce.compensations.create', ['month' => $selectedMonth]) }}" class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 transition hover:bg-amber-400">
                <i class="fas fa-plus mr-2"></i>Record monthly pay
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-white">Monthly compensation</h3>
            <p class="mt-1 text-xs text-gray-500">Monthly entries and daily overtime and commission logs for the selected month.</p>
        </div>
        <form method="GET" action="{{ route('workforce.index') }}" class="flex items-center gap-2">
            <label for="workforce-month" class="text-xs font-medium text-gray-400">Month</label>
            <input id="workforce-month" type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()" class="rounded-lg border border-gray-800 bg-gray-900 px-3 py-2 text-xs text-gray-200 color-scheme-dark">
        </form>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Base salaries</p>
            <p class="mt-2 text-xl font-bold text-white">Rs {{ number_format($totals['base_salary'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Overtime</p>
            <p class="mt-2 text-xl font-bold text-amber-400">Rs {{ number_format($totals['overtime_amount'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Commissions</p>
            <p class="mt-2 text-xl font-bold text-emerald-400">Rs {{ number_format($totals['commission_amount'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Total compensation</p>
            <p class="mt-2 text-xl font-bold text-white">Rs {{ number_format($totals['gross_pay'], 2) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="flex items-center justify-between gap-3 border-b border-gray-800 px-5 py-4">
            <div>
                <h3 class="text-sm font-semibold text-white">Employees</h3>
                <p class="mt-1 text-xs text-gray-500">Daily overtime uses the employee's saved hourly rate; daily commission is entered manually.</p>
            </div>
            <span class="text-xs text-gray-500">{{ $employees->count() }} {{ $employees->count() === 1 ? 'employee' : 'employees' }}</span>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[900px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500">
                        <th class="px-5 py-3 font-semibold">Employee</th>
                        <th class="px-4 py-3 font-semibold text-right">Base salary</th>
                        <th class="px-4 py-3 font-semibold text-right">Overtime (month)</th>
                        <th class="px-4 py-3 font-semibold text-right">Commission (month)</th>
                        <th class="px-4 py-3 font-semibold text-right">Total pay</th>
                        <th class="px-5 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    @forelse ($employees as $employee)
                        @php($compensation = $employee->compensations->first())
                        @php($overtimeHours = (float) ($compensation?->overtime_hours ?? 0) + (float) $employee->dailyCompensations->sum('overtime_hours'))
                        @php($overtimeAmount = (float) ($compensation?->overtime_amount ?? 0) + (float) $employee->dailyCompensations->sum('overtime_amount'))
                        @php($commissionAmount = (float) ($compensation?->commission_amount ?? 0) + (float) $employee->dailyCompensations->sum('commission_amount'))
                        @php($baseSalary = (float) ($compensation?->base_salary ?? $employee->base_salary))
                        <tr class="hover:bg-gray-800/30">
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-gray-200">{{ $employee->name }}</p>
                                <p class="mt-1 text-gray-500">{{ $employee->position ?: 'No role listed' }}{{ $employee->is_active ? '' : ' · Inactive' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-gray-200">Rs {{ number_format((float) ($compensation?->base_salary ?? $employee->base_salary), 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono {{ $compensation || $employee->dailyCompensations->isNotEmpty() ? 'text-amber-400' : 'text-gray-500' }}">
                                @if ($compensation || $employee->dailyCompensations->isNotEmpty())
                                    {{ number_format($overtimeHours, 2) }} hrs · Rs {{ number_format($overtimeAmount, 2) }}
                                @else
                                    Not entered
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono {{ $compensation || $employee->dailyCompensations->isNotEmpty() ? 'text-emerald-400' : 'text-gray-500' }}">
                                {{ $compensation || $employee->dailyCompensations->isNotEmpty() ? 'Rs '.number_format($commissionAmount, 2) : 'Not entered' }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-semibold text-white">
                                @if ($compensation || $employee->dailyCompensations->isNotEmpty())
                                    Rs {{ number_format($baseSalary + $overtimeAmount + $commissionAmount, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('workforce.daily-compensations.create', ['month' => $selectedMonth, 'employee_id' => $employee->id]) }}" class="font-semibold text-amber-300 hover:text-amber-200">Add daily pay</a>
                                @if ($compensation)
                                    <span class="px-1 text-gray-600">·</span>
                                    <a href="{{ route('workforce.compensations.edit', $compensation) }}" class="font-semibold text-gray-400 hover:text-gray-200">Edit month</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-xs text-gray-500">No employees found. Add an employee to start tracking compensation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="flex items-center justify-between gap-3 border-b border-gray-800 px-5 py-4">
            <div>
                <h3 class="text-sm font-semibold text-white">Daily overtime & commission</h3>
                <p class="mt-1 text-xs text-gray-500">Overtime amount is calculated from logged hours and the saved hourly rate.</p>
            </div>
            <span class="text-xs text-gray-500">{{ $dailyCompensations->count() }} {{ $dailyCompensations->count() === 1 ? 'entry' : 'entries' }}</span>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[900px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500">
                        <th class="px-5 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Employee</th>
                        <th class="px-4 py-3 text-right font-semibold">Overtime</th>
                        <th class="px-4 py-3 text-right font-semibold">Overtime pay</th>
                        <th class="px-4 py-3 text-right font-semibold">Commission</th>
                        <th class="px-4 py-3 text-right font-semibold">Daily total</th>
                        <th class="px-5 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    @forelse ($dailyCompensations as $dailyCompensation)
                        <tr class="hover:bg-gray-800/30">
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-400">{{ $dailyCompensation->work_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3.5 font-medium text-gray-200">{{ $dailyCompensation->employee->name }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-amber-300">{{ number_format((float) $dailyCompensation->overtime_hours, 2) }} hrs × Rs {{ number_format((float) $dailyCompensation->overtime_hourly_rate, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-amber-300">Rs {{ number_format((float) $dailyCompensation->overtime_amount, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-emerald-300">Rs {{ number_format((float) $dailyCompensation->commission_amount, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono font-semibold text-white">Rs {{ number_format((float) $dailyCompensation->overtime_amount + (float) $dailyCompensation->commission_amount, 2) }}</td>
                            <td class="px-5 py-3.5 text-right"><a href="{{ route('workforce.daily-compensations.edit', $dailyCompensation) }}" class="font-semibold text-amber-300 hover:text-amber-200">Edit</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-xs text-gray-500">No daily overtime or commission recorded for this month.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
