@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <h2 class="text-xl font-bold text-white">{{ $employee ? 'Edit employee' : 'Add employee' }}</h2>
        <p class="mt-1 text-sm text-gray-400">Set employee details, base salary, and overtime rate.</p>
    </div>

    <form method="POST" action="{{ $employee ? route('workforce.employees.update', $employee) : route('workforce.employees.store') }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5">
        @csrf
        @if ($employee)
            @method('PUT')
        @endif

        <div>
            <label for="name" class="mb-1 block text-xs font-semibold text-gray-300">Employee name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $employee?->name) }}" required maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white">
            @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="position" class="mb-1 block text-xs font-semibold text-gray-300">Position <span class="font-normal text-gray-500">(optional)</span></label>
            <input id="position" name="position" type="text" value="{{ old('position', $employee?->position) }}" maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white">
            @error('position')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="base_salary" class="mb-1 block text-xs font-semibold text-gray-300">Current base salary (Rs)</label>
            <input id="base_salary" name="base_salary" type="number" min="0.01" max="9999999999.99" step="0.01" value="{{ old('base_salary', $employee?->base_salary) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white">
            <p class="mt-1 text-xs text-gray-500">A salary update only affects future monthly pay entries.</p>
            @error('base_salary')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="overtime_hourly_rate" class="mb-1 block text-xs font-semibold text-gray-300">Overtime rate per hour (Rs)</label>
            <input id="overtime_hourly_rate" name="overtime_hourly_rate" type="number" min="0" max="9999999999.99" step="0.01" value="{{ old('overtime_hourly_rate', $employee?->overtime_hourly_rate ?? '0.00') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white">
            <p class="mt-1 text-xs text-gray-500">Daily overtime pay is calculated using the rate saved when that day is recorded.</p>
            @error('overtime_hourly_rate')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="is_active" class="mb-1 block text-xs font-semibold text-gray-300">Status</label>
            <select id="is_active" name="is_active" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white">
                <option value="1" @selected(old('is_active', $employee?->is_active ?? true))>Active</option>
                <option value="0" @selected((string) old('is_active', $employee?->is_active ?? true) === '0')>Inactive</option>
            </select>
            @error('is_active')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-wrap justify-end gap-2 border-t border-gray-800 pt-4">
            <a href="{{ route('workforce.employees.index') }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 transition hover:bg-gray-800">Cancel</a>
            <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 transition hover:bg-amber-400">{{ $employee ? 'Save changes' : 'Add employee' }}</button>
        </div>
    </form>
</div>
@endsection
