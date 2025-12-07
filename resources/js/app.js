import './bootstrap';

// Import components
import { filterTransactions } from './components/filter.js';
import { openMainTab } from './components/tabs.js';
import { formatCurrency, updateDashboard } from './components/utils.js';

// Import pages (sẽ được gọi từ Blade templates)
export { initExpensePage } from './pages/expense.js';
export { initIncomePage } from './pages/income.js';
export { initDashboardPage } from './pages/dashboard.js';

// Make functions globally available
window.filterTransactions = filterTransactions;
window.openMainTab = openMainTab;
window.formatCurrency = formatCurrency;
window.updateDashboard = updateDashboard;