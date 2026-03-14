<section>

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Update Password</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use a long, random password to keep your account secure.</p>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        {{-- Current Password --}}
        <div class="relative">
            <input id="update_password_current_password" name="current_password" type="password" placeholder=" " autocomplete="current-password"
                class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent pr-12 {{ $errors->updatePassword->has('current_password') ? 'border-red-500 focus:ring-red-400 focus:border-red-500' : '' }}">
            <label for="update_password_current_password" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                Current Password
            </label>
            <button type="button" onclick="togglePwd('update_password_current_password','eye-current')"
                class="eye-toggle absolute inset-y-0 right-0 flex items-center pr-3">
                <svg id="eye-current-on" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <svg id="eye-current-off" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </button>
            <x-input-error class="mt-1.5 text-xs" :messages="$errors->updatePassword->get('current_password')" />
        </div>

        {{-- New Password --}}
        <div class="relative">
            <input id="update_password_password" name="password" type="password" placeholder=" " autocomplete="new-password"
                class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent pr-12 {{ $errors->updatePassword->has('password') ? 'border-red-500 focus:ring-red-400 focus:border-red-500' : '' }}">
            <label for="update_password_password" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                New Password
            </label>
            <button type="button" onclick="togglePwd('update_password_password','eye-new')"
                class="eye-toggle absolute inset-y-0 right-0 flex items-center pr-3">
                <svg id="eye-new-on" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <svg id="eye-new-off" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </button>
            <x-input-error class="mt-1.5 text-xs" :messages="$errors->updatePassword->get('password')" />
        </div>

        {{-- Confirm Password --}}
        <div class="relative">
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" placeholder=" " autocomplete="new-password"
                class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent pr-12 {{ $errors->updatePassword->has('password_confirmation') ? 'border-red-500 focus:ring-red-400 focus:border-red-500' : '' }}">
            <label for="update_password_password_confirmation" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                Confirm Password
            </label>
            <button type="button" onclick="togglePwd('update_password_password_confirmation','eye-confirm')"
                class="eye-toggle absolute inset-y-0 right-0 flex items-center pr-3">
                <svg id="eye-confirm-on" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <svg id="eye-confirm-off" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </button>
            <x-input-error class="mt-1.5 text-xs" :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        {{-- Save --}}
        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                class="flex justify-center items-center px-6 py-[17px] bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                Update Password
            </button>
        </div>
    </form>

    <style>
        .profile-label { color: #6e6e73; }
        input:focus ~ .profile-label,
        input:not(:placeholder-shown) ~ .profile-label { color: #6366f1; }
        .eye-toggle { color: #6e6e73; }
        .eye-toggle:hover { color: #6366f1; }
        .dark .profile-label { color: #9ca3af; }
        .dark input:focus ~ .profile-label,
        .dark input:not(:placeholder-shown) ~ .profile-label { color: #818cf8; }
        .dark .eye-toggle { color: #9ca3af; }
        .dark .eye-toggle:hover { color: #818cf8; }
    </style>

    <script>
        function togglePwd(inputId, iconPrefix) {
            const input = document.getElementById(inputId);
            const on = document.getElementById(iconPrefix + '-on');
            const off = document.getElementById(iconPrefix + '-off');
            if (input.type === 'password') {
                input.type = 'text';
                on.classList.add('hidden');
                off.classList.remove('hidden');
            } else {
                input.type = 'password';
                on.classList.remove('hidden');
                off.classList.add('hidden');
            }
        }
    </script>
</section>
