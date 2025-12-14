<div class="bg-white shadow-soft rounded-xl p-6 space-y-4">
    <h1 class="text-xl font-semibold text-gray-800 flex items-center gap-2">📝 Enter Expense</h1>
    <form id="mainEntryForm" method="POST" action="{{ route('expenses.store') }}" class="space-y-3">
        @csrf
        <div class="grid gap-3 lg:grid-cols-[1.2fr_1fr_1fr_auto] md:grid-cols-2">
            <x-text-input
                type="number"
                name="amount"
                id="entryAmount"
                placeholder="Amount ($)"
                required
                min="0.01"
                step="0.01"
                :value="old('amount')"
            />

            <select name="expense_category_id" id="entryCategory" required
                class="block w-full rounded-lg border-gray-300 focus:border-mint-dark focus:ring-mint-dark text-sm shadow-sm">
                <option value="">Select Category</option>
                @foreach($expenseCategories ?? [] as $category)
                    <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <x-text-input
                type="date"
                name="date"
                id="entryDate"
                :value="old('date', date('Y-m-d'))"
                required
            />

            <x-primary-button class="w-full md:w-auto">Save Transaction</x-primary-button>
        </div>

        <x-text-input
            type="text"
            name="note"
            id="entryNote"
            placeholder="Note (e.g.: Highland Coffee)"
            :value="old('note')"
        />
    </form>
</div>