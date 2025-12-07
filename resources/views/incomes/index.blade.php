@extends('layouts.expense-tracker')

@section('title', 'Incomes - Expense Tracker PRO')

@section('content')
    <div class="main-tabs">
        <a href="{{ route('expenses.index') }}" class="main-tab-button {{ request()->routeIs('expenses.*') ? 'active' : '' }}">Spending</a>
        <a href="{{ route('incomes.index') }}" class="main-tab-button {{ request()->routeIs('incomes.*') ? 'active' : '' }}">Income</a>
        <a href="{{ route('dashboard') }}" class="main-tab-button {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
    </div>

    <div class="tab-content-container">
        <div id="Income" class="main-tab-content active" style="display: block;">
            @include('components.income-form')
            @include('components.income-list')
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module">
        import { initIncomePage } from '{{ Vite::asset("resources/js/app.js") }}';
        initIncomePage();
    </script>
@endpush