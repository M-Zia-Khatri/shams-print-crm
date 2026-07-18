import { wireOfflineFormSubmit } from './offline/form-offline.js';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('daily-laberi-form');

    if (!form) {
        return;
    }

    wireOfflineFormSubmit({
        form,
        module: 'employee-daily-laberi',
    });
});
