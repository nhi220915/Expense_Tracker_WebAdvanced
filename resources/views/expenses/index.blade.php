@extends('layouts.expense-tracker')

@section('title', 'Spending - Expense Tracker PRO')

@section('content')
    @php
        $expensesCollection = collect($expenses ?? []);
        $budgetsCollection = collect($budgets ?? []);
        $totalSpent = $expensesCollection->sum('amount');
        $totalLimit = $budgetsCollection->sum('limit');
        $overallPercentage = $totalLimit > 0 ? min(100, ($totalSpent / $totalLimit) * 100) : 0;
    @endphp

    <div class="grid-container">
        <div class="main-tabs">
            <a href="{{ route('expenses.index') }}" class="main-tab-button {{ request()->routeIs('expenses.*') ? 'active' : '' }}">Spending</a>
            <a href="{{ route('incomes.index') }}" class="main-tab-button {{ request()->routeIs('incomes.*') ? 'active' : '' }}">Income</a>
            <a href="{{ route('dashboard') }}" class="main-tab-button {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        </div>

        <div class="spending-content-grid">
            <div class="spending-column-left">
                <div class="dashboard-block">
                    <h1>📝 Enter Expense</h1>
                    <div class="entry-form-container">
                        <form id="mainEntryForm" method="POST" action="{{ route('expenses.store') }}" class="main-entry-form">
                            @csrf
                            <input
                                type="number"
                                name="amount"
                                id="entryAmount"
                                placeholder="Amount ($)"
                                required
                                min="0.01"
                                step="0.01"
                                value="{{ old('amount') }}"
                            />

                            <select name="expense_category_id" id="entryCategory" required>
                                <option value="">Select Category</option>
                                @foreach($expenseCategories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            <input
                                type="date"
                                name="date"
                                id="entryDate"
                                value="{{ old('date', date('Y-m-d')) }}"
                                required
                            />

                            <input
                                type="text"
                                name="note"
                                id="entryNote"
                                placeholder="Note (e.g.: Highland Coffee)"
                                value="{{ old('note') }}"
                                class="col-span-2"
                            />

                            <button type="submit" class="btn-add">Save Transaction</button>
                        </form>
                    </div>
                </div>

                <div class="dashboard-block">
                    <div class="report-section">
                        <div class="expense-filter-control">
                            <label for="monthFilterSpending">Filter by month:</label>
                            <input
                                type="month"
                                id="monthFilterSpending"
                                value="{{ request('month', date('Y-m')) }}"
                                onchange="updateDashboard(this.value, 'Spending')"
                            >
                        </div>

                        <div class="expense-banner">
                            <h3>📜 Recent Expense History</h3>
                        </div>

                        <div class="transaction-filters" id="expenseFilters">
                            <button class="filter-button active" onclick="filterTransactions('All', this)">All</button>
                            @foreach($expenseCategories ?? [] as $category)
                                <button class="filter-button" onclick="filterTransactions('{{ $category->name }}', this)">{{ $category->name }}</button>
                            @endforeach
                        </div>

                        <ul class="transaction-list" id="recentExpenseList">
                            @forelse($expenses ?? [] as $expense)
                                @php
                                    $categoryName = $expense->category->name ?? $expense->category;
                                    $badgeClass = 'category-badge';
                                @endphp
                                <li data-category="{{ $categoryName }}">
                                    <div class="transaction-name-category">
                                        <span class="name">{{ $expense->note ?: $categoryName }}</span>
                                        <span class="{{ $badgeClass }}">{{ $categoryName }}</span>
                                    </div>
                                    <span class="expense-amount">- ${{ number_format($expense->amount, 2) }}</span>
                                    <span class="transaction-date">{{ $expense->date->format('d/m/Y') }}</span>
                                    <div class="transaction-actions">
                                        <button
                                            type="button"
                                            class="btn-edit"
                                            onclick="window.editExpense?.({{ $expense->id }}, {{ $expense->amount }}, {{ $expense->expense_category_id }}, '{{ $expense->date->format('Y-m-d') }}', @js($expense->note ?? ''))"
                                            title="Edit"
                                        >✏️</button>
                                        <button
                                            type="button"
                                            class="btn-delete"
                                            onclick="window.deleteExpense?.({{ $expense->id }})"
                                            title="Delete"
                                        >🗑️</button>
                                    </div>
                                </li>
                            @empty
                                <li class="user-status" style="display: list-item; font-weight: 500; color: var(--text-color);">No expenses found</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="spending-column-right">
                <div class="dashboard-block">
                    <div class="report-section">
                        <h3>🎯 Monthly Budget Summary</h3>
                        <div class="budget-summary-box" id="budgetSummary">
                            <div class="budget-summary-chart" style="background: conic-gradient(#00a896 {{ $overallPercentage }}%, #e0e0e0 0);">
                                <div>{{ round($overallPercentage) }}%</div>
                            </div>
                            <div class="budget-summary-details">
                                <p>Limit: ${{ number_format($totalLimit, 2) }}</p>
                                <p>Spent: ${{ number_format($totalSpent, 2) }}</p>
                            </div>
                        </div>
                        <div class="action-links">
                            <a class="action-link" href="#" onclick="event.preventDefault(); if(typeof window.openModal === 'function') { window.openModal('limitModal'); } else { console.error('openModal not available'); }">Change Budget Limit</a>
                            <a class="action-link" href="#" onclick="event.preventDefault(); if(typeof window.openModal === 'function') { window.openModal('allocationModal'); } else { console.error('openModal not available'); }">Allocation (%) Setup</a>
                        </div>
                    </div>

                    <div class="report-section">
                        <h3>Category Budgets</h3>
                        <ul class="fixed-progress-list" id="fixedAllocationProgress">
                            @forelse($budgets ?? [] as $budget)
                                @php
                                    $categoryName = $budget->category->name ?? $budget->category;
                                    $categorySpent = $expensesCollection->where('expense_category_id', $budget->expense_category_id)->sum('amount');
                                    $percentage = $budget->limit > 0 ? min(100, ($categorySpent / $budget->limit) * 100) : 0;
                                    $barClass = $percentage > 100 ? 'red' : ($percentage >= 70 ? 'yellow' : 'green');
                                @endphp
                                <li class="fixed-progress-item">
                                    <h4>
                                        <span>{{ $categoryName }}</span>
                                        <span style="color: {{ $barClass === 'red' ? '#e74c3c' : ($barClass === 'yellow' ? '#f39c12' : '#6c7a89') }};">
                                            ${{ number_format($categorySpent, 2) }} / ${{ number_format($budget->limit, 2) }}
                                        </span>
                                    </h4>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar {{ $barClass }}" style="width: {{ $percentage }}%;"></div>
                                    </div>
                                </li>
                            @empty
                                <li class="user-status" style="display: list-item; font-weight: 500; color: var(--text-color);">No budgets set yet</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <!-- Budget Limit Modal -->
    <div id="limitModal" class="modal-overlay" style="display: none;" onclick="closeModal('limitModal')">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h3>Change Overall Monthly Limit</h3>
            <p>The total monthly limit impacts all category budgets.</p>
            <form id="overallBudgetForm" method="POST" action="{{ route('budgets.update-limit') }}">
                @csrf
                @method('PUT')
                <label for="overallLimit">New Total Limit ($):</label>
                <input
                    type="number"
                    id="overallLimit"
                    name="overall_limit"
                    value="{{ $totalLimit }}"
                    min="100"
                    step="10"
                    required
                />
                <input type="hidden" name="month" value="{{ request('month', date('Y-m')) }}">
                <button type="submit" class="btn-add" style="width: 100%; border-radius: 8px">
                    Save Limit
                </button>
            </form>
        </div>
    </div>

    <!-- Allocation Modal -->
    <div id="allocationModal" class="modal-overlay" style="display: none;" onclick="closeModal('allocationModal')">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h3>Allocation Setup (%)</h3>
            <p>Set spending allocation for each primary category:</p>
            <form id="modalAllocationForm" method="POST" action="{{ route('budgets.update-allocation') }}">
                @csrf
                @method('PUT')
                <ul id="modalAllocationList" class="allocation-list"></ul>
                <div id="totalAllocationError" class="total-allocation-error" style="display: none">
                    Total percentage must equal 100%.
                </div>
                <input type="hidden" name="month" value="{{ request('month', date('Y-m')) }}">
                <button type="submit" class="btn-add" id="saveAllocationButton" style="width: 100%; border-radius: 8px">
                    Save Allocation
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Expense Modal -->
    <div id="editExpenseModal" class="modal-overlay" style="display: none;" onclick="closeModal('editExpenseModal')">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h3>Edit Expense</h3>
            <form id="editExpenseForm" method="POST">
                @csrf
                @method('PUT')
                <label for="editExpenseAmount">Amount ($):</label>
                <input type="number" id="editExpenseAmount" name="amount" required min="0.01" step="0.01" />

                <label for="editExpenseCategory">Category:</label>
                <select name="expense_category_id" id="editExpenseCategory" required>
                    <option value="">Select Category</option>
                    @foreach($expenseCategories ?? [] as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <label for="editExpenseDate">Date:</label>
                <input type="date" id="editExpenseDate" name="date" required />

                <label for="editExpenseNote">Note:</label>
                <input type="text" id="editExpenseNote" name="note" placeholder="Note (e.g.: Highland Coffee)" />

                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button type="submit" class="btn-add" style="flex: 1;">Update Expense</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('editExpenseModal')" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Expense Confirmation Modal -->
    <div id="deleteExpenseModal" class="modal-overlay" style="display: none;" onclick="closeModal('deleteExpenseModal')">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h3>Delete Expense</h3>
            <p>Are you sure you want to delete this expense? This action cannot be undone.</p>
            <form id="deleteExpenseForm" method="POST">
                @csrf
                @method('DELETE')
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button type="submit" class="btn-delete" style="flex: 1;">Delete</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('deleteExpenseModal')" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script type="module">
        import { initExpensePage } from '{{ Vite::asset("resources/js/app.js") }}';
        import { openModal, closeModal } from '{{ Vite::asset("resources/js/components/budget-modals.js") }}';
        import { initExpenseCrud } from '{{ Vite::asset("resources/js/pages/expense-crud.js") }}';
        
        // Ensure functions are available globally
        window.openModal = openModal;
        window.closeModal = closeModal;
        
        initExpensePage();
        initExpenseCrud();
    </script>
@endpush
