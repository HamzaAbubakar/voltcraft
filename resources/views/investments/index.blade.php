@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Investments</h2>
            <p class="mt-1 text-sm text-gray-400">Track investor principal, agreed profit share, and repayment dates.</p>
        </div>
        <a href="{{ route('investments.create') }}" class="shrink-0 rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 shadow-lg shadow-amber-500/10 transition hover:bg-amber-400">
            <i class="fas fa-plus mr-2"></i>Record Investment
        </a>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
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

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Active investments</p>
            <p class="mt-2 text-xl font-bold text-white">{{ number_format($activeInvestmentCount) }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Principal currently held</p>
            <p class="mt-2 text-xl font-bold text-amber-400">Rs {{ number_format((float) $activePrincipalTotal, 2) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="flex items-center justify-between gap-3 border-b border-gray-800 px-5 py-4">
            <div>
                <h3 class="text-sm font-semibold text-white">Investment Records</h3>
                <p class="mt-1 text-xs text-gray-500">Profit payout is entered manually when you record repayment.</p>
            </div>
            <span class="text-xs text-gray-500">{{ $investments->count() }} {{ $investments->count() === 1 ? 'record' : 'records' }}</span>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[980px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500">
                        <th class="px-5 py-3 font-semibold">Investor</th>
                        <th class="px-4 py-3 text-right font-semibold">Principal</th>
                        <th class="px-4 py-3 text-right font-semibold">Profit share</th>
                        <th class="px-4 py-3 font-semibold">Received</th>
                        <th class="px-4 py-3 font-semibold">Return due</th>
                        <th class="px-4 py-3 font-semibold">Cash account</th>
                        <th class="px-5 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    @forelse ($investments as $investment)
                        <tr class="hover:bg-gray-800/30">
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-gray-200">{{ $investment->investor_name }}</p>
                                <p class="mt-1 text-gray-500">{{ $investment->contact_info ?: 'No contact information' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-semibold text-gray-200">Rs {{ number_format((float) $investment->principal_amount, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-amber-400">{{ number_format((float) $investment->profit_share_percentage, 2) }}%</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-gray-400">{{ $investment->received_date->format('M d, Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if ($investment->status === 'active')
                                    @php
                                        $daysUntilMaturity = (int) today()->diffInDays($investment->maturity_date, false);
                                    @endphp
                                    <p class="text-gray-300">{{ $investment->maturity_date->format('M d, Y') }}</p>
                                    <p class="mt-1 {{ $daysUntilMaturity < 0 ? 'text-red-400' : ($daysUntilMaturity <= 3 ? 'text-amber-400' : 'text-gray-500') }}">
                                        @if ($daysUntilMaturity < 0)
                                            Overdue by {{ abs($daysUntilMaturity) }} {{ abs($daysUntilMaturity) === 1 ? 'day' : 'days' }}
                                        @elseif ($daysUntilMaturity === 0)
                                            Due today
                                        @else
                                            {{ $daysUntilMaturity }} {{ $daysUntilMaturity === 1 ? 'day' : 'days' }} remaining
                                        @endif
                                    </p>
                                @else
                                    <p class="text-emerald-400">Settled {{ $investment->settlement_date->format('M d, Y') }}</p>
                                    <p class="mt-1 text-gray-500">Profit paid: Rs {{ number_format((float) $investment->profit_paid, 2) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-gray-400">{{ $investment->initialCashTransaction->cashAccount->name }}</td>
                            <td class="px-5 py-3.5 text-right">
                                @if ($investment->status === 'active')
                                    <a href="{{ route('investments.edit', $investment) }}" class="font-semibold text-amber-300 hover:text-amber-200">Edit</a>
                                    <span class="px-1 text-gray-600">·</span>
                                    <a href="{{ route('investments.settlement.create', $investment) }}" class="font-semibold text-emerald-300 hover:text-emerald-200">Settle</a>
                                @else
                                    <span class="rounded border border-emerald-900 bg-emerald-950/40 px-2 py-1 text-emerald-400">Settled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-xs text-gray-500">No investments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
