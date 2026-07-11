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
