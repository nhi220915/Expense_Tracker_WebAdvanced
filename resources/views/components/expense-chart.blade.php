<div class="dashboard-block">
    <h3>📈 Expense By Category</h3>
    <div class="pie-chart-container" id="expensePieChart"></div>
    <ul class="chart-legend" id="expenseChartLegend">
        <!-- Legend will be dynamically rendered by JavaScript -->
    </ul>
    <p style="text-align: center; margin-top: 20px;">
        <a href="{{ route('expenses.index', ['month' => request('month', date('Y-m'))]) }}"
            style="color: var(--mint-dark); text-decoration: none; font-weight: 600;">
            View detailed report
        </a>
    </p>
</div>