@props([])

<div id="pwa-update-banner" class="alert alert-warning shadow-sm mb-4 hidden items-center justify-between fixed bottom-24 inset-x-4 sm:inset-x-auto sm:right-4 sm:max-w-sm z-50">
    <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        <span>A new version of Shams Print CRM is available.</span>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <button type="button" id="pwa-update-reload" class="btn btn-sm btn-primary">Reload</button>
        <button type="button" id="pwa-update-dismiss" class="btn btn-sm btn-ghost">Later</button>
    </div>
</div>

<script>
    (function () {
        var banner = document.getElementById('pwa-update-banner');
        var reloadBtn = document.getElementById('pwa-update-reload');
        var dismissBtn = document.getElementById('pwa-update-dismiss');

        if (!banner || !reloadBtn || !dismissBtn) {
            return;
        }

        function showBanner() {
            banner.classList.remove('hidden');
            banner.classList.add('flex');
        }

        function hideBanner() {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }

        window.addEventListener('pwa-update-available', showBanner);

        reloadBtn.addEventListener('click', function () {
            reloadBtn.disabled = true;

            if (typeof window.__pwaUpdateSW === 'function') {
                // registerSW()'s returned function; passing true forces
                // skipWaiting + controllerchange reload per vite-plugin-pwa.
                window.__pwaUpdateSW(true);
                return;
            }

            window.location.reload();
        });

        dismissBtn.addEventListener('click', hideBanner);

        // In case the update was detected before this listener attached.
        if (window.__pwaUpdateAvailable) {
            showBanner();
        }
    })();
</script>