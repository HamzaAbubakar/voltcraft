<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashAccountController extends Controller
{
    public function create(): View
    {
        return view('cash.accounts.form', ['cashAccount' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        CashAccount::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
        ]));

        return redirect()
            ->route('cash.index')
            ->with('success', 'Cash account added.');
    }

    public function edit(CashAccount $cashAccount): View
    {
        return view('cash.accounts.form', compact('cashAccount'));
    }

    public function update(Request $request, CashAccount $cashAccount): RedirectResponse
    {
        $cashAccount->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]));

        return redirect()
            ->route('cash.index')
            ->with('success', 'Cash account updated.');
    }
}
