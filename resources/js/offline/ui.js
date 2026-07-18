import { getPending } from './sync-queue.js';
import db from './db.js';

const SYNC_POLL_INTERVAL_MS = 4000;

/**
 * @param {'idle'|'syncing'|'error'} state
 * @param {number|null} lastSyncAt
 * @param {number} pendingCount
 * @param {number} failedCount
 */
function dispatchSyncStatus(state, lastSyncAt, pendingCount, failedCount) {
    window.dispatchEvent(new CustomEvent('pwa-sync-status', {
        detail: { state, lastSyncAt, pendingCount, failedCount },
    }));
}

async function pollSyncStatus() {
    let pendingCount = 0;
    let failedCount = 0;
    let lastSyncAt = null;

    try {
        const pending = await getPending();
        pendingCount = pending.length;
    } catch (error) {
        console.error('Failed to read pending sync requests', error);
    }

    try {
        failedCount = await db.pending_requests.where('status').equals('failed').count();
    } catch (error) {
        console.error('Failed to read failed sync requests', error);
    }

    try {
        const lastSuccess = await db.offline_logs
            .where('level')
            .equals('success')
            .last();

        if (lastSuccess) {
            lastSyncAt = lastSuccess.created_at;
        }
    } catch (error) {
        console.error('Failed to read sync logs', error);
    }

    let state = 'idle';

    if (pendingCount > 0 && navigator.onLine) {
        // A pending queue while online implies the 'online'/service-worker
        // message replay() wired in form-offline.js is (or is about to be)
        // draining it. This module intentionally never calls replay()
        // itself, to avoid double-draining the same queue.
        state = 'syncing';
    } else if (failedCount > 0) {
        state = 'error';
    }

    dispatchSyncStatus(state, lastSyncAt, pendingCount, failedCount);
}

function startSyncStatusPolling() {
    pollSyncStatus();
    window.setInterval(pollSyncStatus, SYNC_POLL_INTERVAL_MS);
    window.addEventListener('online', pollSyncStatus);
    window.addEventListener('offline', pollSyncStatus);
}

function wireInstallPrompt() {
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        window.__deferredInstallPrompt = event;
        window.dispatchEvent(new CustomEvent('pwa-install-available'));
    });

    window.addEventListener('appinstalled', () => {
        window.__deferredInstallPrompt = null;
    });
}

function wireServiceWorkerUpdates() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    // vite-plugin-pwa is configured with registerType: 'prompt' and
    // injectRegister: false in vite.config.js, so registration must be
    // triggered manually via the plugin's virtual module. No prior
    // registration call existed anywhere in the codebase before this file.
    import('virtual:pwa-register')
        .then(({ registerSW }) => {
            const updateSW = registerSW({
                onNeedRefresh() {
                    window.__pwaUpdateAvailable = true;
                    window.dispatchEvent(new CustomEvent('pwa-update-available'));
                },
                onOfflineReady() {
                    // No UI action needed; navigateFallback + precache
                    // already cover the "ready for offline use" case.
                },
            });

            window.__pwaUpdateSW = updateSW;
        })
        .catch((error) => {
            console.error('Service worker registration module failed to load', error);
        });
}

wireInstallPrompt();
wireServiceWorkerUpdates();
startSyncStatusPolling();