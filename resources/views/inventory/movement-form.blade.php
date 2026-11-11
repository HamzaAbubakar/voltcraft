@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="border-b border-gray-800 pb-4">
        <a href="{{ route('inventory.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to inventory</a>
        <h2 class="mt-2 text-xl font-bold text-white">Receive or Adjust Stock</h2>
        <p class="mt-1 text-xs text-gray-400">Every stock change is recorded in the item’s movement history.</p>
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

    @if ($inventoryItems->isEmpty())
        <div class="rounded-xl border border-gray-800 bg-gray-900 px-5 py-12 text-center">
            <i class="fas fa-box-open text-2xl text-gray-600"></i>
            <p class="mt-3 text-sm font-semibold text-gray-300">Add an inventory item first</p>
            <a href="{{ route('inventory.create') }}" class="mt-4 inline-flex rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 hover:bg-amber-400">Add Item</a>
        </div>
    @else
        <form method="POST" action="{{ route('inventory.movements.store') }}" class="space-y-5 rounded-xl border border-gray-800 bg-gray-900 p-5 sm:p-7">
            @csrf
            <div class="space-y-2">
                <label for="inventory_item_id" class="block text-xs font-semibold text-gray-300">Inventory item</label>
                <select id="inventory_item_id" name="inventory_item_id" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    <option value="">Select an item</option>
                    @foreach ($inventoryItems as $item)
                        <option value="{{ $item->id }}" @selected((string) old('inventory_item_id', $selectedItemId) === (string) $item->id)>{{ $item->name }} ({{ $item->sku }})</option>
                    @endforeach
                </select>
                @error('inventory_item_id')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="type" class="block text-xs font-semibold text-gray-300">Movement type</label>
                <select id="type" name="type" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                    <option value="received" @selected(old('type', 'received') === 'received')>Receive stock</option>
                    <option value="adjustment" @selected(old('type') === 'adjustment')>Stock adjustment</option>
                </select>
                @error('type')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="quantity_change" class="block text-xs font-semibold text-gray-300">Quantity change</label>
                <input id="quantity_change" name="quantity_change" type="number" step="0.001" value="{{ old('quantity_change') }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500">
                <p class="text-[11px] text-gray-500">Receipts must be positive. Adjustments may be positive or negative; stock cannot go below zero.</p>
                @error('quantity_change')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-2">
                <label for="note" class="block text-xs font-semibold text-gray-300">Note <span class="font-normal text-gray-500">(optional)</span></label>
                <textarea id="note" name="note" rows="3" maxlength="1000" class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white outline-none placeholder:text-gray-600 focus:border-amber-500">{{ old('note') }}</textarea>
                @error('note')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-800 pt-5">
                <a href="{{ route('inventory.index') }}" class="rounded-lg border border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-300 hover:bg-gray-800">Cancel</a>
                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-gray-950 hover:bg-amber-400">Record Movement</button>
            </div>
        </form>
    @endif
</div>
@endsection
