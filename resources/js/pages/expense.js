import { initExpenseForm } from '../components/form.js';
import { filterTransactions } from '../components/filter.js';

export function initExpensePage() {
    // Initialize forms
    initExpenseForm();

    // Initialize filter on page load
    document.addEventListener("DOMContentLoaded", () => {
        const allButton = document.querySelector('#Spending .filter-button[onclick*="\'All\'"]');
        if (allButton) {
            filterTransactions('All', allButton);
        }

        // Set default date
        const entryDate = document.getElementById('entryDate');
        if (entryDate && !entryDate.value) {
            entryDate.value = new Date().toISOString().split('T')[0];
        }
    });
}