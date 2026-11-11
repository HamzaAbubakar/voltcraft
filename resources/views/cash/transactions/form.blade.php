@extends('layouts.dashboard')

@section('content')
@php($isEditing = $cashTransaction !== null)
<div class="mx-auto max-w-2xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <a href="{{ route('cash.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to Cash In and Out</a>
        <h2 class="mt-2 text-xl font-bold text-white">{{ $isEditing ? 'Edit Cash Transaction' : 'Record Cash Transaction' }}</h2>
        <p class="mt-1 text-xs text-gray-400">Record a cash receipt or deduction against a salesperson account.</p>
    </div>

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
            <p class="mt-1 text-xs text-gray-500">A transaction must belong to a salesperson account.</p>
            <a href="{{ route('cash.accounts.create') }}" class="mt-4 inline-flex rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 hover:bg-amber-400">Add Account</a>
        </div>
    @else
        <form method="POST" action="{{ $isEditing ? route('cash.transactions.update', $cashTransaction) : route('cash.transactions.store') }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5 sm:p-7">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="space-y-2">
                <label for="cash_account_id" class="block text-xs font-semibold text-gray-300">Salesperson account</label>
                <select id="cash_account_id" name="cash_account_id" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    <option value="">Select an account</option>
                    @foreach ($cashAccounts as $account)
                        <option value="{{ $account->id }}" @selected((string) old('cash_account_id', $cashTransaction?->cash_account_id) === (string) $account->id)>
                            {{ $account->name }}{{ ! $account->is_active ? ' (Inactive)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('cash_account_id')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="type" class="block text-xs font-semibold text-gray-300">Direction</label>
                    <select id="type" name="type" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                        <option value="cash_in" @selected(old('type', $cashTransaction?->type ?? 'cash_in') === 'cash_in')>Cash in</option>
                        <option value="cash_out" @selected(old('type', $cashTransaction?->type) === 'cash_out')>Cash out</option>
                    </select>
                    @error('type')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <label for="amount" class="block text-xs font-semibold text-gray-300">Amount (Rs)</label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" max="9999999999.99" value="{{ old('amount', $cashTransaction?->amount) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    @error('amount')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="space-y-2">
                <label for="description" class="block text-xs font-semibold text-gray-300">Description</label>
                <input id="description" name="description" value="{{ old('description', $cashTransaction?->description) }}" required maxlength="255" placeholder="e.g. Customer payment" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none placeholder:text-gray-600 focus:border-amber-500">
                @error('description')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label for="transaction_date" class="block text-xs font-semibold text-gray-300">Transaction date</label>
                <input id="transaction_date" name="transaction_date" type="date" value="{{ old('transaction_date', $cashTransaction?->transaction_date?->format('Y-m-d') ?? now()->toDateString()) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('transaction_date')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label for="note" class="block text-xs font-semibold text-gray-300">Note <span class="font-normal text-gray-500">(optional)</span></label>
                <textarea id="note" name="note" rows="3" maxlength="1000" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">{{ old('note', $cashTransaction?->note) }}</textarea>
                @error('note')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-800 pt-5">
                <a href="{{ route('cash.index') }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 hover:bg-gray-800">Cancel</a>
                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 hover:bg-amber-400">{{ $isEditing ? 'Save Changes' : 'Record Transaction' }}</button>
            </div>
        </form>
    @endif
</div>
@endsection
