document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('bulk-payment-rows');
    const template = document.getElementById('bulk-payment-row-template');
    const addButton = document.getElementById('add-bulk-row-button');

    if (!container || !template || !addButton) return;

    let index = container.querySelectorAll('.bulk-payment-row').length;

    function attachRemoveHandler(row) {
        const removeButton = row.querySelector('.remove-bulk-row');
        if (!removeButton) return;

        removeButton.addEventListener('click', function () {
            row.remove();
        });
    }

    container.querySelectorAll('.bulk-payment-row').forEach(attachRemoveHandler);

    addButton.addEventListener('click', function () {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', index);
        const row = wrapper.firstElementChild;

        const removeButton = row.querySelector('.remove-bulk-row');
        if (removeButton) {
            removeButton.classList.remove('hidden');
        }

        container.appendChild(row);
        attachRemoveHandler(row);
        index += 1;
    });
});
