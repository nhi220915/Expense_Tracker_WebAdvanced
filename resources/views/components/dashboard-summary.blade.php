<div class="dashboard-block">
    @include('partials.month-filter', ['filterId' => 'monthFilterDashboard', 'selectedMonth' => request('month', date('Y-m')), 'tabName' => 'Dashboard'])
    
    <h3 id="summaryTitle">📊 Financial Summary This Month</h3>
    <div class="summary-block income">
        <h4>Income</h4>
        <p id="incomeValue">+ ${{ number_format($totalIncome ?? 0, 2) }}</p>
    </div>
    <div class="summary-block expense">
        <h4>Expense</h4>
        <p id="expenseValue">- ${{ number_format($totalExpense ?? 0, 2) }}</p>
    </div>
    <div class="summary-block balance">
        <h4>Balance</h4>
        <p id="balanceValue">${{ number_format(($totalIncome ?? 0) - ($totalExpense ?? 0), 2) }}</p>
    </div>
</div>