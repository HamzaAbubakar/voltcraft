<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\CashTransactionItem;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();
        $lowStockQuery = InventoryItem::query()
            ->whereColumn('quantity_on_hand', '<=', 'reorder_point');

        $monthlyBatterySales = CashTransactionItem::query()
            ->select('item_name')
            ->selectRaw('SUM(cash_transaction_items.quantity) as quantity_sold')
            ->whereHas('cashTransaction', function (Builder $query) use ($monthStart, $monthEnd): void {
                $query->where('type', 'cash_in')
                    ->whereBetween('transaction_date', [$monthStart, $monthEnd]);
            })
            ->groupBy('item_name')
            ->pluck('quantity_sold', 'item_name');

        return view('dashboard.index', [
            'cashAccounts' => CashAccount::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name']),
            'cashAccountSummaries' => CashAccount::query()
                ->withSum([
                    'transactions as cash_in_total' => fn (Builder $query): Builder => $query->where('type', 'cash_in'),
                ], 'amount')
                ->withSum([
                    'transactions as cash_out_total' => fn (Builder $query): Builder => $query->where('type', 'cash_out'),
                ], 'amount')
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name', 'role', 'is_active']),
            'inventoryItems' => InventoryItem::query()
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'sku', 'name', 'quantity_on_hand', 'unit']),
            'lowStockCount' => (clone $lowStockQuery)->count(),
            'lowStockItems' => $lowStockQuery
                ->orderBy('quantity_on_hand')
                ->orderBy('name')
                ->orderBy('id')
                ->limit(6)
                ->get(['id', 'sku', 'name', 'category', 'quantity_on_hand', 'unit', 'reorder_point']),
            'batteryOptions' => CashTransactionItem::BATTERY_OPTIONS,
            'batterySales' => collect(CashTransactionItem::BATTERY_OPTIONS)
                ->map(fn (string $name): array => [
                    'name' => $name,
                    'quantity' => (float) ($monthlyBatterySales[$name] ?? 0),
                ]),
            'salesMonth' => $now->format('F Y'),
            'recentTransactions' => CashTransaction::query()
                ->with([
                    'cashAccount:id,name',
                    'items:id,cash_transaction_id,inventory_item_id,item_name,quantity',
                ])
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->limit(4)
                ->get(),
        ]);
    }
}
