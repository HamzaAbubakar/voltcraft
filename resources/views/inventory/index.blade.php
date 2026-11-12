@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-amber-400">
                <i class="fas fa-boxes-stacked"></i>
                <span>STOCK CONTROL</span>
            </div>
            <h2 class="mt-1 text-xl font-bold text-white">Inventory Management</h2>
            <p class="mt-0.5 text-xs text-gray-400">Track stock quantities, reorder points, and item costs</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('inventory.battery-models.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-700 bg-gray-900 px-4 py-2 text-xs font-semibold text-gray-200 transition-colors hover:bg-gray-800">
                <i class="fas fa-battery-three-quarters mr-2"></i>Battery Mappings
            </a>
            <a href="{{ route('inventory.movements.create') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-700 bg-gray-900 px-4 py-2 text-xs font-semibold text-gray-200 transition-colors hover:bg-gray-800">
                <i class="fas fa-truck-ramp-box mr-2"></i>Receive / Adjust Stock
            </a>
            <a href="{{ route('inventory.create') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 shadow-lg shadow-amber-500/10 transition-colors hover:bg-amber-400">
                <i class="fas fa-plus mr-2"></i>Add Item
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <div class="flex items-center justify-between text-xs text-gray-400"><span>Total SKUs</span><i class="fas fa-barcode text-amber-400"></i></div>
            <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalItems) }}</p>
            <p class="mt-1 text-[11px] text-gray-500">Active inventory items</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <div class="flex items-center justify-between text-xs text-gray-400"><span>Inventory Value</span><i class="fas fa-coins text-emerald-400"></i></div>
            <p class="mt-2 text-2xl font-bold text-white">Rs {{ number_format((float) $inventoryValue, 2) }}</p>
            <p class="mt-1 text-[11px] text-gray-500">Based on current quantity and unit cost</p>
        </div>
        <div class="rounded-xl border border-red-900/60 bg-red-950/20 p-4">
            <div class="flex items-center justify-between text-xs text-gray-400"><span>Low Stock Items</span><i class="fas fa-triangle-exclamation text-red-400"></i></div>
            <p class="mt-2 text-2xl font-bold text-red-300">{{ number_format($lowStockItems) }}</p>
            <p class="mt-1 text-[11px] text-red-400">At or below their reorder point</p>
        </div>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('inventory.index', ['search' => $search]) }}" class="rounded-lg border px-3 py-2 text-xs font-semibold {{ $status !== 'low' ? 'border-amber-500/30 bg-amber-500/10 text-amber-300' : 'border-gray-800 bg-gray-900 text-gray-400 hover:text-white' }}">
                All Items <span class="ml-1 {{ $status !== 'low' ? 'text-amber-500' : 'text-gray-500' }}">{{ number_format($totalItems) }}</span>
            </a>
            <a href="{{ route('inventory.index', ['status' => 'low', 'search' => $search]) }}" class="rounded-lg border px-3 py-2 text-xs font-semibold {{ $status === 'low' ? 'border-red-500/30 bg-red-500/10 text-red-300' : 'border-gray-800 bg-gray-900 text-gray-400 hover:text-white' }}">
                Low Stock <span class="ml-1 text-red-400">{{ number_format($lowStockItems) }}</span>
            </a>
        </div>
        <form method="GET" action="{{ route('inventory.index') }}" class="flex gap-2">
            @if ($status === 'low')
                <input type="hidden" name="status" value="low">
            @endif
            <label class="flex min-w-0 items-center gap-2 rounded-lg border border-gray-800 bg-gray-900 px-3 text-gray-500">
                <i class="fas fa-search text-xs"></i>
                <input type="search" name="search" value="{{ $search }}" placeholder="Search inventory..." aria-label="Search inventory" class="w-full bg-transparent py-2 text-xs text-gray-200 outline-none placeholder:text-gray-500 sm:w-48">
            </label>
            <button type="submit" class="rounded-lg border border-gray-700 bg-gray-900 px-3 text-xs text-gray-200 transition-colors hover:bg-gray-800">Search</button>
            @if ($search !== '')
                <a href="{{ route('inventory.index', $status === 'low' ? ['status' => 'low'] : []) }}" class="rounded-lg border border-gray-800 bg-gray-900 px-3 py-2 text-xs text-gray-400 hover:text-white">Clear</a>
            @endif
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="border-b border-gray-800 px-5 py-4">
            <h3 class="text-sm font-semibold text-white">Stock Items</h3>
            <p class="mt-0.5 text-xs text-gray-500">Current quantities and reorder status</p>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[860px] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500">
                        <th class="px-5 py-3 font-semibold">Item</th>
                        <th class="px-4 py-3 font-semibold">Category</th>
                        <th class="px-4 py-3 text-right font-semibold">On hand</th>
                        <th class="px-4 py-3 text-right font-semibold">Reorder point</th>
                        <th class="px-4 py-3 font-semibold">Stock level</th>
                        <th class="px-5 py-3 text-right font-semibold">Unit cost</th>
                        <th class="px-5 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/70">
                    @forelse ($items as $item)
                        @php($isLowStock = (float) $item->quantity_on_hand <= (float) $item->reorder_point)
                        <tr class="transition-colors hover:bg-gray-800/30">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('inventory.show', $item) }}" class="font-semibold text-gray-200 hover:text-amber-300">{{ $item->name }}</a>
                                <p class="mt-0.5 font-mono text-[10px] text-gray-500">{{ $item->sku }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-gray-400">{{ $item->category }}</td>
                            <td class="px-4 py-3.5 text-right font-mono font-semibold {{ $isLowStock ? 'text-red-400' : 'text-gray-200' }}">
                                {{ rtrim(rtrim(number_format((float) $item->quantity_on_hand, 3, '.', ','), '0'), '.') }} {{ $item->unit }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-gray-400">
                                {{ rtrim(rtrim(number_format((float) $item->reorder_point, 3, '.', ','), '0'), '.') }} {{ $item->unit }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $isLowStock ? 'bg-red-500/10 text-red-300' : 'bg-emerald-500/10 text-emerald-300' }}">
                                    {{ (float) $item->quantity_on_hand < 0 ? 'Negative stock' : ((float) $item->quantity_on_hand === 0.0 ? 'Out of stock' : ($isLowStock ? 'Low' : 'Healthy')) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono text-gray-300">Rs {{ number_format((float) $item->unit_cost, 2) }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('inventory.show', $item) }}" class="font-semibold text-amber-300 hover:text-amber-200">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <i class="fas fa-box-open text-2xl text-gray-600"></i>
                                <p class="mt-3 text-sm font-semibold text-gray-300">{{ $search !== '' || $status === 'low' ? 'No matching inventory items' : 'Your inventory is empty' }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $search !== '' || $status === 'low' ? 'Try another search or view all items.' : 'Add an item to start tracking stock.' }}</p>
                                @if ($search === '' && $status !== 'low')
                                    <a href="{{ route('inventory.create') }}" class="mt-4 inline-flex rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 hover:bg-amber-400">Add your first item</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages() || $items->total() > 0)
            <div class="flex flex-col gap-2 border-t border-gray-800 px-5 py-3 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between">
                <span>
                    Showing <span class="font-medium text-gray-300">{{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }}</span>
                    of <span class="font-medium text-gray-300">{{ number_format($items->total()) }}</span> items
                </span>
                <div>{{ $items->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
