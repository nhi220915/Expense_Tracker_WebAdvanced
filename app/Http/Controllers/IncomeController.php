<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Services\IncomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    protected IncomeService $incomeService;

    public function __construct(IncomeService $incomeService)
    {
        $this->incomeService = $incomeService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->input('month', date('Y-m'));

        // Get incomes using service
        $incomes = $this->incomeService->listForUserByMonth($user, $selectedMonth);

        return view('incomes.index', compact('incomes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(IncomeService::validationRules());

        $this->incomeService->create(Auth::user(), $validated);

        return redirect()->route('incomes.index', ['month' => substr($validated['date'], 0, 7)])
            ->with('success', 'Income added successfully!');
    }

    public function update(Request $request, Income $income)
    {
        $validated = $request->validate(IncomeService::validationRules());

        try {
            $this->incomeService->update(Auth::user(), $income, $validated);

            return redirect()->route('incomes.index', ['month' => substr($validated['date'], 0, 7)])
                ->with('success', 'Income updated successfully!');
        } catch (\Exception $e) {
            abort($e->getCode() === 403 ? 403 : 500, $e->getMessage());
        }
    }

    public function destroy(Income $income)
    {
        try {
            $this->incomeService->delete(Auth::user(), $income);

            return redirect()->back()
                ->with('success', 'Income deleted successfully!');
        } catch (\Exception $e) {
            abort($e->getCode() === 403 ? 403 : 500, $e->getMessage());
        }
    }
}