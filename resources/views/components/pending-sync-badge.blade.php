@props([])

<div id="pending-sync-badge"
    class="fixed bottom-16 left-4 z-50 hidden items-center gap-1 bg-base-100 border border-base-300 shadow-sm rounded-full px-3 py-1.5">
    <span class="badge badge-error badge-sm font-bold" id="pending-sync-count">0</span>
    <span class="text-xs text-base-content/60">pending</span>
</div>

<script>
    (function() {
        var wrapper = document.getElementById('pending-sync-badge');
        var countEl = document.getElementById('pending-sync-count');

        if (!wrapper || !countEl) {
            return;
        }

        window.addEventListener('pwa-sync-status', function(event) {
            var detail = event.detail || {};
            var pending = typeof detail.pendingCount === 'number' ? detail.pendingCount : 0;

            countEl.textContent = String(pending);
            wrapper.classList.toggle('hidden', pending === 0);
            wrapper.classList.toggle('flex', pending > 0);
        });
    })();
</script>
