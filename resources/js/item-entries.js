function escapeValue(value) {
    return String(value ?? '').replace(/[&<>\"]/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '\"': '&quot;',
    })[character]);
}

function fieldName(pieceIndex, group, rowIndex, field) {
    return `pieces[${pieceIndex}][${group}][${rowIndex}][${field}]`;
}

function inputName(pieceIndex, field) {
    return `pieces[${pieceIndex}][${field}]`;
}

function option(value, label, selected = false) {
    return `<option value="${escapeValue(value)}"${selected ? ' selected' : ''}>${escapeValue(label)}</option>`;
}

function colorRow(form, pieceIndex, rowIndex, color = {}) {
    const types = JSON.parse(form.dataset.colorTypes || '[]');
    const knownType = types.includes(color.type);
    const selectedType = knownType ? color.type : (color.type ? '__new__' : '');
    const options = [option('', 'Select type', !selectedType)]
        .concat(types.map((type) => option(type, type, selectedType === type)))
        .concat(option('__new__', 'Add new type', selectedType === '__new__'))
        .join('');

    return `
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end rounded-xl border border-base-300 p-3" data-color-row>
            <label class="form-control md:col-span-3">
                <span class="label-text font-semibold">Type</span>
                <select class="select select-bordered rounded-xl" data-color-type-select>${options}</select>
                <input class="input input-bordered rounded-xl mt-2 ${selectedType === '__new__' ? '' : 'hidden'}" data-new-color-type placeholder="New type" value="${knownType ? '' : escapeValue(color.type)}">
                <input type="hidden" name="${fieldName(pieceIndex, 'colors', rowIndex, 'type')}" data-color-type-value value="${escapeValue(color.type)}">
            </label>
            <label class="form-control md:col-span-3">
                <span class="label-text font-semibold">Rate</span>
                <input type="number" min="0" step="0.01" name="${fieldName(pieceIndex, 'colors', rowIndex, 'rate')}" class="input input-bordered rounded-xl" value="${escapeValue(color.rate)}" required>
            </label>
            <label class="form-control md:col-span-4">
                <span class="label-text font-semibold">Color Count</span>
                <input type="number" min="1" name="${fieldName(pieceIndex, 'colors', rowIndex, 'type_color_count')}" class="input input-bordered rounded-xl" value="${escapeValue(color.type_color_count)}" required>
            </label>
            <button type="button" class="btn btn-error text-error-content rounded-xl md:col-span-2" data-delete-row>Delete</button>
        </div>`;
}

function sizeRow(pieceIndex, rowIndex, size = {}) {
    return `
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end rounded-xl border border-base-300 p-3" data-size-row>
            <label class="form-control md:col-span-5">
                <span class="label-text font-semibold">Size</span>
                <input name="${fieldName(pieceIndex, 'sizes', rowIndex, 'size')}" class="input input-bordered rounded-xl" value="${escapeValue(size.size)}" required>
            </label>
            <label class="form-control md:col-span-5">
                <span class="label-text font-semibold">Percentage</span>
                <input type="number" min="0" max="100" step="0.01" name="${fieldName(pieceIndex, 'sizes', rowIndex, 'percentage')}" class="input input-bordered rounded-xl" value="${escapeValue(size.percentage)}" required>
            </label>
            <button type="button" class="btn btn-error text-error-content rounded-xl md:col-span-2" data-delete-row>Delete</button>
        </div>`;
}

function pieceBlock(form, pieceIndex, piece = {}) {
    const colors = piece.colors?.length ? piece.colors : [{}];
    const sizes = piece.sizes?.length ? piece.sizes : [{}];
    const colorsHtml = colors.map((color, rowIndex) => colorRow(form, pieceIndex, rowIndex, color)).join('');
    const sizesHtml = sizes.map((size, rowIndex) => sizeRow(pieceIndex, rowIndex, size)).join('');

    return `
        <section class="card bg-base-200/50 border border-base-300 rounded-2xl" data-piece>
            <div class="card-body p-4 sm:p-5 space-y-4">
                <div class="flex justify-between gap-3 items-center">
                    <h3 class="font-bold text-lg">Piece <span data-piece-number>${pieceIndex + 1}</span></h3>
                    <button type="button" class="btn btn-error btn-sm text-error-content rounded-xl" data-delete-piece>Delete Piece</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="form-control">
                        <span class="label-text font-bold mb-1">Piece Name</span>
                        <input name="${inputName(pieceIndex, 'name')}" class="input input-bordered rounded-xl" value="${escapeValue(piece.name)}" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text font-bold mb-1">Total Pieces</span>
                        <input type="number" min="1" name="${inputName(pieceIndex, 'total_pieces')}" class="input input-bordered rounded-xl" value="${escapeValue(piece.total_pieces)}" required>
                    </label>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="font-bold">Colors</h4>
                            <button type="button" class="btn btn-outline btn-sm rounded-xl" data-add-color>Add Color</button>
                        </div>
                        <div class="space-y-3" data-colors-container>${colorsHtml}</div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="font-bold">Sizes</h4>
                            <button type="button" class="btn btn-outline btn-sm rounded-xl" data-add-size>Add Size</button>
                        </div>
                        <div class="space-y-3" data-sizes-container>${sizesHtml}</div>
                    </div>
                </div>
            </div>
        </section>`;
}

