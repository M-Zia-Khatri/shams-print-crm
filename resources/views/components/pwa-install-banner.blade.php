@props([
    'dismissKey' => 'pwa-install-dismissed',
])

<div id="pwa-install-banner" class="alert alert-info shadow-sm mb-4 hidden items-center justify-between fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-4 sm:max-w-sm z-50">
    <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 16.5V9.75m0 6.75-3-3m3 3 3-3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
        </svg>
        <span>Install Shams Print CRM for faster, offline-ready access.</span>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <button type="button" id="pwa-install-accept" class="btn btn-sm btn-primary">Install</button>
        <button type="button" id="pwa-install-dismiss" class="btn btn-sm btn-ghost">Dismiss</button>
    </div>
</div>

<script>
    (function () {
        var DISMISS_KEY = @json($dismissKey);
        var banner = document.getElementById('pwa-install-banner');
        var acceptBtn = document.getElementById('pwa-install-accept');
        var dismissBtn = document.getElementById('pwa-install-dismiss');

        if (!banner || !acceptBtn || !dismissBtn) {
            return;
        }

        function isDismissed() {
            try {
                return localStorage.getItem(DISMISS_KEY) === '1';
            } catch (e) {
                return false;
            }
        }

        function setDismissed() {
            try {
                localStorage.setItem(DISMISS_KEY, '1');
            } catch (e) {
                // Ignore storage errors (private browsing, quota, etc.).
            }
        }

        function showBanner() {
            if (isDismissed()) {
                return;
            }
            banner.classList.remove('hidden');
            banner.classList.add('flex');
        }

        function hideBanner() {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }

        window.addEventListener('pwa-install-available', showBanner);

        acceptBtn.addEventListener('click', function () {
            var deferredPrompt = window.__deferredInstallPrompt;
            hideBanner();

            if (!deferredPrompt) {
                return;
            }

            deferredPrompt.prompt();
            deferredPrompt.userChoice.finally(function () {
                window.__deferredInstallPrompt = null;
            });
        });

        dismissBtn.addEventListener('click', function () {
            setDismissed();
            hideBanner();
        });

        window.addEventListener('appinstalled', function () {
            setDismissed();
            hideBanner();
        });

        // In case the beforeinstallprompt event fired before this listener attached.
        if (window.__deferredInstallPrompt) {
            showBanner();
        }
    })();
</script>