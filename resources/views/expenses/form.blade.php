@extends('layouts.dashboard')

@section('content')
@php($isEditing = $expense !== null)
<div class="mx-auto max-w-2xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <a href="{{ route('expenses.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to Expenses</a>
        <h2 class="mt-2 text-xl font-bold text-white">{{ $isEditing ? 'Edit Expense' : 'File New Expense Record' }}</h2>
        <p class="mt-1 text-xs text-gray-400">Record a company or salesperson expense and update the cash ledger.</p>
    </div>

    @if (session('warning'))
        <div role="status" class="rounded-lg border border-amber-800 bg-amber-950/40 px-4 py-3 text-sm text-amber-300">{{ session('warning') }}</div>
    @endif

    @if ($errors->any())
        <div role="alert" class="rounded-lg border border-red-900 bg-red-950/40 px-4 py-3 text-sm text-red-300">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($cashAccounts->isEmpty())
        <div class="rounded-xl border border-gray-800 bg-gray-900 px-5 py-12 text-center">
            <p class="text-sm font-semibold text-gray-300">Add a cash account first</p>
            <p class="mt-1 text-xs text-gray-500">An expense payment must be linked to a cash account.</p>
            <a href="{{ route('cash.accounts.create') }}" class="mt-4 inline-flex rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 hover:bg-amber-400">Add Account</a>
        </div>
    @else
        <form method="POST" action="{{ $isEditing ? route('expenses.update', $expense) : route('expenses.store') }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5 sm:p-7">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="space-y-2">
                <label for="description" class="block text-xs font-semibold text-gray-300">Expense</label>
                <input id="description" name="description" list="expense-descriptions" value="{{ old('description', $expense?->description?->name) }}" required maxlength="255" placeholder="Search or enter an expense" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none placeholder:text-gray-600 focus:border-amber-500">
                <datalist id="expense-descriptions">
                    @foreach ($expenseDescriptions as $expenseDescription)
                        <option value="{{ $expenseDescription->name }}"></option>
                    @endforeach
                </datalist>
                <p class="text-[11px] text-gray-500">Choose a saved entry or type a new one. New entries are saved to this dropdown.</p>
                @error('description')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="category" class="block text-xs font-semibold text-gray-300">Category</label>
                    <input id="category" name="category" list="expense-categories" value="{{ old('category', $expense?->category) }}" required maxlength="255" placeholder="e.g. Operations" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none placeholder:text-gray-600 focus:border-amber-500">
                    <datalist id="expense-categories">
                        <option value="Operations"></option>
                        <option value="Equipment"></option>
                        <option value="Personal support"></option>
                        <option value="Facilities"></option>
                    </datalist>
                    @error('category')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <label for="paid_for" class="block text-xs font-semibold text-gray-300">Paid for</label>
                    <input id="paid_for" name="paid_for" list="expense-paid-for-options" value="{{ old('paid_for', $expense?->paid_for ?? 'Company') }}" required maxlength="255" placeholder="Company or person" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none placeholder:text-gray-600 focus:border-amber-500">
                    <datalist id="expense-paid-for-options">
                        <option value="Company"></option>
                        @foreach ($cashAccounts as $account)
                            <option value="{{ $account->name }}"></option>
                        @endforeach
                    </datalist>
                    @error('paid_for')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="cash_account_id" class="block text-xs font-semibold text-gray-300">Paid from cash account</label>
                    <select id="cash_account_id" name="cash_account_id" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                        <option value="">Select an account</option>
                        @foreach ($cashAccounts as $account)
                            <option value="{{ $account->id }}" @selected((string) old('cash_account_id', $expense?->cashTransaction?->cash_account_id) === (string) $account->id)>
                                {{ $account->name }}{{ ! $account->is_active ? ' (Inactive)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('cash_account_id')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <label for="amount" class="block text-xs font-semibold text-gray-300">Amount (Rs)</label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" max="9999999999.99" value="{{ old('amount', $expense?->cashTransaction?->amount) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    @error('amount')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="space-y-2">
                <label for="transaction_date" class="block text-xs font-semibold text-gray-300">Expense date</label>
                <input id="transaction_date" name="transaction_date" type="date" value="{{ old('transaction_date', $expense?->cashTransaction?->transaction_date?->format('Y-m-d') ?? now()->toDateString()) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('transaction_date')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-800 pt-5">
                <a href="{{ route('expenses.index') }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 hover:bg-gray-800">Cancel</a>
                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 hover:bg-amber-400">{{ $isEditing ? 'Save Changes' : 'File Expense' }}</button>
            </div>
        </form>
    @endif
</div>
@endsection
