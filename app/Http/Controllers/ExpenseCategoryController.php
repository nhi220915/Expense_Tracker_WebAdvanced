<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,NULL,id,user_id,' . Auth::id(),
            'color' => 'nullable|string|max:7',
        ]);

        ExpenseCategory::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#00a896',
        ]);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        // Check ownership
        if ($expenseCategory->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id . ',id,user_id,' . Auth::id(),
            'color' => 'nullable|string|max:7',
        ]);

        $expenseCategory->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Check ownership
        if ($expenseCategory->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if category has expenses
        if ($expenseCategory->expenses()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with existing expenses!');
        }

        $expenseCategory->delete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }
}