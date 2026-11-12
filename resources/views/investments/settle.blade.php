@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <a href="{{ route('investments.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to Investments</a>
        <h2 class="mt-2 text-xl font-bold text-white">Settle Investment</h2>
        <p class="mt-1 text-xs text-gray-400">Record the principal returned and the actual profit paid to the investor.</p>
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

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Investor</p>
            <p class="mt-2 text-sm font-semibold text-white">{{ $investment->investor_name }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Principal to return</p>
            <p class="mt-2 text-sm font-semibold text-white">Rs {{ number_format((float) $investment->principal_amount, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Agreed profit share</p>
            <p class="mt-2 text-sm font-semibold text-amber-400">{{ number_format((float) $investment->profit_share_percentage, 2) }}%</p>
        </div>
    </div>

    @if ($cashAccounts->isEmpty())
        <div class="rounded-xl border border-gray-800 bg-gray-900 px-5 py-8 text-center">
            <p class="text-sm font-semibold text-gray-300">No active cash accounts</p>
            <p class="mt-1 text-xs text-gray-500">Activate or add a cash account before recording repayment.</p>
            <a href="{{ route('cash.index') }}" class="mt-4 inline-flex rounded-lg border border-gray-700 px-4 py-2 text-xs font-semibold text-gray-200 hover:bg-gray-800">Manage Cash Accounts</a>
        </div>
    @else
        <form method="POST" action="{{ route('investments.settlement.store', $investment) }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5 sm:p-7">
            @csrf

            <div class="space-y-2">
                <label for="cash_account_id" class="block text-xs font-semibold text-gray-300">Pay from cash account</label>
                <select id="cash_account_id" name="cash_account_id" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    <option value="">Select an account</option>
                    @foreach ($cashAccounts as $account)
                        <option value="{{ $account->id }}" @selected((string) old('cash_account_id', $investment->initialCashTransaction->cash_account_id) === (string) $account->id)>{{ $account->name }}</option>
                    @endforeach
                </select>
                @error('cash_account_id')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="profit_paid" class="block text-xs font-semibold text-gray-300">Actual profit paid (Rs)</label>
                    <input id="profit_paid" name="profit_paid" type="number" step="0.01" min="0" max="9999999999.99" value="{{ old('profit_paid', '0.00') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    @error('profit_paid')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <label for="settlement_date" class="block text-xs font-semibold text-gray-300">Date repaid</label>
                    <input id="settlement_date" name="settlement_date" type="date" value="{{ old('settlement_date', now()->toDateString()) }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500 color-scheme-dark">
                    @error('settlement_date')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <p class="text-xs text-gray-500">The cash ledger will record one cash-out for the full principal plus the profit amount entered above.</p>

            <div class="flex justify-end gap-2 border-t border-gray-800 pt-5">
                <a href="{{ route('investments.index') }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 hover:bg-gray-800">Cancel</a>
                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 hover:bg-amber-400">Confirm Settlement</button>
            </div>
        </form>
    @endif
</div>
@endsection
