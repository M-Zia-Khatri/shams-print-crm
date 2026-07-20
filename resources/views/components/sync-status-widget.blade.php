@props([])

<div id="sync-status-widget" class="fixed bottom-28 left-4 z-50 hidden items-center gap-2 bg-base-100 border border-base-300 shadow-sm rounded-full px-3 py-1.5">
    <span id="sync-status-badge" class="badge badge-ghost badge-sm">Idle</span>
    <span id="sync-status-last" class="text-xs text-base-content/60">No syncs yet</span>
    <span id="sync-status-data" class="text-xs text-base-content/60">No data sync yet</span>
</div>

<script>
    (function () {
        var widget = document.getElementById('sync-status-widget');
        var badgeEl = document.getElementById('sync-status-badge');
        var lastEl = document.getElementById('sync-status-last');
        var dataEl = document.getElementById('sync-status-data');

        if (!widget || !badgeEl || !lastEl || !dataEl) {
            return;
        }

        function formatTime(timestamp) {
            if (!timestamp) {
                return 'No syncs yet';
            }

            try {
                return 'Last sync ' + new Date(timestamp).toLocaleTimeString();
            } catch (e) {
                return 'No syncs yet';
            }
        }

        function badgeClassFor(state) {
            return {
                idle: 'badge badge-ghost badge-sm',
                syncing: 'badge badge-info badge-sm',
                error: 'badge badge-error badge-sm',
            }[state] || 'badge badge-ghost badge-sm';
        }

        // Populated by resources/js/offline/ui.js, which polls sync-queue.js's
        // getPending() plus the offline_logs table and dispatches this event.
        window.addEventListener('pwa-sync-status', function (event) {
            var detail = event.detail || {};

            widget.classList.remove('hidden');
            widget.classList.add('flex');

            badgeEl.className = badgeClassFor(detail.state);
            badgeEl.textContent = detail.state === 'syncing'
                ? 'Syncing'
                : (detail.state === 'error' ? 'Sync error' : 'Idle');

            lastEl.textContent = formatTime(detail.lastSyncAt);

            if (detail.dataSyncState || detail.dataLastSyncAt) {
                dataEl.textContent = detail.dataSyncState === 'syncing'
                    ? 'Data sync running'
                    : formatTime(detail.dataLastSyncAt).replace('Last sync', 'Data synced');
            }
        });
    })();
</script>
