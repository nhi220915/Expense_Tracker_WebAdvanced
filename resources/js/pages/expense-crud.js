import { openModal, closeModal } from '../components/budget-modals.js';

export function initExpenseCrud() {
  // Setup create expense form with API
  const createForm = document.getElementById('mainEntryForm');
  if (createForm) {
    createForm.addEventListener('submit', async (e) => {
      e.preventDefault(); // Prevent default form submission
      e.stopPropagation();

      const submitButton = createForm.querySelector('button[type="submit"]');
      const originalText = submitButton.textContent;
      submitButton.disabled = true;
      submitButton.textContent = 'Saving...';

      const formData = {
        amount: document.getElementById('entryAmount').value,
        expense_category_id: parseInt(document.getElementById('entryCategory').value),
        date: document.getElementById('entryDate').value,
        note: document.getElementById('entryNote').value || ''
      };

      try {
        const response = await fetch('/api/expenses', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (response.ok) {
          console.log('✅ Expense created successfully!');
          console.log('📊 Cache-Control:', response.headers.get('Cache-Control'));
          console.log('🏷️ ETag:', response.headers.get('ETag'));
          console.log('📦 Response:', result);

          // Show success message
          showSuccessMessage('✅ Expense added successfully!');

          // Reset form
          createForm.reset();
          document.getElementById('entryDate').value = new Date().toISOString().split('T')[0];

          // Add expense to list dynamically
          if (result.data) {
            addExpenseToList(result.data);
          }

          // Reload page to update budget summary
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          showErrorMessage('❌ Error: ' + (result.message || 'Failed to create expense'));
          console.error('Error:', result);
        }
      } catch (error) {
        console.error('Network error:', error);
        showErrorMessage('❌ Network error: ' + error.message);
      } finally {
        submitButton.disabled = false;
        submitButton.textContent = originalText;
      }
    });
  }

  // Edit expense function
  window.editExpense = (id, amount, categoryId, date, note) => {
    const form = document.getElementById('editExpenseForm');
    if (!form) return;

    // Store expense ID
    form.dataset.expenseId = id;

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

  // Setup edit form handler
  const editForm = document.getElementById('editExpenseForm');
  if (editForm) {
    editForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();

      const id = editForm.dataset.expenseId;
      if (!id) {
        showErrorMessage('Error: Expense ID not found');
        return;
      }

      const submitButton = editForm.querySelector('button[type="submit"]');
      const originalText = submitButton.textContent;
      submitButton.disabled = true;
      submitButton.textContent = 'Updating...';

      const formData = {
        amount: document.getElementById('editExpenseAmount').value,
        expense_category_id: parseInt(document.getElementById('editExpenseCategory').value),
        date: document.getElementById('editExpenseDate').value,
        note: document.getElementById('editExpenseNote').value || ''
      };

      try {
        const response = await fetch(`/api/expenses/${id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (response.ok) {
          console.log('✅ Expense updated!');
          console.log('📊 Cache-Control:', response.headers.get('Cache-Control'));
          console.log('🏷️ ETag:', response.headers.get('ETag'));

          showSuccessMessage('✅ Expense updated successfully!');
          closeModal('editExpenseModal');

          // Reload to show updated data
          setTimeout(() => {
            window.location.reload();
          }, 500);
        } else {
          showErrorMessage('❌ Error: ' + (result.message || 'Failed to update expense'));
        }
      } catch (error) {
        console.error('Network error:', error);
        showErrorMessage('❌ Network error: ' + error.message);
      } finally {
        submitButton.disabled = false;
        submitButton.textContent = originalText;
      }
    });
  }

  // Delete expense function
  window.deleteExpense = (id) => {
    const form = document.getElementById('deleteExpenseForm');
    if (!form) return;

    form.dataset.expenseId = id;
    openModal('deleteExpenseModal');
  };

  // Setup delete form handler
  const deleteForm = document.getElementById('deleteExpenseForm');
  if (deleteForm) {
    deleteForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();

      const id = deleteForm.dataset.expenseId;
      if (!id) {
        showErrorMessage('Error: Expense ID not found');
        return;
      }

      const submitButton = deleteForm.querySelector('button[type="submit"]');
      const originalText = submitButton.textContent;
      submitButton.disabled = true;
      submitButton.textContent = 'Deleting...';

      try {
        const response = await fetch(`/api/expenses/${id}`, {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });

        const result = await response.json();

        if (response.ok) {
          console.log('✅ Expense deleted! Cache invalidated.');
          console.log('📊 Cache-Control:', response.headers.get('Cache-Control'));

          showSuccessMessage('✅ Expense deleted successfully!');
          closeModal('deleteExpenseModal');

          // Reload to show updated data
          setTimeout(() => {
            window.location.reload();
          }, 500);
        } else {
          showErrorMessage('❌ Error: ' + (result.message || 'Failed to delete expense'));
        }
      } catch (error) {
        console.error('Network error:', error);
        showErrorMessage('❌ Network error: ' + error.message);
      } finally {
        submitButton.disabled = false;
        submitButton.textContent = originalText;
      }
    });
  }
}

// Helper function to show success message
function showSuccessMessage(message) {
  const alertDiv = document.createElement('div');
  alertDiv.className = 'alert-success';
  alertDiv.textContent = message;
  alertDiv.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: #00a896;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    z-index: 10000;
    animation: slideIn 0.3s ease-out;
  `;

  document.body.appendChild(alertDiv);

  setTimeout(() => {
    alertDiv.style.animation = 'slideOut 0.3s ease-out';
    setTimeout(() => alertDiv.remove(), 300);
  }, 3000);
}

// Helper function to show error message
function showErrorMessage(message) {
  const alertDiv = document.createElement('div');
  alertDiv.className = 'alert-error';
  alertDiv.textContent = message;
  alertDiv.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: #e74c3c;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    z-index: 10000;
    animation: slideIn 0.3s ease-out;
  `;

  document.body.appendChild(alertDiv);

  setTimeout(() => {
    alertDiv.style.animation = 'slideOut 0.3s ease-out';
    setTimeout(() => alertDiv.remove(), 300);
  }, 5000);
}

// Helper function to add expense to list
function addExpenseToList(expense) {
  const list = document.getElementById('recentExpenseList');
  if (!list) return;

  const categoryName = expense.category?.name || 'Unknown';
  const formattedAmount = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(expense.amount);
  const formattedDate = new Date(expense.date).toLocaleDateString('en-GB');

  const li = document.createElement('li');
  li.dataset.category = categoryName;
  li.innerHTML = `
    <div class="transaction-name-category">
      <span class="name">${expense.note || categoryName}</span>
      <span class="category-badge">${categoryName}</span>
    </div>
    <span class="expense-amount">- $${formattedAmount}</span>
    <span class="transaction-date">${formattedDate}</span>
    <div class="transaction-actions">
      <button type="button" class="btn-edit" 
        onclick="window.editExpense?.(${expense.id}, ${expense.amount}, ${expense.expense_category_id}, '${expense.date}', '${expense.note || ''}')" 
        title="Edit">✏️</button>
      <button type="button" class="btn-delete" 
        onclick="window.deleteExpense?.(${expense.id})" 
        title="Delete">🗑️</button>
    </div>
  `;

  // Add to top of list with animation
  li.style.opacity = '0';
  li.style.transform = 'translateY(-20px)';
  list.insertBefore(li, list.firstChild);

  // Animate in
  setTimeout(() => {
    li.style.transition = 'all 0.3s ease-out';
    li.style.opacity = '1';
    li.style.transform = 'translateY(0)';
  }, 10);
}
