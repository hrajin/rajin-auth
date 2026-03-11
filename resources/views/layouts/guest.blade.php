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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">

            {{-- Left Panel: Branding --}}
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
                    {{-- Abstract lock/shield illustration --}}
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
            <div class="flex-1 flex flex-col justify-center items-center px-6 py-12 bg-white">
                {{-- Mobile logo --}}
                <div class="lg:hidden mb-8 w-full max-w-md">
                    <a href="/" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <span class="text-gray-900 font-semibold text-xl">{{ config('app.name', 'Rajin Auth') }}</span>
                    </a>
                </div>

                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>

        </div>
    </body>
</html>
