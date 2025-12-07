<div class="date-filter-inline">
    <label for="{{ $filterId }}">Filter by month:</label>
    <input type="month" id="{{ $filterId }}" value="{{ $selectedMonth }}" onchange="updateDashboard(this.value, '{{ $tabName }}')">
</div>