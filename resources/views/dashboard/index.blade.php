@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
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
                <!-- Row 1: Low Stock Alerts Header Banner -->
                <div class="rounded-2xl border p-5 {{ $lowStockCount > 0 ? 'border-red-900/50 bg-gradient-to-r from-red-950/30 to-amber-950/20' : 'border-emerald-900/50 bg-gradient-to-r from-emerald-950/20 to-gray-900' }}">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex h-3 w-3 relative">
                                @if ($lowStockCount > 0)
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                @endif
                                <span class="relative inline-flex rounded-full h-3 w-3 {{ $lowStockCount > 0 ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                            </span>
                            <h2 class="text-lg font-bold {{ $lowStockCount > 0 ? 'text-red-200' : 'text-emerald-200' }}">Critical Low Stock Inventory Watchlist</h2>
                        </div>
                        <span class="rounded-full border px-2.5 py-1 text-xs font-medium {{ $lowStockCount > 0 ? 'border-red-700/50 bg-red-900/60 text-red-200' : 'border-emerald-700/50 bg-emerald-900/40 text-emerald-200' }}">
                            {{ $lowStockCount > 0 ? $lowStockCount.' item'.($lowStockCount === 1 ? '' : 's').' need attention' : 'Stock levels healthy' }}
                        </span>
                    </div>
                    @if ($lowStockItems->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach ($lowStockItems as $item)
                                @php
                                    $stockBufferPercent = (float) $item->reorder_point > 0
                                        ? max(0, min(100, (float) $item->quantity_on_hand / (float) $item->reorder_point * 100))
                                        : 0;
                                    $stockIsCritical = $stockBufferPercent <= 50;
                                @endphp
                                <div class="flex flex-col justify-between rounded-xl border border-gray-800 bg-gray-900/80 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-200">{{ $item->name }}</p>
                                            <p class="mt-0.5 text-xs text-gray-400">{{ $item->sku }} · {{ $item->category }}</p>
                                        </div>
                                        <span class="whitespace-nowrap rounded border border-red-900 bg-red-950 px-2 py-0.5 font-mono text-xs text-red-400">
                                            {{ number_format((float) $item->quantity_on_hand, 3) }} {{ $item->unit }}
                                        </span>
                                    </div>
                                    <div class="mt-4">
                                        <div class="mb-1 flex justify-between text-xs text-gray-400">
                                            <span>On hand / reorder point</span>
                                            <span class="{{ $stockIsCritical ? 'text-red-400' : 'text-amber-400' }} font-medium">
                                                {{ number_format((float) $item->quantity_on_hand, 3) }} / {{ number_format((float) $item->reorder_point, 3) }} {{ $item->unit }}
                                            </span>
                                        </div>
                                        <div class="h-1.5 w-full rounded-full bg-gray-800">
                                            <div class="h-1.5 rounded-full {{ $stockIsCritical ? 'bg-red-500' : 'bg-amber-500' }}" style="width: {{ $stockBufferPercent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($lowStockCount > $lowStockItems->count())
                            <p class="mt-3 text-right text-xs text-gray-400">
                                Showing {{ $lowStockItems->count() }} of {{ $lowStockCount }} items.
                                <a href="{{ route('inventory.index') }}" class="font-semibold text-amber-400 hover:text-amber-300">View inventory</a>
                            </p>
                        @endif
                    @else
                        <p class="rounded-xl border border-emerald-900/50 bg-gray-900/60 p-4 text-sm text-emerald-200">
                            No inventory items are at or below their reorder point.
                            <a href="{{ route('inventory.index') }}" class="ml-1 font-semibold text-amber-400 hover:text-amber-300">Manage inventory</a>
                        </p>
                    @endif
                </div>

                <div>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Battery Type Sales Volume Breakdown ({{ $salesMonth }})</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach ($batterySales as $batterySale)
                            <div class="relative overflow-hidden rounded-2xl border border-gray-800 bg-gray-900 p-5">
                                <div class="absolute right-0 top-0 -mr-6 -mt-6 h-24 w-24 rounded-full bg-amber-500/5"></div>
                                <p class="text-xs font-medium text-gray-400">{{ $batterySale['name'] }}</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-2xl font-bold tracking-tight text-white">{{ number_format($batterySale['quantity'], 0) }}</span>
                                    <span class="text-xs text-gray-500">units sold</span>
                                </div>
                                <p class="mt-1 text-[11px] text-gray-500">Recorded battery sale quantity for {{ $salesMonth }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Cash Accounts & Held Capital Pipeline</h3>
                        <a href="{{ route('cash.index') }}" class="text-xs font-semibold text-amber-400 hover:text-amber-300">Manage accounts</a>
                    </div>
                    @if ($cashAccountSummaries->isEmpty())
                        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-5 text-sm text-gray-400">
                            No cash accounts have been set up.
                            <a href="{{ route('cash.accounts.create') }}" class="ml-1 font-semibold text-amber-400 hover:text-amber-300">Add a cash account</a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @foreach ($cashAccountSummaries as $cashAccount)
                                @php
                                    $cashInTotal = (float) ($cashAccount->cash_in_total ?? 0);
                                    $cashOutTotal = (float) ($cashAccount->cash_out_total ?? 0);
                                @endphp
                                <div class="rounded-2xl border border-gray-800 bg-gradient-to-b from-gray-900 to-gray-950 p-5 shadow-xl">
                                    <div class="flex items-center justify-between gap-3 border-b border-gray-800/60 pb-4">
                                        <div class="min-w-0">
                                            <h4 class="truncate text-sm font-bold text-white">{{ $cashAccount->name }}</h4>
                                            <p class="mt-1 text-xs text-gray-400">{{ $cashAccount->role ?: 'Cash account' }}</p>
                                        </div>
                                        <span class="whitespace-nowrap rounded-full border px-2 py-0.5 text-xs {{ $cashAccount->is_active ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400' : 'border-gray-700 bg-gray-800 text-gray-400' }}">
                                            {{ $cashAccount->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="space-y-3 py-4">
                                        <div>
                                            <p class="text-[11px] uppercase tracking-wide text-gray-400">Current account balance</p>
                                            <p class="mt-0.5 font-mono text-2xl font-bold {{ $cashInTotal >= $cashOutTotal ? 'text-emerald-400' : 'text-red-400' }}">
                                                Rs {{ number_format($cashInTotal - $cashOutTotal, 2) }}
                                            </p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                                            <div class="rounded-lg border border-gray-800 bg-gray-900/50 p-2">
                                                <span class="block text-[10px] uppercase text-gray-400">Cash in</span>
                                                <span class="font-mono font-semibold text-white">Rs {{ number_format($cashInTotal, 2) }}</span>
                                            </div>
                                            <div class="rounded-lg border border-gray-800 bg-gray-900/50 p-2">
                                                <span class="block text-[10px] uppercase text-gray-400">Cash out</span>
                                                <span class="font-mono font-semibold text-red-400">Rs {{ number_format($cashOutTotal, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Row 4: Transaction Entry Console & Real-time Ledger Logs -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Posting Console -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 h-fit">
                        <div class="flex items-center space-x-2 pb-3 border-b border-gray-800 mb-4">
                            <i class="fas fa-sliders-h text-amber-500"></i>
                            <h4 class="font-bold text-white text-sm">Post New Capital Flow Activity</h4>
                        </div>
                        @if ($cashAccounts->isEmpty())
                            <div class="space-y-3 rounded-lg border border-gray-800 bg-gray-950 p-4 text-center">
                                <p class="text-xs text-gray-400">Add an active cash account before recording activity.</p>
                                <a href="{{ route('cash.accounts.create') }}" class="inline-flex rounded-lg bg-amber-500 px-3 py-2 text-xs font-bold text-gray-950 hover:bg-amber-400">Add Cash Account</a>
                            </div>
                        @else
                            @php
                                $capitalFlowFormState = [
                                    'type' => old('type', 'cash_in'),
                                    'batteryItems' => is_array(old('battery_items')) ? old('battery_items') : [],
                                    'inventoryItems' => is_array(old('inventory_items')) ? old('inventory_items') : [],
                                    'hasBatteryOptions' => $batteryOptions->isNotEmpty(),
                                ];
                            @endphp
                            <form
                                id="capital-flow-form"
                                method="POST"
                                action="{{ route('dashboard.capital-flows.store') }}"
                                class="space-y-4"
                            >
                                @csrf
                                <div>
                                    <label for="capital-flow-account" class="mb-1 block text-xs font-semibold text-gray-400">Target Account</label>
                                    <select id="capital-flow-account" name="cash_account_id" required class="w-full rounded-lg border border-gray-800 bg-gray-950 p-2.5 text-sm text-gray-200 focus:border-amber-500 focus:outline-none">
                                        <option value="">Select an account</option>
                                        @foreach ($cashAccounts as $account)
                                            <option value="{{ $account->id }}" @selected((string) old('cash_account_id') === (string) $account->id)>{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('cash_account_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="capital-flow-type" class="mb-1 block text-xs font-semibold text-gray-400">Transaction Category</label>
                                    <select id="capital-flow-type" name="type" required class="w-full rounded-lg border border-gray-800 bg-gray-950 p-2.5 text-sm text-gray-200 focus:border-amber-500 focus:outline-none">
                                        <option value="cash_in" @selected($capitalFlowFormState['type'] === 'cash_in')>Credit: Battery Pack Unit Sale</option>
                                        <option value="cash_out" @selected($capitalFlowFormState['type'] === 'cash_out') @disabled($inventoryItems->isEmpty())>Debit: Supplier Inventory Purchase</option>
                                    </select>
                                    @error('type')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                                </div>

                                <div id="battery-items-section" class="space-y-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <h5 class="text-xs font-semibold text-gray-300">Battery selections</h5>
                                            <p class="mt-1 text-[10px] text-gray-500">Mapped components are deducted from inventory when sold.</p>
                                        </div>
                                        @if ($batteryOptions->isNotEmpty())
                                            <button type="button" data-add-item="battery" class="rounded border border-gray-700 px-2.5 py-1.5 text-[10px] font-semibold text-amber-300 hover:bg-gray-800">Add Battery</button>
                                        @endif
                                    </div>
                                    @if ($batteryOptions->isNotEmpty())
                                        <div id="battery-items-container" class="space-y-2"></div>
                                        <template id="battery-item-template">
                                            <div class="grid grid-cols-[minmax(0,1fr)_5rem_auto] items-end gap-2">
                                                <div>
                                                    <label for="battery-model-__INDEX__" class="mb-1 block text-[10px] font-medium text-gray-500">Battery</label>
                                                    <select id="battery-model-__INDEX__" name="battery_items[__INDEX__][battery_model_id]" required class="w-full rounded-lg border border-gray-800 bg-gray-950 p-2 text-xs text-gray-200 focus:border-amber-500 focus:outline-none">
                                                        <option value="">Choose model</option>
                                                        @foreach ($batteryOptions as $batteryOption)
                                                            <option value="{{ $batteryOption->id }}">{{ $batteryOption->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="battery-quantity-__INDEX__" class="mb-1 block text-[10px] font-medium text-gray-500">Qty</label>
                                                    <input id="battery-quantity-__INDEX__" name="battery_items[__INDEX__][quantity]" type="number" min="1" max="999999" step="1" required class="w-full rounded-lg border border-gray-800 bg-gray-950 p-2 text-xs text-gray-200 focus:border-amber-500 focus:outline-none">
                                                </div>
                                                <button type="button" data-remove-item aria-label="Remove battery selection" class="mb-0.5 rounded border border-gray-700 px-2 py-2 text-xs text-gray-400 hover:border-red-700 hover:text-red-300"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </template>
                                    @else
                                        <p class="rounded-lg border border-amber-900/60 bg-amber-950/30 p-3 text-xs text-amber-200">
                                            Configure component mappings before recording battery sales.
                                            <a href="{{ route('inventory.battery-models.index') }}" class="font-semibold underline">Map battery models</a>
                                        </p>
                                    @endif
                                    @error('battery_items')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                                </div>

                                <div id="inventory-items-section" class="space-y-3" hidden>
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <h5 class="text-xs font-semibold text-gray-300">Supplier items received</h5>
                                            <p class="mt-1 text-[10px] text-gray-500">Quantities will be added to inventory.</p>
                                        </div>
                                        @unless ($inventoryItems->isEmpty())
                                            <button type="button" data-add-item="inventory" class="rounded border border-gray-700 px-2.5 py-1.5 text-[10px] font-semibold text-amber-300 hover:bg-gray-800">Add Item</button>
                                        @endunless
                                    </div>
                                    @if ($inventoryItems->isEmpty())
                                        <p class="rounded-lg border border-amber-900/60 bg-amber-950/30 p-3 text-xs text-amber-200">Add inventory items before recording a supplier receipt.</p>
                                    @else
                                        <div id="inventory-items-container" class="space-y-2"></div>
                                        <template id="inventory-item-template">
                                            <div class="grid grid-cols-[minmax(0,1fr)_5rem_auto] items-end gap-2">
                                                <div>
                                                    <label for="inventory-item-__INDEX__" class="mb-1 block text-[10px] font-medium text-gray-500">Inventory item</label>
                                                    <select id="inventory-item-__INDEX__" name="inventory_items[__INDEX__][inventory_item_id]" required class="w-full rounded-lg border border-gray-800 bg-gray-950 p-2 text-xs text-gray-200 focus:border-amber-500 focus:outline-none">
                                                        <option value="">Choose item</option>
                                                        @foreach ($inventoryItems as $inventoryItem)
                                                            <option value="{{ $inventoryItem->id }}">{{ $inventoryItem->sku }} — {{ $inventoryItem->name }} ({{ number_format((float) $inventoryItem->quantity_on_hand, 3) }} {{ $inventoryItem->unit }} on hand)</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="inventory-quantity-__INDEX__" class="mb-1 block text-[10px] font-medium text-gray-500">Qty</label>
                                                    <input id="inventory-quantity-__INDEX__" name="inventory_items[__INDEX__][quantity]" type="number" min="0.001" max="999999999.999" step="0.001" required class="w-full rounded-lg border border-gray-800 bg-gray-950 p-2 text-xs text-gray-200 focus:border-amber-500 focus:outline-none">
                                                </div>
                                                <button type="button" data-remove-item aria-label="Remove inventory item" class="mb-0.5 rounded border border-gray-700 px-2 py-2 text-xs text-gray-400 hover:border-red-700 hover:text-red-300"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </template>
                                        @error('inventory_items')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                                    @endif
                                </div>

                                <div>
                                    <label for="capital-flow-amount" class="mb-1 block text-xs font-semibold text-gray-400">Capital Value Amount (Rs)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-sm text-gray-500">Rs</span>
                                        <input id="capital-flow-amount" name="amount" type="number" min="0.01" max="9999999999.99" step="0.01" value="{{ old('amount') }}" required class="w-full rounded-lg border border-gray-800 bg-gray-950 py-2 pl-9 pr-3 font-mono text-sm text-gray-200 focus:border-amber-500 focus:outline-none">
                                    </div>
                                    @error('amount')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="capital-flow-date" class="mb-1 block text-xs font-semibold text-gray-400">Transaction date</label>
                                    <input id="capital-flow-date" name="transaction_date" type="date" value="{{ old('transaction_date', now()->toDateString()) }}" required class="w-full rounded-lg border border-gray-800 bg-gray-950 p-2.5 text-sm text-gray-200 focus:border-amber-500 focus:outline-none">
                                    @error('transaction_date')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="capital-flow-note" class="mb-1 block text-xs font-semibold text-gray-400">Note <span class="font-normal text-gray-500">(optional)</span></label>
                                    <textarea id="capital-flow-note" name="note" rows="2" maxlength="1000" class="w-full rounded-lg border border-gray-800 bg-gray-950 p-2.5 text-sm text-gray-200 focus:border-amber-500 focus:outline-none">{{ old('note') }}</textarea>
                                    @error('note')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                                </div>

                                <button type="submit" class="w-full rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-bold text-gray-950 shadow-lg shadow-amber-500/10 transition-all hover:bg-amber-400">
                                    Log Entry into Active Ledger
                                </button>
                            </form>
                            @push('scripts')
                                <script>
                                    (() => {
                                        const form = document.getElementById('capital-flow-form');
                                        const type = document.getElementById('capital-flow-type');
                                        const batterySection = document.getElementById('battery-items-section');
                                        const inventorySection = document.getElementById('inventory-items-section');
                                        const batteryContainer = document.getElementById('battery-items-container');
                                        const inventoryContainer = document.getElementById('inventory-items-container');
                                        const state = {{ Illuminate\Support\Js::from($capitalFlowFormState) }};
                                        let batteryIndex = 0;
                                        let inventoryIndex = 0;

                                        const addItem = (kind, values = {}) => {
                                            const isBattery = kind === 'battery';
                                            const container = isBattery ? batteryContainer : inventoryContainer;
                                            const template = document.getElementById(`${kind}-item-template`);

                                            if (!container || !template) {
                                                return;
                                            }

                                            const index = isBattery ? batteryIndex++ : inventoryIndex++;
                                            const row = template.content.cloneNode(true);

                                            row.querySelectorAll('[name], [id], label[for]').forEach((element) => {
                                                ['name', 'id', 'for'].forEach((attribute) => {
                                                    if (element.hasAttribute(attribute)) {
                                                        element.setAttribute(attribute, element.getAttribute(attribute).replaceAll('__INDEX__', index));
                                                    }
                                                });
                                            });

                                            const fields = row.querySelectorAll('select, input[type="number"]');

                                            if (isBattery) {
                                                fields[0].value = values.battery_model_id ?? '';
                                                fields[1].value = values.quantity ?? 1;
                                            } else {
                                                fields[0].value = values.inventory_item_id ?? '';
                                                fields[1].value = values.quantity ?? '';
                                            }

                                            container.append(row);
                                        };

                                        const updateSections = () => {
                                            const showBatteries = type.value === 'cash_in';
                                            batterySection.hidden = !showBatteries;
                                            inventorySection.hidden = showBatteries;

                                            [batterySection, inventorySection].forEach((section) => {
                                                section.querySelectorAll('select, input').forEach((input) => {
                                                    input.disabled = section.hidden;
                                                });
                                            });
                                        };

                                        const initialType = ['cash_in', 'cash_out'].includes(state.type) ? state.type : 'cash_in';
                                        type.value = initialType;
                                        if (state.hasBatteryOptions) {
                                            (state.batteryItems.length ? state.batteryItems : [{}]).forEach((item) => addItem('battery', item));
                                        }
                                        (state.inventoryItems.length ? state.inventoryItems : [{}]).forEach((item) => addItem('inventory', item));
                                        updateSections();

                                        form.addEventListener('change', (event) => {
                                            if (event.target === type) {
                                                updateSections();
                                            }
                                        });

                                        form.addEventListener('click', (event) => {
                                            const addButton = event.target.closest('[data-add-item]');
                                            const removeButton = event.target.closest('[data-remove-item]');

                                            if (addButton) {
                                                const container = addButton.dataset.addItem === 'battery' ? batteryContainer : inventoryContainer;
                                                const limit = addButton.dataset.addItem === 'battery' ? 20 : 50;

                                                if (container && container.children.length < limit) {
                                                    addItem(addButton.dataset.addItem);
                                                }
                                            }

                                            if (removeButton) {
                                                removeButton.closest('.grid').remove();
                                            }
                                        });
                                    })();
                                </script>
                            @endpush
                        @endif
                    </div>

                    <!-- Ledger Table Grid -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 lg:col-span-2 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between pb-3 border-b border-gray-800 mb-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-history text-amber-500"></i>
                                    <h4 class="font-bold text-white text-sm">Real-time Account Activity Logs</h4>
                                </div>
                                <span class="text-[11px] text-gray-400 font-mono">Showing last 4 entries</span>
                            </div>
                            
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="text-gray-400 border-b border-gray-800">
                                            <th class="pb-3 font-semibold">Timestamp</th>
                                            <th class="pb-3 font-semibold">Account</th>
                                            <th class="pb-3 font-semibold">Activity Details</th>
                                            <th class="pb-3 font-semibold text-right">Impact Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800/50">
                                        @forelse ($recentTransactions as $transaction)
                                            <tr>
                                                <td class="whitespace-nowrap py-3 font-mono text-gray-500">{{ $transaction->created_at->format('M d, H:i') }}</td>
                                                <td class="py-3 font-medium text-gray-200">{{ $transaction->cashAccount->name }}</td>
                                                <td class="py-3">
                                                    <span class="mr-2 rounded border px-2 py-0.5 text-[10px] font-medium {{ $transaction->type === 'cash_in' ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400' : 'border-red-500/20 bg-red-500/10 text-red-400' }}">{{ $transaction->type === 'cash_in' ? 'CASH IN' : 'CASH OUT' }}</span>
                                                    {{ $transaction->description }}
                                                    @if ($transaction->items->isNotEmpty())
                                                        <ul class="mt-1 space-y-0.5 text-[10px] text-gray-500">
                                                            @foreach ($transaction->items as $item)
                                                                <li>{{ number_format((float) $item->quantity, 3) }} × {{ $item->item_name }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap py-3 text-right font-mono font-bold {{ $transaction->type === 'cash_in' ? 'text-emerald-400' : 'text-red-400' }}">{{ $transaction->type === 'cash_in' ? '+' : '-' }}Rs {{ number_format((float) $transaction->amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-8 text-center text-xs text-gray-500">No capital-flow activity has been recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-800 text-center">
                            <a href="{{ route('cash.index') }}" class="text-xs font-semibold text-amber-500 hover:text-amber-400 flex items-center justify-center space-x-1">
                                <span>Access Full Operational Ledger Archive</span>
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
@endsection
