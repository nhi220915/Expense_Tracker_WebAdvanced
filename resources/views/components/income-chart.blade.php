<div class="dashboard-block">
    <h3>💰 Income By Source</h3>
    <div class="pie-chart-container" id="incomePieChart"></div>
    <ul class="chart-legend" id="incomeChartLegend">
        <!-- Legend will be dynamically rendered by JavaScript -->
    </ul>
    <p style="text-align: center; margin-top: 20px;">
        <a href="{{ route('incomes.index', ['month' => request('month', date('Y-m'))]) }}"
            style="color: var(--mint-dark); text-decoration: none; font-weight: 600;">
            View detailed report
        </a>
    </p>
</div>