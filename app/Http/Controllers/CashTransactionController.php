<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashTransactionController extends Controller
{
    public function create(): View
    {
        return view('cash.transactions.form', [
            'cashTransaction' => null,
            'cashAccounts' => CashAccount::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'is_active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->transactionRules());
        $validated['recorded_by_user_id'] = $request->user()->id;

        CashTransaction::create($validated);

        return redirect()
            ->route('cash.index')
            ->with('success', 'Cash transaction recorded.');
    }

    public function edit(CashTransaction $cashTransaction): View|RedirectResponse
    {
        if ($cashTransaction->expense()->exists()) {
            return redirect()
                ->route('expenses.edit', $cashTransaction->expense)
                ->with('warning', 'Edit this linked transaction through the Expenses module.');
        }

        if ($cashTransaction->items()->exists()) {
            return redirect()
                ->route('cash.index')
                ->with('warning', 'Capital-flow transactions with item details cannot be edited from this form.');
        }

        return view('cash.transactions.form', [
            'cashTransaction' => $cashTransaction,
            'cashAccounts' => CashAccount::query()
                ->where(function (Builder $query) use ($cashTransaction): void {
                    $query->where('is_active', true)
                        ->orWhereKey($cashTransaction->cash_account_id);
                })
                ->orderBy('name')
                ->get(['id', 'name', 'is_active']),
        ]);
    }

    public function update(Request $request, CashTransaction $cashTransaction): RedirectResponse
    {
        abort_if($cashTransaction->expense()->exists(), 403, 'Linked expense transactions must be edited through the Expenses module.');
        abort_if($cashTransaction->items()->exists(), 403, 'Capital-flow transactions with item details cannot be edited from this form.');

        $cashTransaction->update($request->validate($this->transactionRules($cashTransaction)));

        return redirect()
            ->route('cash.index')
            ->with('success', 'Cash transaction updated.');
    }

    /**
     * Get the validation rules for creating or updating a cash transaction.
     *
     * @return array<string, array<int, mixed>|string>
     */
    private function transactionRules(?CashTransaction $cashTransaction = null): array
    {
        $accountRule = Rule::exists('cash_accounts', 'id')
            ->where(function (QueryBuilder $query) use ($cashTransaction): void {
                $query->where('is_active', true);

                if ($cashTransaction !== null) {
                    $query->orWhere('id', $cashTransaction->cash_account_id);
                }
            });

        return [
            'cash_account_id' => ['required', 'integer', $accountRule],
            'type' => ['required', Rule::in(['cash_in', 'cash_out'])],
            'amount' => ['required', 'numeric', 'min:0.01', 'decimal:0,2', 'max:9999999999.99'],
            'description' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
