@extends('layouts.dashboard')

@section('content')
@php($isEditing = $cashAccount !== null)
<div class="mx-auto max-w-2xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <a href="{{ route('cash.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to Cash In and Out</a>
        <h2 class="mt-2 text-xl font-bold text-white">{{ $isEditing ? 'Edit Cash Account' : 'Add Cash Account' }}</h2>
        <p class="mt-1 text-xs text-gray-400">Manage the salesperson who holds or records cash.</p>
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

    <form method="POST" action="{{ $isEditing ? route('cash.accounts.update', $cashAccount) : route('cash.accounts.store') }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5 sm:p-7">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif
        <div class="space-y-2">
            <label for="name" class="block text-xs font-semibold text-gray-300">Salesperson name</label>
            <input id="name" name="name" value="{{ old('name', $cashAccount?->name) }}" required maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
            @error('name')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-2">
            <label for="role" class="block text-xs font-semibold text-gray-300">Role / title <span class="font-normal text-gray-500">(optional)</span></label>
            <input id="role" name="role" value="{{ old('role', $cashAccount?->role) }}" maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
            @error('role')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        @if ($isEditing)
            <div class="rounded-lg border border-gray-800 bg-gray-950 p-4">
                <input type="hidden" name="is_active" value="0">
                <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $cashAccount->is_active)) class="h-4 w-4 rounded border-gray-700 bg-gray-900 text-amber-500 focus:ring-amber-500">
                    Active account
                </label>
                <p class="mt-2 text-[11px] text-gray-500">Inactive accounts are kept for history and can’t be selected for new transactions.</p>
                @error('is_active')<p class="mt-2 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        @endif

        <div class="flex justify-end gap-2 border-t border-gray-800 pt-5">
            <a href="{{ route('cash.index') }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 hover:bg-gray-800">Cancel</a>
            <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 hover:bg-amber-400">{{ $isEditing ? 'Save Changes' : 'Add Account' }}</button>
        </div>
    </form>
</div>
@endsection
