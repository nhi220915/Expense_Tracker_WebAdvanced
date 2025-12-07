<div class="dashboard-block">
    <div class="report-section">
        @include('partials.month-filter', ['filterId' => 'monthFilterSpending', 'selectedMonth' => request('month', date('Y-m')), 'tabName' => 'Spending'])
        
        <div class="transaction-header-content">
            <h3>📜 Recent Expense History</h3>
            <div class="transaction-filters" id="expenseFilters">
                <button class="filter-button active" onclick="filterTransactions('All', this)">All</button>
                @foreach($expenseCategories ?? [] as $category)
                    <button class="filter-button" onclick="filterTransactions('{{ $category->name }}', this)">{{ $category->name }}</button>
                @endforeach
            </div>
        </div>
        <ul class="transaction-list" id="recentExpenseList">
            @forelse($expenses ?? [] as $expense)
                <li data-category="{{ $expense->category->name ?? $expense->category }}">
                    <div class="transaction-name-category">
                        <span class="name">{{ $expense->note ?: ($expense->category->name ?? $expense->category) }} ({{ $expense->category->name ?? $expense->category }})</span>
                    </div>
                    <span class="expense-amount">- ${{ number_format($expense->amount, 2) }}</span>
                    <span class="transaction-date">{{ $expense->date->format('d/m/Y') }}</span>
                </li>
            @empty
                <li>No expenses found</li>
            @endforelse
        </ul>
    </div>
</div>