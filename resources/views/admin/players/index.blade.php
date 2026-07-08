<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-bold text-lg text-blue-100">Players</h2>
            <a href="{{ route('admin.players.create') }}"
               class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700 transition-colors">
                + Add Player
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4">
        @if(session('success'))
            <div class="mb-4 bg-emerald-900/50 text-emerald-300 border border-emerald-700 px-4 py-3 rounded-md text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-blue-900 shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[360px]">
                    <thead class="bg-blue-800 text-blue-200 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Email</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-800">
                        @forelse($players as $player)
                            <tr class="hover:bg-blue-800/40">
                                <td class="px-6 py-4 font-medium text-white">{{ $player->name }}</td>
                                <td class="px-6 py-4 text-blue-300 hidden sm:table-cell">{{ $player->email }}</td>
                                <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                    <a href="{{ route('admin.players.edit', $player) }}" class="text-teal-400 hover:text-teal-300 font-medium">Edit</a>
                                    <form method="POST" action="{{ route('admin.players.destroy', $player) }}" class="inline"
                                          onsubmit="return confirm('Delete {{ $player->name }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-400 hover:text-red-300 font-medium">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-blue-400">No players yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
