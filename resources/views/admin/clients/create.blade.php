<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Register New OAuth Client</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.clients.store') }}">
                    @csrf

                    <!-- Name -->
                    <div>
                        <x-input-label for="name" value="Application Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        <p class="mt-1 text-xs text-gray-500">Shown to users on the consent screen.</p>
                    </div>

                    <!-- Redirect URI -->
                    <div class="mt-4">
                        <x-input-label for="redirect_uri" value="Redirect URI" />
                        <x-text-input id="redirect_uri" name="redirect_uri" type="url" class="mt-1 block w-full" :value="old('redirect_uri')" placeholder="https://myapp.example.com/auth/callback" required />
                        <x-input-error :messages="$errors->get('redirect_uri')" class="mt-2" />
                    </div>

                    <!-- Logout URI -->
                    <div class="mt-4">
                        <x-input-label for="logout_uri" value="Logout URI (optional)" />
                        <x-text-input id="logout_uri" name="logout_uri" type="url" class="mt-1 block w-full" :value="old('logout_uri')" placeholder="https://myapp.example.com/auth/logout" />
                        <x-input-error :messages="$errors->get('logout_uri')" class="mt-2" />
                        <p class="mt-1 text-xs text-gray-500">When a user triggers global logout, rajin-auth will POST to this URL so your app can destroy the local session. A <strong>logout secret</strong> will be generated — save it after creation.</p>
                    </div>

                    <!-- Client Type -->
                    <div class="mt-4">
                        <x-input-label value="Client Type" />
                        <div class="mt-2 space-y-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="radio" name="confidential" value="1" checked class="text-indigo-600 focus:ring-indigo-500">
                                <span><strong>Confidential</strong> — server-side apps that can keep a secret</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="radio" name="confidential" value="0" class="text-indigo-600 focus:ring-indigo-500">
                                <span><strong>Public (PKCE)</strong> — SPAs or mobile apps with no server-side backend</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>Create Client</x-primary-button>
                        <a href="{{ route('admin.clients.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
