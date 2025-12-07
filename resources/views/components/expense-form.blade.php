<div class="dashboard-block">
    <h1>📝 Enter Expense</h1>
    <div class="entry-form-container">
        <form id="mainEntryForm" class="main-entry-form" method="POST" action="{{ route('expenses.store') }}">
            @csrf
            <input type="number" name="amount" id="entryAmount" placeholder="Amount ($)" required min="0.01" step="0.01" value="{{ old('amount') }}">
            <select name="expense_category_id" id="entryCategory" required>
                <option value="">Select Category</option>
                @foreach($expenseCategories ?? [] as $category)
                    <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date" id="entryDate" value="{{ old('date', date('Y-m-d')) }}" required>
            <input type="text" name="note" id="entryNote" placeholder="Note (e.g.: Highland Coffee)" value="{{ old('note') }}">
            <button type="submit" class="btn-add">Save Transaction</button>
        </form>
    </div>
</div>