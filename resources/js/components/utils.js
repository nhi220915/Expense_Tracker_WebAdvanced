// Utility functions
export function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

export function updateDashboard(selectedMonth, activeTab) {
    // Redirect to same page with month parameter
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('month', selectedMonth);
    window.location.href = currentUrl.toString();
}

// Make globally available
window.formatCurrency = formatCurrency;
window.updateDashboard = updateDashboard;