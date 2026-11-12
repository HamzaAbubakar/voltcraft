@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-amber-400">
                <i class="fas fa-battery-three-quarters"></i>
                <span>PRODUCT SETUP</span>
            </div>
            <h2 class="mt-1 text-xl font-bold text-white">Battery Model Inventory Mapping</h2>
            <p class="mt-1 text-xs text-gray-400">Set the quantity of each inventory item used to build one battery.</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-700 bg-gray-900 px-4 py-2 text-xs font-semibold text-gray-200 transition-colors hover:bg-gray-800">
            <i class="fas fa-boxes-stacked mr-2"></i>Inventory
        </a>
    </div>

    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif
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
        <div class="rounded-xl border border-amber-900/60 bg-amber-950/30 p-4 text-sm text-amber-200">
            Add inventory items before configuring battery models.
            <a href="{{ route('inventory.create') }}" class="ml-1 font-semibold text-amber-300 underline">Add an inventory item</a>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        @foreach ($batteryModels as $batteryModel)
            <form
                method="POST"
                action="{{ route('inventory.battery-models.update', $batteryModel) }}"
                data-battery-model="{{ $batteryModel->id }}"
                class="space-y-4 rounded-2xl border border-gray-800 bg-gray-900 p-5"
            >
                @csrf
                @method('PUT')

                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-white">{{ $batteryModel->name }}</h3>
                        <p class="mt-1 text-xs text-gray-500">Component quantities are per battery.</p>
                    </div>
                    <button
                        type="button"
                        data-add-component
                        class="rounded-lg border border-gray-700 px-3 py-2 text-xs font-semibold text-amber-300 transition-colors hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled($inventoryItems->isEmpty())
                    >
                        <i class="fas fa-plus mr-1"></i>Add component
                    </button>
                </div>

                <div data-component-container class="space-y-2"></div>
                <p data-empty-message class="rounded-lg border border-gray-800 bg-gray-950/60 p-3 text-xs text-gray-400">
                    This model becomes available for sales after at least one component is saved.
                </p>

                <template data-component-template>
                    <div class="grid grid-cols-[minmax(0,1fr)_7rem_auto] items-end gap-2">
                        <div>
                            <label class="mb-1 block text-[10px] font-medium text-gray-500">Inventory item</label>
                            <select data-field="inventory_item_id" required class="w-full rounded-lg border border-gray-800 bg-gray-950 p-2 text-xs text-gray-200 focus:border-amber-500 focus:outline-none">
                                <option value="">Choose item</option>
                                @foreach ($inventoryItems as $inventoryItem)
                                    <option value="{{ $inventoryItem->id }}">{{ $inventoryItem->sku }} — {{ $inventoryItem->name }} ({{ $inventoryItem->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-medium text-gray-500">Qty / battery</label>
                            <input data-field="quantity_per_battery" type="number" min="0.001" max="999999.999" step="0.001" required class="w-full rounded-lg border border-gray-800 bg-gray-950 p-2 text-xs text-gray-200 focus:border-amber-500 focus:outline-none">
                        </div>
                        <button type="button" data-remove-component aria-label="Remove component" class="mb-0.5 rounded border border-gray-700 px-2 py-2 text-xs text-gray-400 hover:border-red-700 hover:text-red-300">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </template>

                <div class="flex justify-end border-t border-gray-800 pt-4">
                    <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 transition-colors hover:bg-amber-400">
                        Save mapping
                    </button>
                </div>
            </form>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const mappingState = {{ Illuminate\Support\Js::from($batteryModels->mapWithKeys(fn ($batteryModel) => [
                $batteryModel->id => $batteryModel->inventoryItems->map(fn ($inventoryItem) => [
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity_per_battery' => $inventoryItem->pivot->quantity_per_battery,
                ])->values(),
            ])) }};

            document.querySelectorAll('[data-battery-model]').forEach((form) => {
                const modelId = form.dataset.batteryModel;
                const container = form.querySelector('[data-component-container]');
                const template = form.querySelector('[data-component-template]');
                const emptyMessage = form.querySelector('[data-empty-message]');
                let componentIndex = 0;

                const addComponent = (component = {}) => {
                    const row = template.content.cloneNode(true);
                    const itemSelect = row.querySelector('[data-field="inventory_item_id"]');
                    const quantityInput = row.querySelector('[data-field="quantity_per_battery"]');

                    itemSelect.name = `components[${componentIndex}][inventory_item_id]`;
                    quantityInput.name = `components[${componentIndex}][quantity_per_battery]`;
                    itemSelect.value = component.inventory_item_id ?? '';
                    quantityInput.value = component.quantity_per_battery ?? '';
                    componentIndex += 1;
                    container.append(row);
                    emptyMessage.hidden = container.children.length > 0;
                };

                const currentComponents = mappingState[modelId] ?? [];
                currentComponents.forEach(addComponent);
                emptyMessage.hidden = currentComponents.length > 0;

                if (currentComponents.length === 0 && !form.querySelector('[data-add-component]').disabled) {
                    addComponent();
                    emptyMessage.hidden = false;
                }

                form.addEventListener('click', (event) => {
                    if (event.target.closest('[data-add-component]')) {
                        addComponent();
                    }

                    const removeButton = event.target.closest('[data-remove-component]');
                    if (removeButton) {
                        removeButton.closest('.grid').remove();
                        emptyMessage.hidden = container.children.length > 0 && currentComponents.length > 0;
                    }
                });
            });
        })();
    </script>
@endpush
