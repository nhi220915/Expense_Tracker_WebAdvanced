<div class="dashboard-block"> 
    <h3>📈 Expense By Category</h3>
    <div class="pie-chart-container" id="expensePieChart"></div>
    <ul class="chart-legend" id="expenseChartLegend">
        @foreach($expenseByCategory ?? [] as $category => $amount)
            @php
                $percentage = ($totalExpense ?? 0) > 0 ? ($amount / ($totalExpense ?? 1)) * 100 : 0;
            @endphp
            <li>{{ $category }} ({{ round($percentage) }}%)</li>
        @endforeach
    </ul>
    <p style="text-align: center; margin-top: 20px;">
        <a href="#" style="color: var(--mint-dark); text-decoration: none; font-weight: 600;">View detailed report</a>
    </p>
</div>