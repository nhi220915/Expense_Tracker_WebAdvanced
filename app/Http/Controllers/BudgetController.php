<?php

namespace App\Http\Controllers;

use App\Models\Budget;
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
}