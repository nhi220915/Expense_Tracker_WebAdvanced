<div class="dashboard-block"> 
    <h3>💰 Income By Source</h3>
    <div class="pie-chart-container" id="incomePieChart"></div>
    <ul class="chart-legend" id="incomeChartLegend">
        @foreach($incomeByCategory ?? [] as $category => $amount)
            @php
                $percentage = ($totalIncome ?? 0) > 0 ? ($amount / ($totalIncome ?? 1)) * 100 : 0;
            @endphp
            <li>{{ $category }} ({{ round($percentage) }}%)</li>
        @endforeach
    </ul>
    <p style="text-align: center; margin-top: 20px;">
        <a href="#" style="color: var(--mint-dark); text-decoration: none; font-weight: 600;">View detailed report</a>
    </p>
</div>