document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('item-entry-form');
    if (!form) return;

    const DRAFT_KEY = 'item-entries-draft';
    const draftAlert = document.getElementById('draft-restored-alert');
    const discardButton = document.getElementById('discard-draft-button');
    const partyNamesDataNode = document.getElementById('party-names-data');
    
    let partyNames = [];
    if (partyNamesDataNode) {
        try {
            partyNames = JSON.parse(partyNamesDataNode.textContent);
        } catch (e) {
            console.error('Failed to parse party names', e);
        }
    }

    // --- Feature 1: Party Name Select with Fallback ---
    function initPartySelect(entryNode) {
        if (!partyNames || partyNames.length === 0) return;

        const input = entryNode.querySelector('input[name$="[client_business_name]"]');
        if (!input || input.hasAttribute('data-select-initialized')) return;
        input.setAttribute('data-select-initialized', 'true');

        const select = document.createElement('select');
        select.className = 'select select-bordered w-full';
        
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.text = 'Select Party Name';
        defaultOption.disabled = true;
        // Do NOT set selected = true unconditionally, let the logic below determine selection
        select.appendChild(defaultOption);

        partyNames.forEach(name => {
            const option = document.createElement('option');
            option.value = name;
            option.text = name;
            select.appendChild(option);
        });

        const addNewOption = document.createElement('option');
        addNewOption.value = '__ADD_NEW__';
        addNewOption.text = '+ Add New Party';
        select.appendChild(addNewOption);

        input.parentNode.insertBefore(select, input);

        function applySelection(isInitial = false) {
            if (select.value === '__ADD_NEW__') {
                input.type = 'text';
                input.required = true;
                if (!isInitial) {
                    input.value = '';
                }
                select.classList.add('hidden');
            } else if (select.value !== '') {
                input.type = 'hidden';
                input.required = false;
                input.value = select.value;
                select.classList.remove('hidden');
            } else {
                // If it is the default option
                input.type = 'hidden';
                input.required = true;
                input.value = '';
                select.classList.remove('hidden');
            }
            if (!isInitial) {
                triggerDraftSave();
            }
        }

        // Initialize state if input has existing value
        if (input.value && input.value !== '__ADD_NEW__') {
            if (partyNames.includes(input.value)) {
                select.value = input.value;
            } else {
                select.value = '__ADD_NEW__';
            }
        } else {
            select.value = '';
        }

        select.addEventListener('change', () => {
            applySelection(false);
        });

        applySelection(true);
    }

    function initializeNewEntries() {
        document.querySelectorAll('[data-entry-fields]').forEach(entryNode => {
            initPartySelect(entryNode);
        });
    }

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes) {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1 && node.hasAttribute('data-entry-fields')) {
                        initPartySelect(node);
                    } else if (node.nodeType === 1) {
                        const entries = node.querySelectorAll('[data-entry-fields]');
                        entries.forEach(entry => initPartySelect(entry));
                    }
                });
            }
        });
    });

    observer.observe(document.getElementById('entries-container'), { childList: true, subtree: true });
    initializeNewEntries();

    // --- Feature 2: Local-Draft Autosave ---
    let saveTimeout;
    let isSubmitting = false;
    
    function triggerDraftSave() {
        if (isSubmitting) return;
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveDraft, 600);
    }

    function saveDraft() {
        if (isSubmitting) return;
        const formData = new FormData(form);
        const draft = {};
        for (const [key, value] of formData.entries()) {
            if (value instanceof File || key === '_token' || key === '_method') continue;
            draft[key] = value;
        }
        localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
    }

    form.addEventListener('input', triggerDraftSave);
    form.addEventListener('change', triggerDraftSave);

    // Watch for node removals to trigger autosave (e.g. "Remove Entry" button)
    form.addEventListener('click', (e) => {
        if (e.target && e.target.textContent === 'Remove Entry') {
            triggerDraftSave();
        }
    });

    function restoreDraft() {
        // If there are server-side errors, do not restore layout that clobbers the old() inputs
        const hasErrors = document.querySelector('.alert-error');
        if (hasErrors) {
             // We won't restore draft if there was a server error, since the server old() repopulated it.
             // We re-hydrate the draft from the old() values directly.
             saveDraft();
             return;
        }

        const draftJson = localStorage.getItem(DRAFT_KEY);
        if (!draftJson) return;

        try {
            const draft = JSON.parse(draftJson);
            
            let maxIndex = 0;
            for (const key in draft) {
                const match = key.match(/^entries\[(\d+)\]/);
                if (match) {
                    const index = parseInt(match[1], 10);
                    if (index > maxIndex) maxIndex = index;
                }
            }

            const addButton = document.getElementById('add-entry-button');
            for (let i = 1; i <= maxIndex; i++) {
                if (addButton) addButton.click();
            }

            setTimeout(() => {
                for (const key in draft) {
                    const input = form.querySelector(`[name="${key}"]`);
                    // Specifically do not restore file inputs
                    if (input && input.type !== 'file') {
                        input.value = draft[key];
                    }
                }

                // Sync selects according to restored hidden input values
                document.querySelectorAll('input[name$="[client_business_name]"]').forEach(input => {
                    const select = input.previousElementSibling;
                    if (select && select.tagName === 'SELECT') {
                        if (partyNames.includes(input.value)) {
                            select.value = input.value;
                            select.classList.remove('hidden');
                            input.type = 'hidden';
                            input.required = false;
                        } else if (input.value) {
                            select.value = '__ADD_NEW__';
                            select.classList.add('hidden');
                            input.type = 'text';
                            input.required = true;
                        } else {
                            select.value = '';
                            select.classList.remove('hidden');
                            input.type = 'hidden';
                            input.required = true;
                        }
                    }
                });
                
                draftAlert.classList.remove('hidden');
                draftAlert.classList.add('flex');
            }, 0); // Allow DOM to update from simulated ADD button clicks

        } catch (e) {
            console.error('Failed to restore draft', e);
        }
    }

    if (discardButton) {
        discardButton.addEventListener('click', () => {
            localStorage.removeItem(DRAFT_KEY);
            window.location.reload();
        });
    }

    form.addEventListener('submit', () => {
        isSubmitting = true;
        clearTimeout(saveTimeout);
        // Clear local storage draft immediately upon form submission attempt.
        // If validation fails server-side, the page will reload with `.alert-error`
        // and our `restoreDraft()` logic will re-save the `old()` inputs into the draft.
        localStorage.removeItem(DRAFT_KEY);
    });

    import('./offline/form-offline.js').then(({ wireOfflineFormSubmit }) => {
        wireOfflineFormSubmit({
            form,
            module: 'item-entries',
        });
    });

    restoreDraft();
});
