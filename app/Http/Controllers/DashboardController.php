<?php

namespace App\Http\Controllers;

use App\Models\BatteryModel;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\CashTransactionItem;
use App\Models\InventoryItem;
use App\Models\Investment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(): View
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $canAccessCash = $user->canAccessPage('cash');
        $canAccessInventory = $user->canAccessPage('inventory');
        $canAccessInvestments = $user->canAccessPage('investments');
        $lowStockQuery = InventoryItem::query()
            ->whereColumn('quantity_on_hand', '<=', 'reorder_point');

        $monthlyBatterySales = $canAccessCash
            ? CashTransactionItem::query()
                ->whereNotNull('battery_model_id')
                ->select('battery_model_id')
                ->selectRaw('SUM(cash_transaction_items.quantity) as quantity_sold')
                ->whereHas('cashTransaction', function (Builder $query) use ($monthStart, $monthEnd): void {
                    $query->where('type', 'cash_in')
                        ->whereBetween('transaction_date', [$monthStart, $monthEnd]);
                })
                ->groupBy('battery_model_id')
                ->pluck('quantity_sold', 'battery_model_id')
            : collect();

        $dueInvestments = $canAccessInvestments
            ? Investment::query()
                ->where('status', 'active')
                ->whereDate('maturity_date', '<=', $now->copy()->addDays(3)->toDateString())
                ->orderBy('maturity_date')
                ->orderBy('id')
                ->get(['id', 'investor_name', 'principal_amount', 'maturity_date'])
            : collect();

        if ($canAccessInvestments && $dueInvestments->isNotEmpty()) {
            $this->notificationService->investmentDueAlert(
                $dueInvestments->map(fn (Investment $investment): array => [
                    'investor_name' => $investment->investor_name,
                    'maturity_date' => $investment->maturity_date->format('Y-m-d'),
                    'days_until_maturity' => (int) today()->diffInDays($investment->maturity_date, false),
                ])->toArray(),
            );
        }

        $lastDbBackupDownloadedAt = SystemSetting::get('last_db_backup_downloaded_at');
        $showDbBackupAlert = auth()->user()->is_admin
            && ($lastDbBackupDownloadedAt === null
                || now()->diffInDays($lastDbBackupDownloadedAt) >= 7);

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
            'dueInvestments' => $dueInvestments,
            'lowStockCount' => $canAccessInventory ? (clone $lowStockQuery)->count() : 0,
            'lowStockItems' => $canAccessInventory
                ? $lowStockQuery
                    ->orderBy('quantity_on_hand')
                    ->orderBy('name')
                    ->orderBy('id')
                    ->limit(6)
                    ->get(['id', 'sku', 'name', 'category', 'quantity_on_hand', 'unit', 'reorder_point'])
                : collect(),
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
                    ->get(['id', 'name'])
                    ->map(fn (BatteryModel $batteryModel): array => [
                        'name' => $batteryModel->name,
                        'quantity' => (float) ($monthlyBatterySales[$batteryModel->id] ?? 0),
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
            'inventoryItems' => $canAccessInventory
                ? InventoryItem::query()
                    ->orderBy('name')
                    ->orderBy('id')
                    ->limit(10)
                    ->get(['id', 'sku', 'name', 'quantity_on_hand', 'unit'])
                : collect(),
            'showDbBackupAlert' => $showDbBackupAlert,
        ]);
    }
}
