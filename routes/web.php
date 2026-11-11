<?php

use App\Http\Controllers\CapitalFlowController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkforceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('auth.login');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/capital-flows', [CapitalFlowController::class, 'store'])
        ->name('dashboard.capital-flows.store');
    Route::resource('inventory', InventoryController::class)
        ->parameters(['inventory' => 'inventoryItem']);
    Route::get('/inventory-movements/create', [InventoryController::class, 'createMovement'])
        ->name('inventory.movements.create');
    Route::post('/inventory-movements', [InventoryController::class, 'storeMovement'])
        ->name('inventory.movements.store');
    Route::get('/cash', [CashController::class, 'index'])->name('cash.index');
    Route::get('/cash/accounts/create', [CashAccountController::class, 'create'])->name('cash.accounts.create');
    Route::post('/cash/accounts', [CashAccountController::class, 'store'])->name('cash.accounts.store');
    Route::get('/cash/accounts/{cashAccount}/edit', [CashAccountController::class, 'edit'])->name('cash.accounts.edit');
    Route::put('/cash/accounts/{cashAccount}', [CashAccountController::class, 'update'])->name('cash.accounts.update');
    Route::get('/cash/transactions/create', [CashTransactionController::class, 'create'])->name('cash.transactions.create');
    Route::post('/cash/transactions', [CashTransactionController::class, 'store'])->name('cash.transactions.store');
    Route::get('/cash/transactions/{cashTransaction}/edit', [CashTransactionController::class, 'edit'])->name('cash.transactions.edit');
    Route::put('/cash/transactions/{cashTransaction}', [CashTransactionController::class, 'update'])->name('cash.transactions.update');
    Route::get('/workforce', [WorkforceController::class, 'index'])->name('workforce.index');
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
