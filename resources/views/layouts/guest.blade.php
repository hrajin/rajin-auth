<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Rajin Auth') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Dark mode init: must run before CSS renders to prevent flash -->
        <script>
            (function () {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">

        <div class="min-h-screen flex dark:bg-gray-950 relative">

            {{-- Theme toggle button --}}
            <button id="theme-toggle"
                onclick="toggleTheme()"
                style="position: absolute; top: 1rem; right: 1rem; z-index: 50;"
                class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center shadow-md hover:shadow-lg transition-colors"
                aria-label="Toggle dark mode">
                {{-- Sun icon — shown in dark mode --}}
                <svg id="icon-sun" class="hidden w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
                {{-- Moon icon — shown in light mode --}}
                <svg id="icon-moon" class="block w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
            <div class="hidden lg:flex lg:w-1/3 relative bg-indigo-600 flex-col justify-between p-12 overflow-hidden">

                {{-- Background decorative circles --}}
                <div class="absolute top-0 left-0 w-full h-full">
                    <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-indigo-500 opacity-50"></div>
                    <div class="absolute top-1/2 -right-32 w-80 h-80 rounded-full bg-indigo-700 opacity-40"></div>
                    <div class="absolute -bottom-16 left-1/3 w-64 h-64 rounded-full bg-indigo-400 opacity-30"></div>
                    <div class="absolute top-1/4 left-1/2 w-40 h-40 rounded-full bg-white opacity-5"></div>
                </div>

                {{-- Logo --}}
                <div class="relative z-10">
                    <a href="/" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <span class="text-white font-semibold text-xl tracking-tight">{{ config('app.name', 'Rajin Auth') }}</span>
                    </a>
                </div>

                {{-- Center content --}}
                <div class="relative z-10">
                    <div class="mb-10">
                        <div class="w-32 h-32 mx-auto relative">
                            <div class="absolute inset-0 bg-white opacity-10 rounded-3xl rotate-6"></div>
                            <div class="absolute inset-0 bg-white opacity-10 rounded-3xl -rotate-3"></div>
                            <div class="relative w-full h-full bg-white bg-opacity-15 rounded-3xl flex items-center justify-center backdrop-blur-sm border border-white border-opacity-20">
                                <svg class="w-16 h-16 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-white text-3xl font-bold leading-snug mb-4">
                        One login for<br>all our apps.
                    </h2>
                    <p class="text-indigo-200 text-base leading-relaxed">
                        A central identity provider that keeps your accounts secure across every product you use.
                    </p>

                    {{-- Feature dots --}}
                    <div class="mt-10 space-y-3">
                        @foreach(['Secure OAuth 2.0 & OpenID Connect', 'Google social login support', 'Single sign-on across all apps'] as $feature)
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full bg-white bg-opacity-20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-indigo-100 text-sm">{{ $feature }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Footer --}}
                <div class="relative z-10">
                    <p class="text-indigo-300 text-xs">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Rajin Auth') }}. All rights reserved.
                    </p>
                </div>
            </div>

            {{-- Right Panel: Form --}}
            <div class="flex-1 flex flex-col bg-white dark:bg-gray-900 transition-colors duration-200">
                {{-- Mobile logo — pinned to top, same height as toggle button --}}
                <div class="lg:hidden px-6 pt-4">
                    <a href="/" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <span class="text-gray-900 dark:text-white font-semibold text-xl">{{ config('app.name', 'Rajin Auth') }}</span>
                    </a>
                </div>

                {{-- Form — centered in remaining space --}}
                <div class="flex-1 flex flex-col justify-center items-center px-6 py-12">
                    <div class="w-full max-w-md">
                        {{ $slot }}
                    </div>
                </div>
            </div>

        </div>

        <script>
            function toggleTheme() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                document.getElementById('icon-sun').classList.toggle('hidden', !isDark);
                document.getElementById('icon-moon').classList.toggle('hidden', isDark);
            }

            // Set correct icon on load
            (function () {
                const isDark = document.documentElement.classList.contains('dark');
                document.getElementById('icon-sun').classList.toggle('hidden', !isDark);
                document.getElementById('icon-moon').classList.toggle('hidden', isDark);
            })();
        </script>

        <x-toast />
    </body>
</html>
