<section>

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Profile Information</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update your personal details, avatar and address.</p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5" enctype="multipart/form-data" novalidate>
        @csrf
        @method('patch')

        {{-- Avatar --}}
        <div>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profile Picture</p>
            <div class="flex items-center gap-4">
                @if ($user->avatar)
                    <img src="{{ $user->avatar }}" alt="Avatar" class="w-16 h-16 rounded-full object-cover border-2 border-indigo-100 dark:border-indigo-900">
                @else
                    <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/30 border-2 border-indigo-100 dark:border-indigo-800 flex items-center justify-center">
                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                @endif
                <div>
                    <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp"
                        class="text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 file:text-sm file:font-medium hover:file:bg-indigo-100 cursor-pointer">
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">JPG, PNG or WebP · max 2MB</p>
                </div>
            </div>
            <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('avatar')" />
        </div>

        {{-- Name --}}
        <div class="relative">
            <input id="name" name="name" type="text" placeholder=" " required autocomplete="name"
                value="{{ old('name', $user->name) }}"
                class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent {{ $errors->has('name') ? 'border-red-500 focus:ring-red-400 focus:border-red-500' : '' }}">
            <label for="name" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                Name
            </label>
            <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div class="relative">
            <input id="email" name="email" type="email" placeholder=" " required autocomplete="username"
                value="{{ old('email', $user->email) }}"
                class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent {{ $errors->has('email') ? 'border-red-500 focus:ring-red-400 focus:border-red-500' : '' }}">
            <label for="email" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                Email
            </label>
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-1.5 text-xs text-amber-600 dark:text-amber-400">
                    Unverified.
                    <button form="send-verification" class="underline hover:text-amber-700 dark:hover:text-amber-300">Resend verification email</button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-1 text-xs text-green-600 dark:text-green-400">Verification link sent.</p>
                @endif
            @endif
            <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('email')" />
        </div>

        {{-- Phone --}}
        <div class="relative">
            <input id="phone_number" name="phone_number" type="tel" placeholder=" " autocomplete="tel"
                value="{{ old('phone_number', $user->phone_number) }}"
                class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent pr-24 {{ $errors->has('phone_number') ? 'border-red-500 focus:ring-red-400 focus:border-red-500' : '' }}">
            <label for="phone_number" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                Phone Number
            </label>
            @if ($user->phone_number)
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium {{ $user->phone_verified_at ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                    {{ $user->phone_verified_at ? '✓ Verified' : 'Unverified' }}
                </span>
            @endif
            <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('phone_number')" />
        </div>

        {{-- Date of birth + Gender --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="relative">
                <input id="date_of_birth" name="date_of_birth" type="date" placeholder=" "
                    value="{{ old('date_of_birth', $user->profile?->date_of_birth?->format('Y-m-d')) }}"
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent">
                <label for="date_of_birth" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                    peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                    peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                    Date of Birth
                </label>
                <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('date_of_birth')" />
            </div>
            <div class="relative">
                <select id="gender" name="gender"
                    class="block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition">
                    <option value="" disabled {{ !old('gender', $user->profile?->gender) ? 'selected' : '' }}>Gender</option>
                    @foreach(['male' => 'Male', 'female' => 'Female', 'non_binary' => 'Non-binary', 'prefer_not_to_say' => 'Prefer not to say'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('gender', $user->profile?->gender) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('gender')" />
            </div>
        </div>

        {{-- Bio --}}
        <div class="relative">
            <textarea id="bio" name="bio" placeholder=" " rows="3" maxlength="500"
                class="peer block w-full px-4 py-4 rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent resize-none">{{ old('bio', $user->profile?->bio) }}</textarea>
            <label for="bio" class="profile-label pointer-events-none absolute left-3 top-5 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                peer-focus:-top-2.5 peer-focus:text-xs peer-focus:font-medium
                peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                Bio
            </label>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Max 500 characters</p>
            <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('bio')" />
        </div>

        {{-- Website --}}
        <div class="relative">
            <input id="website" name="website" type="url" placeholder=" "
                value="{{ old('website', $user->profile?->website) }}"
                class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent">
            <label for="website" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                Website
            </label>
            <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('website')" />
        </div>

        {{-- Address --}}
        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Address</p>

            <div class="relative">
                <input id="street_address" name="street_address" type="text" placeholder=" " autocomplete="street-address"
                    value="{{ old('street_address', $user->profile?->street_address) }}"
                    class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent">
                <label for="street_address" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                    peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                    peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                    Street Address
                </label>
                <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('street_address')" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="relative">
                    <input id="city" name="city" type="text" placeholder=" " autocomplete="address-level2"
                        value="{{ old('city', $user->profile?->city) }}"
                        class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent">
                    <label for="city" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                        peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                        peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                        City
                    </label>
                    <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('city')" />
                </div>
                <div class="relative">
                    <input id="state" name="state" type="text" placeholder=" " autocomplete="address-level1"
                        value="{{ old('state', $user->profile?->state) }}"
                        class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent">
                    <label for="state" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                        peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                        peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                        State / Province
                    </label>
                    <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('state')" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="relative">
                    <input id="postal_code" name="postal_code" type="text" placeholder=" " autocomplete="postal-code"
                        value="{{ old('postal_code', $user->profile?->postal_code) }}"
                        class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent">
                    <label for="postal_code" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                        peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                        peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                        Postal Code
                    </label>
                    <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('postal_code')" />
                </div>
                <div class="relative">
                    <input id="country" name="country" type="text" placeholder=" " autocomplete="country-name"
                        value="{{ old('country', $user->profile?->country) }}"
                        class="peer block w-full px-4 py-[17px] rounded-xl border border-[#86868b] dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-transparent">
                    <label for="country" class="profile-label pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-1 text-sm transition-all duration-200
                        peer-focus:-top-2.5 peer-focus:translate-y-0 peer-focus:text-xs peer-focus:font-medium
                        peer-[:not(:placeholder-shown)]:-top-2.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-medium">
                        Country
                    </label>
                    <x-input-error class="mt-1.5 text-xs" :messages="$errors->get('country')" />
                </div>
            </div>
        </div>

        {{-- Save --}}
        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                class="flex justify-center items-center px-6 py-[17px] bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                Save Changes
            </button>
        </div>
    </form>

    <style>
        .profile-label { color: #6e6e73; }
        input:focus ~ .profile-label,
        input:not(:placeholder-shown) ~ .profile-label,
        textarea:focus ~ .profile-label,
        textarea:not(:placeholder-shown) ~ .profile-label { color: #6366f1; }
        .dark .profile-label { color: #9ca3af; }
        .dark input:focus ~ .profile-label,
        .dark input:not(:placeholder-shown) ~ .profile-label,
        .dark textarea:focus ~ .profile-label,
        .dark textarea:not(:placeholder-shown) ~ .profile-label { color: #818cf8; }
    </style>
</section>
