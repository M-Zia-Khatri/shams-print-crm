// resources/js/expenses-create.js

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('expense-form');
    const container = document.getElementById('expense-items-container');
    const addItemBtn = document.getElementById('add-item-btn');
    let itemIndex = container.querySelectorAll('.expense-item').length;

    function calculateSubtotals() {
        const items = container.querySelectorAll('.expense-item');
        let total = 0;

        items.forEach((item, index) => {
            const qtyInput = item.querySelector(`input[name="expense_items[${index}][qty]"]`) || 
                            item.querySelector('input[type="number"]:nth-of-type(1)');
            const priceInput = item.querySelector(`input[name="expense_items[${index}][unit_price]"]`) || 
                              item.querySelector('input[type="number"]:nth-of-type(2)');
            const subtotalDisplay = item.querySelector('.flex.items-end > .form-control input');

            if (qtyInput && priceInput && subtotalDisplay) {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const subtotal = qty * price;
                subtotalDisplay.value = subtotal.toFixed(2);
                total += subtotal;
            }
        });

        document.getElementById('total-display').textContent = total.toFixed(2);
    }

    function updateRemoveButtons() {
        const items = container.querySelectorAll('.expense-item');
        const removeButtons = container.querySelectorAll('.remove-item');
        
        removeButtons.forEach((btn, index) => {
            if (items.length <= 1) {
                btn.classList.add('hidden');
            } else {
                btn.classList.remove('hidden');
            }
        });
    }

    function attachEventListeners(item) {
        const inputs = item.querySelectorAll('input[type="number"], input[type="text"]');
        inputs.forEach(input => {
            input.addEventListener('input', calculateSubtotals);
            input.addEventListener('change', calculateSubtotals);
        });

        const removeBtn = item.querySelector('.remove-item');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                item.remove();
                itemIndex--;
                updateRemoveButtons();
                calculateSubtotals();
            });
        }
    }

    addItemBtn.addEventListener('click', function () {
        const newItem = document.createElement('div');
        newItem.className = 'expense-item grid grid-cols-1 md:grid-cols-5 gap-3 rounded-lg border border-base-300 bg-base-100/60 p-4';
        newItem.innerHTML = `
            <label class="form-control w-full">
                <span class="label-text font-semibold text-sm">Item</span>
                <input type="text" name="expense_items[${itemIndex}][description]" 
                    placeholder="Item description" class="input input-bordered input-sm w-full" required>
            </label>
            <label class="form-control w-full">
                <span class="label-text font-semibold text-sm">Qty</span>
                <input type="number" name="expense_items[${itemIndex}][qty]" value="1"
                    class="input input-bordered input-sm w-full" min="1" required>
            </label>
            <label class="form-control w-full">
                <span class="label-text font-semibold text-sm">Unit Price</span>
                <input type="number" name="expense_items[${itemIndex}][unit_price]" placeholder="0.00"
                    class="input input-bordered input-sm w-full" step="0.01" min="0" required>
            </label>
            <div class="flex items-end">
                <div class="form-control w-full">
                    <span class="label-text font-semibold text-sm">Subtotal</span>
                    <input type="text" class="input input-bordered input-sm w-full bg-base-200" disabled>
                </div>
            </div>
            <div class="flex items-end">
                <button type="button" class="btn btn-error btn-sm remove-item">Remove</button>
            </div>
        `;

        container.appendChild(newItem);
        attachEventListeners(newItem);
        updateRemoveButtons();
        calculateSubtotals();
        itemIndex++;
    });

    // Attach listeners to existing items
    container.querySelectorAll('.expense-item').forEach((item) => {
        attachEventListeners(item);
    });

    updateRemoveButtons();
    calculateSubtotals();

    if (form) {
        import('./offline/form-offline.js').then(({ wireOfflineFormSubmit }) => {
            wireOfflineFormSubmit({
                form,
                module: 'expenses',
            });
        });
    }
});