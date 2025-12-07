<div class="dashboard-block">
    <div class="report-section">
        <h3>🎯 Set Monthly Budget</h3>
        <form id="budgetForm" class="main-entry-form" method="POST" action="{{ route('budgets.store') }}">
            @csrf
            <select name="expense_category_id" id="budCategory" required>
                <option value="">Select Category</option>
                @foreach($expenseCategories ?? [] as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <input type="number" name="limit" id="budLimit" placeholder="Limit ($)" required min="1" step="1">
            <input type="hidden" name="month" value="{{ request('month', date('m')) }}">
            <input type="hidden" name="year" value="{{ request('year', date('Y')) }}">
            <button type="submit" class="btn-add">Set Budget</button>
        </form>
    </div>
    <div class="report-section">
        <h3>Set Budgets</h3>
        <ul class="budget-progress-list" id="budgetList">
            @foreach($budgets ?? [] as $budget)
                <li>
                    <span>🎯 {{ $budget->category->name ?? $budget->category }}:</span> 
                    <span>${{ number_format($budget->limit, 2) }} / month</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>