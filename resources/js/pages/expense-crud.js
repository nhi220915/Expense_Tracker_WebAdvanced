import { openModal } from '../components/budget-modals.js';

export function initExpenseCrud() {
  window.editExpense = (id, amount, categoryId, date, note) => {
    const form = document.getElementById('editExpenseForm');
    if (!form) return;

    form.action = `/expenses/${id}`;
    const amountEl = document.getElementById('editExpenseAmount');
    const catEl = document.getElementById('editExpenseCategory');
    const dateEl = document.getElementById('editExpenseDate');
    const noteEl = document.getElementById('editExpenseNote');

    if (amountEl) amountEl.value = amount ?? '';
    if (catEl) catEl.value = String(categoryId ?? '');
    if (dateEl) dateEl.value = date ?? '';
    if (noteEl) noteEl.value = note ?? '';

    openModal('editExpenseModal');
  };

  window.deleteExpense = (id) => {
    const form = document.getElementById('deleteExpenseForm');
    if (!form) return;

    form.action = `/expenses/${id}`;
    openModal('deleteExpenseModal');
  };
}

