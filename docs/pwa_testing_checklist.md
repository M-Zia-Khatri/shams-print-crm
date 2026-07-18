# PWA Testing Checklist — Shams Print CRM

Manual QA checklist for Phases 1-6 of the PWA rollout. Run through this on
both a desktop Chromium browser and an Android Chrome device before signing
off a deploy.

## 1. Install Flow

### Android (Chrome)
- [ ] Visit the site over HTTPS (or `localhost`) while logged in.
- [ ] Confirm the `pwa-install-banner` appears (bottom of screen, "Install
      Shams Print CRM for faster, offline-ready access.").
- [ ] Tap **Install** → confirm the native Chrome install sheet appears →
      accept it.
- [ ] Confirm the app icon appears on the home screen using the 192/512
      icons from `public/manifest.json`.
- [ ] Launch from the home screen icon → confirm it opens in standalone
      mode (no browser chrome/address bar).
- [ ] Re-open the site in a normal browser tab afterward → confirm the
      install banner does **not** reappear (dismissed via `appinstalled`
      writing to `localStorage`).

### Desktop (Chrome/Edge)
- [ ] Confirm the install banner appears, or the browser's own
      install-icon appears in the address bar.
- [ ] Click **Install** in the banner → confirm the native install dialog
      appears → accept it.
- [ ] Confirm the app opens as a standalone window afterward.
- [ ] Click **Dismiss** on a fresh profile (no install) → confirm the
      banner does not reappear on reload (localStorage dismissal key).

## 2. Offline Load of Previously-Cached Pages

- [ ] While online, visit: `/`, `/item-entries`, `/expenses`, `/employees`,
      and one `/employees/{id}` page (populates the NetworkFirst cache).
- [ ] Go offline (DevTools → Network → Offline, or airplane mode).
- [ ] Reload each of the above pages → confirm they load from cache
      (no `offline.html` fallback shown for these).
- [ ] Visit a page not in the NetworkFirst/StaleWhileRevalidate list that
      was never visited while online (e.g. a fresh `/item-entries/{id}/edit`
      you haven't opened) → confirm `offline.html` is shown instead of a
      browser error page.
- [ ] Confirm the `offline-indicator` pill shows **Offline** (red dot,
      ping animation hidden) within the current tab.

## 3. Offline Submission + Queued Replay (approved modules only)

Repeat for each of the five wired modules. For each: go offline, submit the
form, confirm no error is shown to the user, confirm the item appears in
IndexedDB (`shams_offline` → `pending_requests`, status `pending`), then go
back online and confirm it syncs.

- [ ] **Item entries create** (`/item-entries/create`) — submit one entry
      offline (image upload included) → queued → reconnect → entry appears
      in the index after replay.
- [ ] **Expenses create** (`/expenses/create`) — submit offline → queued →
      reconnect → expense appears in the index after replay.
- [ ] **Employee shifts bulk create** (`/employees/shifts/create`) — submit
      offline → queued → reconnect → shifts appear after replay.
- [ ] **Employee daily laberi create** (`/employees/{employee}/daily-laberi/create`)
      — submit offline → queued → reconnect → entry appears after replay.
- [ ] **Item payment received** (index page modal, AJAX) — submit the
      "Add Received Payment" modal offline → confirm the "queued offline"
      toast appears → reconnect → confirm the payment appears after replay.
- [ ] For each of the above, confirm `pending-sync-badge` shows a
      non-zero count while offline and drops back to hidden/`0` after a
      successful replay.
- [ ] Confirm `sync-status-widget` shows **Syncing** immediately after
      reconnect and **Idle** with an updated "Last sync" time once the
      queue drains.

## 4. Background Sync on Reconnect

- [ ] Queue at least one offline submission (any module above).
- [ ] Close the tab entirely (don't just background it) while still
      offline.
- [ ] Reconnect the network, then reopen the site.
- [ ] Confirm the submission was replayed either automatically via the
      Background Sync event (`shams-offline-sync` tag in
      `public/sw-offline-sync.js`) or via the `online` event listener in
      `form-offline.js` on reopen — check `pending_requests` status flips
      to `synced` and the record exists server-side.
- [ ] Repeat with the tab kept open in the background (not closed) to
      confirm the `online` event path also works.

## 5. Update Banner + Reload Flow

- [ ] Deploy a change that alters a Vite-built asset (e.g. touch
      `resources/js/app.js`) and run a fresh build so filenames/hashes
      change.
- [ ] With the app already open and installed/cached from the previous
      build, reload or navigate — confirm `pwa-update-banner` appears
      ("A new version of Shams Print CRM is available.").
- [ ] Click **Reload** → confirm the page reloads and the new build's
      assets are now active (check a version-specific marker or the
      Network tab for new hashed filenames).
- [ ] Click **Later** on a separate test run → confirm the banner
      disappears and the app keeps functioning on the old cached version
      until the next reload/navigation re-triggers the check.
- [ ] **Known gap to verify before relying on this**: no service worker
      registration call existed anywhere in the codebase prior to
      `resources/js/offline/ui.js`. Confirm `virtual:pwa-register`
      resolves correctly at runtime (check DevTools console for import
      errors) — this is new wiring, not previously-tested Phase 1-4 code.

## 6. Confirm Excluded Routes Are Never Cached or Queued

For each route below, go offline first, then attempt the action, and
confirm it fails cleanly (browser/network error or a clear "you are
offline" state) rather than silently queuing or serving stale cached data:

- [ ] `/employee-payroll` (index, lock, unlock) — never cached
      (NetworkOnly), never queued offline.
- [ ] `/employees/{employee}/paid-laberi` and
      `/employees/{employee}/paid-laberi/create` — never cached, never
      queued.
- [ ] `/employee-paid-laberi/bulk-create` and `/employee-paid-laberi/bulk-store`
      — never cached, never queued.
- [ ] `/login` and `/logout` — never cached, never queued. Confirm logging
      in/out while offline fails outright rather than appearing to
      succeed.
- [ ] Any `DELETE` action (item entries, expenses, employees, payments) —
      confirm none of these are queued for offline replay; they should
      simply fail while offline with no silent retry.
- [ ] Confirm none of the above routes ever produce entries in
      `pending_requests` regardless of network state.

## 7. Lighthouse PWA Audit

- [ ] Run Lighthouse (Chrome DevTools → Lighthouse → PWA category) against
      a production-like build (`npm run build` served via the production
      Docker/Nginx stack, not `vite dev`).
- [ ] Confirm "Installable" passes (valid manifest, icons, service worker
      with a fetch handler).
- [ ] Confirm "PWA Optimized" checks pass: themed omnibox, viewport meta,
      offline fallback (`offline.html`) working.
- [ ] Review the report for any manifest icon/purpose warnings given the
      manifest declares the same 512×512 icon with both `purpose: "any"`
      and `purpose: "maskable"` — a dedicated maskable icon may be flagged
      as a recommendation.

## 8. Nginx Cache Header Verification

- [ ] `curl -I https://<host>/build/<any-hashed-file>.js` → confirm
      `Cache-Control: public, immutable`.
- [ ] `curl -I https://<host>/sw.js` (or the actual built path, see the
      note in `docker/production/nginx/default.conf`) → confirm
      `Cache-Control: no-cache, must-revalidate`.
- [ ] `curl -I https://<host>/manifest.json` → confirm
      `Cache-Control: no-cache, must-revalidate`.
- [ ] Confirm the generic static-asset regex block still serves 30-day
      `public, immutable` caching for non-`/build/` static files (e.g.
      `/favicon.svg`, `/icons/icon-192.png`) — unaffected by this change.

## 9. Known Environment Constraint

- [ ] **Brotli**: the production image (`nginx:1.27-alpine`) does not
      ship the `ngx_brotli` module. Only gzip compression is available
      as configured. If brotli is desired later, it requires a custom
      Nginx image/module build — out of scope for this change per the
      task constraints.