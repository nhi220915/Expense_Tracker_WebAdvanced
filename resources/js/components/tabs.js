// Tab switching component
export function openMainTab(evt, tabName) {
    const tabContents = document.querySelectorAll('.main-tab-content');
    const tabButtons = document.querySelectorAll('.main-tab-button');
    
    tabContents.forEach(content => {
        content.style.display = 'none';
        content.classList.remove('active');
    });
    
    tabButtons.forEach(button => button.classList.remove('active'));

    const tabElement = document.getElementById(tabName);
    if (tabElement) {
        tabElement.style.display = 'block';
        tabElement.classList.add('active');
    }
    
    if (evt && evt.currentTarget) {
        evt.currentTarget.classList.add('active');
    }

    // Update dashboard if needed
    let filterId = tabName === 'Spending' ? 'monthFilterSpending' : 
                   tabName === 'Income' ? 'monthFilterIncome' : 'monthFilterDashboard';
    let monthToUse = document.getElementById(filterId)?.value;
    
    if (monthToUse && window.updateDashboard) {
        updateDashboard(monthToUse, tabName);
    }
}

// Make globally available
window.openMainTab = openMainTab;