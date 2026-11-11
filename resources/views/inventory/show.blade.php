@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('inventory.index') }}" class="text-xs text-gray-400 hover:text-white"><i class="fas fa-arrow-left mr-1"></i>Back to inventory</a>
            <div class="mt-2 flex items-center gap-2 text-xs font-medium text-amber-400"><i class="fas fa-box"></i><span>{{ $inventoryItem->sku }}</span></div>
            <h2 class="mt-1 text-xl font-bold text-white">{{ $inventoryItem->name }}</h2>
            <p class="mt-1 text-xs text-gray-400">{{ $inventoryItem->category }} · Rs {{ number_format((float) $inventoryItem->unit_cost, 2) }} per {{ $inventoryItem->unit }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('inventory.movements.create', ['inventory_item_id' => $inventoryItem->id]) }}" class="inline-flex items-center rounded-lg border border-gray-700 bg-gray-900 px-4 py-2 text-xs font-semibold text-gray-200 hover:bg-gray-800"><i class="fas fa-arrows-rotate mr-2"></i>Receive / Adjust</a>
            <a href="{{ route('inventory.edit', $inventoryItem) }}" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 hover:bg-amber-400"><i class="fas fa-pen mr-2"></i>Edit Item</a>
            <form method="POST" action="{{ route('inventory.destroy', $inventoryItem) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-900 px-4 py-2 text-xs font-semibold text-red-300 hover:bg-red-950/50">Archive</button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">On hand</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $inventoryItem->quantity_on_hand }} <span class="text-sm font-medium text-gray-400">{{ $inventoryItem->unit }}</span></p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Reorder point</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $inventoryItem->reorder_point }} <span class="text-sm font-medium text-gray-400">{{ $inventoryItem->unit }}</span></p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <p class="text-xs text-gray-400">Stock value</p>
            <p class="mt-2 text-2xl font-bold text-white">Rs {{ number_format((float) $inventoryItem->quantity_on_hand * (float) $inventoryItem->unit_cost, 2) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="border-b border-gray-800 px-5 py-4">
            <h3 class="text-sm font-semibold text-white">Stock Movement History</h3>
            <p class="mt-0.5 text-xs text-gray-500">Opening stock, receipts, and adjustments for this item</p>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[700px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500">
                        <th class="px-5 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 text-right font-semibold">Change</th>
                        <th class="px-4 py-3 font-semibold">Note</th>
                        <th class="px-5 py-3 font-semibold">Recorded by</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-400">{{ $movement->created_at->format('M j, Y g:i A') }}</td>
                            <td class="px-4 py-3.5 font-medium text-gray-200">{{ str($movement->type)->replace('_', ' ')->title() }}</td>
                            <td class="px-4 py-3.5 text-right font-mono font-semibold {{ (float) $movement->quantity_change > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ (float) $movement->quantity_change > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float) $movement->quantity_change, 3, '.', ','), '0'), '.') }} {{ $inventoryItem->unit }}
                            </td>
                            <td class="px-4 py-3.5 text-gray-400">{{ $movement->note ?: '—' }}</td>
                            <td class="px-5 py-3.5 text-gray-400">{{ $movement->user?->name ?? 'Unknown user' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500">No stock movements recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($movements->hasPages())
            <div class="border-t border-gray-800 px-5 py-3">{{ $movements->links() }}</div>
        @endif
    </div>
</div>
@endsection
