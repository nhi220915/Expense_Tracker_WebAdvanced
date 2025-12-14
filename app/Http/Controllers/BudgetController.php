<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'limit' => 'required|numeric|min:1',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        // Check if budget already exists for this category, month, year
        $existingBudget = Budget::where('user_id', Auth::id())
            ->where('expense_category_id', $validated['expense_category_id'])
            ->where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->first();

        if ($existingBudget) {
            // Update existing budget
            $existingBudget->update(['limit' => $validated['limit']]);
        } else {
            // Create new budget
            Budget::create([
                'user_id' => Auth::id(),
                'expense_category_id' => $validated['expense_category_id'],
                'limit' => $validated['limit'],
                'month' => $validated['month'],
                'year' => $validated['year'],
            ]);
        }

        return redirect()->route('expenses.index', [
            'month' => sprintf('%d-%02d', $validated['year'], $validated['month'])
        ])->with('success', 'Budget set successfully!');
    }

    public function updateLimit(Request $request)
    {
        $validated = $request->validate([
            'overall_limit' => 'required|numeric|min:100',
            'month' => 'required|string',
        ]);

        [$year, $month] = explode('-', $validated['month']);
        
        // Get all budgets for this month
        $budgets = Budget::where('user_id', Auth::id())
            ->where('year', $year)
            ->where('month', (int)$month)
            ->get();

        // Calculate current total and ratio
        $currentTotal = $budgets->sum('limit');
        $newTotal = $validated['overall_limit'];
        
        // If no budgets exist, create default budgets for all user categories
        if ($budgets->isEmpty()) {
            // Get all expense categories for the current user
            $categories = ExpenseCategory::where('user_id', Auth::id())->get();
            
            if ($categories->isEmpty()) {
                return redirect()->back()->with('error', 'Please create expense categories first.');
            }
            
            // Distribute the overall limit equally among all categories
            $limitPerCategory = round($newTotal / $categories->count(), 2);
            $totalDistributed = 0;
            
            foreach ($categories as $index => $category) {
                // For the last category, use the remainder to ensure exact total
                if ($index === $categories->count() - 1) {
                    $limit = round($newTotal - $totalDistributed, 2);
                } else {
                    $limit = $limitPerCategory;
                    $totalDistributed += $limit;
                }
                
                Budget::create([
                    'user_id' => Auth::id(),
                    'expense_category_id' => $category->id,
                    'limit' => $limit,
                    'month' => (int)$month,
                    'year' => $year,
                ]);
            }
        } else {
            // Update existing budgets proportionally
            $ratio = $currentTotal > 0 ? $newTotal / $currentTotal : 1;
            foreach ($budgets as $budget) {
                $budget->update(['limit' => round($budget->limit * $ratio, 2)]);
            }
        }

        return redirect()->route('expenses.index', ['month' => $validated['month']])
            ->with('success', 'Budget limit updated successfully!');
    }

    public function updateAllocation(Request $request)
    {
        $validated = $request->validate([
            'percentages' => 'required|array',
            'month' => 'required|string',
        ]);

        [$year, $month] = explode('-', $validated['month']);
        $percentages = $validated['percentages'];
        
        // Validate total is 100%
        $total = array_sum($percentages);
        if ($total != 100) {
            return redirect()->back()->with('error', 'Total allocation must equal 100%');
        }

        // Get overall limit from existing budgets
        $existingBudgets = Budget::where('user_id', Auth::id())
            ->where('year', $year)
            ->where('month', (int)$month)
            ->get();
        
        $overallLimit = $existingBudgets->sum('limit');
        
        // If no budgets exist, use a default
        if ($overallLimit == 0) {
            $overallLimit = 2000; // Default
        }

        // Get expense categories - FIX: Filter by user_id to prevent getting other users' categories
        $categories = ExpenseCategory::where('user_id', Auth::id())
            ->whereIn('name', array_keys($percentages))
            ->get();

        // Get category IDs that are in the allocation
        $allocatedCategoryIds = $categories->pluck('id')->toArray();

        // Update or create budgets for allocated categories
        foreach ($categories as $category) {
            $percentage = $percentages[$category->name] ?? 0;
            $limit = ($percentage / 100) * $overallLimit;

            Budget::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'expense_category_id' => $category->id,
                    'year' => $year,
                    'month' => (int)$month,
                ],
                ['limit' => round($limit, 2)]
            );
        }

        // FIX: Delete budgets for categories not in the allocation to prevent duplicates
        Budget::where('user_id', Auth::id())
            ->where('year', $year)
            ->where('month', (int)$month)
            ->whereNotIn('expense_category_id', $allocatedCategoryIds)
            ->delete();

        return redirect()->route('expenses.index', ['month' => $validated['month']])
            ->with('success', 'Budget allocation updated successfully!');
    }
}