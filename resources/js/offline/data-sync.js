import db from './db.js';

const resources = {
    itemEntries: {
        resource: 'item_entries',
        endpoint: '/api/sync/item-entries',
        store: 'item_entries',
        fields: ['lart_number', 'client_business_name', 'description', 'size_description', 'darjan', 'total_color', 'total_rate', 'total_amount'],
    },
    expenses: {
        resource: 'expenses',
        endpoint: '/api/sync/expenses',
        store: 'expenses',
        fields: ['expense_date', 'description', 'expense_list', 'total_expense'],
    },
    employeeDailyLaberi: {
        resource: 'employee_daily_laberi_entries',
        endpoint: '/api/sync/employee-daily-laberi',
        store: 'employee_daily_laberi_entries',
        fields: ['employee.name', 'laberi_date', 'daily_shift'],
    },
    itemPaymentReceiveds: {
        resource: 'item_payment_receiveds',
        endpoint: '/api/sync/item-payment-receiveds',
        store: 'item_payment_receiveds',
        fields: ['party_name', 'description', 'received_amount'],
    },
};

// Must match the `cacheName` of the "network-first-pages" runtimeCaching
// entry in vite.config.js. The dashboard route ('/') is only ever populated
// into this Cache Storage bucket by an intercepted navigation — which does
// not reliably happen on the same load that first registers the service
// worker. Explicitly priming it here (independent of whether the SW is
// currently controlling this page) guarantees offline.html is never shown
// for a dashboard that has actually completed a sync.
const DASHBOARD_CACHE_NAME = 'network-first-pages';
const DASHBOARD_CACHE_URL = '/';

let isFullSyncRunning = false;

function dispatchDataSyncStatus(state, lastSyncAt = null) {
    window.dispatchEvent(new CustomEvent('pwa-sync-status', {
        detail: { dataSyncState: state, dataLastSyncAt: lastSyncAt },
    }));
}

function valueForPath(row, path) {
    return path.split('.').reduce((value, segment) => value?.[segment], row);
}

function searchableValue(value) {
    if (value === null || value === undefined) {
        return '';
    }

    if (Array.isArray(value) || typeof value === 'object') {
        return JSON.stringify(value);
    }

    return String(value);
}

async function lastDataSyncTime() {
    const metaRows = await db.sync_meta.toArray();
    const syncTimes = metaRows
        .map((row) => row.last_sync)
        .filter(Boolean)
        .sort();

    return syncTimes.at(-1) ?? null;
}

/**
 * Explicitly write the current dashboard document into the same Cache
 * Storage bucket Workbox's NetworkFirst route uses for '/'. This makes the
 * offline dashboard available even if the service worker hadn't yet started
 * intercepting navigations on this visit (see comment above).
 */
async function warmDashboardCache() {
    if (!('caches' in window)) {
        return;
    }

    try {
        const response = await fetch(DASHBOARD_CACHE_URL, {
            credentials: 'same-origin',
            headers: { Accept: 'text/html' },
        });

        if (!response || !response.ok) {
            return;
        }

        const cache = await caches.open(DASHBOARD_CACHE_NAME);
        await cache.put(DASHBOARD_CACHE_URL, response.clone());
    } catch (error) {
        // Non-fatal: offline dashboard will simply fall back to offline.html
        // until the next successful sync manages to warm the cache.
        console.warn('Failed to warm offline dashboard cache', error);
    }
}

export async function syncDashboardSummary() {
    const response = await fetch('/api/sync/dashboard-summary', {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`Dashboard summary sync failed with HTTP ${response.status}`);
    }

    const summary = await response.json();
    await db.dashboard_summary.put({ ...summary, key: 'current' });
    await db.sync_meta.put({ resource: 'dashboard_summary', last_sync: summary.server_time });

    return summary;
}

export async function syncResource(name) {
    const config = resources[name];

    if (!config) {
        throw new Error(`Unknown sync resource: ${name}`);
    }

    const meta = await db.sync_meta.get(config.resource);
    const url = new URL(config.endpoint, window.location.origin);

    if (meta?.last_sync) {
        url.searchParams.set('since', meta.last_sync);
    }

    const response = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`${config.resource} sync failed with HTTP ${response.status}`);
    }

    const payload = await response.json();
    const rows = [...(payload.created ?? []), ...(payload.updated ?? [])];

    if (rows.length > 0) {
        await db[config.store].bulkPut(rows);
    }

    if ((payload.deleted ?? []).length > 0) {
        await db[config.store].bulkDelete(payload.deleted);
    }

    await db.sync_meta.put({ resource: config.resource, last_sync: payload.server_time });

    return payload;
}

export async function runFullSync() {
    if (isFullSyncRunning || !navigator.onLine) {
        return;
    }

    isFullSyncRunning = true;
    dispatchDataSyncStatus('syncing', await lastDataSyncTime());

    try {
        for (const name of Object.keys(resources)) {
            await syncResource(name);
        }

        await syncDashboardSummary();
        await warmDashboardCache();
        dispatchDataSyncStatus('idle', await lastDataSyncTime());
    } catch (error) {
        console.error('Offline read-cache sync failed', error);
        dispatchDataSyncStatus('error', await lastDataSyncTime());
    } finally {
        isFullSyncRunning = false;
    }
}

async function searchStore(name, query) {
    const config = resources[name];
    const normalizedQuery = String(query ?? '').trim().toLowerCase();

    if (normalizedQuery === '') {
        return db[config.store].toArray();
    }

    return db[config.store]
        .filter((row) => config.fields.some((field) => searchableValue(valueForPath(row, field)).toLowerCase().includes(normalizedQuery)))
        .toArray();
}

export function searchItemEntries(query) {
    return searchStore('itemEntries', query);
}

export function searchExpenses(query) {
    return searchStore('expenses', query);
}

export function searchEmployeeDailyLaberi(query) {
    return searchStore('employeeDailyLaberi', query);
}

export function searchItemPaymentReceiveds(query) {
    return searchStore('itemPaymentReceiveds', query);
}

export function exposeOfflineDataCache() {
    window.ShamsOfflineDataCache = {
        db,
        runFullSync,
        syncResource,
        searchItemEntries,
        searchExpenses,
        searchEmployeeDailyLaberi,
        searchItemPaymentReceiveds,
    };
}

export function startDataSync() {
    exposeOfflineDataCache();
    if (navigator.onLine) {
        runFullSync();
    }

    window.addEventListener('online', runFullSync);

    navigator.serviceWorker?.addEventListener('message', (event) => {
        if (event.data?.type === 'SHAMS_OFFLINE_SYNC') {
            runFullSync();
        }
    });
}

export default {
    runFullSync,
    syncResource,
    searchItemEntries,
    searchExpenses,
    searchEmployeeDailyLaberi,
    searchItemPaymentReceiveds,
    startDataSync,
    exposeOfflineDataCache,
};