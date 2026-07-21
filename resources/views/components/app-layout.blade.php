<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Shams Print CRM</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Inline Theme script to prevent flash
        (function() {
            const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)')
                .matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>

<body class="min-h-screen bg-base-200 text-base-content antialiased transition-colors duration-200">

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

    <!-- Dashboard Layout Wrapper -->
    <div class="flex flex-col min-h-screen">

        <!-- Top Navbar -->
        <header
            class="navbar bg-base-100 border-b border-base-300 px-4 sm:px-8 py-3 sticky top-0 z-40 shadow-sm backdrop-blur bg-base-100/95">
            <div class="flex-1 gap-2.5">
                <!-- Branding stacks logo -->
                <div class="p-2 px-6 bg-primary/10 text-primary rounded-xl w-fit flex gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                        <path d="M12 2L2 7l10 5 10-5-10-5z" />
                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5" opacity="0.8" />
                    </svg>
                    <div>
                        <h2 class="text-md font-bold tracking-tight text-base-content leading-none">Shams Print CRM</h2>
                        <p class="text-[9px] text-base-content/50 uppercase tracking-widest font-semibold mt-0.5">
                            Control
                            Center</p>
                    </div>
                </div>
            </div>

            <!-- Header Menu / Settings -->
            <div class="flex gap-2">

                <!-- Nav Links for Desktop (Visual placebo) -->
                <nav class="hidden md:flex items-center gap-1.5 mr-4">
                    <a href="/" prefetch
                        class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Dashboard</a>
                    <a href="/item-entries" prefetch
                        class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Entry</a>

                    <div class="dropdown dropdown-hover">
                        <div tabindex="0" role="button"
                            class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">
                            Employees</div>
                        <ul tabindex="0"
                            class="menu menu-sm dropdown-content mt-1 z-[1] p-2 shadow-xl bg-base-100 border border-base-300 rounded-xl w-56">
                            <li><a href="{{ route('employees.index') }}">Employee List</a></li>
                            <li><a href="{{ route('employees.create') }}">Add Employee</a></li>
                            <li><a href="{{ route('employees.shifts.create') }}">Add Shift</a></li>
                            <li><a href="{{ route('employee-payroll.index') }}">Weekly Payroll Summary</a></li>
                        </ul>
                    </div>

                    <a href="/expenses" prefetch
                        class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Expenses</a>
                    <a href="#"
                        class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content btn-disabled">Invoices</a>
                </nav>

                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="btn btn-ghost btn-circle" aria-label="Toggle Theme">
                    <!-- Sun icon -->
                    <svg id="theme-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v2.25m0 13.5V21M5.22 5.22l1.59 1.59m10.38 10.38l1.59 1.59M12 6.75a5.25 5.25 0 100 10.5 5.25 5.25 0 000-10.5zM3 12h2.25m13.5 0H21M5.22 18.78l1.59-1.59m10.38-10.38l1.59-1.59" />
                    </svg>
                    <!-- Moon icon -->
                    <svg id="theme-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>

                <!-- Profile Dropdown Component -->
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button"
                        class="btn btn-ghost btn-circle avatar placeholder bg-base-200 border border-base-300 ">
                        <div
                            class="bg-primary/10 text-primary rounded-full w-10 flex items-center justify-center content-center">
                            <span class="text-sm font-bold">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                        </div>
                    </div>
                    <ul tabindex="0"
                        class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow-xl bg-base-100 border border-base-350 rounded-xl w-56">
                        <li
                            class="menu-title px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-base-content/40">
                            Account Details</li>
                        <div class="px-4 py-2 flex justify-between border-b border-base-200/60 pb-3">
                            <p class="font-bold text-sm text-base-content truncate">{{ $user->name ?? 'User Name' }}</p>
                            <span
                                class="badge {{ $roleBadgeClass }} badge-xs font-bold mt-1.5 py-2 px-2.5 rounded-md">{{ $roleName }}</span>
                        </div>
                        <li class=" mt-1.5 pt-1.5">
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left text-error hover:bg-error/10 py-2.5 px-3 rounded-lg flex items-center gap-2 font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.8" stroke="currentColor" class="w-4.5 h-4.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                    </svg>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Nav Links for mobile (Visual placebo) -->
        <div class="md:hidden border-y border-base-300 bg-base-100/95 backdrop-blur sticky top-[4.5rem] z-30">
            <nav class="flex items-center gap-1.5 mr-4 flex-wrap px-4 py-2.5">
                <a href="/" prefetch
                    class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Dashboard</a>
                <a href="/item-entries" prefetch
                    class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Entry</a>
                <a href="{{ route('employees.index') }}"
                    class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Employees</a>
                <a href="{{ route('employees.shifts.create') }}"
                    class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Add Shift</a>
                <a href="{{ route('employee-payroll.index') }}"
                    class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Payroll</a>
                <a href="/expenses" prefetch
                    class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content">Expenses</a>
                <a href="#"
                    class="btn btn-ghost btn-sm rounded-lg text-base-content/70 hover:text-base-content btn-disabled">Invoices</a>
            </nav>
        </div>


        <!-- Main Body Area -->
        <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto space-y-8">
            {{ $slot }}
        </main>

        <!-- Bottom copyright footer -->
        <footer
            class="footer footer-center p-6 bg-base-100 border-t border-base-300 text-base-content/50 text-xs mt-auto">
            <aside>
                <p>&copy; {{ date('Y') }} Shams Print CRM. Made by Muhammad Zia khatri</p>
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
