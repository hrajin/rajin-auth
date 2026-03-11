<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Users</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($user->email_verified_at)
                                        <span class="text-green-600">Yes</span>
                                    @else
                                        <span class="text-red-500">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="{{ $user->is_active ? 'text-green-600' : 'text-red-500' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->toDateString() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">No users yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
