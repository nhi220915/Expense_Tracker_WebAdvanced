<div class="dashboard-block">
    <div class="report-section">
        @include('partials.month-filter', ['filterId' => 'monthFilterIncome', 'selectedMonth' => request('month', date('Y-m')), 'tabName' => 'Income'])
        
        <h3>📜 Recent Income History</h3>
        <ul class="transaction-list" id="recentIncomeList">
            @forelse($incomes ?? [] as $income)
                <li>
                    <div class="transaction-name-category">
                        <span class="name">{{ $income->note ?: $income->category }} (Income: {{ $income->category }})</span>
                    </div>
                    <span class="income-amount">+ ${{ number_format($income->amount, 2) }}</span>
                    <span class="transaction-date">{{ $income->date->format('d/m/Y') }}</span>
                </li>
            @empty
                <li>No income found</li>
            @endforelse
        </ul>
    </div>
</div>