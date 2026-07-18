document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('shift-form');
    if (!form) return;

    const advancedToggle = document.getElementById('advanced-mode-toggle');
    const defaultShiftSelect = document.getElementById('default-shift-select');
    const advancedColumns = form.querySelectorAll('[data-advanced-column]');
    const overrideSelects = form.querySelectorAll('[data-employee-override]');

    function syncOverridesWithDefault() {
        overrideSelects.forEach((select) => {
            select.value = defaultShiftSelect.value;
        });
    }

    function toggleAdvancedMode() {
        const isAdvanced = advancedToggle.checked;
        advancedColumns.forEach((column) => column.classList.toggle('hidden', !isAdvanced));

        if (!isAdvanced) {
            syncOverridesWithDefault();
        }
    }

    advancedToggle.addEventListener('change', toggleAdvancedMode);
    defaultShiftSelect.addEventListener('change', () => {
        if (!advancedToggle.checked) {
            syncOverridesWithDefault();
        }
    });

    syncOverridesWithDefault();

    function prepareShiftFieldsForSubmit() {
        if (!advancedToggle.checked) {
            // Non-advanced mode: don't submit per-employee overrides, let the
            // server apply the shared default shift to every checked employee.
            overrideSelects.forEach((select) => {
                select.disabled = true;
            });
        }

        form.querySelectorAll('[data-employee-checkbox]').forEach((checkbox) => {
            if (!checkbox.checked) {
                const row = checkbox.closest('tr');
                const overrideSelect = row ? row.querySelector('[data-employee-override]') : null;
                if (overrideSelect) {
                    overrideSelect.disabled = true;
                }
            }
        });
    }

    form.addEventListener('submit', function () {
        prepareShiftFieldsForSubmit();
    });

    import('./offline/form-offline.js').then(({ wireOfflineFormSubmit }) => {
        wireOfflineFormSubmit({
            form,
            module: 'employee-shifts',
            beforeNativeSubmit: prepareShiftFieldsForSubmit,
        });
    });
});
