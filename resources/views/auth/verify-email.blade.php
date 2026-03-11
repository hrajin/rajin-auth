<x-guest-layout>

    {{-- Heading --}}
    <div class="mb-8">
        <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl flex items-center justify-center mb-6">
            <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Check your email</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            We sent a verification link to your email address. Click the link to activate your account.
        </p>
    </div>

    {{-- Success status --}}
    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-sm text-green-700 dark:text-green-400">A new verification link has been sent to your email address.</p>
        </div>
    @endif

    {{-- Resend button --}}
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit"
            class="w-full flex justify-center items-center px-4 py-[17px] bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
            Resend verification email
        </button>
    </form>

    {{-- Log out --}}
    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit"
            class="w-full flex justify-center items-center px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">
            Log out
        </button>
    </form>

</x-guest-layout>
