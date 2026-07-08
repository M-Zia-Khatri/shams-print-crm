<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Shams Print CRM</title>
    
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
        // Inline Theme script to prevent flash of wrong theme
        (function() {
            const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body class="min-h-screen bg-base-200 text-base-content antialiased transition-colors duration-200">

    <!-- Theme Toggle Button -->
    <div class="absolute top-4 right-4 z-50">
        <button id="theme-toggle" class="btn btn-ghost btn-circle bg-base-100/50 backdrop-blur border border-base-300 shadow-sm" aria-label="Toggle Theme">
            <!-- Sun icon (shows in dark mode) -->
            <svg id="theme-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M5.22 5.22l1.59 1.59m10.38 10.38l1.59 1.59M12 6.75a5.25 5.25 0 100 10.5 5.25 5.25 0 000-10.5zM3 12h2.25m13.5 0H21M5.22 18.78l1.59-1.59m10.38-10.38l1.59-1.59" />
            </svg>
            <!-- Moon icon (shows in light mode) -->
            <svg id="theme-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
        </button>
    </div>

    <!-- Main Container -->
    <main class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
        
        <!-- Branding Panel (Left - visible on lg screens) -->
        <div class="lg:col-span-5 hidden lg:flex flex-col justify-between p-12 bg-neutral text-neutral-content relative overflow-hidden">
            <!-- Decorative Radial Background Glows -->
            <div class="absolute -top-40 -left-45 w-[500px] h-[500px] rounded-full bg-primary/10 blur-[120px] pointer-events-none"></div>
            <div class="absolute -bottom-40 -right-45 w-[500px] h-[500px] rounded-full bg-secondary/15 blur-[120px] pointer-events-none"></div>
            
            <!-- Branding Header -->
            <div class="flex items-center gap-3 z-10">
                <div class="p-2.5 bg-neutral-content/10 border border-neutral-content/20 rounded-xl">
                    <!-- Layered printing stacks logo -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-primary">
                        <path d="M12 2L2 7l10 5 10-5-10-5z" />
                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5" opacity="0.8" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-wider uppercase text-neutral-content">Shams Print</h2>
                    <p class="text-[10px] text-neutral-content/50 tracking-widest font-semibold uppercase">Management Suite</p>
                </div>
            </div>

            <!-- Beautiful Slogan / Quote center area -->
            <div class="my-auto max-w-sm z-10 space-y-6">
                <h1 class="text-3xl font-extrabold leading-tight text-white tracking-tight">
                    Manage client print workflows seamlessly
                </h1>
                
                <p class="text-sm text-neutral-content/85 leading-relaxed font-light">
                    Track orders, dispatch quotations, update invoices, and monitor inventory items through one unified platform.
                </p>

                <!-- Value propositions with micro icons -->
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <span class="p-1 bg-emerald-500/10 text-emerald-400 rounded-md shrink-0 mt-0.5 border border-emerald-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-semibold text-white">Centralized Client Profiles</h4>
                            <p class="text-xs text-neutral-content/65 leading-normal mt-0.5">Track every interaction, quotation and ledger in one view.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="p-1 bg-indigo-500/10 text-indigo-400 rounded-md shrink-0 mt-0.5 border border-indigo-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-semibold text-white">End-to-End Quotation Dispatch</h4>
                            <p class="text-xs text-neutral-content/65 leading-normal mt-0.5">Generate estimates and convert them into active invoices with one flag.</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Footer indicator -->
            <div class="text-xs text-neutral-content/40 z-10 font-normal">
                &copy; {{ date('Y') }} Shams Print CRM. All rights reserved. Registered SaaS platform.
            </div>
        </div>

        <!-- Form Panel (Right - centered on small screens) -->
        <div class="lg:col-span-7 flex flex-col justify-center items-center p-6 sm:p-12 relative">
            <!-- Decorative Subtle Accent Glow for Mobile View -->
            <div class="absolute top-10 right-10 w-[250px] h-[250px] rounded-full bg-primary/5 blur-[80px] pointer-events-none lg:hidden animate-pulse"></div>

            <div class="w-full max-w-md">
                
                <!-- Logo & Intro (Mobile Only view header) -->
                <div class="flex flex-col items-center mb-8 lg:hidden">
                    <div class="p-3 bg-primary/10 text-primary rounded-2xl mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10">
                            <path d="M12 2L2 7l10 5 10-5-10-5z" />
                            <path d="M2 17l10 5 10-5M2 12l10 5 10-5" opacity="0.8" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-base-content text-center">Shams Print CRM</h2>
                    <p class="text-sm text-base-content/65 text-center mt-1">Management Suite & Workspace Login</p>
                </div>
                
                <!-- Main Login Card -->
                <div class="card w-full bg-base-100 shadow-xl border border-base-300 rounded-2xl">
                    <div class="card-body p-8">
                        
                        <!-- Desktop Header Title -->
                        <div class="hidden lg:block mb-8">
                            <h3 class="card-title text-2xl font-extrabold text-base-content tracking-tight">Sign In</h3>
                            <p class="text-sm text-base-content/60 mt-1 font-light leading-relaxed">Enter your system credentials below to access your workspace dashboard.</p>
                        </div>
                        
                        <!-- General error alerts (Laravel-wide if any field errors exist or auth failed) -->
                        @if ($errors->any())
                            <div class="alert alert-error mb-6 shadow-sm rounded-xl py-3 px-4 flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5 mt-0.5 text-error-content" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-xs text-error-content font-medium leading-normal flex-1">
                                    <p class="font-bold mb-0.5">Please check your inputs:</p>
                                    @foreach ($errors->all() as $error)
                                        <p>&bull; {{ $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Form action must match exact requirements -->
                        <form method="POST" action="{{ route('login.store') }}" id="login-form">
                            @csrf

                            <!-- Username Input Component -->
                            <div class="form-control w-full mb-4">
                                <label class="label pb-1.5" for="name">
                                    <span class="label-text font-bold text-base-content/80 text-xs uppercase tracking-wider">Username / Name</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-base-content/40 pointer-events-none">
                                        <!-- User SVGIcon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    </span>
                                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Enter username" class="input input-bordered w-full pl-11 rounded-xl @error('name') input-error @enderror" required autofocus>
                                </div>
                            </div>

                            <!-- Password Input Component with visibility toggle -->
                            <div class="form-control w-full mb-4">
                                <label class="label pb-1.5" for="password">
                                    <span class="label-text font-bold text-base-content/80 text-xs uppercase tracking-wider">Password</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-base-content/40 pointer-events-none">
                                        <!-- Lock SVGIcon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                    </span>
                                    <input id="password" type="password" name="password" placeholder="••••••••" class="input input-bordered w-full pl-11 pr-11 rounded-xl @error('password') input-error @enderror" required>
                                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-base-content/45 hover:text-base-content transition-colors" aria-label="Toggle Password Visibility">
                                        <svg id="eye-show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <svg id="eye-hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.47 10.47 0 001.936 12C3.21 16.8 7.34 20 12 20c1.91 0 3.73-.42 5.37-1.16m3.93-9.35A10.47 10.47 0 0022.064 12c-1.27 4.8-5.4 8-10.06 8-1.16 0-2.28-.21-3.32-.59m11.13-11.46L19.5 3.75M3 3l1.8 1.8" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Role Select Input Component (Required by existing controller) -->
                            <div class="form-control w-full mb-4">
                                <label class="label pb-1.5" for="role">
                                    <span class="label-text font-bold text-base-content/80 text-xs uppercase tracking-wider">User Role</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-base-content/40 pointer-events-none z-10 animate-fade">
                                        <!-- Shield SVGIcon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                        </svg>
                                    </span>
                                    <select id="role" name="role" class="select select-bordered w-full pl-11 rounded-xl @error('role') select-error @enderror" required>
                                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select a role</option>
                                        <option value="super_admin" @selected(old('role') === 'super_admin')>Super Admin</option>
                                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                                        <option value="viewer" @selected(old('role') === 'viewer')>Viewer</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Remember Me + Forgot Password row -->
                            <div class="flex items-center justify-between mt-5 mb-6">
                                <label class="label cursor-pointer gap-2.5 justify-start py-0 select-none">
                                    <input type="checkbox" name="remember" id="remember" class="checkbox checkbox-primary checkbox-sm rounded-md" />
                                    <span class="label-text text-sm font-semibold text-base-content/75">Remember device</span>
                                </label>
                                <a href="#" class="text-xs font-bold text-primary hover:underline transition-all">Forgot?</a>
                            </div>

                            <!-- Submit Button with loading/disabled behavior -->
                            <div class="form-control mt-6">
                                <button type="submit" id="submit-btn" class="btn btn-primary w-full text-base font-bold shadow-md hover:shadow-lg rounded-xl transition-all duration-200 gap-2">
                                    <span id="btn-text">Log In To Dashboard</span>
                                    <span id="btn-spinner" class="loading loading-spinner loading-md hidden"></span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- Bottom Footer (Mobile view helper) -->
                <div class="mt-8 text-center text-xs text-base-content/40 lg:hidden">
                    &copy; {{ date('Y') }} Shams Print CRM. All rights reserved.
                </div>

            </div>
        </div>

    </main>

    <!-- Vanilla Javascript Logic -->
    <script>
        // Password visibility toggle handler
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeShow = document.getElementById('eye-show');
            const eyeHide = document.getElementById('eye-hide');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeShow.classList.add('hidden');
                eyeHide.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeShow.classList.remove('hidden');
                eyeHide.classList.add('hidden');
            }
        }

        // Form Submission state handler
        const loginForm = document.getElementById('login-form');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');

        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                btnText.textContent = 'Authenticating...';
                btnSpinner.classList.remove('hidden');
            });
        }

        // Theme Toggle script
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
                    sunIcon.classList.remove('hidden');
                    moonIcon.classList.add('hidden');
                } else {
                    sunIcon.classList.add('hidden');
                    moonIcon.classList.remove('hidden');
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
