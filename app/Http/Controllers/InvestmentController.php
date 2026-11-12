<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestmentRequest;
use App\Http\Requests\SettleInvestmentRequest;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function index(): View
    {
        return view('investments.index', [
            'investments' => Investment::query()
                ->with([
                    'initialCashTransaction.cashAccount',
                    'settlementCashTransaction.cashAccount',
                ])
                ->orderBy('status')
                ->orderBy('maturity_date')
                ->orderBy('id')
                ->get(),
            'activePrincipalTotal' => Investment::query()
                ->where('status', 'active')
                ->sum('principal_amount'),
            'activeInvestmentCount' => Investment::query()
                ->where('status', 'active')
                ->count(),
        ]);
    }

    public function create(): View
    {
        return view('investments.form', [
            'investment' => null,
            'cashAccounts' => CashAccount::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'is_active']),
        ]);
    }

    public function store(InvestmentRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $user): void {
            $cashTransaction = CashTransaction::create([
                'cash_account_id' => $validated['cash_account_id'],
                'recorded_by_user_id' => $user->id,
                'type' => 'cash_in',
                'amount' => $validated['principal_amount'],
                'description' => 'Investment from '.$validated['investor_name'],
                'note' => $validated['notes'] ?? null,
                'transaction_date' => $validated['received_date'],
            ]);

            Investment::create([
                'investor_name' => $validated['investor_name'],
                'contact_info' => $validated['contact_info'] ?? null,
                'principal_amount' => $validated['principal_amount'],
                'received_date' => $validated['received_date'],
                'maturity_date' => $validated['maturity_date'],
                'profit_share_percentage' => $validated['profit_share_percentage'],
                'notes' => $validated['notes'] ?? null,
                'initial_cash_transaction_id' => $cashTransaction->id,
            ]);
        });

        return redirect()
            ->route('investments.index')
            ->with('success', 'Investment recorded and cash balance updated.');
    }

    public function edit(Investment $investment): View
    {
        abort_unless($investment->status === 'active', 404);
        $investment->load('initialCashTransaction');

        return view('investments.form', [
            'investment' => $investment,
            'cashAccounts' => CashAccount::query()
                ->where(function ($query) use ($investment): void {
                    $query->where('is_active', true)
                        ->orWhereKey($investment->initialCashTransaction->cash_account_id);
                })
                ->orderBy('name')
                ->get(['id', 'name', 'is_active']),
        ]);
    }

    public function update(InvestmentRequest $request, Investment $investment): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $investment): void {
            $lockedInvestment = Investment::query()
                ->lockForUpdate()
                ->findOrFail($investment->id);

            if ($lockedInvestment->status !== 'active') {
                throw ValidationException::withMessages([
                    'investment' => 'A settled investment cannot be edited.',
                ]);
            }

            CashTransaction::query()
                ->whereKey($lockedInvestment->initial_cash_transaction_id)
                ->lockForUpdate()
                ->firstOrFail()
                ->update([
                    'cash_account_id' => $validated['cash_account_id'],
                    'amount' => $validated['principal_amount'],
                    'description' => 'Investment from '.$validated['investor_name'],
                    'note' => $validated['notes'] ?? null,
                    'transaction_date' => $validated['received_date'],
                ]);

            $lockedInvestment->update([
                'investor_name' => $validated['investor_name'],
                'contact_info' => $validated['contact_info'] ?? null,
                'principal_amount' => $validated['principal_amount'],
                'received_date' => $validated['received_date'],
                'maturity_date' => $validated['maturity_date'],
                'profit_share_percentage' => $validated['profit_share_percentage'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('investments.index')
            ->with('success', 'Investment updated and cash ledger synchronized.');
    }

    public function createSettlement(Investment $investment): View
    {
        abort_unless($investment->status === 'active', 404);
        $investment->load('initialCashTransaction.cashAccount');

        return view('investments.settle', [
            'investment' => $investment,
            'cashAccounts' => CashAccount::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function settle(
        SettleInvestmentRequest $request,
        Investment $investment,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $investment, $user): void {
            $lockedInvestment = Investment::query()
                ->lockForUpdate()
                ->findOrFail($investment->id);

            if ($lockedInvestment->status !== 'active') {
                throw ValidationException::withMessages([
                    'investment' => 'This investment has already been settled.',
                ]);
            }

            $totalSettlementCents = $this->moneyToCents($lockedInvestment->principal_amount)
                + $this->moneyToCents($validated['profit_paid']);

            if ($totalSettlementCents > 999_999_999_999) {
                throw ValidationException::withMessages([
                    'profit_paid' => 'The principal and profit total exceeds the supported cash transaction amount.',
                ]);
            }

            $cashTransaction = CashTransaction::create([
                'cash_account_id' => $validated['cash_account_id'],
                'recorded_by_user_id' => $user->id,
                'type' => 'cash_out',
                'amount' => $this->centsToMoney($totalSettlementCents),
                'description' => 'Investment settlement: '.$lockedInvestment->investor_name,
                'note' => 'Principal returned: Rs '.$this->centsToMoney(
                    $this->moneyToCents($lockedInvestment->principal_amount),
                ).'; profit paid: Rs '.$this->centsToMoney(
                    $this->moneyToCents($validated['profit_paid']),
                ),
                'transaction_date' => $validated['settlement_date'],
            ]);

            $lockedInvestment->update([
                'status' => 'settled',
                'profit_paid' => $validated['profit_paid'],
                'settlement_date' => $validated['settlement_date'],
                'settlement_cash_transaction_id' => $cashTransaction->id,
            ]);
        });

        return redirect()
            ->route('investments.index')
            ->with('success', 'Investment settled and cash balance updated.');
    }

    private function moneyToCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function centsToMoney(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
