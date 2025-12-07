<div class="dashboard-block">
    <h1>➕ Enter Income</h1>
    <div class="entry-form-container">
        <form id="incomeEntryForm" class="main-entry-form" method="POST" action="{{ route('incomes.store') }}">
            @csrf
            <input type="number" name="amount" id="incomeEntryAmount" placeholder="Amount ($)" required min="0.01" step="0.01" value="{{ old('amount') }}">
            <select name="category" id="incomeEntryCategory" required>
                <option value="">Select Income Source</option>
                <option value="Salary" {{ old('category') == 'Salary' ? 'selected' : '' }}>Salary</option>
                <option value="Freelance" {{ old('category') == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                <option value="Bonus" {{ old('category') == 'Bonus' ? 'selected' : '' }}>Bonus</option>
                <option value="Investment" {{ old('category') == 'Investment' ? 'selected' : '' }}>Investment</option>
                <option value="Other Income" {{ old('category') == 'Other Income' ? 'selected' : '' }}>Other Income</option>
            </select>
            <input type="date" name="date" id="incomeEntryDate" value="{{ old('date', date('Y-m-d')) }}" required>
            <input type="text" name="note" id="incomeEntryNote" placeholder="Source (e.g.: November Salary)" value="{{ old('note') }}">
            <button type="submit" class="btn-add">Save Transaction</button>
        </form>
    </div>
</div>