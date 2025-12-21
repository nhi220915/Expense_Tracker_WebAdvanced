@extends('layouts.expense-tracker')

@section('title', 'Dashboard - Expense Tracker PRO')

@section('content')
    <div class="main-tabs">
        <a href="{{ route('expenses.index') }}"
            class="main-tab-button {{ request()->routeIs('expenses.*') ? 'active' : '' }}"
            onclick="window.location.href='{{ route('expenses.index') }}'; return false;">Spending</a>
        <a href="{{ route('incomes.index') }}" class="main-tab-button {{ request()->routeIs('incomes.*') ? 'active' : '' }}"
            onclick="window.location.href='{{ route('incomes.index') }}'; return false;">Income</a>
        <a href="{{ route('dashboard') }}" class="main-tab-button {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            onclick="window.location.href='{{ route('dashboard') }}'; return false;">Dashboard</a>
    </div>

    <div class="tab-content-container">
        <div id="Dashboard" class="main-tab-content active" style="display: block;">
            <div class="dashboard-content-grid">
                <div class="grid-column-left">
                    @include('components.dashboard-summary')
                    @include('components.budget-progress')
                </div>

                <div class="grid-column-right">
                    @include('components.expense-chart')
                    @include('components.income-chart')
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module">
        import '{{ Vite::asset("resources/js/app.js") }}';
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const expenseData = @json($expenseByCategory ?? []);
            const incomeData = @json($incomeByCategory ?? []);
            const totalExpense = {{ $totalExpense ?? 0 }};
            const totalIncome = {{ $totalIncome ?? 0 }};

            if (typeof window.initDashboardPage === 'function') {
                window.initDashboardPage(expenseData, incomeData, totalExpense, totalIncome);
            } else {
                console.error('initDashboardPage is not available on window');
            }
        });
    </script>
@endpush