@php
    $incomeCategoryList = collect($incomes ?? [])
        ->map(fn ($income) => $income->category->name ?? $income->category)
        ->filter()
        ->unique()
        ->values();
@endphp

<div class="income-history-card">
    <div class="income-filter-control">
        <label for="monthFilterIncome">Filter by month:</label>
        <input
            type="month"
            id="monthFilterIncome"
            value="{{ request('month', date('Y-m')) }}"
            onchange="updateDashboard(this.value, 'Income')"
        >
    </div>

    <div class="income-banner">
        <h3>📜 Recent Income History</h3>
    </div>

    <div class="income-filter-buttons" id="incomeFilters">
        <button class="filter-button active" data-category="All">All</button>
        @foreach($incomeCategoryList as $cat)
            <button class="filter-button" data-category="{{ $cat }}">{{ $cat }}</button>
        @endforeach
    </div>

    <ul class="transaction-list" id="recentIncomeList">
        @forelse($incomes ?? [] as $income)
            @php
                $catName = $income->category->name ?? $income->category;
            @endphp
            <li data-category="{{ $catName }}" data-income-id="{{ $income->id }}">
                <div class="transaction-name-category">
                    <span class="name">{{ $income->note ?: $catName }}</span>
                    <span class="category-badge">{{ $catName }}</span>
                </div>
                <span class="income-amount">+ ${{ number_format($income->amount, 2) }}</span>
                <span class="transaction-date">{{ $income->date->format('d/m/Y') }}</span>
                <div class="transaction-actions">
                    <button 
                        type="button" 
                        class="btn-edit" 
                        onclick="editIncome({{ $income->id }}, {{ $income->amount }}, '{{ addslashes($income->category) }}', '{{ $income->date->format('Y-m-d') }}', '{{ addslashes($income->note ?? '') }}')"
                        title="Edit"
                    >
                        ✏️
                    </button>
                    <button 
                        type="button" 
                        class="btn-delete" 
                        onclick="deleteIncome({{ $income->id }})"
                        title="Delete"
                    >
                        🗑️
                    </button>
                </div>
            </li>
        @empty
            <li class="user-status" style="display: list-item; font-weight: 500; color: var(--text-color);">No income found</li>
        @endforelse
    </ul>
</div>