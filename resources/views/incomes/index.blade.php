@extends('layouts.expense-tracker')

@section('title', 'Incomes - Expense Tracker PRO')

@section('content')
    <div class="grid-container">
        <div class="main-tabs">
            <a href="{{ route('expenses.index') }}" class="main-tab-button {{ request()->routeIs('expenses.*') ? 'active' : '' }}">Spending</a>
            <a href="{{ route('incomes.index') }}" class="main-tab-button {{ request()->routeIs('incomes.*') ? 'active' : '' }}">Income</a>
            <a href="{{ route('dashboard') }}" class="main-tab-button {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        </div>

        <div class="spending-content-grid" style="grid-template-columns: 1fr;">
            <div>
                @include('components.income-form')
                @include('components.income-list')
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <!-- Edit Income Modal -->
    <div id="editIncomeModal" class="modal-overlay" style="display: none;" onclick="closeModal('editIncomeModal')">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h3>Edit Income</h3>
            <form id="editIncomeForm" method="POST">
                @csrf
                @method('PUT')
                <label for="editIncomeAmount">Amount ($):</label>
                <input
                    type="number"
                    id="editIncomeAmount"
                    name="amount"
                    required
                    min="0.01"
                    step="0.01"
                />

                <label for="editIncomeCategory">Category:</label>
                <select name="category" id="editIncomeCategory" required>
                    <option value="">Select Income Source</option>
                    <option value="Salary">Salary</option>
                    <option value="Freelance">Freelance</option>
                    <option value="Bonus">Bonus</option>
                    <option value="Investment">Investment</option>
                    <option value="Other Income">Other Income</option>
                </select>

                <label for="editIncomeDate">Date:</label>
                <input
                    type="date"
                    id="editIncomeDate"
                    name="date"
                    required
                />

                <label for="editIncomeNote">Note:</label>
                <input
                    type="text"
                    id="editIncomeNote"
                    name="note"
                    placeholder="Source (e.g.: November Salary)"
                />

                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button type="submit" class="btn-add" style="flex: 1;">Update Income</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('editIncomeModal')" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Income Confirmation Modal -->
    <div id="deleteIncomeModal" class="modal-overlay" style="display: none;" onclick="closeModal('deleteIncomeModal')">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h3>Delete Income</h3>
            <p>Are you sure you want to delete this income? This action cannot be undone.</p>
            <form id="deleteIncomeForm" method="POST">
                @csrf
                @method('DELETE')
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button type="submit" class="btn-delete" style="flex: 1;">Delete</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('deleteIncomeModal')" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script type="module">
        import { initIncomePage } from '{{ Vite::asset("resources/js/app.js") }}';
        import { openModal, closeModal } from '{{ Vite::asset("resources/js/components/budget-modals.js") }}';
        import { editIncome, deleteIncome } from '{{ Vite::asset("resources/js/components/crud-operations.js") }}';
        
        // Ensure functions are available globally
        window.openModal = openModal;
        window.closeModal = closeModal;
        window.editIncome = editIncome;
        window.deleteIncome = deleteIncome;
        
        initIncomePage();

        const incomeFilterButtons = document.querySelectorAll('#incomeFilters .filter-button');
        const incomeItems = document.querySelectorAll('#recentIncomeList li');

        function filterIncomeList(category) {
            incomeItems.forEach(item => {
                const itemCat = item.getAttribute('data-category');
                const show = category === 'All' || itemCat === category;
                item.style.display = show ? 'grid' : 'none';
            });
        }

        incomeFilterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                incomeFilterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filterIncomeList(btn.dataset.category);
            });
        });
    </script>
@endpush