<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', 'low'])],
        ]);

        $search = $filters['search'] ?? '';
        $itemsQuery = InventoryItem::query();

        if ($search !== '') {
            $itemsQuery->where(function (Builder $query) use ($search): void {
                $query->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if (($filters['status'] ?? 'all') === 'low') {
            $itemsQuery->whereColumn('quantity_on_hand', '<=', 'reorder_point');
        }

        $items = $itemsQuery
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $totalItems = InventoryItem::query()->count();
        $lowStockItems = InventoryItem::query()
            ->whereColumn('quantity_on_hand', '<=', 'reorder_point')
            ->count();
        $inventoryValue = InventoryItem::query()
            ->selectRaw('COALESCE(SUM(quantity_on_hand * unit_cost), 0) as inventory_value')
            ->value('inventory_value');

        return view('inventory.index', [
            'items' => $items,
            'search' => $search,
            'status' => $filters['status'] ?? 'all',
            'totalItems' => $totalItems,
            'lowStockItems' => $lowStockItems,
            'inventoryValue' => $inventoryValue,
        ]);
    }

    public function create(): View
    {
        return view('inventory.form', ['inventoryItem' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->itemRules());
        $openingQuantity = $validated['quantity_on_hand'];
        unset($validated['quantity_on_hand']);

        $inventoryItem = DB::transaction(function () use ($validated, $openingQuantity, $request): InventoryItem {
            $inventoryItem = InventoryItem::create([
                ...$validated,
                'sku' => 'TEMP-'.Str::uuid(),
                'quantity_on_hand' => $openingQuantity,
            ]);
            $inventoryItem->update([
                'sku' => 'INV-'.str_pad((string) $inventoryItem->id, 6, '0', STR_PAD_LEFT),
            ]);

            if ((float) $openingQuantity > 0) {
                $inventoryItem->stockMovements()->create([
                    'user_id' => $request->user()->id,
                    'type' => 'opening',
                    'quantity_change' => $openingQuantity,
                    'note' => 'Opening stock',
                ]);
            }

            return $inventoryItem;
        });

        return redirect()
            ->route('inventory.show', $inventoryItem)
            ->with('success', 'Inventory item added.');
    }

    public function show(InventoryItem $inventoryItem): View
    {
        $movements = $inventoryItem->stockMovements()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('inventory.show', compact('inventoryItem', 'movements'));
    }

    public function edit(InventoryItem $inventoryItem): View
    {
        return view('inventory.form', compact('inventoryItem'));
    }

    public function update(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $inventoryItem->update($request->validate($this->itemRules($inventoryItem)));

        return redirect()
            ->route('inventory.show', $inventoryItem)
            ->with('success', 'Inventory item updated.');
    }

    public function destroy(InventoryItem $inventoryItem): RedirectResponse
    {
        $inventoryItem->delete();

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory item archived.');
    }

    public function createMovement(Request $request): View
    {
        return view('inventory.movement-form', [
            'inventoryItems' => InventoryItem::query()->orderBy('name')->get(['id', 'sku', 'name', 'unit']),
            'selectedItemId' => $request->query('inventory_item_id'),
        ]);
    }

    public function storeMovement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inventory_item_id' => [
                'required',
                'integer',
                Rule::exists('inventory_items', 'id')->whereNull('deleted_at'),
            ],
            'type' => ['required', Rule::in(['received', 'adjustment'])],
            'quantity_change' => ['required', 'numeric', 'decimal:0,3', 'max:999999999.999', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $quantityChange = (float) $validated['quantity_change'];

        if ($validated['type'] === 'received' && $quantityChange < 0) {
            throw ValidationException::withMessages([
                'quantity_change' => 'Received stock must be a positive quantity.',
            ]);
        }

        DB::transaction(function () use ($validated, $quantityChange, $request): void {
            $inventoryItem = InventoryItem::query()->findOrFail($validated['inventory_item_id']);
            $quantityQuery = InventoryItem::query()->whereKey($inventoryItem->id);

            if ($quantityChange < 0) {
                $updatedRows = $quantityQuery
                    ->where('quantity_on_hand', '>=', abs($quantityChange))
                    ->decrement('quantity_on_hand', abs($quantityChange));
            } else {
                $updatedRows = $quantityQuery->increment('quantity_on_hand', $quantityChange);
            }

            if ($updatedRows !== 1) {
                throw ValidationException::withMessages([
                    'quantity_change' => 'The adjustment cannot reduce stock below zero.',
                ]);
            }

            $inventoryItem->stockMovements()->create([
                'user_id' => $request->user()->id,
                'type' => $validated['type'],
                'quantity_change' => $validated['quantity_change'],
                'note' => $validated['note'] ?? null,
            ]);
        });

        return redirect()
            ->route('inventory.show', $validated['inventory_item_id'])
            ->with('success', 'Stock movement recorded.');
    }

    /**
     * Get the validation rules for creating or updating an inventory item.
     *
     * @return array<string, array<int, mixed>|string>
     */
    private function itemRules(?InventoryItem $inventoryItem = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['Cell', 'Other'])],
            'unit' => ['required', 'string', 'max:20'],
            'reorder_point' => ['required', 'numeric', 'min:0', 'decimal:0,3', 'max:999999999.999'],
            'unit_cost' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999.99'],
        ];

        if ($inventoryItem === null) {
            $rules['quantity_on_hand'] = ['required', 'numeric', 'min:0', 'decimal:0,3', 'max:999999999.999'];
        }

        return $rules;
    }
}
