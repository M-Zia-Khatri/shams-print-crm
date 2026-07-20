@props([])

<div id="sync-status-widget" class="fixed bottom-32 left-4 z-40 hidden flex-col gap-1.5 bg-base-100 border border-base-300 shadow-sm rounded-xl px-3 py-2 text-xs max-w-[16rem]">
    <div class="flex items-center gap-2">
        <span id="sync-status-badge" class="badge badge-ghost badge-sm">Idle</span>
        <span id="sync-status-last" class="text-base-content/60">No queued syncs yet</span>
    </div>
    <div id="sync-status-data-row" class="hidden items-center gap-1.5 border-t border-base-300 pt-1.5">
        <span class="font-semibold text-base-content/50">Data:</span>
        <span id="sync-status-data" class="text-base-content/60"></span>
    </div>
</div>

<script>
    (function () {
        var widget = document.getElementById('sync-status-widget');
        var badgeEl = document.getElementById('sync-status-badge');
        var lastEl = document.getElementById('sync-status-last');
        var dataRowEl = document.getElementById('sync-status-data-row');
        var dataEl = document.getElementById('sync-status-data');

        if (!widget || !badgeEl || !lastEl || !dataRowEl || !dataEl) {
            return;
        }

        function formatTime(timestamp) {
            if (!timestamp) {
                return null;
            }

            try {
                return new Date(timestamp).toLocaleTimeString();
            } catch (e) {
                return null;
            }
        }

        function badgeClassFor(state) {
            return {
                idle: 'badge badge-ghost badge-sm',
                syncing: 'badge badge-info badge-sm',
                error: 'badge badge-error badge-sm',
            }[state] || 'badge badge-ghost badge-sm';
        }

        // Two independent subsystems dispatch this same event:
        // - resources/js/offline/ui.js -> write-queue replay status
        //   (`state`, `lastSyncAt`, `pendingCount`, `failedCount`)
        // - resources/js/offline/data-sync.js -> read-cache pull status
        //   (`dataSyncState`, `dataLastSyncAt`)
        // Each dispatch only carries its own keys, so each handler below
        // only touches the row it owns. This keeps "queue never synced"
        // and "data synced at 7:51 PM" from ever being merged into one
        // contradictory sentence.
        window.addEventListener('pwa-sync-status', function (event) {
            var detail = event.detail || {};
            var isQueueUpdate = 'state' in detail;
            var isDataUpdate = 'dataSyncState' in detail || 'dataLastSyncAt' in detail;

            widget.classList.remove('hidden');
            widget.classList.add('flex');

            if (isQueueUpdate) {
                badgeEl.className = badgeClassFor(detail.state);
                badgeEl.textContent = detail.state === 'syncing'
                    ? 'Syncing'
                    : (detail.state === 'error' ? 'Sync error' : 'Idle');

                var queueTime = formatTime(detail.lastSyncAt);
                lastEl.textContent = queueTime ? ('Queue synced ' + queueTime) : 'No queued syncs yet';
            }

            if (isDataUpdate) {
                var dataTime = formatTime(detail.dataLastSyncAt);

                if (detail.dataSyncState === 'syncing') {
                    dataEl.textContent = 'Syncing…';
                    dataRowEl.classList.remove('hidden');
                    dataRowEl.classList.add('flex');
                } else if (dataTime) {
                    dataEl.textContent = 'Synced ' + dataTime;
                    dataRowEl.classList.remove('hidden');
                    dataRowEl.classList.add('flex');
                } else {
                    dataRowEl.classList.add('hidden');
                    dataRowEl.classList.remove('flex');
                }
            }
        });
    })();
</script>