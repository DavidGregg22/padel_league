<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-bold text-lg text-blue-100">🎾 {{ $season->name }} <span class="text-blue-300 font-normal">({{ $season->year }})</span></h2>
            <a href="{{ route('club.league', $club) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← Standings</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Singles --}}
                <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                    <div class="bg-teal-600 px-5 py-3.5"><h3 class="text-white font-bold">Singles</h3></div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[340px]">
                            <thead class="bg-blue-800 text-blue-200 uppercase text-xs"><tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">Player</th><th class="px-3 py-3 text-center">P</th><th class="px-3 py-3 text-center">W</th><th class="px-3 py-3 text-center">D</th><th class="px-3 py-3 text-center">L</th><th class="px-3 py-3 text-center">Pts</th></tr></thead>
                            <tbody class="divide-y divide-blue-800">
                                @forelse($singlesStandings as $i => $row)
                                <tr class="{{ $i === 0 ? 'bg-blue-800/60' : 'hover:bg-blue-800/40' }}"><td class="px-4 py-3 text-blue-400">{{ $i+1 }}</td><td class="px-4 py-3 font-medium text-white">{{ $i === 0 ? '🏆 ' : '' }}{{ $row['player']->name }}</td><td class="px-3 py-3 text-center text-blue-300">{{ $row['played'] }}</td><td class="px-3 py-3 text-center text-emerald-400">{{ $row['won'] }}</td><td class="px-3 py-3 text-center text-amber-400">{{ $row['drawn'] }}</td><td class="px-3 py-3 text-center text-red-400">{{ $row['lost'] }}</td><td class="px-3 py-3 text-center font-bold text-teal-300">{{ $row['points'] }}</td></tr>
                                @empty<tr><td colspan="7" class="px-4 py-6 text-center text-blue-400">No matches yet.</td></tr>@endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Doubles --}}
                <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                    <div class="bg-amber-500 px-5 py-3.5"><h3 class="text-white font-bold">Doubles</h3></div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[340px]">
                            <thead class="bg-blue-800 text-blue-200 uppercase text-xs"><tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">Pair</th><th class="px-3 py-3 text-center">P</th><th class="px-3 py-3 text-center">W</th><th class="px-3 py-3 text-center">D</th><th class="px-3 py-3 text-center">L</th><th class="px-3 py-3 text-center">Pts</th></tr></thead>
                            <tbody class="divide-y divide-blue-800">
                                @forelse($doublesStandings as $i => $row)
                                <tr class="{{ $i === 0 ? 'bg-blue-800/60' : 'hover:bg-blue-800/40' }}"><td class="px-4 py-3 text-blue-400">{{ $i+1 }}</td><td class="px-4 py-3 font-medium text-white">{{ $i === 0 ? '🏆 ' : '' }}{{ $row['pair']->displayName() }}</td><td class="px-3 py-3 text-center text-blue-300">{{ $row['played'] }}</td><td class="px-3 py-3 text-center text-emerald-400">{{ $row['won'] }}</td><td class="px-3 py-3 text-center text-amber-400">{{ $row['drawn'] }}</td><td class="px-3 py-3 text-center text-red-400">{{ $row['lost'] }}</td><td class="px-3 py-3 text-center font-bold text-amber-400">{{ $row['points'] }}</td></tr>
                                @empty<tr><td colspan="7" class="px-4 py-6 text-center text-blue-400">No matches yet.</td></tr>@endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Match results --}}
            @if($singlesMatches->count())
            <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                <div class="bg-teal-700/40 border-b border-teal-700 px-5 py-3.5"><h3 class="font-bold text-teal-300">Singles Results</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[400px]">
                        <thead class="bg-blue-800 text-blue-200 uppercase text-xs"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-center">Player 1</th><th class="px-4 py-3 text-center">Score</th><th class="px-4 py-3 text-center">Player 2</th></tr></thead>
                        <tbody class="divide-y divide-blue-800">
                            @foreach($singlesMatches as $match)
                            <tr class="hover:bg-blue-800/40">
                                <td class="px-4 py-3 text-blue-400 whitespace-nowrap">{{ $match->played_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-center {{ $match->score1 > $match->score2 ? 'font-bold text-emerald-400' : 'text-blue-100' }}">{{ $match->player1->name }}</td>
                                <td class="px-4 py-3 text-center"><span class="font-mono font-bold text-white">{{ $match->score1 }}–{{ $match->score2 }}</span>@if($match->sets)<div class="text-xs text-blue-400 mt-0.5">{{ $match->setsDisplay() }}</div>@endif</td>
                                <td class="px-4 py-3 text-center {{ $match->score2 > $match->score1 ? 'font-bold text-emerald-400' : 'text-blue-100' }}">{{ $match->player2->name }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if($doublesMatches->count())
            <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                <div class="bg-amber-600/30 border-b border-amber-700 px-5 py-3.5"><h3 class="font-bold text-amber-300">Doubles Results</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[400px]">
                        <thead class="bg-blue-800 text-blue-200 uppercase text-xs"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-center">Pair 1</th><th class="px-4 py-3 text-center">Score</th><th class="px-4 py-3 text-center">Pair 2</th></tr></thead>
                        <tbody class="divide-y divide-blue-800">
                            @foreach($doublesMatches as $match)
                            <tr class="hover:bg-blue-800/40">
                                <td class="px-4 py-3 text-blue-400 whitespace-nowrap">{{ $match->played_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-center {{ $match->score1 > $match->score2 ? 'font-bold text-emerald-400' : 'text-blue-100' }}">{{ $match->pair1->displayName() }}</td>
                                <td class="px-4 py-3 text-center"><span class="font-mono font-bold text-white">{{ $match->score1 }}–{{ $match->score2 }}</span>@if($match->sets)<div class="text-xs text-blue-400 mt-0.5">{{ $match->setsDisplay() }}</div>@endif</td>
                                <td class="px-4 py-3 text-center {{ $match->score2 > $match->score1 ? 'font-bold text-emerald-400' : 'text-blue-100' }}">{{ $match->pair2->displayName() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
