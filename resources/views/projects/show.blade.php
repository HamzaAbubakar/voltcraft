@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('projects.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to projects</a>
            <h2 class="mt-2 text-xl font-bold text-white">{{ $project->name }}</h2>
            <p class="mt-1 text-sm text-gray-400">{{ $project->customer_name ?: 'No customer' }}{{ $project->site_location ? ' · '.$project->site_location : '' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('projects.edit', $project) }}" class="rounded-lg border border-gray-700 px-4 py-2 text-xs font-semibold text-gray-200 hover:bg-gray-800">Edit project</a>
            @if ($project->status === 'active')
                <form method="POST" action="{{ route('projects.complete', $project) }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-emerald-700 bg-emerald-950/40 px-4 py-2 text-xs font-semibold text-emerald-300 hover:bg-emerald-900/50">Complete project</button>
                </form>
            @endif
            <a href="{{ route('projects.expenses.create', $project) }}" class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 hover:bg-amber-400"><i class="fas fa-plus mr-2"></i>Add site expense</a>
        </div>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div role="status" class="rounded-lg border border-amber-800 bg-amber-950/40 px-4 py-3 text-sm text-amber-300">{{ session('warning') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Contract value</p>
            <p class="mt-2 text-xl font-bold text-white">Rs {{ number_format($project->contract_value, 2) }}</p>
            <p class="mt-1 text-[11px] text-gray-500">{{ number_format($project->system_size_watts) }} W × Rs {{ number_format((float) $project->customer_rate_per_watt, 2) }} / W</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Outsourcing cost</p>
            <p class="mt-2 text-xl font-bold text-white">Rs {{ number_format($project->outsourcing_cost, 2) }}</p>
            <p class="mt-1 text-[11px] text-gray-500">{{ number_format($project->system_size_watts) }} W × Rs {{ number_format((float) $project->outsourced_rate_per_watt, 2) }} / W</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Gross margin</p>
            <p class="mt-2 text-xl font-bold text-amber-400">Rs {{ number_format($project->gross_margin, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Net project profit</p>
            <p class="mt-2 text-xl font-bold {{ $project->net_profit < 0 ? 'text-red-400' : 'text-emerald-400' }}">Rs {{ number_format($project->net_profit, 2) }}</p>
            <p class="mt-1 text-[11px] text-gray-500">After Rs {{ number_format($project->site_expenses_total, 2) }} in project-only site expenses</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-5 lg:col-span-1">
            <h3 class="text-sm font-semibold text-white">Project details</h3>
            <dl class="mt-4 space-y-3 text-xs">
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Lead team member</dt><dd class="text-right text-gray-200">{{ $project->assignedEmployee?->name ?: 'Not assigned' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Status</dt><dd class="text-right text-gray-200">{{ ucfirst($project->status) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Start date</dt><dd class="text-right text-gray-200">{{ $project->start_date->format('M d, Y') }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Completed date</dt><dd class="text-right text-gray-200">{{ $project->completed_date?->format('M d, Y') ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900 lg:col-span-2">
            <div class="border-b border-gray-800 px-5 py-4">
                <h3 class="text-sm font-semibold text-white">Project site expenses</h3>
                <p class="mt-1 text-xs text-gray-500">These records only reduce this project's profit. They do not affect company Expenses or Cash.</p>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[600px] border-collapse text-left text-xs">
                    <thead><tr class="border-b border-gray-800 text-gray-500"><th class="px-5 py-3 font-semibold">Date</th><th class="px-4 py-3 font-semibold">Category</th><th class="px-4 py-3 font-semibold">Team member</th><th class="px-4 py-3 font-semibold">Note</th><th class="px-5 py-3 text-right font-semibold">Amount</th></tr></thead>
                    <tbody class="divide-y divide-gray-800/70">
                        @forelse ($project->expenses as $expense)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3.5 text-gray-400">{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3.5 capitalize text-gray-300">{{ $expense->category }}</td>
                                <td class="px-4 py-3.5 text-gray-400">{{ $expense->employee?->name ?: '—' }}</td>
                                <td class="px-4 py-3.5 text-gray-500">{{ $expense->note ?: '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right font-mono font-semibold text-gray-200">Rs {{ number_format((float) $expense->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-xs text-gray-500">No site expenses recorded for this project.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot><tr class="border-t border-gray-800"><td colspan="4" class="px-5 py-3.5 text-right font-semibold text-gray-400">Total project site expenses</td><td class="px-5 py-3.5 text-right font-mono font-bold text-amber-300">Rs {{ number_format($project->site_expenses_total, 2) }}</td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
