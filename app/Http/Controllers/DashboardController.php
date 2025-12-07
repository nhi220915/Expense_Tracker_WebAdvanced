<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->input('month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Get total income and expense
        $totalIncome = Income::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $totalExpense = Expense::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        // Get expense by category - SỬA: chỉ rõ bảng cho user_id và date
        $expenseByCategory = Expense::where('expenses.user_id', $user->id)
            ->whereYear('expenses.date', $year)
            ->whereMonth('expenses.date', $month)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name as category', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name')
            ->pluck('total', 'category')
            ->toArray();

        // Get income by category
        $incomeByCategory = Income::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // Get budget progress
        $budgets = Budget::where('user_id', $user->id)
            ->where('year', $year)
            ->where('month', $month)
            ->with('category')
            ->get();

        $budgetProgress = [];
        foreach ($budgets as $budget) {
            $spent = Expense::where('user_id', $user->id)
                ->where('expense_category_id', $budget->expense_category_id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            $budgetProgress[] = [
                'category' => $budget->category->name,
                'limit' => $budget->limit,
                'spent' => $spent,
            ];
        }

        return view('dashboard.index', compact(
            'totalIncome',
            'totalExpense',
            'expenseByCategory',
            'incomeByCategory',
            'budgetProgress'
        ));
    }
}