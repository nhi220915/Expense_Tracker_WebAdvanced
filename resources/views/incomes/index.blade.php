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

@push('scripts')
    <script type="module">
        import { initIncomePage } from '{{ Vite::asset("resources/js/app.js") }}';
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