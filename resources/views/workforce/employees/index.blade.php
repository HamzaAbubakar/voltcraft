@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Employees</h2>
            <p class="mt-1 text-sm text-gray-400">Manage employee details and current base salary rates.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('workforce.index') }}" class="rounded-lg border border-gray-700 px-4 py-2 text-xs font-semibold text-gray-200 transition hover:bg-gray-800">Back to workforce</a>
            <a href="{{ route('workforce.employees.create') }}" class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 transition hover:bg-amber-400">
                <i class="fas fa-plus mr-2"></i>Add employee
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[640px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500">
                        <th class="px-5 py-3 font-semibold">Employee</th>
                        <th class="px-4 py-3 font-semibold">Position</th>
                        <th class="px-4 py-3 text-right font-semibold">Current base salary</th>
                        <th class="px-4 py-3 text-right font-semibold">Overtime rate / hr</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-gray-800/30">
                            <td class="px-5 py-3.5 font-semibold text-gray-200">{{ $employee->name }}</td>
                            <td class="px-4 py-3.5 text-gray-400">{{ $employee->position ?: '—' }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-gray-200">Rs {{ number_format((float) $employee->base_salary, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-amber-300">Rs {{ number_format((float) $employee->overtime_hourly_rate, 2) }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="rounded border px-2 py-1 text-[10px] {{ $employee->is_active ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border-gray-700 bg-gray-800 text-gray-400' }}">
                                    {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('workforce.employees.edit', $employee) }}" class="font-semibold text-amber-300 hover:text-amber-200">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-xs text-gray-500">No employees added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
