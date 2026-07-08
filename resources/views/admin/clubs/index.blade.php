<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-bold text-lg text-blue-100">Super Admin — All Clubs</h2>
            <a href="{{ route('super.clubs.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700">+ Create Club</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto px-4">
        @if(session('success'))<div class="mb-4 bg-emerald-900/50 text-emerald-300 border border-emerald-700 px-4 py-3 rounded-md text-sm">{{ session('success') }}</div>@endif
        <div class="bg-blue-900 shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[400px]">
                    <thead class="bg-blue-800 text-blue-200 uppercase text-xs"><tr><th class="px-6 py-3 text-left">Club</th><th class="px-6 py-3 text-center">Members</th><th class="px-6 py-3 text-right">Actions</th></tr></thead>
                    <tbody class="divide-y divide-blue-800">
                        @forelse($clubs as $club)
                        <tr class="hover:bg-blue-800/40">
                            <td class="px-6 py-4 font-medium text-white">{{ $club->name }}</td>
                            <td class="px-6 py-4 text-center text-blue-300">{{ $club->users_count }}</td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ route('super.clubs.destroy', $club) }}" class="inline" onsubmit="return confirm('Delete club?')">@csrf @method('DELETE')<button class="text-red-400 hover:text-red-300 text-xs font-medium">Delete</button></form>
                            </td>
                        </tr>
                        @empty<tr><td colspan="3" class="px-6 py-8 text-center text-blue-400">No clubs yet.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
