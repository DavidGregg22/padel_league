<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Admin — Seasons</h2>
            <a href="{{ route('admin.seasons.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">+ New Season</a>
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
                        <th class="px-6 py-3 text-left">Season</th>
                        <th class="px-6 py-3 text-center">Year</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($seasons as $season)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $season->name }}</td>
                            <td class="px-6 py-4 text-center">{{ $season->year }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($season->active)
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">Active</span>
                                @else
                                    <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded-full text-xs">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.seasons.show', $season) }}" class="text-indigo-600 hover:underline">Manage</a>
                                @if(!$season->active)
                                    <form method="POST" action="{{ route('admin.seasons.activate', $season) }}" class="inline">
                                        @csrf
                                        <button class="text-green-600 hover:underline">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No seasons yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
