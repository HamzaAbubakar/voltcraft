<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\View\View;

class CashController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = $validated['search'] ?? '';

        $cashAccounts = CashAccount::query()
            ->withSum([
                'transactions as cash_in_total' => fn (Builder $query): Builder => $query->where('type', 'cash_in'),
            ], 'amount')
            ->withSum([
                'transactions as cash_out_total' => fn (Builder $query): Builder => $query->where('type', 'cash_out'),
            ], 'amount')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $cashInTotal = CashTransaction::query()->where('type', 'cash_in')->sum('amount');
        $cashOutTotal = CashTransaction::query()->where('type', 'cash_out')->sum('amount');

        $transactions = CashTransaction::query()
            ->with(['cashAccount:id,name', 'recordedBy:id,name', 'expense:id,cash_transaction_id'])
            ->withCount('items')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('cashAccount', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('cash.index', [
            'cashAccounts' => $cashAccounts,
            'cashInTotal' => $cashInTotal,
            'cashOutTotal' => $cashOutTotal,
            'cashHeldTotal' => $cashInTotal - $cashOutTotal,
            'transactions' => $transactions,
            'search' => $search,
        ]);
    }

    public function export(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cash_export.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date',
                'Account',
                'Type',
                'Amount',
                'Description',
                'Note',
            ]);

            foreach (CashTransaction::query()
                ->with('cashAccount:id,name')
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->cursor() as $transaction) {
                fputcsv($handle, [
                    $transaction->transaction_date->format('Y-m-d'),
                    $transaction->cashAccount->name,
                    $transaction->type,
                    number_format((float) $transaction->amount, 2),
                    $transaction->description,
                    $transaction->note ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
