@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">User Management</h2>
            <p class="mt-1 text-sm text-gray-400">Create accounts and choose which application pages each user can access.</p>
        </div>
        <a href="{{ route('users.create') }}" class="shrink-0 rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 transition hover:bg-amber-400">
            <i class="fas fa-plus mr-2"></i>Add user
        </a>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="border-b border-gray-800 px-5 py-4">
            <h3 class="text-sm font-semibold text-white">Accounts</h3>
            <p class="mt-1 text-xs text-gray-500">Administrators can access every page. Other users only see the pages assigned to them.</p>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[700px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500">
                        <th class="px-5 py-3 font-semibold">User</th>
                        <th class="px-4 py-3 font-semibold">Page access</th>
                        <th class="px-5 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-800/30">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-200">{{ $user->name }}</p>
                                <p class="mt-1 text-gray-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-4 py-4">
                                @if ($user->is_admin)
                                    <span class="rounded border border-amber-500/30 bg-amber-500/10 px-2 py-1 text-[10px] font-semibold text-amber-300">Administrator · all pages</span>
                                @elseif (count($user->accessible_pages ?? []) > 0)
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($user->accessible_pages as $page)
                                            <span class="rounded border border-gray-700 bg-gray-800 px-2 py-1 text-[10px] text-gray-300">{{ $pages[$page] ?? $page }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-500">No page access</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('users.edit', $user) }}" class="font-semibold text-amber-300 hover:text-amber-200">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-xs text-gray-500">No user accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-gray-800 px-5 py-4">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
