@extends('layouts.dashboard')

@section('content')
@php($isEditing = $inventoryItem !== null)
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between gap-4 border-b border-gray-800 pb-4">
        <div>
            <a href="{{ $isEditing ? route('inventory.show', $inventoryItem) : route('inventory.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to inventory</a>
            <h2 class="mt-2 text-xl font-bold text-white">{{ $isEditing ? 'Edit Inventory Item' : 'Add Inventory Item' }}</h2>
            <p class="mt-1 text-xs text-gray-400">Record item details and stock thresholds.</p>
        </div>
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

    <form method="POST" action="{{ $isEditing ? route('inventory.update', $inventoryItem) : route('inventory.store') }}" class="space-y-6 rounded-xl border border-gray-800 bg-gray-900 p-5 sm:p-7">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="space-y-2">
                <span class="block text-xs font-semibold text-gray-300">SKU</span>
                <p class="rounded-lg border border-gray-800 bg-gray-950 px-3 py-2.5 font-mono text-sm text-gray-300">
                    {{ $inventoryItem?->sku ?? 'Generated automatically when saved' }}
                </p>
            </div>
            <div class="space-y-2">
                <label for="name" class="block text-xs font-semibold text-gray-300">Item name</label>
                <input id="name" name="name" value="{{ old('name', $inventoryItem?->name) }}" required maxlength="255" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('name')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="category" class="block text-xs font-semibold text-gray-300">Category</label>
                <select id="category" name="category" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    <option value="">Select a category</option>
                    <option value="Cell" @selected(old('category', $inventoryItem?->category ?? 'Cell') === 'Cell')>Cell</option>
                    <option value="Other" @selected(old('category', $inventoryItem?->category) === 'Other')>Other</option>
                </select>
                @error('category')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="unit" class="block text-xs font-semibold text-gray-300">Unit</label>
                <input id="unit" name="unit" value="{{ old('unit', $inventoryItem?->unit ?? 'pcs') }}" required maxlength="20" placeholder="e.g. pcs, kg, m" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none placeholder:text-gray-600 focus:border-amber-500">
                @error('unit')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            @unless ($isEditing)
                <div class="space-y-2">
                    <label for="quantity_on_hand" class="block text-xs font-semibold text-gray-300">Opening quantity</label>
                    <input id="quantity_on_hand" name="quantity_on_hand" type="number" step="0.001" min="0" value="{{ old('quantity_on_hand', '0') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    <p class="text-[11px] text-gray-500">A non-zero opening quantity is saved in stock history.</p>
                    @error('quantity_on_hand')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            @else
                <div class="space-y-2">
                    <span class="block text-xs font-semibold text-gray-300">Current quantity</span>
                    <p class="rounded-lg border border-gray-800 bg-gray-950 px-3 py-2.5 text-sm text-gray-300">{{ $inventoryItem->quantity_on_hand }} {{ $inventoryItem->unit }}</p>
                    <p class="text-[11px] text-gray-500">Use a stock movement to change on-hand quantity.</p>
                </div>
            @endunless
            <div class="space-y-2">
                <label for="reorder_point" class="block text-xs font-semibold text-gray-300">Reorder point</label>
                <input id="reorder_point" name="reorder_point" type="number" step="0.001" min="0" value="{{ old('reorder_point', $inventoryItem?->reorder_point ?? '0') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('reorder_point')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="unit_cost" class="block text-xs font-semibold text-gray-300">Unit cost (Rs)</label>
                <input id="unit_cost" name="unit_cost" type="number" step="0.01" min="0" value="{{ old('unit_cost', $inventoryItem?->unit_cost ?? '0.00') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                @error('unit_cost')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-2 border-t border-gray-800 pt-5">
            <a href="{{ $isEditing ? route('inventory.show', $inventoryItem) : route('inventory.index') }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 hover:bg-gray-800">Cancel</a>
            <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 hover:bg-amber-400">{{ $isEditing ? 'Save Changes' : 'Add Item' }}</button>
        </div>
    </form>
</div>
@endsection
