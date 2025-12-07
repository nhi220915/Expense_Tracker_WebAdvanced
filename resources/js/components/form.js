// Form handling component
import { formatCurrency, updateDashboard } from './utils.js';

export function initExpenseForm() {
    const form = document.getElementById('mainEntryForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        // Form sẽ submit bình thường qua Laravel
        // Nếu cần AJAX thì uncomment và xử lý ở đây
        // e.preventDefault();
    });
}

export function initIncomeForm() {
    const form = document.getElementById('incomeEntryForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        // Form sẽ submit bình thường qua Laravel
    });
}

export function initBudgetForm() {
    const form = document.getElementById('budgetForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        // Form sẽ submit bình thường qua Laravel
    });
}