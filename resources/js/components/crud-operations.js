// CRUD Operations for Expenses and Incomes
import { openModal, closeModal } from './budget-modals.js';

// Helper function to get current month from URL or input
function getCurrentMonth() {
    // Try to get from month filter input
    const monthFilter = document.getElementById('monthFilterSpending') || document.getElementById('monthFilterIncome');
    if (monthFilter && monthFilter.value) {
        return monthFilter.value;
    }
    
    // Fallback to current month
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
}

// Expense CRUD Functions
export function editExpense(id, amount, categoryId, date, note, categoryName) {
    const form = document.getElementById('editExpenseForm');
    if (!form) {
        console.error('Edit expense form not found');
        return;
    }

    // Set form action
    form.action = `/expenses/${id}`;

    // Populate form fields
    document.getElementById('editExpenseAmount').value = amount;
    document.getElementById('editExpenseCategory').value = categoryId;
    document.getElementById('editExpenseDate').value = date;
    document.getElementById('editExpenseNote').value = note || '';

    // Open modal
    openModal('editExpenseModal');
}

export function deleteExpense(id) {
    const form = document.getElementById('deleteExpenseForm');
    if (!form) {
        console.error('Delete expense form not found');
        return;
    }

    // Set form action
    form.action = `/expenses/${id}`;

    // Open modal
    openModal('deleteExpenseModal');
}

// Income CRUD Functions
export function editIncome(id, amount, category, date, note) {
    const form = document.getElementById('editIncomeForm');
    if (!form) {
        console.error('Edit income form not found');
        return;
    }

    // Set form action
    form.action = `/incomes/${id}`;

    // Populate form fields
    document.getElementById('editIncomeAmount').value = amount;
    document.getElementById('editIncomeCategory').value = category;
    document.getElementById('editIncomeDate').value = date;
    document.getElementById('editIncomeNote').value = note || '';

    // Open modal
    openModal('editIncomeModal');
}

export function deleteIncome(id) {
    const form = document.getElementById('deleteIncomeForm');
    if (!form) {
        console.error('Delete income form not found');
        return;
    }

    // Set form action
    form.action = `/incomes/${id}`;

    // Open modal
    openModal('deleteIncomeModal');
}

// Make functions globally available
window.editExpense = editExpense;
window.deleteExpense = deleteExpense;
window.editIncome = editIncome;
window.deleteIncome = deleteIncome;
