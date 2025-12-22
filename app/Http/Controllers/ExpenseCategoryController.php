<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryController extends Controller
{
    protected ExpenseCategoryService $categoryService;

    public function __construct(ExpenseCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            ExpenseCategoryService::createValidationRules(Auth::id())
        );

        $this->categoryService->create(Auth::user(), $validated);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate(
            ExpenseCategoryService::updateValidationRules(Auth::id(), $expenseCategory->id)
        );

        try {
            $this->categoryService->update(Auth::user(), $expenseCategory, $validated);

            return redirect()->back()->with('success', 'Category updated successfully!');
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $this->categoryService->delete(Auth::user(), $expenseCategory);

            return redirect()->back()->with('success', 'Category deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}