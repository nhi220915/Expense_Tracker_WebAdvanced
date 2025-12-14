<div class="bg-white shadow-soft rounded-xl p-6 space-y-4">
    <div class="space-y-3">
        <h3 class="text-lg font-semibold text-gray-800">🎯 Set Monthly Budget</h3>
        <form id="budgetForm" method="POST" action="{{ route('budgets.store') }}" class="grid gap-3 md:grid-cols-[2fr_1fr_auto] items-center">
            @csrf
            <select name="expense_category_id" id="budCategory" required
                class="block w-full rounded-lg border-gray-300 focus:border-mint-dark focus:ring-mint-dark text-sm shadow-sm">
                <option value="">Select Category</option>
                @foreach($expenseCategories ?? [] as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <x-text-input
                type="number"
                name="limit"
                id="budLimit"
                placeholder="Limit ($)"
                required
                min="1"
                step="1"
            />

            <x-primary-button class="w-full md:w-auto">Set Budget</x-primary-button>

            <input type="hidden" name="month" value="{{ request('month', date('m')) }}">
            <input type="hidden" name="year" value="{{ request('year', date('Y')) }}">
        </form>
    </div>

    <div class="space-y-2">
        <h3 class="text-md font-semibold text-gray-800">Set Budgets</h3>
        <ul class="space-y-2" id="budgetList">
            @foreach($budgets ?? [] as $budget)
                <li class="flex items-center justify-between text-sm text-gray-700 bg-gray-50 rounded-lg px-3 py-2">
                    <span class="font-medium">🎯 {{ $budget->category->name ?? $budget->category }}</span>
                    <span class="text-gray-600">${{ number_format($budget->limit, 2) }} / month</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>