<x-guest-layout>

    {{-- Heading --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Set new password</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Choose a strong password for your account.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" novalidate>
        @csrf

        {{-- Password Reset Token --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div>
            <div class="relative">
                <x-text-input
                    id="email"
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent {{ $errors->has('email') ? 'input-invalid' : '' }}"
                    type="email"
                    name="email"
                    :value="old('email', $request->email)"
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
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs" />
        </div>

        {{-- New Password --}}
        <div>
            <div class="relative">
                <x-text-input
                    id="password"
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent pr-12 {{ $errors->has('password') ? 'input-invalid' : '' }}"
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
                    New Password
                </label>
                <button type="button" onclick="togglePassword('password', 'eye-icon', 'eye-off-icon')"
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
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs" />
            <p id="password-error" class="mt-1.5 text-xs text-red-500 hidden"></p>
        </div>

        {{-- Confirm Password --}}
        <div>
            <div class="relative">
                <x-text-input
                    id="password_confirmation"
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent pr-12"
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
                <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-confirm', 'eye-off-icon-confirm')"
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
            <p id="confirm-error" class="mt-1.5 text-xs text-red-500 hidden"></p>
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="w-full flex justify-center items-center px-4 py-[17px] bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
            Reset Password
        </button>
    </form>

    <style>
        .floating-label { color: #6e6e73; }
        input:focus ~ .floating-label,
        input:not(:placeholder-shown) ~ .floating-label { color: #6366f1; }
        .eye-toggle { color: #6e6e73; }
        .eye-toggle:hover { color: #6366f1; }
        .input-invalid { border-color: #ef4444 !important; }
        .input-invalid:focus { border-color: #ef4444 !important; --tw-ring-color: rgba(239, 68, 68, 0.4); }
        .input-invalid ~ .floating-label { color: #ef4444 !important; }
        .dark .floating-label { color: #9ca3af; }
        .dark input:focus ~ .floating-label,
        .dark input:not(:placeholder-shown) ~ .floating-label { color: #818cf8; }
        .dark .eye-toggle { color: #9ca3af; }
        .dark .eye-toggle:hover { color: #818cf8; }
    </style>

    <script>
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const passwordError = document.getElementById('password-error');
        const confirmError = document.getElementById('confirm-error');

        function setFieldError(input, errorEl, message) {
            input.classList.add('input-invalid');
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }

        function clearFieldError(input, errorEl) {
            input.classList.remove('input-invalid');
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }

        function validatePassword() {
            if (passwordInput.value === '') {
                setFieldError(passwordInput, passwordError, 'Password is required.');
                return false;
            } else if (passwordInput.value.length < 8) {
                setFieldError(passwordInput, passwordError, 'Password must be at least 8 characters.');
                return false;
            } else {
                clearFieldError(passwordInput, passwordError);
                return true;
            }
        }

        function validateConfirm() {
            if (confirmInput.value === '') {
                setFieldError(confirmInput, confirmError, 'Please confirm your password.');
                return false;
            } else if (confirmInput.value !== passwordInput.value) {
                setFieldError(confirmInput, confirmError, 'Passwords do not match.');
                return false;
            } else {
                clearFieldError(confirmInput, confirmError);
                return true;
            }
        }

        passwordInput.addEventListener('input', () => {
            if (passwordInput.classList.contains('input-invalid')) validatePassword();
        });

        confirmInput.addEventListener('input', () => {
            if (confirmInput.classList.contains('input-invalid')) validateConfirm();
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            const passwordValid = validatePassword();
            const confirmValid = validateConfirm();
            if (!passwordValid || !confirmValid) e.preventDefault();
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
