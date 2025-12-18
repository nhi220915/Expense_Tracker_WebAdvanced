<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - Đổi name từ 'dashboard.index' thành 'dashboard'
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    
    // Incomes
    Route::get('/incomes', [IncomeController::class, 'index'])->name('incomes.index');
    Route::post('/incomes', [IncomeController::class, 'store'])->name('incomes.store');
    Route::put('/incomes/{income}', [IncomeController::class, 'update'])->name('incomes.update');
    Route::delete('/incomes/{income}', [IncomeController::class, 'destroy'])->name('incomes.destroy');
    
    // Budgets
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    
    // Expense Categories
    Route::post('/expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
    Route::put('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
    Route::delete('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Budgets
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::put('/budgets/update-limit', [BudgetController::class, 'updateLimit'])->name('budgets.update-limit');
    Route::put('/budgets/update-allocation', [BudgetController::class, 'updateAllocation'])->name('budgets.update-allocation');
});

    // Slack
    Route::get('/test-slack', function () {
        Log::critical('Đây là một thông báo lỗi thử nghiệm từ Laravel!');
        return 'Đã gửi thông báo về Slack!';
    });

     // Route này sẽ kích hoạt cả Slack và Sentry cùng lúc
    Route::get('/test-monitor', function () {
    // 1. Gửi thông báo nhanh qua Slack
        Log::critical('Hệ thống giám sát: Phát hiện lỗi thử nghiệm!');

    // 2. Tạo lỗi thực tế để Sentry bắt được
        throw new \Exception('Sentry Test Error: Hệ thống đã kết nối thành công!');
    });
    

require __DIR__.'/auth.php';