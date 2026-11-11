@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-emerald-400">
                <i class="fas fa-money-bill-transfer"></i>
                <span>CASH POSITION</span>
            </div>
            <h2 class="mt-1 text-xl font-bold text-white">Cash In and Out</h2>
            <p class="mt-0.5 text-xs text-gray-400">Track cash transactions and balances by salesperson</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cash.accounts.create') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-700 bg-gray-900 px-4 py-2 text-xs font-semibold text-gray-200 transition-colors hover:bg-gray-800">
                <i class="fas fa-user-plus mr-2"></i>Add Account
            </a>
            <a href="{{ route('cash.transactions.create') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 shadow-lg shadow-amber-500/10 transition-colors hover:bg-amber-400">
                <i class="fas fa-plus mr-2"></i>Record Transaction
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div role="status" class="rounded-lg border border-amber-800 bg-amber-950/40 px-4 py-3 text-sm text-amber-300">{{ session('warning') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <div class="flex items-center justify-between text-xs text-gray-400"><span>Cash Held by Sales Team</span><i class="fas fa-wallet text-amber-400"></i></div>
            <p class="mt-2 text-2xl font-bold text-white">Rs {{ number_format((float) $cashHeldTotal, 2) }}</p>
            <p class="mt-1 text-[11px] text-gray-500">Cash in less cash out across all accounts</p>
        </div>
        <div class="rounded-xl border border-emerald-900/50 bg-emerald-950/20 p-4">
            <div class="flex items-center justify-between text-xs text-gray-400"><span>Total Cash In</span><i class="fas fa-arrow-down-to-line text-emerald-400"></i></div>
            <p class="mt-2 text-2xl font-bold text-emerald-300">Rs {{ number_format((float) $cashInTotal, 2) }}</p>
            <p class="mt-1 text-[11px] text-gray-500">Across all recorded accounts</p>
        </div>
        <div class="rounded-xl border border-red-900/50 bg-red-950/20 p-4">
            <div class="flex items-center justify-between text-xs text-gray-400"><span>Total Cash Out</span><i class="fas fa-arrow-up-from-line text-red-400"></i></div>
            <p class="mt-2 text-2xl font-bold text-red-300">Rs {{ number_format((float) $cashOutTotal, 2) }}</p>
            <p class="mt-1 text-[11px] text-gray-500">Across all recorded accounts</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="flex flex-col gap-3 border-b border-gray-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-white">Salesperson Accounts</h3>
                <p class="mt-0.5 text-xs text-gray-500">Balances are calculated from each account’s transaction history.</p>
            </div>
            <a href="{{ route('cash.accounts.create') }}" class="text-xs font-semibold text-amber-400 hover:text-amber-300"><i class="fas fa-plus mr-1"></i>Add account</a>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[700px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500">
                        <th class="px-5 py-3 font-semibold">Salesperson</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 text-right font-semibold">Cash In</th>
                        <th class="px-4 py-3 text-right font-semibold">Cash Out</th>
                        <th class="px-4 py-3 text-right font-semibold">Balance</th>
                        <th class="px-5 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    @forelse ($cashAccounts as $account)
                        @php($accountBalance = (float) $account->cash_in_total - (float) $account->cash_out_total)
                        <tr class="hover:bg-gray-800/30">
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-gray-200">{{ $account->name }}</p>
                                <p class="mt-0.5 text-[10px] text-gray-500">{{ $account->role ?: 'Sales account' }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $account->is_active ? 'bg-emerald-500/10 text-emerald-300' : 'bg-gray-700/50 text-gray-400' }}">
                                    {{ $account->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-emerald-400">Rs {{ number_format((float) $account->cash_in_total, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-red-400">Rs {{ number_format((float) $account->cash_out_total, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono font-semibold {{ $accountBalance >= 0 ? 'text-gray-200' : 'text-red-300' }}">Rs {{ number_format($accountBalance, 2) }}</td>
                            <td class="px-5 py-3.5 text-right"><a href="{{ route('cash.accounts.edit', $account) }}" class="font-semibold text-amber-300 hover:text-amber-200">Edit</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center">
                                <p class="text-sm font-semibold text-gray-300">No cash accounts yet</p>
                                <p class="mt-1 text-xs text-gray-500">Add a salesperson account to start recording cash.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($cashAccounts->isNotEmpty())
                    <tfoot>
                        <tr class="border-t border-gray-800 bg-gray-950/40 font-semibold">
                            <td colspan="2" class="px-5 py-3 text-gray-300">Combined totals</td>
                            <td class="px-4 py-3 text-right font-mono text-emerald-400">Rs {{ number_format((float) $cashInTotal, 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-red-400">Rs {{ number_format((float) $cashOutTotal, 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-white">Rs {{ number_format((float) $cashHeldTotal, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="flex flex-col gap-3 border-b border-gray-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-white">Cash Transactions</h3>
                <p class="mt-0.5 text-xs text-gray-500">Record receipts and deductions; edit an entry to correct it.</p>
            </div>
            <form method="GET" action="{{ route('cash.index') }}" class="flex gap-2">
                <label class="flex min-w-0 items-center gap-2 rounded-lg border border-gray-800 bg-gray-950 px-3 text-gray-500">
                    <i class="fas fa-search text-xs"></i>
                    <input type="search" name="search" value="{{ $search }}" aria-label="Search transactions" placeholder="Search transactions..." class="w-full bg-transparent py-2 text-xs text-gray-200 outline-none placeholder:text-gray-500 sm:w-48">
                </label>
                <button type="submit" class="rounded-lg border border-gray-700 bg-gray-900 px-3 text-xs text-gray-200 transition-colors hover:bg-gray-800">Search</button>
                @if ($search !== '')
                    <a href="{{ route('cash.index') }}" class="rounded-lg border border-gray-800 bg-gray-900 px-3 py-2 text-xs text-gray-400 hover:text-white">Clear</a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[850px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500">
                        <th class="px-5 py-3 font-semibold">Salesperson</th>
                        <th class="px-4 py-3 font-semibold">Description</th>
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Direction</th>
                        <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        <th class="px-4 py-3 font-semibold">Recorded By</th>
                        <th class="px-5 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    @forelse ($transactions as $transaction)
                        <tr class="hover:bg-gray-800/30">
                            <td class="px-5 py-3.5 font-semibold text-gray-200">{{ $transaction->cashAccount->name }}</td>
                            <td class="px-4 py-3.5">
                                <p class="text-gray-300">{{ $transaction->description }}</p>
                                @if ($transaction->note)
                                    <p class="mt-0.5 text-[10px] text-gray-500">{{ $transaction->note }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-gray-400">{{ $transaction->transaction_date->format('M j, Y') }}</td>
                            <td class="px-4 py-3.5">
                                <span class="rounded border px-2 py-1 text-[10px] {{ $transaction->type === 'cash_in' ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400' : 'border-red-500/20 bg-red-500/10 text-red-400' }}">
                                    {{ $transaction->type === 'cash_in' ? 'Cash in' : 'Cash out' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-semibold {{ $transaction->type === 'cash_in' ? 'text-emerald-400' : 'text-red-400' }}">Rs {{ number_format((float) $transaction->amount, 2) }}</td>
                            <td class="px-4 py-3.5 text-gray-400">{{ $transaction->recordedBy?->name ?? 'Unknown user' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                @if ($transaction->expense)
                                    <a href="{{ route('expenses.edit', $transaction->expense) }}" class="font-semibold text-amber-300 hover:text-amber-200">Edit expense</a>
                                @elseif ($transaction->items_count > 0)
                                    <span class="text-gray-500">Stock tracked</span>
                                @else
                                    <a href="{{ route('cash.transactions.edit', $transaction) }}" class="font-semibold text-amber-300 hover:text-amber-200">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center">
                                <p class="text-sm font-semibold text-gray-300">{{ $search !== '' ? 'No matching transactions' : 'No transactions recorded yet' }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $search !== '' ? 'Try another search.' : 'Record a transaction to see it in this ledger.' }}</p>
                                @if ($search === '' && $cashAccounts->contains('is_active', true))
                                    <a href="{{ route('cash.transactions.create') }}" class="mt-4 inline-flex rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 hover:bg-amber-400">Record Transaction</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transactions->hasPages() || $transactions->total() > 0)
            <div class="flex flex-col gap-2 border-t border-gray-800 px-5 py-3 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between">
                <span>Showing <span class="font-medium text-gray-300">{{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }}</span> of <span class="font-medium text-gray-300">{{ number_format($transactions->total()) }}</span> transactions</span>
                <div>{{ $transactions->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
