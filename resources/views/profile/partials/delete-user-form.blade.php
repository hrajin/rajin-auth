<section x-data="{ confirmingDeletion: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Delete Account</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Once deleted, all your data will be permanently removed and cannot be recovered.</p>
    </div>

    <button type="button" @click="confirmingDeletion = true"
        class="px-6 py-[17px] bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-sm font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
        Delete Account
    </button>

    {{-- Confirmation Modal --}}
    <div x-show="confirmingDeletion" x-transition.opacity style="display:none"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 dark:bg-black/70"
        @keydown.escape.window="confirmingDeletion = false"
        @click.self="confirmingDeletion = false">

        <div x-show="confirmingDeletion" x-transition
            class="w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 p-8">

            <div class="mb-5">
                <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-900/30 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete your account?</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This action is permanent and cannot be undone. Enter your password to confirm.</p>
            </div>

            <form method="post" action="{{ route('profile.destroy') }}" class="space-y-5">
                @csrf
                @method('delete')

                <div class="relative">
                    <input id="delete_password" name="password" type="password" placeholder=" " autocomplete="current-password"
                        class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition placeholder-transparent {{ $errors->userDeletion->has('password') ? 'border-red-500 focus:ring-red-400 focus:border-red-500' : '' }}">
                    <label for="delete_password" class="delete-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                        peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                        peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                        Password
                    </label>
                    <x-input-error class="mt-1.5 text-xs" :messages="$errors->userDeletion->get('password')" />
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                        class="flex-1 py-[17px] bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-sm font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                        Delete Account
                    </button>
                    <button type="button" @click="confirmingDeletion = false"
                        class="flex-1 py-[17px] bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .delete-label { color: #6e6e73; }
        input:focus ~ .delete-label,
        input:not(:placeholder-shown) ~ .delete-label { color: #ef4444; }
        .dark .delete-label { color: #9ca3af; }
        .dark input:focus ~ .delete-label,
        .dark input:not(:placeholder-shown) ~ .delete-label { color: #f87171; }
    </style>
</section>


