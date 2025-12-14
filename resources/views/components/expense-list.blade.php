<div class="bg-white shadow-soft rounded-xl p-6 space-y-4">
    @include('partials.month-filter', ['filterId' => 'monthFilterSpending', 'selectedMonth' => request('month', date('Y-m')), 'tabName' => 'Spending'])
    
    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-gray-800">📜 Recent Expense History</h3>
            <div class="flex flex-wrap gap-2" id="expenseFilters">
                <button class="px-3 py-1 rounded-full border text-sm text-gray-700 bg-mint-light border-mint-medium" onclick="filterTransactions('All', this)">All</button>
                @foreach($expenseCategories ?? [] as $category)
                    <button class="px-3 py-1 rounded-full border text-sm text-gray-700 hover:bg-gray-100" onclick="filterTransactions('{{ $category->name }}', this)">{{ $category->name }}</button>
                @endforeach
            </div>
        </div>

        <ul class="divide-y divide-gray-100" id="recentExpenseList">
            @forelse($expenses ?? [] as $expense)
                <li class="grid grid-cols-[1fr_auto_auto] items-center gap-3 py-3" data-category="{{ $expense->category->name ?? $expense->category }}">
                    <div class="text-sm font-semibold text-gray-800">
                        {{ $expense->note ?: ($expense->category->name ?? $expense->category) }} ({{ $expense->category->name ?? $expense->category }})
                    </div>
                    <span class="text-sm font-semibold text-red-500">- ${{ number_format($expense->amount, 2) }}</span>
                    <span class="text-xs text-gray-500">{{ $expense->date->format('d/m/Y') }}</span>
                </li>
            @empty
                <li class="py-3 text-sm text-gray-500">No expenses found</li>
            @endforelse
        </ul>
    </div>
</div>