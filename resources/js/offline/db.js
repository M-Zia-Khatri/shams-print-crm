import Dexie from 'dexie';

/**
 * Offline IndexedDB schema for queued form submissions and sync logs.
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

export default db;
