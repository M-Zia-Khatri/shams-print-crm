document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-item-entries-filter-form]');

    if (!form) {
        return;
    }

    const searchInput = form.querySelector('[data-debounced-search]');
    const dateModeFilter = form.querySelector('[data-date-mode-filter]');
    const monthFilterWrapper = form.querySelector('[data-month-filter-wrapper]');
    let debounceTimer = null;

    const submitForm = () => {
        form.requestSubmit();
    };

    const toggleMonthFilter = () => {
        if (!monthFilterWrapper || !dateModeFilter) {
            return;
        }

        monthFilterWrapper.classList.toggle('hidden', dateModeFilter.value !== 'monthly');
    };

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(submitForm, 400);
        });
    }

    form.querySelectorAll('[data-immediate-filter]').forEach((filter) => {
        filter.addEventListener('change', () => {
            toggleMonthFilter();
            submitForm();
        });
    });

    toggleMonthFilter();
});

window.openPaymentModal = () => {
    const modal = document.getElementById('receivedPaymentModal');
    if (!modal) return;
    
    document.getElementById('modalTitle').textContent = 'Add Received Payment';
    document.getElementById('paymentId').value = '';
    const form = document.getElementById('paymentForm');
    form.reset();
    
    // reset CSRF input if wiped
    let csrfToken = window.csrfToken || document.querySelector('input[name="_token"]')?.value;
    if (csrfToken) {
        let tokenInput = form.querySelector('input[name="_token"]');
        if (tokenInput) tokenInput.value = csrfToken;
    }

    modal.showModal();
};

window.editPaymentModal = (id, description, partyName, amount) => {
    const modal = document.getElementById('receivedPaymentModal');
    if (!modal) return;

    document.getElementById('modalTitle').textContent = 'Edit Received Payment';
    document.getElementById('paymentId').value = id;
    document.getElementById('paymentDescription').value = description;
    document.getElementById('paymentPartyName').value = partyName;
    document.getElementById('paymentAmount').value = amount;
    
    modal.showModal();
};

window.deletePayment = async (id) => {
    if (!confirm('Are you sure you want to delete this received payment?')) return;
    
    try {
        const response = await fetch(`/item-payment-receiveds/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            window.location.reload();
        } else {
            alert('Error deleting payment');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
};

window.submitPaymentForm = async (e) => {
    e.preventDefault();
    
    const form = e.target;
    let formData = new FormData(form);
    const id = formData.get('payment_id');
    const url = id ? `/item-payment-receiveds/${id}` : '/item-payment-receiveds';
    
    if (id) {
        formData.append('_method', 'PUT');
    }

    try {
        const bgBtn = document.getElementById('savePaymentBtn');
        if (bgBtn) bgBtn.disabled = true;

        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
            }
        });
        
        if (response.ok) {
            document.getElementById('receivedPaymentModal').close();
            window.location.reload();
        } else {
            const data = await response.json();
            alert('Error: ' + (data.message || 'Validation failed'));
            if (bgBtn) bgBtn.disabled = false;
        }
    } catch (error) {
        alert('Error: ' + error.message);
        const bgBtn = document.getElementById('savePaymentBtn');
        if (bgBtn) bgBtn.disabled = false;
    }
};
