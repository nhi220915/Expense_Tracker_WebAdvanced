<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->input('month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Get expenses for selected month
        $expenses = Expense::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'desc')
            ->get();

        return view('expenses.index', compact('expenses'));
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