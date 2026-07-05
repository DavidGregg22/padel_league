<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">
                Manage: {{ $season->name }} ({{ $season->year }})
                @if($season->active)
                    <span class="ml-2 bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Active</span>
                @endif
            </h2>
            <a href="{{ route('admin.seasons.index') }}" class="text-sm text-indigo-600 hover:underline">← All Seasons</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 space-y-8">
        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        {{-- Doubles Pairs --}}
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-800">Doubles Pairs</h3>
                <form method="POST" action="{{ route('admin.seasons.randomize-pairs', $season) }}">
                    @csrf
                    <button type="submit"
                        onclick="return confirm('This will re-randomize all pairs for this season. Continue?')"
                        class="bg-yellow-500 text-white px-4 py-2 rounded-md text-sm hover:bg-yellow-600">
                        🔀 Randomize Pairs
                    </button>
                </form>
            </div>

            @if($players->count() < 2)
                <p class="text-gray-500 text-sm">Need at least 2 registered players to create pairs.</p>
            @elseif($pairs->isEmpty())
                <p class="text-gray-500 text-sm">No pairs yet. Click "Randomize Pairs" to assign them for this season.</p>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($pairs as $pair)
                        <div class="border border-gray-200 rounded-lg px-4 py-3 text-sm">
                            <div class="font-medium text-gray-800">{{ $pair->player1->name }}</div>
                            <div class="text-gray-400 text-xs my-1">paired with</div>
                            <div class="font-medium text-gray-800">{{ $pair->player2->name }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Add Matches --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="font-bold text-gray-800 mb-3">Singles Matches</h3>
                <a href="{{ route('admin.matches.singles.create', $season) }}"
                    class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">
                    + Add Singles Result
                </a>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="font-bold text-gray-800 mb-3">Doubles Matches</h3>
                @if($pairs->isEmpty())
                    <p class="text-gray-400 text-sm">Randomize pairs first.</p>
                @else
                    <a href="{{ route('admin.matches.doubles.create', $season) }}"
                        class="inline-block bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                        + Add Doubles Result
                    </a>
                @endif
            </div>
        </div>

        {{-- Singles Match List --}}
        @if($singlesMatches->count())
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-indigo-50 px-6 py-3 flex justify-between items-center">
                <h3 class="font-bold text-indigo-800">Singles Results</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-center">Player 1</th>
                        <th class="px-4 py-3 text-center">Score</th>
                        <th class="px-4 py-3 text-center">Player 2</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($singlesMatches as $match)
                    <tr>
                        <td class="px-4 py-3 text-gray-400">{{ $match->played_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">{{ $match->player1->name }}</td>
                        <td class="px-4 py-3 text-center font-mono font-bold">{{ $match->score1 }} – {{ $match->score2 }}</td>
                        <td class="px-4 py-3 text-center">{{ $match->player2->name }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('admin.matches.singles.edit', [$season, $match]) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.matches.singles.destroy', [$season, $match]) }}" class="inline"
                                  onsubmit="return confirm('Delete this match?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Doubles Match List --}}
        @if($doublesMatches->count())
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-green-50 px-6 py-3">
                <h3 class="font-bold text-green-800">Doubles Results</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-center">Pair 1</th>
                        <th class="px-4 py-3 text-center">Score</th>
                        <th class="px-4 py-3 text-center">Pair 2</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($doublesMatches as $match)
                    <tr>
                        <td class="px-4 py-3 text-gray-400">{{ $match->played_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">{{ $match->pair1->displayName() }}</td>
                        <td class="px-4 py-3 text-center font-mono font-bold">{{ $match->score1 }} – {{ $match->score2 }}</td>
                        <td class="px-4 py-3 text-center">{{ $match->pair2->displayName() }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('admin.matches.doubles.edit', [$season, $match]) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.matches.doubles.destroy', [$season, $match]) }}" class="inline"
                                  onsubmit="return confirm('Delete this match?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Activate / Delete Season --}}
        <div class="flex justify-between items-center">
            @if(!$season->active)
                <form method="POST" action="{{ route('admin.seasons.activate', $season) }}">
                    @csrf
                    <button class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">Set as Active Season</button>
                </form>
            @else
                <span class="text-sm text-gray-400">This is the active season.</span>
            @endif

            <form method="POST" action="{{ route('admin.seasons.destroy', $season) }}"
                  onsubmit="return confirm('Delete this entire season and all its matches? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="text-red-500 text-sm hover:underline">Delete Season</button>
            </form>
        </div>
    </div>
</x-app-layout>
