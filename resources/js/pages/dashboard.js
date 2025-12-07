import { renderExpenseChart, renderIncomeChart } from '../components/chart.js';

export function initDashboardPage(expenseData, incomeData, totalExpense, totalIncome) {
    // Render charts if data is available
    if (expenseData && totalExpense > 0) {
        renderExpenseChart(expenseData, totalExpense);
    }

    if (incomeData && totalIncome > 0) {
        renderIncomeChart(incomeData, totalIncome);
    }
}