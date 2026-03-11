<x-guest-layout>

    {{-- Heading --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome back</h1>
        <p class="mt-2 text-sm text-gray-500">Enter your credentials to access your account.</p>
    </div>

    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mt-6">
            <div class="relative">
                <x-text-input
                    id="email"
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] bg-white text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent {{ $errors->has('email') ? 'input-invalid' : '' }}"
                    type="email"
                    name="email"
                    :value="old('email')"
                    placeholder=" "
                    required
                    autocomplete="username"
                />
                <label for="email"
                    class="floating-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white px-1 text-sm transition-all duration-200
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
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] bg-white text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent {{ $errors->has('password') ? 'input-invalid' : '' }}"
                    type="password"
                    name="password"
                    placeholder=" "
                    required
                    autocomplete="current-password"
                />
                <label for="password"
                    class="floating-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white px-1 text-sm transition-all duration-200
                           peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                           peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                    Password
                </label>
                {{-- Show/hide toggle --}}
                <button type="button"
                    onclick="togglePassword()"
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

        {{-- Remember me + Forgot password --}}
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    name="remember"
                />
                <span class="text-sm text-gray-600">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition">
                    Forgot password?
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="w-full flex justify-center items-center px-4 py-[17px] bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
            Sign in
        </button>
    </form>

    {{-- Divider --}}
    <div class="my-6 flex items-center gap-3">
        <div class="flex-1 h-px bg-gray-200"></div>
        <span class="text-xs text-[#6e6e73] font-medium">OR</span>
        <div class="flex-1 h-px bg-gray-200"></div>
    </div>

    {{-- Google --}}
    <a href="{{ route('auth.social.redirect', 'google') }}"
       class="w-full flex justify-center items-center gap-3 px-4 py-[17px] bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition shadow-sm">
        <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Continue with Google
    </a>

    {{-- Register link --}}
    <p class="mt-6 text-center text-sm text-gray-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition">
            Create one
        </a>
    </p>

    <style>
        .floating-label { color: #6e6e73; }
        input:focus ~ .floating-label,
        input:not(:placeholder-shown) ~ .floating-label { color: #6366f1; }
        .eye-toggle { color: #6e6e73; }
        .eye-toggle:hover { color: #6366f1; }
        #email.input-invalid { border-color: #ef4444 !important; }
        #email.input-invalid:focus { border-color: #ef4444 !important; --tw-ring-color: rgba(239, 68, 68, 0.4); }
        #email.input-invalid ~ .floating-label { color: #ef4444 !important; }
        #password.input-invalid { border-color: #ef4444 !important; }
        #password.input-invalid:focus { border-color: #ef4444 !important; --tw-ring-color: rgba(239, 68, 68, 0.4); }
        #password.input-invalid ~ .floating-label { color: #ef4444 !important; }
    </style>

    <script>
        const emailInput = document.getElementById('email');
        const emailError = document.getElementById('email-error');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        function validateEmail() {
            const value = emailInput.value.trim();
            if (value === '') {
                setEmailError('Email is required.');
                return false;
            } else if (!emailRegex.test(value)) {
                setEmailError('Please enter a valid email address.');
                return false;
            } else {
                clearEmailError();
                return true;
            }
        }

        function setEmailError(message) {
            emailInput.classList.add('input-invalid');
            emailError.textContent = message;
            emailError.classList.remove('hidden');
        }

        function clearEmailError() {
            emailInput.classList.remove('input-invalid');
            emailError.textContent = '';
            emailError.classList.add('hidden');
        }

        emailInput.addEventListener('input', function () {
            document.getElementById('server-email-error')?.classList.add('hidden');
            if (emailInput.classList.contains('input-invalid')) validateEmail();
        });

        const passwordInput = document.getElementById('password');
        const passwordError = document.getElementById('password-error');

        function validatePassword() {
            if (passwordInput.value === '') {
                setPasswordError('Password is required.');
                return false;
            } else {
                clearPasswordError();
                return true;
            }
        }

        function setPasswordError(message) {
            passwordInput.classList.add('input-invalid');
            passwordError.textContent = message;
            passwordError.classList.remove('hidden');
        }

        function clearPasswordError() {
            passwordInput.classList.remove('input-invalid');
            passwordError.textContent = '';
            passwordError.classList.add('hidden');
        }

        passwordInput.addEventListener('input', function () {
            document.getElementById('server-password-error')?.classList.add('hidden');
            passwordInput.classList.remove('input-invalid');
            if (passwordInput.classList.contains('input-invalid')) validatePassword();
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            const emailValid = validateEmail();
            const passwordValid = validatePassword();
            if (!emailValid || !passwordValid) e.preventDefault();
        });

        function togglePassword() {
            const input = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');
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
