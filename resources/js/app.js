import './bootstrap';
import './components/navigation-fix.js';


// Import components
import { filterTransactions } from './components/filter.js';
import { openMainTab } from './components/tabs.js';
import { formatCurrency, updateDashboard } from './components/utils.js';

// Import pages (sẽ được gọi từ Blade templates)
export { initExpensePage } from './pages/expense.js';
export { initIncomePage } from './pages/income.js';
export { initDashboardPage } from './pages/dashboard.js';
export { initExpenseCrud } from './pages/expense-crud.js';
export { initIncomeCrud } from './pages/income-crud.js';

import { openModal, closeModal, checkTotalPercentage, autoAdjustRemainingCategories, renderAllocationModalContent } from './components/budget-modals.js';
import { initDashboardPage } from './pages/dashboard.js';

// Make functions globally available
window.filterTransactions = filterTransactions;
window.openMainTab = openMainTab;
window.formatCurrency = formatCurrency;
window.updateDashboard = updateDashboard;

window.openModal = openModal;
window.closeModal = closeModal;
window.checkTotalPercentage = checkTotalPercentage;
window.autoAdjustRemainingCategories = autoAdjustRemainingCategories;
window.renderAllocationModalContent = renderAllocationModalContent;
window.initDashboardPage = initDashboardPage;