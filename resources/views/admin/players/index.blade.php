<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Players</h2>
            <a href="{{ route('admin.players.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">+ Add Player</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">
        @if(session('success'))
            <div class="mb-4 bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Name</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($players as $player)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $player->name }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $player->email }}</td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="{{ route('admin.players.edit', $player) }}" class="text-indigo-600 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.players.destroy', $player) }}" class="inline"
                                      onsubmit="return confirm('Delete {{ $player->name }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">No players yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
