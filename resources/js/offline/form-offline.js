import { enqueue, replay } from './sync-queue.js';

/**
 * Convert FormData into a Dexie-safe serializable structure (supports File/Blob).
 *
 * @param {FormData} formData
 * @returns {Promise<Array<{key: string, kind: 'string'|'file', value?: string, name?: string, type?: string, lastModified?: number, blob?: Blob}>>}
 */
export async function serializeFormData(formData) {
    const entries = [];

    for (const [key, value] of formData.entries()) {
        if (value instanceof File) {
            if (value.size === 0 && value.name === '') {
                continue;
            }

            entries.push({
                key,
                kind: 'file',
                name: value.name,
                type: value.type,
                lastModified: value.lastModified,
                blob: value,
            });
            continue;
        }

        entries.push({
            key,
            kind: 'string',
            value: String(value),
        });
    }

    return entries;
}

/**
 * Convert FormData to a plain object (string values only; last-wins for duplicate keys).
 *
 * @param {FormData} formData
 * @returns {Record<string, string>}
 */
export function formDataToPlainObject(formData) {
    /** @type {Record<string, string>} */
    const payload = {};

    for (const [key, value] of formData.entries()) {
        if (value instanceof File) {
            continue;
        }

        payload[key] = String(value);
    }

    return payload;
}

/**
 * Show a short toast confirming an offline queue.
 *
 * @param {string} message
 */
export function showQueuedToast(message = 'Saved offline — will sync when you are back online.') {
    let toast = document.getElementById('offline-queue-toast');

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'offline-queue-toast';
        toast.className =
            'toast toast-top toast-end z-[100]';
        toast.innerHTML = `
            <div class="alert alert-info shadow-lg">
                <span data-toast-message></span>
            </div>
        `;
        document.body.appendChild(toast);
    }

    const messageNode = toast.querySelector('[data-toast-message]');
    if (messageNode) {
        messageNode.textContent = message;
    }

    toast.classList.remove('hidden');

    window.clearTimeout(showQueuedToast._timer);
    showQueuedToast._timer = window.setTimeout(() => {
        toast.classList.add('hidden');
    }, 4000);
}

/**
 * Register online + Background Sync hooks that call replay().
 */
export function registerSyncListeners() {
    if (registerSyncListeners._bound) {
        return;
    }

    registerSyncListeners._bound = true;

    window.addEventListener('online', () => {
        replay().catch((error) => {
            console.error('Offline sync replay failed', error);
        });
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data?.type === 'SHAMS_OFFLINE_SYNC') {
                replay().catch((error) => {
                    console.error('Offline sync replay failed', error);
                });
            }
        });
    }
}

/**
 * Ask the service worker to schedule a Background Sync tag.
 *
 * @returns {Promise<void>}
 */
export async function requestBackgroundSync() {
    if (!('serviceWorker' in navigator) || !('SyncManager' in window)) {
        return;
    }

    try {
        const registration = await navigator.serviceWorker.ready;
        await registration.sync.register('shams-offline-sync');
    } catch (error) {
        console.warn('Background Sync registration failed', error);
    }
}

/**
 * Wire a standard HTML form for offline-first enqueue when offline or network fails.
 * Online successful submits keep the native browser POST behavior.
 *
 * @param {object} options
 * @param {HTMLFormElement} options.form
 * @param {string} options.module
 * @param {() => void} [options.beforeNativeSubmit]
 */
export function wireOfflineFormSubmit({ form, module, beforeNativeSubmit }) {
    if (!form || form.dataset.offlineWired === 'true') {
        return;
    }

    form.dataset.offlineWired = 'true';
    registerSyncListeners();

    form.addEventListener(
        'submit',
        async (event) => {
            beforeNativeSubmit?.();

            if (navigator.onLine) {
                return;
            }

            event.preventDefault();

            const formData = new FormData(form);
            const payload = await serializeFormData(formData);
            const csrf =
                formData.get('_token') ||
                document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value ||
                '';

            await enqueue({
                url: form.getAttribute('action') || window.location.href,
                method: (form.getAttribute('method') || 'POST').toUpperCase(),
                module,
                payload,
                headers: csrf
                    ? {
                          'X-CSRF-TOKEN': String(csrf),
                          'X-Requested-With': 'XMLHttpRequest',
                          Accept: 'text/html, application/xhtml+xml',
                      }
                    : {
                          'X-Requested-With': 'XMLHttpRequest',
                          Accept: 'text/html, application/xhtml+xml',
                      },
            });

            await requestBackgroundSync();
            showQueuedToast();
        },
        true,
    );
}
