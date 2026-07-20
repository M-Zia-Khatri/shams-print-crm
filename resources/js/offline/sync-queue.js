import { db } from './db.js';

/**
 * Stable JSON stringify for duplicate detection (sorted object keys).
 *
 * @param {unknown} value
 * @returns {string}
 */
function stableStringify(value) {
    if (value === undefined) {
        return 'undefined';
    }

    if (value === null || typeof value !== 'object') {
        return JSON.stringify(value);
    }

    if (Array.isArray(value)) {
        return `[${value.map((item) => stableStringify(item)).join(',')}]`;
    }

    const keys = Object.keys(value).sort();

    return `{${keys.map((key) => `${JSON.stringify(key)}:${stableStringify(value[key])}`).join(',')}}`;
}

/**
 * @param {import('./db.js').PendingRequest} request
 * @returns {Promise<number>}
 */
export async function enqueue(request) {
    const createdAt = request.created_at ?? Date.now();

    const id = await db.pending_requests.add({
        url: request.url,
        method: (request.method ?? 'POST').toUpperCase(),
        module: request.module ?? 'unknown',
        payload: request.payload ?? {},
        headers: request.headers ?? {},
        status: 'pending',
        created_at: createdAt,
    });

    await db.offline_logs.add({
        pending_request_id: id,
        level: 'info',
        message: `Enqueued ${request.method ?? 'POST'} ${request.url}`,
        created_at: Date.now(),
    });

    return id;
}

/**
 * @returns {Promise<import('./db.js').PendingRequest[]>}
 */
export async function getPending() {
    return db.pending_requests
        .where('status')
        .equals('pending')
        .sortBy('created_at');
}

/**
 * @param {number} id
 * @returns {Promise<void>}
 */
export async function markSynced(id) {
    await db.pending_requests.update(id, { status: 'synced' });

    await db.offline_logs.add({
        pending_request_id: id,
        level: 'success',
        message: `Synced request #${id}`,
        created_at: Date.now(),
    });
}

/**
 * @param {number} id
 * @param {string|Error} error
 * @returns {Promise<void>}
 */
export async function markFailed(id, error) {
    const message = error instanceof Error ? error.message : String(error);

    await db.pending_requests.update(id, { status: 'failed' });

    await db.offline_logs.add({
        pending_request_id: id,
        level: 'failure',
        message: `Failed request #${id}: ${message}`,
        created_at: Date.now(),
    });
}

/**
 * Skip replay when an identical url + payload + created_at already succeeded.
 *
 * @param {import('./db.js').PendingRequest} row
 * @returns {Promise<boolean>}
 */
async function alreadySucceededDuplicate(row) {
    const synced = await db.pending_requests
        .where('[url+created_at]')
        .equals([row.url, row.created_at])
        .and((candidate) => candidate.status === 'synced' && candidate.id !== row.id)
        .toArray();

    const payloadKey = stableStringify(row.payload);

    return synced.some((candidate) => stableStringify(candidate.payload) === payloadKey);
}

/**
 * Build a Request body from a stored payload.
 *
 * Supports:
 * - serialized FormData entry arrays from form-offline.js
 * - plain objects of string values
 * - raw strings
 *
 * @param {unknown} payload
 * @returns {BodyInit}
 */
function buildBody(payload) {
    if (typeof payload === 'string') {
        return payload;
    }

    const formData = new FormData();

    if (Array.isArray(payload)) {
        payload.forEach((entry) => {
            if (!entry || typeof entry !== 'object' || !('key' in entry)) {
                return;
            }

            if (entry.kind === 'file' && entry.blob) {
                const file = new File([entry.blob], entry.name || 'file', {
                    type: entry.type || entry.blob.type || 'application/octet-stream',
                    lastModified: entry.lastModified || Date.now(),
                });
                formData.append(entry.key, file);
                return;
            }

            formData.append(entry.key, entry.value ?? '');
        });

        return formData;
    }

    Object.entries(payload ?? {}).forEach(([key, value]) => {
        if (value === null || value === undefined) {
            return;
        }

        if (Array.isArray(value)) {
            value.forEach((item) => {
                formData.append(`${key}[]`, item === null || item === undefined ? '' : String(item));
            });

            return;
        }

        formData.append(key, String(value));
    });

    return formData;
}

/**
 * Replay all pending requests in created_at order.
 *
 * @returns {Promise<{ synced: number, failed: number, skipped: number }>}
 */
export async function replay() {
    const pending = await getPending();
    let synced = 0;
    let failed = 0;
    let skipped = 0;

    for (const row of pending) {
        if (await alreadySucceededDuplicate(row)) {
            await markSynced(row.id);
            skipped += 1;

            await db.offline_logs.add({
                pending_request_id: row.id,
                level: 'info',
                message: `Skipped duplicate request #${row.id}`,
                created_at: Date.now(),
            });

            continue;
        }

        await db.offline_logs.add({
            pending_request_id: row.id,
            level: 'retry',
            message: `Replaying request #${row.id}`,
            created_at: Date.now(),
        });

        try {
            const headers = { ...(row.headers ?? {}) };
            const body = buildBody(row.payload);

            // FormData sets its own Content-Type with boundary; drop a stale one.
            if (body instanceof FormData && headers['Content-Type']) {
                delete headers['Content-Type'];
            }

            const response = await fetch(row.url, {
                method: row.method || 'POST',
                headers,
                body,
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            await markSynced(row.id);
            synced += 1;
        } catch (error) {
            await markFailed(row.id, error);
            failed += 1;
        }
    }

    return { synced, failed, skipped };
}

export default {
    enqueue,
    getPending,
    markSynced,
    markFailed,
    replay,
};
