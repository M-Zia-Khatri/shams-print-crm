@props([])

<div id="offline-indicator" class="fixed bottom-4 left-4 z-50 flex items-center gap-2 bg-base-100 border border-base-300 shadow-sm rounded-full px-3 py-1.5">
    <span class="flex h-2.5 w-2.5 relative">
        <span id="offline-indicator-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
        <span id="offline-indicator-dot" class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
    </span>
    <span id="offline-indicator-label" class="text-xs font-bold text-base-content/70">Online</span>
</div>

<script>
    (function () {
        var pingEl = document.getElementById('offline-indicator-ping');
        var dotEl = document.getElementById('offline-indicator-dot');
        var labelEl = document.getElementById('offline-indicator-label');

        if (!pingEl || !dotEl || !labelEl) {
            return;
        }

        function render() {
            var online = navigator.onLine;

            labelEl.textContent = online ? 'Online' : 'Offline';

            dotEl.classList.toggle('bg-emerald-500', online);
            dotEl.classList.toggle('bg-error', !online);
            pingEl.classList.toggle('bg-emerald-400', online);
            pingEl.classList.toggle('bg-error', !online);
            pingEl.classList.toggle('hidden', !online);
        }

        window.addEventListener('online', render);
        window.addEventListener('offline', render);
        render();
    })();
</script>