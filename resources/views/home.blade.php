<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Shams Print CRM</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link href="https://cdn.jsdelivr.net/npm/daisyui@5.6.16/daisyui.css" rel="stylesheet" type="text/css" />
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <script>
        // Inline Theme script to prevent flash
        (function() {
            const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body class="min-h-screen bg-base-200 text-base-content antialiased transition-colors duration-200">

    @php
        $user = auth()->user();
        $roleName = match($user->role ?? '') {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'viewer' => 'Viewer',
            default => 'Staff Member'
        };
        $roleBadgeClass = match($user->role ?? '') {
            'super_admin' => 'badge-error text-error-content',
            'admin' => 'badge-primary text-primary-content',
            'viewer' => 'badge-ghost border-base-300',
            default => 'badge-neutral'
        };
    @endphp

    <!-- Dashboard Layout Wrapper -->
    <div class="flex flex-col min-h-screen">
        
        <!-- Top Navbar -->
        <header class="navbar bg-base-100 border-b border-base-300 px-4 sm:px-8 py-3 sticky top-0 z-40 shadow-sm backdrop-blur bg-base-100/95">
            <div class="flex-1 gap-2.5">
                <!-- Branding stacks logo -->
                <div class="p-2 bg-primary/10 text-primary rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                        <path d="M12 2L2 7l10 5 10-5-10-5z" />
                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5" opacity="0.8" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-md font-bold tracking-tight text-base-content leading-none">Shams Print CRM</h2>
                    <p class="text-[9px] text-base-content/50 uppercase tracking-widest font-semibold mt-0.5">Control Center</p>
                </div>
            </div>

            <!-- Header Menu / Settings -->
            <div class="flex-none gap-2">
                
                <!-- Nav Links for Desktop (Visual placebo) -->
                <nav class="hidden md:flex items-center gap-1.5 mr-4">
                    <a href="#" class="btn btn-ghost btn-sm rounded-lg text-primary">Dashboard</a>
                    <a href="#" class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Customers</a>
                    <a href="#" class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Orders</a>
                    <a href="#" class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Invoices</a>
                </nav>

                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="btn btn-ghost btn-circle" aria-label="Toggle Theme">
                    <!-- Sun icon -->
                    <svg id="theme-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M5.22 5.22l1.59 1.59m10.38 10.38l1.59 1.59M12 6.75a5.25 5.25 0 100 10.5 5.25 5.25 0 000-10.5zM3 12h2.25m13.5 0H21M5.22 18.78l1.59-1.59m10.38-10.38l1.59-1.59" />
                    </svg>
                    <!-- Moon icon -->
                    <svg id="theme-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>

                <!-- Profile Dropdown Component -->
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar placeholder bg-base-200 border border-base-300">
                        <div class="bg-primary/10 text-primary rounded-full w-10">
                            <span class="text-sm font-bold">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                        </div>
                    </div>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow-xl bg-base-100 border border-base-350 rounded-xl w-56">
                        <li class="menu-title px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-base-content/40">Account Details</li>
                        <div class="px-4 py-2 border-b border-base-200/60 pb-3">
                            <p class="font-bold text-sm text-base-content truncate">{{ $user->name ?? 'User Name' }}</p>
                            <span class="badge {{ $roleBadgeClass }} badge-xs font-bold mt-1.5 py-2 px-2.5 rounded-md">{{ $roleName }}</span>
                        </div>
                        <li class="mt-1.5"><a href="#" class="py-2.5 font-medium rounded-lg">My Profile</a></li>
                        <li><a href="#" class="py-2.5 font-medium rounded-lg">System Settings</a></li>
                        <li class="border-t border-base-200 mt-1.5 pt-1.5">
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="w-full text-left text-error hover:bg-error/10 py-2.5 px-3 rounded-lg flex items-center gap-2 font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4.5 h-4.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                    </svg>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Body Area -->
        <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto space-y-8">
            
            <!-- Greeting & Role Header Section -->
            <div class="card bg-base-100 border border-base-300 shadow-sm rounded-2xl overflow-hidden relative">
                <!-- Background decor shapes -->
                <div class="absolute -top-24 -right-24 w-60 h-60 rounded-full bg-primary/5 blur-3xl pointer-events-none"></div>
                <div class="card-body p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-base-content">
                                Welcome back, {{ $user->name ?? 'User' }}
                            </h1>
                            <span class="badge {{ $roleBadgeClass }} font-bold py-2.5 px-3 rounded-lg shadow-sm border">{{ $roleName }}</span>
                        </div>
                        <p class="text-sm text-base-content/65 mt-1.5 max-w-lg font-light leading-relaxed">
                            Monitor the printer workshop status, generate invoices, draft quotes, and check customer relationships from one dashboard.
                        </p>
                    </div>
                    <div class="shrink-0 flex items-center gap-2.5">
                        <span class="flex h-3.5 w-3.5 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
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
                    
                    <div>
                        <x-section-title 
                            title="Workspace Modules" 
                            subtitle="Directory access for primary CRM business operations." 
                        />
                        
                        <!-- Dashboard grid - responsive -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Customers Card -->
                            <x-dashboard-card 
                                title="Customers" 
                                description="Manage customer profiles, contact directories, and ledger lines."
                            >
                                <x-slot name="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0 1 10.089 20.8c-2.097 0-4.07-.564-5.76-1.543.121-.048.24-.098.359-.152a4.125 4.125 0 0 1 7.533-2.493M15 9.04A3.75 3.75 0 1 1 12.637 4.5M15 9.04a3.75 3.75 0 1 0-3.693 4.5m4.31 0A4.988 4.988 0 0 0 15 9.04M12.113 4.417A4.988 4.988 0 0 1 15 9.04M5.25 5.51a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5ZM3 13.5a2.25 2.25 0 0 0-2.25 2.25v.75A2.25 2.25 0 0 0 3 18.75h.75a2.25 2.25 0 0 0 2.25-2.25v-.75A2.25 2.25 0 0 0 3.75 13.5H3Z" />
                                    </svg>
                                </x-slot>
                            </x-dashboard-card>

                            <!-- Orders Card -->
                            <x-dashboard-card 
                                title="Orders" 
                                description="Track production status, print specifications, and delivery steps."
                            >
                                <x-slot name="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </x-slot>
                            </x-dashboard-card>

                            <!-- Quotations Card -->
                            <x-dashboard-card 
                                title="Quotations" 
                                description="Create cost estimates, send pdf quotes, and convert to active orders."
                            >
                                <x-slot name="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.255-3.413c.195-.29.514-.474.865-.5c1.153-.086 2.294-.214 3.423-.379 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                    </svg>
                                </x-slot>
                            </x-dashboard-card>

                            <!-- Invoices Card -->
                            <x-dashboard-card 
                                title="Invoices" 
                                description="Supervise billing, process advance payments, and check outstanding balances."
                            >
                                <x-slot name="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 2.25 19.5Z" />
                                    </svg>
                                </x-slot>
                            </x-dashboard-card>

                            <!-- Products Card -->
                            <x-dashboard-card 
                                title="Products" 
                                description="Configure printing papers, dimensions, ink types, and pricing rates."
                            >
                                <x-slot name="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                    </svg>
                                </x-slot>
                            </x-dashboard-card>

                            <!-- Reports Card -->
                            <x-dashboard-card 
                                title="Reports & Analytics" 
                                description="Analyze total sales revenue, order counts, and production efficiency rates."
                            >
                                <x-slot name="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                                    </svg>
                                </x-slot>
                            </x-dashboard-card>

                        </div>
                    </div>

                    <!-- Recent Activity Section -->
                    <div class="card bg-base-100 border border-base-300 shadow-sm rounded-2xl p-6">
                        <x-section-title 
                            title="Recent Activity Log" 
                            subtitle="Chronological feed of transactions and edits in the system." 
                        />
                        
                        <!-- Empty State placeholder -->
                        <x-empty-state 
                            title="No recent activity available" 
                            message="All database records are calm. Any updates or changes in client ledger files or orders will show up here."
                        />
                    </div>

                </div>

                <!-- Right Main Section (Quick Actions & Status Indicators) -->
                <aside class="lg:col-span-4 space-y-8">
                    
                    <!-- Quick Actions Card Panel -->
                    <div class="card bg-base-100 border border-base-300 shadow-sm rounded-2xl p-6 relative overflow-hidden">
                        <!-- Decorative glow -->
                        <div class="absolute -bottom-16 -left-16 w-32 h-32 rounded-full bg-secondary/5 blur-xl pointer-events-none"></div>
                        
                        <x-section-title 
                            title="Quick Actions" 
                            subtitle="Instant options to spawn workspace assets." 
                        />

                        <!-- Placeholders buttons -->
                        <div class="flex flex-col gap-3.5 mt-2">
                            
                            <x-action-button variant="primary" class="w-full justify-start rounded-xl font-bold py-3.5">
                                <x-slot name="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0zM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.647-6.374-1.766z" />
                                    </svg>
                                </x-slot>
                                Register New Customer
                            </x-action-button>

                            <x-action-button variant="secondary" class="w-full justify-start rounded-xl font-bold py-3.5">
                                <x-slot name="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </x-slot>
                                Create Job Order
                            </x-action-button>

                            <x-action-button variant="outline" class="w-full justify-start rounded-xl font-bold py-3.5">
                                <x-slot name="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z" />
                                    </svg>
                                </x-slot>
                                Draft Cost Quotation
                            </x-action-button>
                            
                        </div>
                    </div>

                    <!-- Placebo Information / Status Widget -->
                    <div class="card bg-neutral text-neutral-content rounded-2xl p-6 relative overflow-hidden">
                        <div class="absolute -top-16 -right-16 w-32 h-32 rounded-full bg-primary/20 blur-xl pointer-events-none"></div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-primary mb-2">Notice Board</h4>
                        <p class="text-xs text-neutral-content/80 leading-relaxed">
                            No critical server maintenance is scheduled this week. Remember to backup the database archive at the end of the business day.
                        </p>
                        <div class="mt-4 pt-4 border-t border-neutral-content/10 flex justify-between items-center text-[11px] text-neutral-content/50">
                            <span>Backup Status:</span>
                            <span class="text-emerald-400 font-semibold uppercase">Completed Today</span>
                        </div>
                    </div>

                </aside>

            </div>

        </main>
        
        <!-- Bottom copyright footer -->
        <footer class="footer footer-center p-6 bg-base-100 border-t border-base-300 text-base-content/50 text-xs mt-auto">
            <aside>
                <p>&copy; {{ date('Y') }} Shams Print CRM. Design engineered with daisyUI and Tailwind CSS.</p>
            </aside>
        </footer>

    </div>

    <!-- Theme toggle logic script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sunIcon = document.getElementById('theme-sun');
            const moonIcon = document.getElementById('theme-moon');
            const themeToggle = document.getElementById('theme-toggle');
            
            function getTheme() {
                return document.documentElement.getAttribute('data-theme') || 'light';
            }
            
            function setTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
                updateIcons(theme);
            }
            
            function updateIcons(theme) {
                if (theme === 'dark') {
                    if (sunIcon) sunIcon.classList.remove('hidden');
                    if (moonIcon) moonIcon.classList.add('hidden');
                } else {
                    if (sunIcon) sunIcon.classList.add('hidden');
                    if (moonIcon) moonIcon.classList.remove('hidden');
                }
            }
            
            // Initial render
            updateIcons(getTheme());
            
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const newTheme = getTheme() === 'dark' ? 'light' : 'dark';
                    setTheme(newTheme);
                });
            }
        });
    </script>
</body>
</html>
