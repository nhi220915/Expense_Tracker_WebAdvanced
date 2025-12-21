<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Budget;
use App\Services\ExpenseService;
use App\Services\ExpenseCategoryService;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;
    protected ExpenseCategoryService $categoryService;
    protected BudgetService $budgetService;

    public function __construct(
        ExpenseService $expenseService,
        ExpenseCategoryService $categoryService,
        BudgetService $budgetService
    ) {
        $this->expenseService = $expenseService;
        $this->categoryService = $categoryService;
        $this->budgetService = $budgetService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->input('month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Get expense categories using service
        $expenseCategories = $this->categoryService->listForUser($user);

        // Auto-create default categories if user has none
        if ($expenseCategories->isEmpty()) {
            $this->categoryService->createDefaultCategories($user);
            $expenseCategories = $this->categoryService->listForUser($user);
        }

        // Get expenses using service
        $expenses = $this->expenseService->listForUserByMonth($user, $selectedMonth);

        // Get budgets using service
        $budgets = $this->budgetService->listForUserByMonth($user, (int) $year, (int) $month);

        return view('expenses.index', compact('expenses', 'expenseCategories', 'budgets'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate(ExpenseService::validationRules());

        $this->expenseService->create(Auth::user(), $validated);

        return redirect()->route('expenses.index', ['month' => substr($validated['date'], 0, 7)])
            ->with('success', 'Expense added successfully!');
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate(ExpenseService::validationRules());

        try {
            $this->expenseService->update(Auth::user(), $expense, $validated);

            return redirect()->route('expenses.index', ['month' => substr($validated['date'], 0, 7)])
                ->with('success', 'Expense updated successfully!');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // Re-throw HTTP exceptions (like 403) as-is
            throw $e;
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    public function destroy(Expense $expense)
    {
        $month = $expense->date->format('Y-m');

        try {
            $this->expenseService->delete(Auth::user(), $expense);

            return redirect()->route('expenses.index', ['month' => $month])
                ->with('success', 'Expense deleted successfully!');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // Re-throw HTTP exceptions (like 403) as-is
            throw $e;
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    }
}