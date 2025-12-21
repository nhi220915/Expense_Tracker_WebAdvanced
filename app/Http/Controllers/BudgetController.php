<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    protected BudgetService $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate(BudgetService::validationRules());

        $this->budgetService->createOrUpdate(Auth::user(), $validated);

        return redirect()->route('expenses.index', [
            'month' => sprintf('%d-%02d', $validated['year'], $validated['month'])
        ])->with('success', 'Budget set successfully!');
    }

    public function updateLimit(Request $request)
    {
        $validated = $request->validate(BudgetService::updateLimitValidationRules());

        [$year, $month] = explode('-', $validated['month']);

        try {
            $this->budgetService->updateOverallLimit(
                Auth::user(),
                $validated['overall_limit'],
                (int) $year,
                (int) $month
            );

            return redirect()->route('expenses.index', ['month' => $validated['month']])
                ->with('success', 'Budget limit updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updateAllocation(Request $request)
    {
        $validated = $request->validate(BudgetService::updateAllocationValidationRules());

        [$year, $month] = explode('-', $validated['month']);

        try {
            $this->budgetService->updateAllocation(
                Auth::user(),
                $validated['percentages'],
                (int) $year,
                (int) $month
            );

            return redirect()->route('expenses.index', ['month' => $validated['month']])
                ->with('success', 'Budget allocation updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}