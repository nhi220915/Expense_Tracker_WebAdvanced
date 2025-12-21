import { renderExpenseChart, renderIncomeChart } from '../components/chart.js';

export function initDashboardPage(expenseData, incomeData, totalExpense, totalIncome) {
    console.log('Dashboard init:', { expenseData, incomeData, totalExpense, totalIncome });

    // Always try to render expense chart
    renderExpenseChart(expenseData || {}, totalExpense || 0);

    // Always try to render income chart
    renderIncomeChart(incomeData || {}, totalIncome || 0);
}