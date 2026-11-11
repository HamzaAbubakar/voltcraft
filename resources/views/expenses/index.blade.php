@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between gap-4 border-b border-gray-800 pb-2">
        <div>
            <h2 class="text-xl font-bold text-white">Multi-Channel Expense Ledger</h2>
            <p class="mt-0.5 text-xs text-gray-400">Track company and salesperson expenses by day or month</p>
        </div>
        <a href="{{ route('expenses.create') }}" class="shrink-0 rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 shadow-lg shadow-amber-500/10 transition-all hover:bg-amber-400">
            <i class="fas fa-plus mr-2"></i>File New Expense Record
        </a>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-white">Expense Activity</h3>
                <p class="mt-0.5 text-xs text-gray-500">Choose a date or month to review recorded spending</p>
            </div>
            <form id="expense-period-form" method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-center gap-2">
                <input id="expense-period" type="hidden" name="period" value="{{ $period }}">
                <div class="inline-flex rounded-lg border border-gray-800 bg-gray-900 p-1" role="group" aria-label="Expense period">
                    <button type="button" aria-pressed="{{ $period === 'day' ? 'true' : 'false' }}" onclick="setExpensePeriod('day')" class="rounded-md px-3 py-1.5 text-xs {{ $period === 'day' ? 'bg-amber-500 font-semibold text-gray-950' : 'font-medium text-gray-400 hover:text-white' }}">Day</button>
                    <button type="button" aria-pressed="{{ $period === 'month' ? 'true' : 'false' }}" onclick="setExpensePeriod('month')" class="rounded-md px-3 py-1.5 text-xs {{ $period === 'month' ? 'bg-amber-500 font-semibold text-gray-950' : 'font-medium text-gray-400 hover:text-white' }}">Month</button>
                </div>
                <label class="sr-only" for="expense-day-picker">Expense date</label>
                <input id="expense-day-picker" type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="{{ $period === 'day' ? '' : 'hidden' }} rounded-lg border border-gray-800 bg-gray-900 px-3 py-2 text-xs text-gray-200 color-scheme-dark">
                <label class="sr-only" for="expense-month-picker">Expense month</label>
                <input id="expense-month-picker" type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()" class="{{ $period === 'month' ? '' : 'hidden' }} rounded-lg border border-gray-800 bg-gray-900 px-3 py-2 text-xs text-gray-200 color-scheme-dark">
            </form>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                <p class="text-xs text-gray-400">Total Expenses</p>
                <p class="mt-2 text-2xl font-bold text-white">Rs {{ number_format((float) $expenseTotal, 2) }}</p>
                <p class="mt-1 text-[11px] text-gray-500">{{ $period === 'day' ? "Selected day: {$selectedDate}" : "Selected month: {$selectedMonth}" }}</p>
            </div>
            <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                <p class="text-xs text-gray-400">Expense Records</p>
                <p class="mt-2 text-2xl font-bold text-white">{{ number_format($expenseCount) }}</p>
                <p class="mt-1 text-[11px] text-gray-500">Matching the selected period</p>
            </div>
            <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                <p class="text-xs text-gray-400">Average per Record</p>
                <p class="mt-2 text-2xl font-bold text-amber-400">Rs {{ number_format((float) $expenseAverage, 2) }}</p>
                <p class="mt-1 text-[11px] text-gray-500">Total divided by record count</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-800 px-5 py-4">
                <div>
                    <h4 class="text-sm font-semibold text-white">Expense Records</h4>
                    <p class="mt-0.5 text-xs text-gray-500">Company operating costs and salesperson support</p>
                </div>
                <span class="text-xs text-gray-500">{{ number_format($expenseCount) }} {{ $expenseCount === 1 ? 'record' : 'records' }}</span>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[920px] border-collapse text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-500">
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-4 py-3 font-semibold">Expense</th>
                            <th class="px-4 py-3 font-semibold">Category</th>
                            <th class="px-4 py-3 font-semibold">Paid for</th>
                            <th class="px-4 py-3 font-semibold">Bank Account</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount</th>
                            <th class="px-5 py-3 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/70">
                        @forelse ($expenses as $expense)
                            <tr class="hover:bg-gray-800/30">
                                <td class="whitespace-nowrap px-5 py-3.5 text-gray-400">{{ $expense->cashTransaction->transaction_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3.5 font-medium text-gray-200">{{ $expense->description->name }}</td>
                                <td class="px-4 py-3.5 text-gray-400">{{ $expense->category }}</td>
                                <td class="px-4 py-3.5 text-gray-400">{{ $expense->paid_for }}</td>
                                <td class="px-4 py-3.5 text-gray-400">{{ $expense->cashTransaction->cashAccount->name }}</td>
                                <td class="px-4 py-3.5 text-right font-mono font-semibold text-gray-200">Rs {{ number_format((float) $expense->cashTransaction->amount, 2) }}</td>
                                <td class="px-5 py-3.5 text-right"><a href="{{ route('expenses.edit', $expense) }}" class="font-semibold text-amber-300 hover:text-amber-200">Edit</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-xs text-gray-500">No expenses recorded for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">{{ $period === 'day' ? 'Category Breakdown' : 'Monthly Category Breakdown' }}</h3>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-800 bg-gray-900 p-5">
                <div class="mb-4 flex items-center justify-between border-b border-gray-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-industry text-purple-400"></i>
                        <h3 class="text-sm font-bold text-white">Company Corporate Operational Expenses</h3>
                    </div>
                    <span class="font-mono text-xs font-bold text-purple-400">Rs {{ number_format((float) $categoryBreakdown->sum('total'), 2) }}</span>
                </div>
                <div class="space-y-3">
                    @forelse ($categoryBreakdown as $category)
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-800 bg-gray-950 p-3 text-xs">
                            <p class="font-bold text-gray-200">{{ $category->category }}</p>
                            <span class="whitespace-nowrap font-mono font-bold text-gray-300">Rs {{ number_format((float) $category->total, 2) }}</span>
                        </div>
                    @empty
                        <p class="py-4 text-center text-xs text-gray-500">No company expenses for this period.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-gray-800 bg-gray-900 p-5">
                <div class="mb-4 flex items-center justify-between border-b border-gray-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-home text-blue-400"></i>
                        <h3 class="text-sm font-bold text-white">Salesperson Personal Home Expenses Support</h3>
                    </div>
                    <span class="font-mono text-xs font-bold text-blue-400">Rs {{ number_format((float) $salespersonBreakdown->sum('total'), 2) }}</span>
                </div>
                <div class="space-y-3">
                    @forelse ($salespersonBreakdown as $entry)
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-800 bg-gray-950 p-3 text-xs">
                            <div>
                                <p class="font-bold text-gray-200">{{ $entry->paid_for }}: {{ $entry->category }}</p>
                                <span class="text-[9px] text-gray-500">Personal</span>
                            </div>
                            <span class="whitespace-nowrap font-mono font-bold text-gray-300">Rs {{ number_format((float) $entry->total, 2) }}</span>
                        </div>
                    @empty
                        <p class="py-4 text-center text-xs text-gray-500">No salesperson expenses for this period.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function setExpensePeriod(period) {
        document.getElementById('expense-period').value = period;
        document.getElementById('expense-period-form').submit();
    }
</script>
@endpush
