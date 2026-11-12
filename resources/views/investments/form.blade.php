@extends('layouts.dashboard')

@section('content')
@php($isEditing = $investment !== null)
<div class="mx-auto max-w-2xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <a href="{{ route('investments.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to Investments</a>
        <h2 class="mt-2 text-xl font-bold text-white">{{ $isEditing ? 'Edit Investment' : 'Record Investment' }}</h2>
        <p class="mt-1 text-xs text-gray-400">Track the principal, agreed profit-share percentage, and repayment date.</p>
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
            <p class="mt-1 text-xs text-gray-500">The investment principal must be linked to a cash account.</p>
            <a href="{{ route('cash.accounts.create') }}" class="mt-4 inline-flex rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 hover:bg-amber-400">Add Account</a>
        </div>
    @else
        <form method="POST" action="{{ $isEditing ? route('investments.update', $investment) : route('investments.store') }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5 sm:p-7">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="investor_name" class="block text-xs font-semibold text-gray-300">Investor name</label>
                    <input id="investor_name" name="investor_name" value="{{ old('investor_name', $investment?->investor_name) }}" required maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    @error('investor_name')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <label for="contact_info" class="block text-xs font-semibold text-gray-300">Contact information <span class="font-normal text-gray-500">Optional</span></label>
                    <input id="contact_info" name="contact_info" value="{{ old('contact_info', $investment?->contact_info) }}" maxlength="255" placeholder="Phone or email" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none placeholder:text-gray-600 focus:border-amber-500">
                    @error('contact_info')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="principal_amount" class="block text-xs font-semibold text-gray-300">Principal amount (Rs)</label>
                    <input id="principal_amount" name="principal_amount" type="number" step="0.01" min="0.01" max="9999999999.99" value="{{ old('principal_amount', $investment?->principal_amount) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    @error('principal_amount')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <label for="cash_account_id" class="block text-xs font-semibold text-gray-300">Cash account</label>
                    <select id="cash_account_id" name="cash_account_id" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                        <option value="">Select an account</option>
                        @foreach ($cashAccounts as $account)
                            <option value="{{ $account->id }}" @selected((string) old('cash_account_id', $investment?->initialCashTransaction?->cash_account_id) === (string) $account->id)>
                                {{ $account->name }}{{ ! $account->is_active ? ' (Inactive)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('cash_account_id')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div class="space-y-2">
                    <label for="received_date" class="block text-xs font-semibold text-gray-300">Date received</label>
                    <input id="received_date" name="received_date" type="date" value="{{ old('received_date', $investment?->received_date?->format('Y-m-d') ?? now()->toDateString()) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500 color-scheme-dark">
                    @error('received_date')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <label for="maturity_date" class="block text-xs font-semibold text-gray-300">Return due date</label>
                    <input id="maturity_date" name="maturity_date" type="date" value="{{ old('maturity_date', $investment?->maturity_date?->format('Y-m-d') ?? now()->addDays(30)->toDateString()) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500 color-scheme-dark">
                    @error('maturity_date')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <label for="profit_share_percentage" class="block text-xs font-semibold text-gray-300">Profit share (%)</label>
                    <input id="profit_share_percentage" name="profit_share_percentage" type="number" step="0.01" min="0" max="100" value="{{ old('profit_share_percentage', $investment?->profit_share_percentage) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    @error('profit_share_percentage')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="space-y-2">
                <label for="notes" class="block text-xs font-semibold text-gray-300">Notes <span class="font-normal text-gray-500">Optional</span></label>
                <textarea id="notes" name="notes" rows="3" maxlength="5000" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">{{ old('notes', $investment?->notes) }}</textarea>
                @error('notes')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <p class="text-xs text-gray-500">The agreed percentage is stored for reference. Enter the actual profit paid when you settle the investment.</p>

            <div class="flex justify-end gap-2 border-t border-gray-800 pt-5">
                <a href="{{ route('investments.index') }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 hover:bg-gray-800">Cancel</a>
                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 hover:bg-amber-400">{{ $isEditing ? 'Save Changes' : 'Record Investment' }}</button>
            </div>
        </form>
    @endif
</div>
@endsection
