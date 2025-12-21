<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\IncomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes - Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes - Require authentication via Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/me', [AuthController::class, 'me']);

    // User info endpoint (legacy)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Expense routes
    Route::apiResource('expenses', ExpenseController::class);

    // Income routes  
    Route::apiResource('incomes', IncomeController::class);

    // Budget routes
    Route::apiResource('budgets', BudgetController::class);
    Route::put('/budgets/update-limit', [BudgetController::class, 'updateLimit']);
    Route::put('/budgets/update-allocation', [BudgetController::class, 'updateAllocation']);

    // Expense Category routes
    Route::apiResource('expense-categories', ExpenseCategoryController::class);
});

