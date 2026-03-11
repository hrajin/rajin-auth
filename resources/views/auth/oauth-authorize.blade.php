<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-800">{{ $client->name }}</h2>
        <p class="mt-1 text-sm text-gray-600">is requesting access to your account</p>
    </div>

    @if (count($scopes) > 0)
        <div class="mb-6">
            <p class="text-sm font-medium text-gray-700 mb-2">This app will be able to:</p>
            <ul class="space-y-1">
                @foreach ($scopes as $scope)
                    <li class="flex items-start text-sm text-gray-600">
                        <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ $scope->description }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="text-xs text-gray-500 mb-6">
        Authorizing will redirect you to:
        <span class="font-mono break-all">{{ $client->redirect }}</span>
    </p>

    <div class="flex gap-3">
        {{-- Authorize --}}
        <form method="POST" action="/oauth/authorize" class="flex-1">
            @csrf
            @foreach ($request->all() as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Authorize
            </button>
        </form>

        {{-- Deny --}}
        <form method="POST" action="/oauth/authorize" class="flex-1">
            @csrf
            @method('DELETE')
            @foreach ($request->all() as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Deny
            </button>
        </form>
    </div>
</x-guest-layout>
