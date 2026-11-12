@extends('layouts.dashboard')

@section('content')
@php($isEditing = $compensation !== null)
<div class="mx-auto max-w-2xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <h2 class="text-xl font-bold text-white">{{ $isEditing ? 'Edit monthly pay' : 'Record monthly pay' }}</h2>
        <p class="mt-1 text-sm text-gray-400">Use monthly amounts for pay not entered by day. Daily overtime and commission entries are added separately to the month total.</p>
    </div>

    @if (! $isEditing && $employees->isEmpty())
        <div class="rounded-lg border border-amber-800 bg-amber-950/30 px-4 py-3 text-sm text-amber-200">
            Add an active employee before recording monthly pay.
            <a href="{{ route('workforce.employees.create') }}" class="ml-1 font-semibold underline">Add employee</a>
        </div>
    @endif

    <form method="POST" action="{{ $isEditing ? route('workforce.compensations.update', $compensation) : route('workforce.compensations.store') }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        @if ($isEditing)
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold text-gray-400">Employee</p>
                    <p class="mt-1 text-sm text-white">{{ $compensation->employee->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400">Pay month</p>
                    <p class="mt-1 text-sm text-white">{{ $compensation->month }}</p>
                </div>
            </div>
            <div class="rounded-lg border border-gray-800 bg-gray-950/60 px-3 py-2.5 text-xs text-gray-400">
                Base salary snapshot: <span class="font-mono text-gray-200">Rs {{ number_format((float) $compensation->base_salary, 2) }}</span>
            </div>
        @else
            <div>
                <label for="employee_id" class="mb-1 block text-xs font-semibold text-gray-300">Employee</label>
                <select id="employee_id" name="employee_id" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white" @disabled($employees->isEmpty())>
                    <option value="">Choose an employee</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) old('employee_id', $selectedEmployeeId) === (string) $employee->id)>
                            {{ $employee->name }}{{ $employee->position ? ' · '.$employee->position : '' }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="month" class="mb-1 block text-xs font-semibold text-gray-300">Pay month</label>
                <input id="month" name="month" type="month" value="{{ old('month', $selectedMonth) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white color-scheme-dark">
                @error('month')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="overtime_hours" class="mb-1 block text-xs font-semibold text-gray-300">Overtime hours</label>
                <input id="overtime_hours" name="overtime_hours" type="number" min="0" max="999999.99" step="0.01" value="{{ old('overtime_hours', $compensation?->overtime_hours ?? '0.00') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white">
                @error('overtime_hours')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="overtime_amount" class="mb-1 block text-xs font-semibold text-gray-300">Overtime amount (Rs)</label>
                <input id="overtime_amount" name="overtime_amount" type="number" min="0" max="9999999999.99" step="0.01" value="{{ old('overtime_amount', $compensation?->overtime_amount ?? '0.00') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white">
                @error('overtime_amount')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="commission_amount" class="mb-1 block text-xs font-semibold text-gray-300">Commission amount (Rs)</label>
            <input id="commission_amount" name="commission_amount" type="number" min="0" max="9999999999.99" step="0.01" value="{{ old('commission_amount', $compensation?->commission_amount ?? '0.00') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white">
            @error('commission_amount')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-wrap justify-end gap-2 border-t border-gray-800 pt-4">
            <a href="{{ route('workforce.index', ['month' => $selectedMonth]) }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 transition hover:bg-gray-800">Cancel</a>
            <button type="submit" @disabled(! $isEditing && $employees->isEmpty()) class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50">{{ $isEditing ? 'Save changes' : 'Record pay' }}</button>
        </div>
    </form>
</div>
@endsection
