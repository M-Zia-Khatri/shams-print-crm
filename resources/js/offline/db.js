import Dexie from 'dexie';

/**
 * Offline IndexedDB schema for queued form submissions, sync logs, and read-only mirrors.
 *
 * @typedef {object} OfflineForm
 * @property {number} [id]
 * @property {string} module
 * @property {Record<string, unknown>} payload
 * @property {number} created_at
 *
 * @typedef {object} PendingRequest
 * @property {number} [id]
 * @property {string} url
 * @property {string} method
 * @property {string} module
 * @property {Record<string, unknown>|string} payload
 * @property {Record<string, string>} [headers]
 * @property {'pending'|'synced'|'failed'} status
 * @property {number} created_at
 *
 * @typedef {object} OfflineLog
 * @property {number} [id]
 * @property {number|null} pending_request_id
 * @property {'success'|'failure'|'retry'|'info'} level
 * @property {string} message
 * @property {number} created_at
 */

export const db = new Dexie('shams_offline');

db.version(1).stores({
    offline_forms: '++id, module, created_at',
    pending_requests: '++id, url, method, module, status, created_at, [url+created_at]',
    offline_logs: '++id, pending_request_id, level, created_at',
});

db.version(2).stores({
    offline_forms: '++id, module, created_at',
    pending_requests: '++id, url, method, module, status, created_at, [url+created_at]',
    offline_logs: '++id, pending_request_id, level, created_at',
    item_entries: 'id, updated_at',
    expenses: 'id, updated_at',
    employee_daily_laberi_entries: 'id, employee_id, laberi_date, updated_at',
    item_payment_receiveds: 'id, updated_at',
    dashboard_summary: 'key',
    sync_meta: 'resource',
});

export default db;
