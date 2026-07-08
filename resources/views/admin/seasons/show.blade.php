<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-bold text-lg text-blue-100 flex items-center gap-2">
                {{ $season->name }} ({{ $season->year }})
                @if($season->active)<span class="bg-teal-900/60 text-teal-300 border border-teal-700 px-2 py-0.5 rounded-full text-xs font-semibold">Active</span>@endif
            </h2>
            <a href="{{ route('admin.seasons.index', $club) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← All Seasons</a>
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 space-y-6">
        @if(session('success'))<div class="bg-emerald-900/50 text-emerald-300 border border-emerald-700 px-4 py-3 rounded-md text-sm">{{ session('success') }}</div>@endif

        {{-- Pairs --}}
        <div class="bg-blue-900 shadow rounded-lg p-5">
            <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                <h3 class="font-bold text-blue-100">Doubles Pairs</h3>
                <form method="POST" action="{{ route('admin.seasons.randomize-pairs', [$club, $season]) }}">@csrf
                    <button type="submit" onclick="return confirm('Re-randomize all pairs?')" class="bg-amber-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-amber-600 transition-colors">🔀 Randomize</button>
                </form>
            </div>
            @if($players->count() < 2)
                <p class="text-blue-400 text-sm">Need at least 2 members.</p>
            @else
                @if($pairs->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-5">
                    @foreach($pairs as $pair)
                    <div class="border border-blue-700 rounded-lg px-4 py-3 bg-blue-800/50 flex justify-between items-start gap-2">
                        <div class="text-sm"><div class="font-medium text-white">{{ $pair->player1->name }}</div><div class="text-blue-400 text-xs my-0.5">&</div><div class="font-medium text-white">{{ $pair->player2->name }}</div></div>
                        <div class="flex flex-col gap-1 shrink-0">
                            <a href="{{ route('admin.seasons.pairs.edit', [$club, $season, $pair]) }}" class="text-xs text-teal-400 hover:text-teal-300">Edit</a>
                            <form method="POST" action="{{ route('admin.seasons.pairs.destroy', [$club, $season, $pair]) }}" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="text-xs text-red-400 hover:text-red-300">Remove</button></form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                <div class="border-t border-blue-700 pt-4">
                    <p class="text-blue-300 text-sm font-medium mb-3">Add pair manually</p>
                    <form method="POST" action="{{ route('admin.seasons.pairs.store', [$club, $season]) }}" class="flex flex-wrap gap-3 items-end">@csrf
                        <div class="flex-1 min-w-[140px]"><label class="block text-xs text-blue-400 mb-1">Player 1</label><select name="player1_id" required class="w-full bg-blue-800 border-blue-700 text-white text-sm rounded-md"><option value="">Select...</option>@foreach($players as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
                        <div class="flex-1 min-w-[140px]"><label class="block text-xs text-blue-400 mb-1">Player 2</label><select name="player2_id" required class="w-full bg-blue-800 border-blue-700 text-white text-sm rounded-md"><option value="">Select...</option>@foreach($players as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
                        <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700">+ Add</button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Add Matches --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-blue-900 shadow rounded-lg p-5">
                <h3 class="font-bold text-blue-100 mb-3">Singles</h3>
                <a href="{{ route('admin.matches.singles.create', [$club, $season]) }}" class="inline-block bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700">+ Add Result</a>
            </div>
            <div class="bg-blue-900 shadow rounded-lg p-5">
                <h3 class="font-bold text-blue-100 mb-3">Doubles</h3>
                @if($pairs->isEmpty())<p class="text-blue-400 text-sm">Add pairs first.</p>
                @else<a href="{{ route('admin.matches.doubles.create', [$club, $season]) }}" class="inline-block bg-amber-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-amber-600">+ Add Result</a>@endif
            </div>
        </div>

        {{-- Singles Results --}}
        @if($singlesMatches->count())
        <div class="bg-blue-900 shadow rounded-lg overflow-hidden">
            <div class="bg-teal-700/40 border-b border-teal-700 px-5 py-3.5"><h3 class="font-bold text-teal-300">Singles Results</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[480px]">
                    <thead class="bg-blue-800 text-blue-200 uppercase text-xs"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-center">Player 1</th><th class="px-4 py-3 text-center">Score</th><th class="px-4 py-3 text-center">Player 2</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
                    <tbody class="divide-y divide-blue-800">
                        @foreach($singlesMatches as $match)
                        <tr class="hover:bg-blue-800/40">
                            <td class="px-4 py-3 text-blue-400 whitespace-nowrap">{{ $match->played_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-blue-100">{{ $match->player1->name }}</td>
                            <td class="px-4 py-3 text-center"><span class="font-mono font-bold text-white">{{ $match->score1 }}–{{ $match->score2 }}</span>@if($match->sets)<div class="text-xs text-blue-400 mt-0.5">{{ $match->setsDisplay() }}</div>@endif</td>
                            <td class="px-4 py-3 text-center text-blue-100">{{ $match->player2->name }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('admin.matches.singles.edit', [$club, $season, $match]) }}" class="text-teal-400 hover:text-teal-300 text-xs font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.matches.singles.destroy', [$club, $season, $match]) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-400 hover:text-red-300 text-xs font-medium">Delete</button></form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Doubles Results --}}
        @if($doublesMatches->count())
        <div class="bg-blue-900 shadow rounded-lg overflow-hidden">
            <div class="bg-amber-600/30 border-b border-amber-700 px-5 py-3.5"><h3 class="font-bold text-amber-300">Doubles Results</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[480px]">
                    <thead class="bg-blue-800 text-blue-200 uppercase text-xs"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-center">Pair 1</th><th class="px-4 py-3 text-center">Score</th><th class="px-4 py-3 text-center">Pair 2</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
                    <tbody class="divide-y divide-blue-800">
                        @foreach($doublesMatches as $match)
                        <tr class="hover:bg-blue-800/40">
                            <td class="px-4 py-3 text-blue-400 whitespace-nowrap">{{ $match->played_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-blue-100">{{ $match->pair1->displayName() }}</td>
                            <td class="px-4 py-3 text-center"><span class="font-mono font-bold text-white">{{ $match->score1 }}–{{ $match->score2 }}</span>@if($match->sets)<div class="text-xs text-blue-400 mt-0.5">{{ $match->setsDisplay() }}</div>@endif</td>
                            <td class="px-4 py-3 text-center text-blue-100">{{ $match->pair2->displayName() }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('admin.matches.doubles.edit', [$club, $season, $match]) }}" class="text-teal-400 hover:text-teal-300 text-xs font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.matches.doubles.destroy', [$club, $season, $match]) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-400 hover:text-red-300 text-xs font-medium">Delete</button></form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Activate/Delete --}}
        <div class="flex flex-wrap justify-between items-center gap-3 pt-2">
            @if(!$season->active)<form method="POST" action="{{ route('admin.seasons.activate', [$club, $season]) }}">@csrf<button class="bg-emerald-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-700">Set Active</button></form>@else<span class="text-sm text-blue-400">Active season.</span>@endif
            <form method="POST" action="{{ route('admin.seasons.destroy', [$club, $season]) }}" onsubmit="return confirm('Delete season?')">@csrf @method('DELETE')<button class="text-red-400 text-sm hover:text-red-300 font-medium">Delete Season</button></form>
        </div>
    </div>
</x-app-layout>
