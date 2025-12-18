<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->input('month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Get incomes for selected month
        $incomes = Income::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'desc')
            ->get();

        return view('incomes.index', compact('incomes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        Income::create([
            'user_id' => Auth::id(),
            'amount' => $validated['amount'],
            'category' => $validated['category'],
            'date' => $validated['date'],
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('incomes.index', ['month' => substr($validated['date'], 0, 7)])
            ->with('success', 'Income added successfully!');
    }
}