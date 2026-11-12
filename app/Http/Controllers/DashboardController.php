<?php

namespace App\Http\Controllers;

use App\Models\BatteryModel;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\CashTransactionItem;
use App\Models\InventoryItem;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $canAccessCash = $user->canAccessPage('cash');
        $lowStockQuery = InventoryItem::query()
            ->whereColumn('quantity_on_hand', '<=', 'reorder_point');

        $monthlyBatterySales = $canAccessCash
            ? CashTransactionItem::query()
                ->select('item_name')
                ->selectRaw('SUM(cash_transaction_items.quantity) as quantity_sold')
                ->whereHas('cashTransaction', function (Builder $query) use ($monthStart, $monthEnd): void {
                    $query->where('type', 'cash_in')
                        ->whereBetween('transaction_date', [$monthStart, $monthEnd]);
                })
                ->groupBy('item_name')
                ->pluck('quantity_sold', 'item_name')
            : collect();

        return view('dashboard.index', [
            'canAccessCash' => $canAccessCash,
            'cashAccounts' => $canAccessCash
                ? CashAccount::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->orderBy('id')
                    ->get(['id', 'name'])
                : collect(),
            'cashAccountSummaries' => $canAccessCash
                ? CashAccount::query()
                    ->withSum([
                        'transactions as cash_in_total' => fn (Builder $query): Builder => $query->where('type', 'cash_in'),
                    ], 'amount')
                    ->withSum([
                        'transactions as cash_out_total' => fn (Builder $query): Builder => $query->where('type', 'cash_out'),
                    ], 'amount')
                    ->orderBy('name')
                    ->orderBy('id')
                    ->get(['id', 'name', 'role', 'is_active'])
                : collect(),
            'dueInvestments' => Investment::query()
                ->where('status', 'active')
                ->whereDate('maturity_date', '<=', $now->copy()->addDays(3)->toDateString())
                ->orderBy('maturity_date')
                ->orderBy('id')
                ->get(['id', 'investor_name', 'principal_amount', 'maturity_date']),
            'lowStockCount' => (clone $lowStockQuery)->count(),
            'lowStockItems' => $lowStockQuery
                ->orderBy('quantity_on_hand')
                ->orderBy('name')
                ->orderBy('id')
                ->limit(6)
                ->get(['id', 'sku', 'name', 'category', 'quantity_on_hand', 'unit', 'reorder_point']),
            'batteryOptions' => $canAccessCash
                ? BatteryModel::query()
                    ->whereHas('inventoryItems')
                    ->orderBy('name')
                    ->orderBy('id')
                    ->get(['id', 'name'])
                : collect(),
            'batterySales' => $canAccessCash
                ? BatteryModel::query()
                    ->whereHas('inventoryItems')
                    ->orderBy('name')
                    ->orderBy('id')
                    ->get(['name'])
                    ->map(fn (BatteryModel $batteryModel): array => [
                        'name' => $batteryModel->name,
                        'quantity' => (float) ($monthlyBatterySales[$batteryModel->name] ?? 0),
                    ])
                : collect(),
            'salesMonth' => $now->format('F Y'),
            'recentTransactions' => $canAccessCash
                ? CashTransaction::query()
                    ->with([
                        'cashAccount:id,name',
                        'items:id,cash_transaction_id,inventory_item_id,item_name,quantity',
                    ])
                    ->orderByDesc('transaction_date')
                    ->orderByDesc('id')
                    ->limit(4)
                    ->get()
                : collect(),
            'inventoryItems' => $canAccessCash
                ? InventoryItem::query()
                    ->orderBy('name')
                    ->orderBy('id')
                    ->get(['id', 'sku', 'name', 'quantity_on_hand', 'unit'])
                : collect(),
        ]);
    }
}
