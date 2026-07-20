<x-app-layout>
    @php
        $user = auth()->user();
        $roleName = match ($user->role ?? '') {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'viewer' => 'Viewer',
            default => 'Staff Member',
        };
        $roleBadgeClass = match ($user->role ?? '') {
            'super_admin' => 'badge-error text-error-content',
            'admin' => 'badge-primary text-primary-content',
            'viewer' => 'badge-ghost border-base-300',
            default => 'badge-neutral',
        };
    @endphp

    <!-- Greeting & Role Header Section -->
    <div class="card bg-base-100 border border-base-300 shadow-sm rounded-2xl overflow-hidden relative">
        <!-- Background decor shapes -->
        <div class="absolute -top-24 -right-24 w-60 h-60 rounded-full bg-primary/5 blur-3xl pointer-events-none"></div>
        <div class="card-body p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-base-content">
                        Welcome back, {{ ucfirst($user->name) ?? 'User' }}
                    </h1>
                    <span
                        class="badge {{ $roleBadgeClass }} font-bold py-2.5 px-3 rounded-lg shadow-sm border">{{ $roleName }}</span>
                </div>
                <p class="text-sm text-base-content/65 mt-1.5 max-w-lg font-light leading-relaxed">
                    Monitor the printer workshop status, generate invoices, draft quotes, and check customer
                    relationships from one dashboard.
                </p>
            </div>
            <div class="shrink-0 flex items-center gap-2.5">
                <span class="flex h-3.5 w-3.5 relative">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold text-base-content/70">System online & synced</span>
            </div>
        </div>
    </div>

    <!-- Dashboard Columns Layout (Split layout on lg screen) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Main Section (Dashboard Cards) -->
        <div class="lg:col-span-8 space-y-8">


            <div id="offline-dashboard-summary" class="hidden" data-offline-dashboard-summary>
                <x-section-title title="Offline Dashboard Summary"
                    subtitle="Read-only snapshot from the last completed data sync." />

                <div id="offline-dashboard-last-synced" class="mb-4 text-sm font-semibold text-base-content/70"></div>

                <div id="offline-dashboard-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-dashboard-card title="Total Employees" description="Cached employee count.">
                        <div class="text-2xl font-bold text-primary" data-summary-field="total_employees">—</div>
                    </x-dashboard-card>

                    <x-dashboard-card title="Working Today" description="Cached working shift count.">
                        <div class="text-2xl font-bold text-success" data-summary-field="working_today">—</div>
                    </x-dashboard-card>

                    <x-dashboard-card title="Leave / Off Today" description="Cached leave and off count.">
                        <div class="text-2xl font-bold text-warning" data-summary-field="leave_today">—</div>
                    </x-dashboard-card>

                    <x-dashboard-card title="Pending Item Payments" description="Server-computed cached balance.">
                        <div class="text-2xl font-bold text-primary" data-summary-field="pending_item_payments">—</div>
                    </x-dashboard-card>

                    <x-dashboard-card title="Expense Total" description="Server-computed cached expenses.">
                        <div class="text-2xl font-bold text-primary" data-summary-field="expense_total">—</div>
                    </x-dashboard-card>
                </div>

                <div id="offline-dashboard-empty" class="hidden">
                    <x-empty-state title="No offline dashboard cache yet"
                        message="Connect to the internet once to sync the read-only dashboard summary." />
                </div>
            </div>

            <div>
                <x-section-title title="Workspace Modules"
                    subtitle="Directory access for primary CRM business operations." />

                <!-- Dashboard grid - responsive -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <!-- Entry -->
                    <x-dashboard-card title="Item Entries" description="View and manage lart item entries.">
                        <x-slot name="icon">
                            <x-jam-write class="text-primary w-6 h-6" />
                        </x-slot>
                        <a href="/item-entries" prefetch>
                            <x-action-button type="submit" variant="primary" size="sm">Open Item
                                Entries</x-action-button>
                        </a>
                    </x-dashboard-card>

                    <!-- Employees-->
                    <x-dashboard-card title="Employees" description="Employees details.">
                        <x-slot name="icon">
                            <x-clarity-employee-group-line class='text-primary w-6 h-6' />
                        </x-slot>
                        <a href="#" prefetch>
                            <x-action-button type="submit" variant="primary" size="sm">comming
                                soon</x-action-button>
                        </a>
                    </x-dashboard-card>

                    <!-- Expenses -->
                    <x-dashboard-card title="Expenses" description="Track and manage business expenses.">
                        <x-slot name="icon">
                            <x-gameicon-expense class='text-primary w-6 h-6' />
                        </x-slot>
                        <a href="/expenses" prefetch>
                            <x-action-button type="submit" variant="primary" size="sm">Manage
                                Expenses</x-action-button>
                        </a>
                    </x-dashboard-card>

                    <!-- Invoices Card -->
                    <x-dashboard-card title="Invoices" description="Generate and manage invoices for clients.">
                        <x-slot name="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 2.25 19.5Z" />
                            </svg>
                        </x-slot>
                        <a href="#" prefetch>
                            <x-action-button type="submit" variant="primary" size="sm">comming
                                soon</x-action-button>
                        </a>
                    </x-dashboard-card>
                </div>
            </div>

            {{-- <!-- Recent Activity Section -->
            <div class="card bg-base-100 border border-base-300 shadow-sm rounded-2xl p-6">
                <x-section-title title="Recent Activity Log"
                    subtitle="Chronological feed of transactions and edits in the system." />

                <!-- Empty State placeholder -->
                <x-empty-state title="No recent activity available"
                    message="All database records are calm. Any updates or changes in client ledger files or orders will show up here." />
            </div> --}}

        </div>

    </div>


    <script>
        const section = document.querySelector('[data-offline-dashboard-summary]');
        const cards = document.getElementById('offline-dashboard-cards');
        const empty = document.getElementById('offline-dashboard-empty');
        const lastSynced = document.getElementById('offline-dashboard-last-synced');

        function formatNumber(value) {
            const number = Number(value ?? 0);

            return Number.isInteger(number) ? String(number) : number.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }

        async function renderOfflineDashboardSummary() {
            if (!section) {
                return;
            }

            section.classList.toggle('hidden', navigator.onLine);

            if (navigator.onLine) {
                return;
            }

            const db = window.ShamsOfflineDataCache?.db;

            if (!db) {
                return;
            }

            const summary = await db.dashboard_summary.get('current');
            const meta = await db.sync_meta.get('dashboard_summary');

            if (!summary) {
                cards.classList.add('hidden');
                empty.classList.remove('hidden');
                lastSynced.textContent = '';
                return;
            }

            empty.classList.add('hidden');
            cards.classList.remove('hidden');

            section.querySelectorAll('[data-summary-field]').forEach((element) => {
                element.textContent = formatNumber(summary[element.dataset.summaryField]);
            });

            lastSynced.textContent = meta?.last_sync
                ? `Last Synced: ${new Date(meta.last_sync).toLocaleString()}`
                : 'Last Synced: unknown';
        }

        window.addEventListener('online', renderOfflineDashboardSummary);
        window.addEventListener('offline', renderOfflineDashboardSummary);
        window.addEventListener('pwa-sync-status', renderOfflineDashboardSummary);
        renderOfflineDashboardSummary();
    </script>
</x-app-layout>