function refreshNames(form) {
    const pieces = [...form.querySelectorAll('[data-piece]')];
    pieces.forEach((piece, pieceIndex) => {
        piece.querySelector('[data-piece-number]').textContent = pieceIndex + 1;
        piece.querySelector('[name$="[name]"]').name = inputName(pieceIndex, 'name');
        piece.querySelector('[name$="[total_pieces]"]').name = inputName(pieceIndex, 'total_pieces');
        [...piece.querySelectorAll('[data-color-row]')].forEach((row, rowIndex) => {
            row.querySelector('[data-color-type-value]').name = fieldName(pieceIndex, 'colors', rowIndex, 'type');
            row.querySelector('[name$="[rate]"]').name = fieldName(pieceIndex, 'colors', rowIndex, 'rate');
            row.querySelector('[name$="[type_color_count]"]').name = fieldName(pieceIndex, 'colors', rowIndex, 'type_color_count');
        });
        [...piece.querySelectorAll('[data-size-row]')].forEach((row, rowIndex) => {
            row.querySelector('[name$="[size]"]').name = fieldName(pieceIndex, 'sizes', rowIndex, 'size');
            row.querySelector('[name$="[percentage]"]').name = fieldName(pieceIndex, 'sizes', rowIndex, 'percentage');
        });
    });
}

function syncColorType(row) {
    const select = row.querySelector('[data-color-type-select]');
    const newInput = row.querySelector('[data-new-color-type]');
    const hidden = row.querySelector('[data-color-type-value]');
    const isNew = select.value === '__new__';

    newInput.classList.toggle('hidden', !isNew);
    hidden.value = isNew ? newInput.value : select.value;
    newInput.required = isNew;
}

function initItemEntryForm(form) {
    const container = form.querySelector('[data-pieces-container]');
    const entry = JSON.parse(form.dataset.entry || 'null');
    const pieces = entry?.pieces?.length ? entry.pieces : [{}];

    container.innerHTML = pieces.map((piece, pieceIndex) => pieceBlock(form, pieceIndex, piece)).join('');
    form.querySelectorAll('[data-color-row]').forEach(syncColorType);

    form.addEventListener('click', (event) => {
        const target = event.target;

        if (target.matches('[data-add-piece]')) {
            container.insertAdjacentHTML('beforeend', pieceBlock(form, container.querySelectorAll('[data-piece]').length, {}));
        }

        if (target.matches('[data-delete-piece]')) {
            target.closest('[data-piece]').remove();
            if (!container.querySelector('[data-piece]')) {
                container.insertAdjacentHTML('beforeend', pieceBlock(form, 0, {}));
            }
            refreshNames(form);
        }

        if (target.matches('[data-add-color]')) {
            const piece = target.closest('[data-piece]');
            const pieceIndex = [...container.querySelectorAll('[data-piece]')].indexOf(piece);
            const colorsContainer = piece.querySelector('[data-colors-container]');
            colorsContainer.insertAdjacentHTML('beforeend', colorRow(form, pieceIndex, colorsContainer.querySelectorAll('[data-color-row]').length, {}));
        }

        if (target.matches('[data-add-size]')) {
            const piece = target.closest('[data-piece]');
            const pieceIndex = [...container.querySelectorAll('[data-piece]')].indexOf(piece);
            const sizesContainer = piece.querySelector('[data-sizes-container]');
            sizesContainer.insertAdjacentHTML('beforeend', sizeRow(pieceIndex, sizesContainer.querySelectorAll('[data-size-row]').length, {}));
        }

        if (target.matches('[data-delete-row]')) {
            const parentContainer = target.closest('[data-colors-container], [data-sizes-container]');
            target.closest('[data-color-row], [data-size-row]').remove();
            if (!parentContainer.children.length) {
                const piece = parentContainer.closest('[data-piece]');
                const pieceIndex = [...container.querySelectorAll('[data-piece]')].indexOf(piece);
                parentContainer.insertAdjacentHTML('beforeend', parentContainer.matches('[data-colors-container]') ? colorRow(form, pieceIndex, 0, {}) : sizeRow(pieceIndex, 0, {}));
            }
            refreshNames(form);
        }
    });

    form.addEventListener('change', (event) => {
        if (event.target.matches('[data-color-type-select]')) {
            syncColorType(event.target.closest('[data-color-row]'));
        }
    });

    form.addEventListener('input', (event) => {
        if (event.target.matches('[data-new-color-type]')) {
            syncColorType(event.target.closest('[data-color-row]'));
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-item-entry-form]').forEach(initItemEntryForm);
});
