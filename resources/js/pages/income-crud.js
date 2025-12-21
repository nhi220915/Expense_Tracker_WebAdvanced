import { openModal } from '../components/budget-modals.js';

export function initIncomeCrud() {
  window.editIncome = (id, amount, category, date, note) => {
    const form = document.getElementById('editIncomeForm');
    if (!form) return;

    form.action = `/incomes/${id}`;
    const amountEl = document.getElementById('editIncomeAmount');
    const catEl = document.getElementById('editIncomeCategory');
    const dateEl = document.getElementById('editIncomeDate');
    const noteEl = document.getElementById('editIncomeNote');

    if (amountEl) amountEl.value = amount ?? '';
    if (catEl) catEl.value = String(category ?? '');
    if (dateEl) dateEl.value = date ?? '';
    if (noteEl) noteEl.value = note ?? '';

    openModal('editIncomeModal');
  };

  window.deleteIncome = (id) => {
    const form = document.getElementById('deleteIncomeForm');
    if (!form) return;

    form.action = `/incomes/${id}`;
    openModal('deleteIncomeModal');
  };
}

