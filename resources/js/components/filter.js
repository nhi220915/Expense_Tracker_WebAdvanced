// Category filter component
export function filterTransactions(category, clickedButton) {
    const listItems = document.querySelectorAll('#recentExpenseList li');
    const buttons = document.querySelectorAll('.filter-button');

    buttons.forEach(btn => btn.classList.remove('active'));
    if (clickedButton) {
        clickedButton.classList.add('active');
    }

    listItems.forEach(item => {
        const itemCategory = item.getAttribute('data-category');
        
        if (category === 'All' || itemCategory === category) {
            item.style.display = 'grid'; 
        } else {
            item.style.display = 'none';
        }
    });
}

// Make globally available
window.filterTransactions = filterTransactions;