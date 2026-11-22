@extends('layouts.dashboard')

@section('content')
@php
    $isEditing = $project !== null;

    // Determine display unit and value for edit mode
    $systemSizeWatts = old('system_size_watts', $project?->system_size_watts);
    $systemSizeUnit = old('system_size_unit');
    $displayValue = $systemSizeWatts;

    if ($isEditing && $systemSizeWatts && !$systemSizeUnit) {
        // Auto-detect best unit for display
        if ($systemSizeWatts >= 1000000) {
            $systemSizeUnit = 'MW';
            $displayValue = $systemSizeWatts / 1000000;
        } elseif ($systemSizeWatts >= 1000) {
            $systemSizeUnit = 'KW';
            $displayValue = $systemSizeWatts / 1000;
        } else {
            $systemSizeUnit = 'W';
            $displayValue = $systemSizeWatts;
        }
    } elseif (!$systemSizeUnit) {
        $systemSizeUnit = 'KW';
        $displayValue = $systemSizeWatts ? $systemSizeWatts / 1000 : null;
    }
@endphp
<div class="mx-auto max-w-3xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <a href="{{ $isEditing ? route('projects.show', $project) : route('projects.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to {{ $isEditing ? 'project' : 'Projects' }}</a>
        <h2 class="mt-2 text-xl font-bold text-white">{{ $isEditing ? 'Edit project' : 'Create project' }}</h2>
        <p class="mt-1 text-xs text-gray-400">Enter system size and per-watt rates to calculate the project margin.</p>
    </div>

    @if ($errors->any())
        <div role="alert" class="rounded-lg border border-red-900 bg-red-950/40 px-4 py-3 text-sm text-red-300">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEditing ? route('projects.update', $project) : route('projects.store') }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5 sm:p-7">
        @csrf
        @if ($isEditing) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2 sm:col-span-2">
                <label for="name" class="block text-xs font-semibold text-gray-300">Project / site name</label>
                <input id="name" name="name" value="{{ old('name', $project?->name) }}" required maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('name')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="customer_name" class="block text-xs font-semibold text-gray-300">Customer</label>
                <input id="customer_name" name="customer_name" value="{{ old('customer_name', $project?->customer_name) }}" maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('customer_name')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="site_location" class="block text-xs font-semibold text-gray-300">Site location</label>
                <input id="site_location" name="site_location" value="{{ old('site_location', $project?->site_location) }}" maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('site_location')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-gray-300">System size</label>
                <div class="flex gap-2">
                    <input
                        id="system_size_watts"
                        name="system_size_watts"
                        type="number"
                        min="0.001"
                        step="any"
                        value="{{ old('system_size_watts', $displayValue) }}"
                        required
                        class="flex-1 rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500"
                        @if($systemSizeUnit === 'W') min="1" step="1" @elseif($systemSizeUnit === 'KW') step="0.001" @elseif($systemSizeUnit === 'MW') step="0.000001" @endif
                    >
                    <select
                        id="system_size_unit"
                        name="system_size_unit"
                        class="shrink-0 w-24 rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500"
                    >
                        <option value="W" @selected(old('system_size_unit', $systemSizeUnit) === 'W')>W</option>
                        <option value="KW" @selected(old('system_size_unit', $systemSizeUnit) === 'KW')>KW</option>
                        <option value="MW" @selected(old('system_size_unit', $systemSizeUnit) === 'MW')>MW</option>
                    </select>
                </div>
                @error('system_size_watts')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                <p class="text-[10px] text-gray-500">Stored as watts in database</p>
            </div>
            <div class="space-y-2">
                <label for="assigned_employee_id" class="block text-xs font-semibold text-gray-300">Lead team member</label>
                <select id="assigned_employee_id" name="assigned_employee_id" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    <option value="">Not assigned</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) old('assigned_employee_id', $project?->assigned_employee_id) === (string) $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
                @error('assigned_employee_id')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="customer_rate_per_watt" class="block text-xs font-semibold text-gray-300">Customer rate (Rs / watt)</label>
                <input id="customer_rate_per_watt" name="customer_rate_per_watt" type="number" min="0.01" step="0.01" value="{{ old('customer_rate_per_watt', $project?->customer_rate_per_watt) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('customer_rate_per_watt')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="outsourced_rate_per_watt" class="block text-xs font-semibold text-gray-300">Outsourced rate (Rs / watt)</label>
                <input id="outsourced_rate_per_watt" name="outsourced_rate_per_watt" type="number" min="0.01" step="0.01" value="{{ old('outsourced_rate_per_watt', $project?->outsourced_rate_per_watt) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('outsourced_rate_per_watt')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="status" class="block text-xs font-semibold text-gray-300">Status</label>
                <select id="status" name="status" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    @foreach (['active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $project?->status ?? 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="start_date" class="block text-xs font-semibold text-gray-300">Start date</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $project?->start_date?->format('Y-m-d') ?? now()->toDateString()) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('start_date')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="completed_date" class="block text-xs font-semibold text-gray-300">Completed date (optional)</label>
                <input id="completed_date" name="completed_date" type="date" value="{{ old('completed_date', $project?->completed_date?->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('completed_date')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-800 pt-5">
            <a href="{{ $isEditing ? route('projects.show', $project) : route('projects.index') }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 hover:bg-gray-800">Cancel</a>
            <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 hover:bg-amber-400">{{ $isEditing ? 'Save changes' : 'Create project' }}</button>
        </div>
    </form>
</div>
@endsection
