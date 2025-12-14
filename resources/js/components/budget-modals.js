// Modal Functions
export function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Set display to flex - CSS will handle the rest
        modal.style.display = "flex";
        document.body.style.overflow = "hidden"; // Prevent background scrolling
        
        if (modalId === "limitModal") {
            // Limit modal is already populated from server
            const limitInput = document.getElementById("overallLimit");
            if (limitInput) {
                setTimeout(() => limitInput.focus(), 100);
            }
        }
        
        if (modalId === "allocationModal") {
            renderAllocationModalContent();
        }
    } else {
        console.error(`Modal with id "${modalId}" not found. Available modals:`, document.querySelectorAll('.modal-overlay'));
    }
}

export function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.cssText = 'display: none !important;';
        document.body.style.overflow = ""; // Restore background scrolling
    }
}

// Check total percentage in allocation modal
export function checkTotalPercentage() {
    const limitInputs = document.querySelectorAll('#modalAllocationList input[type="number"]');
    let total = 0;
    limitInputs.forEach((input) => {
        total += parseFloat(input.value) || 0;
    });

    const totalDisplay = document.getElementById("totalPercentDisplay");
    const errorDiv = document.getElementById("totalAllocationError");
    const saveButton = document.getElementById("saveAllocationButton");

    if (totalDisplay) {
        totalDisplay.textContent = `${total}%`;

        if (total !== 100) {
            totalDisplay.style.color = "var(--warning-red)";
            if (errorDiv) errorDiv.style.display = "block";
            if (saveButton) saveButton.disabled = true;
        } else {
            totalDisplay.style.color = "var(--emerald-green)";
            if (errorDiv) errorDiv.style.display = "none";
            if (saveButton) saveButton.disabled = false;
        }
    }
    return total;
}

// Auto-adjust remaining categories
export function autoAdjustRemainingCategories(changedInput) {
    const limitInputs = document.querySelectorAll('#modalAllocationList input[type="number"]');
    const changedCategory = changedInput.name;
    let newValue = parseInt(changedInput.value) || 0;

    if (newValue < 0) {
        newValue = 0;
        changedInput.value = 0;
    } else if (newValue > 100) {
        newValue = 100;
        changedInput.value = 100;
    }

    const currentValues = {};
    let totalOthers = 0;
    const otherCategories = [];

    limitInputs.forEach((input) => {
        const category = input.name;
        const value = parseInt(input.value) || 0;
        currentValues[category] = value;

        if (category !== changedCategory) {
            totalOthers += value;
            otherCategories.push({ category, input, currentValue: value });
        }
    });

    const remainingPercent = 100 - newValue;

    if (remainingPercent <= 0) {
        otherCategories.forEach(({ input }) => {
            input.value = 0;
        });
        checkTotalPercentage();
        return;
    }

    if (otherCategories.length === 0) {
        checkTotalPercentage();
        return;
    }

    if (totalOthers === 0) {
        const equalShare = Math.floor(remainingPercent / otherCategories.length);
        let remainder = remainingPercent - equalShare * otherCategories.length;

        otherCategories.forEach(({ input }) => {
            input.value = equalShare;
        });

        for (let i = 0; i < remainder; i++) {
            if (otherCategories[i]) {
                otherCategories[i].input.value = parseInt(otherCategories[i].input.value) + 1;
            }
        }
    } else {
        let distributedTotal = 0;
        const newValues = [];

        otherCategories.forEach(({ category, currentValue }) => {
            const ratio = currentValue / totalOthers;
            const proportionalValue = remainingPercent * ratio;
            newValues.push({
                category,
                input: otherCategories.find((c) => c.category === category).input,
                value: proportionalValue,
            });
        });

        const roundedValues = newValues.map(({ category, input, value }) => {
            const rounded = Math.round(value);
            distributedTotal += rounded;
            return { category, input, value: rounded };
        });

        const difference = remainingPercent - distributedTotal;
        if (difference !== 0) {
            const sortedByDecimal = newValues
                .map((item, index) => ({
                    ...item,
                    decimal: item.value - roundedValues[index].value,
                    index,
                }))
                .sort((a, b) => (difference > 0 ? b.decimal - a.decimal : a.decimal - b.decimal));

            for (let i = 0; i < Math.abs(difference); i++) {
                if (sortedByDecimal[i]) {
                    const idx = sortedByDecimal[i].index;
                    roundedValues[idx].value += difference > 0 ? 1 : -1;
                }
            }
        }

        roundedValues.forEach(({ input, value }) => {
            input.value = Math.max(0, value);
        });
    }

    checkTotalPercentage();
}

// Render allocation modal content
export function renderAllocationModalContent() {
    const modalList = document.getElementById("modalAllocationList");
    if (!modalList) return;

    // Get current budgets from the page
    const budgetItems = document.querySelectorAll('#fixedAllocationProgress .fixed-progress-item');
    const budgets = {};
    const totalLimit = parseFloat(document.querySelector('.budget-summary-details p:first-child')?.textContent.replace(/[^0-9.]/g, '')) || 0;
    
    budgetItems.forEach((item) => {
        const categoryName = item.querySelector('h4 span:first-child')?.textContent.trim();
        const limitText = item.querySelector('h4 span:last-child')?.textContent;
        if (categoryName && limitText) {
            const limit = parseFloat(limitText.match(/\$([\d,]+\.?\d*)/)?.[1]?.replace(/,/g, '')) || 0;
            budgets[categoryName] = totalLimit > 0 ? Math.round((limit / totalLimit) * 100) : 0;
        }
    });

    modalList.innerHTML = "";

    // Get all expense categories
    const categories = Array.from(document.querySelectorAll('#entryCategory option'))
        .map(opt => opt.textContent.trim())
        .filter(name => name && name !== 'Select Category');

    categories.forEach((category) => {
        const currentPercentage = budgets[category] || 0;
        const listItem = document.createElement("li");
        listItem.className = "allocation-item";

        listItem.innerHTML = `
            <label for="percent_${category}">${category}</label>
            <span class="input-with-percent">
                <input type="number" 
                       id="percent_${category}" 
                       name="percentages[${category}]" 
                       placeholder="%" 
                       value="${currentPercentage}" 
                       min="0" max="100" step="1" 
                       oninput="window.autoAdjustRemainingCategories(this); window.checkTotalPercentage();">
            </span>
        `;
        modalList.appendChild(listItem);
    });

    // Add total indicator
    const totalItem = document.createElement("li");
    totalItem.className = "allocation-item";
    totalItem.innerHTML = `
        <label style="font-weight: 700;">Total Allocation</label>
        <span class="input-with-percent">
            <span id="totalPercentDisplay" style="font-weight: 700;">0%</span>
        </span>
    `;
    totalItem.style.borderTop = "1px solid var(--border-default)";
    totalItem.style.paddingTop = "15px";
    modalList.appendChild(totalItem);

    checkTotalPercentage();
}

// Make functions globally available
window.openModal = openModal;
window.closeModal = closeModal;
window.checkTotalPercentage = checkTotalPercentage;
window.autoAdjustRemainingCategories = autoAdjustRemainingCategories;
window.renderAllocationModalContent = renderAllocationModalContent;