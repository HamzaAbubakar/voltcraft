<?php

use App\Http\Controllers\BatteryModelController;
use App\Http\Controllers\CapitalFlowController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeCompensationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDailyCompensationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectExpenseController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WorkforceController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/run_migration78432', function () {
    $status = Artisan::call('migrate --force');

    return response()->json(['status' => $status]);
})->name('health');

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('auth.login');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/capital-flows', [CapitalFlowController::class, 'store'])
        ->middleware('page.access:cash')
        ->name('dashboard.capital-flows.store');

    Route::middleware('page.access:inventory')->group(function () {
        Route::get('/inventory/battery-models', [BatteryModelController::class, 'index'])
            ->name('inventory.battery-models.index');
        Route::put('/inventory/battery-models/{batteryModel}', [BatteryModelController::class, 'update'])
            ->name('inventory.battery-models.update');
        Route::resource('inventory', InventoryController::class)
            ->parameters(['inventory' => 'inventoryItem']);
        Route::get('/inventory-movements/create', [InventoryController::class, 'createMovement'])
            ->name('inventory.movements.create');
        Route::post('/inventory-movements', [InventoryController::class, 'storeMovement'])
            ->name('inventory.movements.store');
    });

    Route::middleware('page.access:cash')->group(function () {
        Route::get('/cash', [CashController::class, 'index'])->name('cash.index');
        Route::get('/cash/accounts/create', [CashAccountController::class, 'create'])->name('cash.accounts.create');
        Route::post('/cash/accounts', [CashAccountController::class, 'store'])->name('cash.accounts.store');
        Route::get('/cash/accounts/{cashAccount}/edit', [CashAccountController::class, 'edit'])->name('cash.accounts.edit');
        Route::put('/cash/accounts/{cashAccount}', [CashAccountController::class, 'update'])->name('cash.accounts.update');
        Route::get('/cash/transactions/create', [CashTransactionController::class, 'create'])->name('cash.transactions.create');
        Route::post('/cash/transactions', [CashTransactionController::class, 'store'])->name('cash.transactions.store');
        Route::get('/cash/transactions/{cashTransaction}/edit', [CashTransactionController::class, 'edit'])->name('cash.transactions.edit');
        Route::put('/cash/transactions/{cashTransaction}', [CashTransactionController::class, 'update'])->name('cash.transactions.update');
    });

    Route::middleware('page.access:workforce')->group(function () {
        Route::get('/workforce', [WorkforceController::class, 'index'])->name('workforce.index');
        Route::resource('workforce/employees', EmployeeController::class)
            ->except(['show', 'destroy'])
            ->names('workforce.employees');
        Route::get('/workforce/compensations/create', [EmployeeCompensationController::class, 'create'])
            ->name('workforce.compensations.create');
        Route::post('/workforce/compensations', [EmployeeCompensationController::class, 'store'])
            ->name('workforce.compensations.store');
        Route::get('/workforce/compensations/{compensation}/edit', [EmployeeCompensationController::class, 'edit'])
            ->name('workforce.compensations.edit');
        Route::put('/workforce/compensations/{compensation}', [EmployeeCompensationController::class, 'update'])
            ->name('workforce.compensations.update');
        Route::get('/workforce/daily-compensations/create', [EmployeeDailyCompensationController::class, 'create'])
            ->name('workforce.daily-compensations.create');
        Route::post('/workforce/daily-compensations', [EmployeeDailyCompensationController::class, 'store'])
            ->name('workforce.daily-compensations.store');
        Route::get('/workforce/daily-compensations/{dailyCompensation}/edit', [EmployeeDailyCompensationController::class, 'edit'])
            ->name('workforce.daily-compensations.edit');
        Route::put('/workforce/daily-compensations/{dailyCompensation}', [EmployeeDailyCompensationController::class, 'update'])
            ->name('workforce.daily-compensations.update');
    });

    Route::middleware('page.access:expenses')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    });

    Route::middleware('page.access:projects')->group(function () {
        Route::resource('projects', ProjectController::class)->except(['destroy']);
        Route::post('/projects/{project}/complete', [ProjectController::class, 'complete'])
            ->name('projects.complete');
        Route::get('/projects/{project}/expenses/create', [ProjectExpenseController::class, 'create'])
            ->name('projects.expenses.create');
        Route::post('/projects/{project}/expenses', [ProjectExpenseController::class, 'store'])
            ->name('projects.expenses.store');
    });

    Route::middleware('page.access:investments')->group(function () {
        Route::get('/investments', [InvestmentController::class, 'index'])->name('investments.index');
        Route::get('/investments/create', [InvestmentController::class, 'create'])->name('investments.create');
        Route::post('/investments', [InvestmentController::class, 'store'])->name('investments.store');
        Route::get('/investments/{investment}/edit', [InvestmentController::class, 'edit'])->name('investments.edit');
        Route::put('/investments/{investment}', [InvestmentController::class, 'update'])->name('investments.update');
        Route::get('/investments/{investment}/settle', [InvestmentController::class, 'createSettlement'])
            ->name('investments.settlement.create');
        Route::post('/investments/{investment}/settle', [InvestmentController::class, 'settle'])
            ->name('investments.settlement.store');
    });

    Route::resource('users', UserManagementController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->middleware('admin');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
