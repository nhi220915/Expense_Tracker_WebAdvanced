import { initIncomeForm } from '../components/form.js';

export function initIncomePage() {
    initIncomeForm();

    document.addEventListener("DOMContentLoaded", () => {
        const incomeEntryDate = document.getElementById('incomeEntryDate');
        if (incomeEntryDate && !incomeEntryDate.value) {
            incomeEntryDate.value = new Date().toISOString().split('T')[0];
        }
    });
}