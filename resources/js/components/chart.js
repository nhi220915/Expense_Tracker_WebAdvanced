// Chart rendering component
const incomeColors = {
    'Salary': '#27ae60',
    'Freelance': '#00a896',
    'Bonus': '#f39c12',
    'Investment': '#3498db',
    'Other Income': '#9b59b6'
};

const expenseColors = {
    'Food': '#00d1c1',
    'Transport': '#27ae60',
    'Entertainment': '#f39c12',
    'Utilities': '#e74c3c',
    'Other': '#00a896'
};

export function renderExpenseChart(data, totalExpense) {
    const chartElement = document.getElementById('expensePieChart');
    const legendElement = document.getElementById('expenseChartLegend');

    if (!chartElement) return;

    if (totalExpense === 0 || !data || Object.keys(data).length === 0) {
        chartElement.style.background = '#e0e0e0';
        if (legendElement) legendElement.innerHTML = '';
        return;
    }

    let gradient = '';
    let currentAngle = 0;
    const categories = Object.keys(data).sort();

    if (legendElement) legendElement.innerHTML = '';

    categories.forEach(category => {
        const amount = data[category];
        const percentage = (amount / totalExpense) * 100;
        const endAngle = currentAngle + percentage;
        const color = expenseColors[category] || '#ccc';

        gradient += `${color} ${currentAngle}% ${endAngle}%, `;
        currentAngle = endAngle;

        if (legendElement) {
            const li = document.createElement('li');
            li.className = category.toLowerCase().replace(/ /g, '-');
            li.style.setProperty('--category-color', color);
            li.textContent = `${category} (${Math.round(percentage)}%)`;
            legendElement.appendChild(li);
        }
    });

    gradient = gradient.slice(0, -2);
    chartElement.style.background = `conic-gradient(${gradient})`;
}

export function renderIncomeChart(data, totalIncome) {
    const chartElement = document.getElementById('incomePieChart');
    const legendElement = document.getElementById('incomeChartLegend');

    if (!chartElement) return;

    if (totalIncome === 0 || !data || Object.keys(data).length === 0) {
        chartElement.style.background = '#e0e0e0';
        if (legendElement) legendElement.innerHTML = '';
        return;
    }

    let gradient = '';
    let currentAngle = 0;
    const categories = Object.keys(data).sort();

    if (legendElement) legendElement.innerHTML = '';

    categories.forEach(category => {
        const amount = data[category];
        const percentage = (amount / totalIncome) * 100;
        const endAngle = currentAngle + percentage;
        const color = incomeColors[category] || '#ccc';
        const categoryClass = category.toLowerCase().replace(/ /g, '-');

        gradient += `${color} ${currentAngle}% ${endAngle}%, `;
        currentAngle = endAngle;

        if (legendElement) {
            const li = document.createElement('li');
            li.className = categoryClass;
            li.style.setProperty('--category-color', color);
            li.textContent = `${category} (${Math.round(percentage)}%)`;
            legendElement.appendChild(li);
        }
    });

    gradient = gradient.slice(0, -2);
    chartElement.style.background = `conic-gradient(${gradient})`;
}

export { incomeColors, expenseColors };