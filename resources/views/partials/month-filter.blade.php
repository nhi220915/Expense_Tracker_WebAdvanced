<div class="flex items-center gap-2 text-sm text-gray-700">
    <label for="{{ $filterId }}" class="font-medium">Filter by month:</label>
    <input
        type="month"
        id="{{ $filterId }}"
        value="{{ $selectedMonth }}"
        onchange="updateDashboard(this.value, '{{ $tabName }}')"
        class="rounded-lg border-gray-300 focus:border-mint-dark focus:ring-mint-dark text-sm"
    >
</div>