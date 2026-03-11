<x-guest-layout>

    {{-- Heading --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reset Your Password</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Enter the email associated with your account and we will send you password reset instructions.</p>
    </div>

    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
        @csrf

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
                    autocomplete="email"
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

        {{-- Submit --}}
        <button type="submit"
            class="w-full flex justify-center items-center px-4 py-[17px] bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
            Send reset link
        </button>
    </form>

    {{-- Back to login --}}
    <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
        Remember your password?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition">
            Sign in
        </a>
    </p>

    <style>
        .floating-label { color: #6e6e73; }
        input:focus ~ .floating-label,
        input:not(:placeholder-shown) ~ .floating-label { color: #6366f1; }
        #email.input-invalid { border-color: #ef4444 !important; }
        #email.input-invalid:focus { border-color: #ef4444 !important; --tw-ring-color: rgba(239, 68, 68, 0.4); }
        #email.input-invalid ~ .floating-label { color: #ef4444 !important; }
        .dark .floating-label { color: #9ca3af; }
        .dark input:focus ~ .floating-label,
        .dark input:not(:placeholder-shown) ~ .floating-label { color: #818cf8; }
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
            emailInput.classList.remove('input-invalid');
            if (emailInput.classList.contains('input-invalid')) validateEmail();
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            if (!validateEmail()) e.preventDefault();
        });
    </script>

</x-guest-layout>
