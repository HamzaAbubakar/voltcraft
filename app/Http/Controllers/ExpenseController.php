<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\ExpenseDescription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'period' => ['nullable', Rule::in(['day', 'month'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $period = $validated['period'] ?? 'month';
        $selectedDate = $validated['date'] ?? now()->toDateString();
        $selectedMonth = $validated['month'] ?? now()->format('Y-m');
        $periodStart = $period === 'day' ? $selectedDate : "{$selectedMonth}-01";
        $periodEnd = $period === 'day'
            ? Carbon::parse($periodStart)->endOfDay()->toDateTimeString()
            : Carbon::parse($periodStart)->endOfMonth()->endOfDay()->toDateTimeString();

        $expenses = Expense::query()
            ->join('cash_transactions as transactions', 'transactions.id', '=', 'expenses.cash_transaction_id')
            ->whereBetween('transactions.transaction_date', [$periodStart, $periodEnd])
            ->select('expenses.*')
            ->with(['description', 'cashTransaction.cashAccount'])
            ->orderByDesc('transactions.transaction_date')
            ->orderByDesc('expenses.id')
            ->get();

        $expenseTotal = CashTransaction::query()
            ->where('type', 'cash_out')
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->whereHas('expense')
            ->sum('amount');

        $categoryBreakdown = Expense::query()
            ->join('cash_transactions as transactions', 'transactions.id', '=', 'expenses.cash_transaction_id')
            ->whereBetween('transactions.transaction_date', [$periodStart, $periodEnd])
            ->where('expenses.paid_for', 'Company')
            ->select('expenses.category')
            ->selectRaw('SUM(transactions.amount) as total')
            ->groupBy('expenses.category')
            ->orderBy('expenses.category')
            ->get();

        $salespersonBreakdown = Expense::query()
            ->join('cash_transactions as transactions', 'transactions.id', '=', 'expenses.cash_transaction_id')
            ->whereBetween('transactions.transaction_date', [$periodStart, $periodEnd])
            ->where('expenses.paid_for', '!=', 'Company')
            ->select('expenses.paid_for', 'expenses.category')
            ->selectRaw('SUM(transactions.amount) as total')
            ->groupBy('expenses.paid_for', 'expenses.category')
            ->orderBy('expenses.paid_for')
            ->orderBy('expenses.category')
            ->get();

        return view('expenses.index', [
            'expenses' => $expenses,
            'expenseDescriptions' => ExpenseDescription::query()->orderBy('name')->get(['id', 'name']),
            'expenseTotal' => $expenseTotal,
            'expenseCount' => $expenses->count(),
            'expenseAverage' => $expenses->isEmpty() ? 0 : $expenseTotal / $expenses->count(),
            'categoryBreakdown' => $categoryBreakdown,
            'salespersonBreakdown' => $salespersonBreakdown,
            'period' => $period,
            'selectedDate' => $selectedDate,
            'selectedMonth' => $selectedMonth,
        ]);
    }

    public function create(): View
    {
        return view('expenses.form', [
            'expense' => null,
            'cashAccounts' => CashAccount::query()->where('is_active', true)->orderBy('name')->get(),
            'expenseDescriptions' => ExpenseDescription::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->expenseRules());
        $descriptionName = trim($validated['description']);
        $transactionDate = $validated['transaction_date'];
        $userId = $request->user()->id;

        DB::transaction(function () use ($validated, $descriptionName, $transactionDate, $userId): void {
            $description = ExpenseDescription::query()->firstOrCreate(['name' => $descriptionName]);

            $cashTransaction = CashTransaction::create([
                'cash_account_id' => $validated['cash_account_id'],
                'recorded_by_user_id' => $userId,
                'type' => 'cash_out',
                'amount' => $validated['amount'],
                'description' => $description->name,
                'transaction_date' => $transactionDate,
            ]);

            Expense::create([
                'expense_description_id' => $description->id,
                'cash_transaction_id' => $cashTransaction->id,
                'created_by_user_id' => $userId,
                'category' => $validated['category'],
                'paid_for' => $validated['paid_for'],
            ]);
        });

        return redirect()
            ->route('expenses.index', ['period' => 'month', 'month' => Carbon::parse($transactionDate)->format('Y-m')])
            ->with('success', 'Expense recorded and cash balance updated.');
    }

    public function edit(Expense $expense): View
    {
        $expense->load(['description', 'cashTransaction']);

        return view('expenses.form', [
            'expense' => $expense,
            'cashAccounts' => CashAccount::query()
                ->where(function (Builder $query) use ($expense): void {
                    $query->where('is_active', true)
                        ->orWhereKey($expense->cashTransaction->cash_account_id);
                })
                ->orderBy('name')
                ->get(),
            'expenseDescriptions' => ExpenseDescription::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate($this->expenseRules($expense));
        $descriptionName = trim($validated['description']);
        $transactionDate = $validated['transaction_date'];

        DB::transaction(function () use ($expense, $validated, $descriptionName, $transactionDate): void {
            $description = ExpenseDescription::query()->firstOrCreate(['name' => $descriptionName]);

            $expense->cashTransaction()->update([
                'cash_account_id' => $validated['cash_account_id'],
                'amount' => $validated['amount'],
                'description' => $description->name,
                'transaction_date' => $transactionDate,
            ]);

            $expense->update([
                'expense_description_id' => $description->id,
                'category' => $validated['category'],
                'paid_for' => $validated['paid_for'],
            ]);
        });

        return redirect()
            ->route('expenses.index', ['period' => 'month', 'month' => Carbon::parse($transactionDate)->format('Y-m')])
            ->with('success', 'Expense updated and cash balance recalculated.');
    }

    /**
     * Get validation rules for a new or existing expense.
     *
     * @return array<string, array<int, mixed>>
     */
    private function expenseRules(?Expense $expense = null): array
    {
        $accountRule = Rule::exists('cash_accounts', 'id')
            ->where(function (QueryBuilder $query) use ($expense): void {
                $query->where('is_active', true);

                if ($expense !== null) {
                    $query->orWhere('id', $expense->cashTransaction->cash_account_id);
                }
            });

        return [
            'cash_account_id' => ['required', 'integer', $accountRule],
            'description' => ['required', 'string', 'max:255', 'regex:/\S/'],
            'category' => ['required', 'string', 'max:255'],
            'paid_for' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'decimal:0,2', 'max:9999999999.99'],
            'transaction_date' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
