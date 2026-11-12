@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <a href="{{ route('projects.show', $project) }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to {{ $project->name }}</a>
        <h2 class="mt-2 text-xl font-bold text-white">Add project site expense</h2>
        <p class="mt-1 text-xs text-gray-400">Only used to calculate this project's profit. This will not update the company expense or cash ledger.</p>
    </div>

    @if ($errors->any())
        <div role="alert" class="rounded-lg border border-red-900 bg-red-950/40 px-4 py-3 text-sm text-red-300">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('projects.expenses.store', $project) }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5 sm:p-7">
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <label for="category" class="block text-xs font-semibold text-gray-300">Expense type</label>
                <select id="category" name="category" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm capitalize text-white outline-none focus:border-amber-500">
                    @foreach (['food' => 'Food', 'accommodation' => 'Accommodation / living', 'travel' => 'Travel', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="amount" class="block text-xs font-semibold text-gray-300">Amount (Rs)</label>
                <input id="amount" name="amount" type="number" min="0.01" max="9999999999.99" step="0.01" value="{{ old('amount') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('amount')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="expense_date" class="block text-xs font-semibold text-gray-300">Expense date</label>
                <input id="expense_date" name="expense_date" type="date" value="{{ old('expense_date', now()->toDateString()) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('expense_date')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="employee_id" class="block text-xs font-semibold text-gray-300">For team member (optional)</label>
                <select id="employee_id" name="employee_id" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    <option value="">Not specified</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2 sm:col-span-2">
                <label for="note" class="block text-xs font-semibold text-gray-300">Note (optional)</label>
                <textarea id="note" name="note" rows="3" maxlength="1000" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">{{ old('note') }}</textarea>
                @error('note')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex justify-end gap-2 border-t border-gray-800 pt-5">
            <a href="{{ route('projects.show', $project) }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 hover:bg-gray-800">Cancel</a>
            <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 hover:bg-amber-400">Record site expense</button>
        </div>
    </form>
</div>
@endsection
