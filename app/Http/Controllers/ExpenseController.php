<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->input('month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Get expense categories for the current user
        $expenseCategories = ExpenseCategory::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        // Auto-create default categories if user has none
        if ($expenseCategories->isEmpty()) {
            $this->createDefaultCategories($user);
            $expenseCategories = ExpenseCategory::where('user_id', $user->id)
                ->orderBy('name')
                ->get();
        }

        // Get expenses for selected month
        $expenses = Expense::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'desc')
            ->get();

        // Get budgets for the selected month
        $budgets = Budget::where('user_id', $user->id)
            ->where('year', $year)
            ->where('month', (int)$month)  // FIX: Cast to integer to match BudgetController
            ->with('category')
            ->get();

        return view('expenses.index', compact('expenses', 'expenseCategories', 'budgets'));
    }

    /**
     * Create default categories for a user
     */
    private function createDefaultCategories($user)
    {
        $defaultCategories = [
            ['name' => 'Food', 'color' => '#00d1c1'],
            ['name' => 'Transport', 'color' => '#27ae60'],
            ['name' => 'Entertainment', 'color' => '#f39c12'],
            ['name' => 'Utilities', 'color' => '#e74c3c'],
            ['name' => 'Other', 'color' => '#00a896'],
        ];

        foreach ($defaultCategories as $category) {
            ExpenseCategory::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $category['name'],
                ],
                [
                    'color' => $category['color'],
                ]
            );
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        Expense::create([
            'user_id' => Auth::id(),
            'expense_category_id' => $validated['expense_category_id'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('expenses.index', ['month' => substr($validated['date'], 0, 7)])
            ->with('success', 'Expense added successfully!');
    }

    public function update(Request $request, Expense $expense)
    {
        // Check ownership
        if ($expense->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index', ['month' => substr($validated['date'], 0, 7)])
            ->with('success', 'Expense updated successfully!');
    }

    public function destroy(Expense $expense)
    {
        // Check ownership
        if ($expense->user_id !== Auth::id()) {
            abort(403);
        }

        $month = $expense->date->format('Y-m');
        $expense->delete();

        return redirect()->route('expenses.index', ['month' => $month])
            ->with('success', 'Expense deleted successfully!');
    }
}