@extends('layouts.dashboard')

@section('content')
@php
    $isEditing = $managedUser !== null;
    $selectedPages = old('accessible_pages', $managedUser?->accessible_pages ?? []);
@endphp
<div class="mx-auto max-w-3xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <a href="{{ route('users.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to users</a>
        <h2 class="mt-2 text-xl font-bold text-white">{{ $isEditing ? 'Edit user' : 'Create user' }}</h2>
        <p class="mt-1 text-xs text-gray-400">Assign access by application page. Users can have no pages selected.</p>
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

    <form method="POST" action="{{ $isEditing ? route('users.update', $managedUser) : route('users.store') }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5 sm:p-7">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <label for="name" class="block text-xs font-semibold text-gray-300">Name</label>
                <input id="name" name="name" value="{{ old('name', $managedUser?->name) }}" required maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('name')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="email" class="block text-xs font-semibold text-gray-300">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $managedUser?->email) }}" required maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('email')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2 sm:col-span-2">
                <label for="password" class="block text-xs font-semibold text-gray-300">Password{{ $isEditing ? ' (leave blank to keep current)' : '' }}</label>
                <input id="password" name="password" type="password" {{ $isEditing ? '' : 'required' }} minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('password')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <section class="space-y-3 border-t border-gray-800 pt-5">
            <div>
                <h3 class="text-sm font-semibold text-white">Page access</h3>
                <p class="mt-1 text-xs text-gray-500">Select the pages this user may open.</p>
            </div>
            @if ($managedUser?->is_admin)
                <div class="rounded-lg border border-amber-500/20 bg-amber-500/5 px-4 py-3 text-sm text-amber-200">
                    This account is an administrator and always has access to every page.
                </div>
            @else
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($pages as $pageKey => $pageLabel)
                        <label for="accessible_pages_{{ $pageKey }}" class="flex items-center gap-3 rounded-lg border border-gray-800 bg-gray-950/70 px-3 py-3 text-sm text-gray-300">
                            <input id="accessible_pages_{{ $pageKey }}" name="accessible_pages[]" type="checkbox" value="{{ $pageKey }}" @checked(is_array($selectedPages) && in_array($pageKey, $selectedPages, true)) class="rounded border-gray-600 bg-gray-900 text-amber-500 focus:ring-amber-500">
                            <span>{{ $pageLabel }}</span>
                        </label>
                    @endforeach
                </div>
                @error('accessible_pages')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                @error('accessible_pages.*')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            @endif
        </section>

        <div class="flex justify-end gap-2 border-t border-gray-800 pt-5">
            <a href="{{ route('users.index') }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 hover:bg-gray-800">Cancel</a>
            <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 hover:bg-amber-400">{{ $isEditing ? 'Save changes' : 'Create user' }}</button>
        </div>
    </form>
</div>
@endsection
