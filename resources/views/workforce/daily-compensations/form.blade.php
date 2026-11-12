@extends('layouts.dashboard')

@section('content')
@php($isEditing = $dailyCompensation !== null)
<div class="mx-auto max-w-2xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <h2 class="text-xl font-bold text-white">{{ $isEditing ? 'Edit daily pay' : 'Add daily overtime & commission' }}</h2>
        <p class="mt-1 text-sm text-gray-400">Enter overtime hours and commission for one employee on one date. Overtime pay uses their hourly rate.</p>
    </div>

    @if (! $isEditing && $employees->isEmpty())
        <div class="rounded-lg border border-amber-800 bg-amber-950/30 px-4 py-3 text-sm text-amber-200">
            Add an active employee before recording daily pay.
            <a href="{{ route('workforce.employees.create') }}" class="ml-1 font-semibold underline">Add employee</a>
        </div>
    @endif

    <form method="POST" action="{{ $isEditing ? route('workforce.daily-compensations.update', $dailyCompensation) : route('workforce.daily-compensations.store') }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        @if ($isEditing)
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold text-gray-400">Employee</p>
                    <p class="mt-1 text-sm text-white">{{ $dailyCompensation->employee->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">Work date</p>
                    <p class="mt-1 text-sm text-white">{{ $dailyCompensation->work_date->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="rounded-lg border border-gray-800 bg-gray-950/60 px-3 py-2.5 text-xs text-gray-400">
                Saved overtime rate: <span class="font-mono text-amber-300">Rs {{ number_format((float) $dailyCompensation->overtime_hourly_rate, 2) }} per hour</span>
            </div>
        @else
            <div>
                <label for="employee_id" class="mb-1 block text-xs font-semibold text-gray-300">Employee</label>
                <select id="employee_id" name="employee_id" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white" @disabled($employees->isEmpty())>
                    <option value="" data-rate="0">Choose an employee</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" data-rate="{{ $employee->overtime_hourly_rate }}" @selected((string) old('employee_id', $selectedEmployeeId) === (string) $employee->id)>
                            {{ $employee->name }}{{ $employee->position ? ' · '.$employee->position : '' }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-gray-500">Overtime rate: <span id="overtime-rate-preview" class="font-mono text-amber-300">Rs 0.00 per hour</span></p>
            </div>

            <div>
                <label for="work_date" class="mb-1 block text-xs font-semibold text-gray-300">Work date</label>
                <input id="work_date" name="work_date" type="date" value="{{ old('work_date', $selectedDate) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white color-scheme-dark">
                @error('work_date')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        @endif

        <div>
            <label for="overtime_hours" class="mb-1 block text-xs font-semibold text-gray-300">Overtime hours</label>
            <input id="overtime_hours" name="overtime_hours" type="number" min="0" max="999999.99" step="0.01" value="{{ old('overtime_hours', $dailyCompensation?->overtime_hours ?? '0.00') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white">
            @error('overtime_hours')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="commission_amount" class="mb-1 block text-xs font-semibold text-gray-300">Commission earned that day (Rs)</label>
            <input id="commission_amount" name="commission_amount" type="number" min="0" max="9999999999.99" step="0.01" value="{{ old('commission_amount', $dailyCompensation?->commission_amount ?? '0.00') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white">
            @error('commission_amount')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <p class="rounded-lg border border-gray-800 bg-gray-950/60 px-3 py-2.5 text-xs text-gray-400">
            Overtime pay is calculated automatically as hours × hourly rate. The rate is saved with the daily entry, so future rate changes do not alter past entries.
        </p>

        <div class="flex flex-wrap justify-end gap-2 border-t border-gray-800 pt-4">
            <a href="{{ route('workforce.index', ['month' => substr($selectedDate, 0, 7)]) }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 transition hover:bg-gray-800">Cancel</a>
            <button type="submit" @disabled(! $isEditing && $employees->isEmpty()) class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50">{{ $isEditing ? 'Save changes' : 'Save daily pay' }}</button>
        </div>
    </form>
</div>
@if (! $isEditing)
    @push('scripts')
        <script>
            const employeeSelect = document.getElementById('employee_id');
            const overtimeRatePreview = document.getElementById('overtime-rate-preview');

            function updateOvertimeRatePreview() {
                const rate = Number(employeeSelect.selectedOptions[0]?.dataset.rate ?? 0);
                overtimeRatePreview.textContent = `Rs ${rate.toFixed(2)} per hour`;
            }

            employeeSelect.addEventListener('change', updateOvertimeRatePreview);
            updateOvertimeRatePreview();
        </script>
    @endpush
@endif
@endsection
