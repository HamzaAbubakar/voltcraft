@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Projects</h2>
            <p class="mt-1 text-sm text-gray-400">Track installation margins and site costs separately from company expenses.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="shrink-0 rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 transition hover:bg-amber-400">
            <i class="fas fa-plus mr-2"></i>New project
        </a>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Projects</p>
            <p class="mt-2 text-xl font-bold text-white">{{ number_format($projectCount) }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Gross margin</p>
            <p class="mt-2 text-xl font-bold text-amber-400">Rs {{ number_format($grossMarginTotal, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Project site expenses</p>
            <p class="mt-2 text-xl font-bold text-white">Rs {{ number_format($siteExpensesTotal, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Net project profit</p>
            <p class="mt-2 text-xl font-bold {{ $netProfitTotal < 0 ? 'text-red-400' : 'text-emerald-400' }}">Rs {{ number_format($netProfitTotal, 2) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="border-b border-gray-800 px-5 py-4">
            <h3 class="text-sm font-semibold text-white">Project records</h3>
            <p class="mt-1 text-xs text-gray-500">Margin is watts × the difference between customer and outsourced rates.</p>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[900px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500">
                        <th class="px-5 py-3 font-semibold">Project</th>
                        <th class="px-4 py-3 font-semibold">Lead</th>
                        <th class="px-4 py-3 text-right font-semibold">System size</th>
                        <th class="px-4 py-3 text-right font-semibold">Gross margin</th>
                        <th class="px-4 py-3 text-right font-semibold">Site costs</th>
                        <th class="px-4 py-3 text-right font-semibold">Net profit</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    @forelse ($projects as $project)
                        <tr class="hover:bg-gray-800/30">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('projects.show', $project) }}" class="font-semibold text-gray-200 hover:text-amber-300">{{ $project->name }}</a>
                                <p class="mt-1 text-gray-500">{{ $project->customer_name ?: $project->site_location ?: 'No customer/site details' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-gray-400">{{ $project->assignedEmployee?->name ?: '—' }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-gray-300">{{ number_format($project->system_size_watts) }} W</td>
                            <td class="px-4 py-3.5 text-right font-mono text-amber-300">Rs {{ number_format($project->gross_margin, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-gray-300">Rs {{ number_format($project->site_expenses_total, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono font-semibold {{ $project->net_profit < 0 ? 'text-red-400' : 'text-emerald-400' }}">Rs {{ number_format($project->net_profit, 2) }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="rounded border px-2 py-1 text-[10px] {{ $project->status === 'active' ? 'border-amber-500/20 bg-amber-500/10 text-amber-300' : ($project->status === 'completed' ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border-gray-700 bg-gray-800 text-gray-400') }}">{{ ucfirst($project->status) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('projects.show', $project) }}" class="font-semibold text-amber-300 hover:text-amber-200">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-xs text-gray-500">No projects yet. Create a project to start tracking margin and site costs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($projects->hasPages())
            <div class="border-t border-gray-800 px-5 py-4">{{ $projects->links() }}</div>
        @endif
    </div>
</div>
@endsection
