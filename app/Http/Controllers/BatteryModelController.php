<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBatteryModelMappingsRequest;
use App\Models\BatteryModel;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BatteryModelController extends Controller
{
    public function index(): View
    {
        return view('inventory.battery-models', [
            'batteryModels' => BatteryModel::query()
                ->with(['inventoryItems' => fn ($query) => $query->orderBy('name')->orderBy('id')])
                ->orderBy('name')
                ->orderBy('id')
                ->get(),
            'inventoryItems' => InventoryItem::query()
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'sku', 'name', 'unit']),
        ]);
    }

    public function update(
        UpdateBatteryModelMappingsRequest $request,
        BatteryModel $batteryModel,
    ): RedirectResponse {
        $components = collect($request->validated('components', []))
            ->mapWithKeys(fn (array $component): array => [
                $component['inventory_item_id'] => [
                    'quantity_per_battery' => $component['quantity_per_battery'],
                ],
            ])
            ->all();

        DB::transaction(function () use ($batteryModel, $components): void {
            $lockedBatteryModel = BatteryModel::query()
                ->whereKey($batteryModel->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedBatteryModel->inventoryItems()->sync($components);
        });

        return redirect()
            ->route('inventory.battery-models.index')
            ->with('success', 'Battery model inventory mapping updated.');
    }
}
