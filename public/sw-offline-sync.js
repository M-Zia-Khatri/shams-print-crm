/* Background Sync bridge: wake open clients so they can run Dexie replay(). */
self.addEventListener('sync', (event) => {
    if (event.tag !== 'shams-offline-sync') {
        return;
    }

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            clients.forEach((client) => {
                client.postMessage({ type: 'SHAMS_OFFLINE_SYNC' });
            });
        }),
    );
});
