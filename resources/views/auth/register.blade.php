<x-guest-layout>

    {{-- Heading --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome to Rajin Auth</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Create an account to get started.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
        @csrf

        {{-- Name --}}
        <div class="mt-6">
            <div class="relative">
                <x-text-input
                    id="name"
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent {{ $errors->has('name') ? 'input-invalid' : '' }}"
                    type="text"
                    name="name"
                    :value="old('name')"
                    placeholder=" "
                    required
                    autocomplete="name"
                />
                <label for="name"
                    class="floating-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                           peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                           peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                    Name
                </label>
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-xs" id="server-name-error" />
            <p id="name-error" class="mt-1.5 text-xs text-red-500 hidden"></p>
        </div>

        {{-- Email --}}
        <div class="mt-6">
            <div class="relative">
                <x-text-input
                    id="email"
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent {{ $errors->has('email') ? 'input-invalid' : '' }}"
                    type="email"
                    name="email"
                    :value="old('email')"
                    placeholder=" "
                    required
                    autocomplete="username"
                />
                <label for="email"
                    class="floating-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                           peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                           peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                    Email
                </label>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs" id="server-email-error" />
            <p id="email-error" class="mt-1.5 text-xs text-red-500 hidden"></p>
        </div>

        {{-- Password --}}
        <div class="mt-6">
            <div class="relative">
                <x-text-input
                    id="password"
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent {{ $errors->has('password') ? 'input-invalid' : '' }}"
                    type="password"
                    name="password"
                    placeholder=" "
                    required
                    autocomplete="new-password"
                />
                <label for="password"
                    class="floating-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                           peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                           peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                    Password
                </label>
                <button type="button"
                    onclick="togglePassword('password', 'eye-icon', 'eye-off-icon')"
                    class="eye-toggle absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs" id="server-password-error" />
            <p id="password-error" class="mt-1.5 text-xs text-red-500 hidden"></p>
        </div>

        {{-- Confirm Password --}}
        <div class="mt-6">
            <div class="relative">
                <x-text-input
                    id="password_confirmation"
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent {{ $errors->has('password_confirmation') ? 'input-invalid' : '' }}"
                    type="password"
                    name="password_confirmation"
                    placeholder=" "
                    required
                    autocomplete="new-password"
                />
                <label for="password_confirmation"
                    class="floating-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                           peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                           peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                    Confirm Password
                </label>
                <button type="button"
                    onclick="togglePassword('password_confirmation', 'eye-icon-confirm', 'eye-off-icon-confirm')"
                    class="eye-toggle absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg id="eye-icon-confirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eye-off-icon-confirm" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs" id="server-confirm-error" />
            <p id="confirm-error" class="mt-1.5 text-xs text-red-500 hidden"></p>
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="w-full flex justify-center items-center px-4 py-[17px] bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
            Create account
        </button>
    </form>

    {{-- Divider --}}
    <div class="my-6 flex items-center gap-3">
        <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
        <span class="text-xs text-[#6e6e73] dark:text-gray-500 font-medium">OR</span>
        <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
    </div>

    {{-- Google --}}
    <a href="{{ route('auth.social.redirect', 'google') }}"
       class="w-full flex justify-center items-center gap-3 px-4 py-[17px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition shadow-sm">
        <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Continue with Google
    </a>

    {{-- Login link --}}
    <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition">
            Sign in
        </a>
    </p>

    <style>
        .floating-label { color: #6e6e73; }
        input:focus ~ .floating-label,
        input:not(:placeholder-shown) ~ .floating-label { color: #6366f1; }
        .eye-toggle { color: #6e6e73; }
        .eye-toggle:hover { color: #6366f1; }
        #name.input-invalid, #email.input-invalid, #password.input-invalid, #password_confirmation.input-invalid { border-color: #ef4444 !important; }
        #name.input-invalid:focus, #email.input-invalid:focus, #password.input-invalid:focus, #password_confirmation.input-invalid:focus { border-color: #ef4444 !important; --tw-ring-color: rgba(239, 68, 68, 0.4); }
        .input-invalid ~ .floating-label { color: #ef4444 !important; }
        .dark .floating-label { color: #9ca3af; }
        .dark input:focus ~ .floating-label,
        .dark input:not(:placeholder-shown) ~ .floating-label { color: #818cf8; }
        .dark .eye-toggle { color: #9ca3af; }
        .dark .eye-toggle:hover { color: #818cf8; }
    </style>

    <script>
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        function setError(input, errorEl, message) {
            input.classList.add('input-invalid');
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }

        function clearError(input, errorEl) {
            input.classList.remove('input-invalid');
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }

        function validateField(input, errorEl, rules) {
            for (const rule of rules) {
                if (!rule.check(input.value)) {
                    setError(input, errorEl, rule.message);
                    return false;
                }
            }
            clearError(input, errorEl);
            return true;
        }

        const fields = [
            {
                input: document.getElementById('name'),
                errorEl: document.getElementById('name-error'),
                serverErrorEl: document.getElementById('server-name-error'),
                rules: [{ check: v => v.trim() !== '', message: 'Name is required.' }]
            },
            {
                input: document.getElementById('email'),
                errorEl: document.getElementById('email-error'),
                serverErrorEl: document.getElementById('server-email-error'),
                rules: [
                    { check: v => v.trim() !== '', message: 'Email is required.' },
                    { check: v => emailRegex.test(v.trim()), message: 'Please enter a valid email address.' }
                ]
            },
            {
                input: document.getElementById('password'),
                errorEl: document.getElementById('password-error'),
                serverErrorEl: document.getElementById('server-password-error'),
                rules: [
                    { check: v => v !== '', message: 'Password is required.' },
                    { check: v => v.length >= 8, message: 'Password must be at least 8 characters.' }
                ]
            },
            {
                input: document.getElementById('password_confirmation'),
                errorEl: document.getElementById('confirm-error'),
                serverErrorEl: document.getElementById('server-confirm-error'),
                rules: [
                    { check: v => v !== '', message: 'Please confirm your password.' },
                    { check: v => v === document.getElementById('password').value, message: 'Passwords do not match.' }
                ]
            },
        ];

        fields.forEach(({ input, errorEl, rules, serverErrorEl }) => {
            input.addEventListener('input', function () {
                serverErrorEl?.classList.add('hidden');
                if (input.classList.contains('input-invalid')) validateField(input, errorEl, rules);
            });
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            const results = fields.map(({ input, errorEl, rules }) => validateField(input, errorEl, rules));
            if (results.includes(false)) e.preventDefault();
        });

        function togglePassword(inputId, eyeId, eyeOffId) {
            const input = document.getElementById(inputId);
            const eyeIcon = document.getElementById(eyeId);
            const eyeOffIcon = document.getElementById(eyeOffId);
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    </script>

</x-guest-layout>
