<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🎾 {{ $season->name }} ({{ $season->year }})
            </h2>
            <a href="{{ route('home') }}" class="text-sm text-indigo-600 hover:underline">← Back to standings</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            {{-- Standings side by side --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="bg-indigo-600 px-6 py-4"><h3 class="text-white font-bold text-lg">Singles Standings</h3></div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Player</th>
                                <th class="px-4 py-3 text-center">P</th>
                                <th class="px-4 py-3 text-center">W</th>
                                <th class="px-4 py-3 text-center">D</th>
                                <th class="px-4 py-3 text-center">L</th>
                                <th class="px-4 py-3 text-center font-bold">Pts</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($singlesStandings as $i => $row)
                                <tr class="{{ $i === 0 ? 'bg-yellow-50' : '' }}">
                                    <td class="px-4 py-3 text-gray-400">{{ $i+1 }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $i === 0 ? '🏆 ' : '' }}{{ $row['player']->name }}</td>
                                    <td class="px-4 py-3 text-center">{{ $row['played'] }}</td>
                                    <td class="px-4 py-3 text-center text-green-600">{{ $row['won'] }}</td>
                                    <td class="px-4 py-3 text-center text-yellow-600">{{ $row['drawn'] }}</td>
                                    <td class="px-4 py-3 text-center text-red-500">{{ $row['lost'] }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-indigo-700">{{ $row['points'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">No matches played yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="bg-green-600 px-6 py-4"><h3 class="text-white font-bold text-lg">Doubles Standings</h3></div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Pair</th>
                                <th class="px-4 py-3 text-center">P</th>
                                <th class="px-4 py-3 text-center">W</th>
                                <th class="px-4 py-3 text-center">D</th>
                                <th class="px-4 py-3 text-center">L</th>
                                <th class="px-4 py-3 text-center font-bold">Pts</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($doublesStandings as $i => $row)
                                <tr class="{{ $i === 0 ? 'bg-yellow-50' : '' }}">
                                    <td class="px-4 py-3 text-gray-400">{{ $i+1 }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $i === 0 ? '🏆 ' : '' }}{{ $row['pair']->displayName() }}</td>
                                    <td class="px-4 py-3 text-center">{{ $row['played'] }}</td>
                                    <td class="px-4 py-3 text-center text-green-600">{{ $row['won'] }}</td>
                                    <td class="px-4 py-3 text-center text-yellow-600">{{ $row['drawn'] }}</td>
                                    <td class="px-4 py-3 text-center text-red-500">{{ $row['lost'] }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-green-700">{{ $row['points'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">No matches played yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Singles Match Results --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-indigo-100 px-6 py-4"><h3 class="font-bold text-indigo-800">Singles Results</h3></div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-center">Player 1</th>
                            <th class="px-4 py-3 text-center">Score</th>
                            <th class="px-4 py-3 text-center">Player 2</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($singlesMatches as $match)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">{{ $match->played_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-center {{ $match->score1 > $match->score2 ? 'font-bold text-green-700' : '' }}">{{ $match->player1->name }}</td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-gray-800">
                                    @if(!is_null($match->score1))
                                        {{ $match->score1 }} – {{ $match->score2 }}
                                    @else
                                        <span class="text-gray-400 font-normal">TBD</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center {{ $match->score2 > $match->score1 ? 'font-bold text-green-700' : '' }}">{{ $match->player2->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No singles matches yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Doubles Match Results --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-green-100 px-6 py-4"><h3 class="font-bold text-green-800">Doubles Results</h3></div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-center">Pair 1</th>
                            <th class="px-4 py-3 text-center">Score</th>
                            <th class="px-4 py-3 text-center">Pair 2</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($doublesMatches as $match)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">{{ $match->played_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-center {{ $match->score1 > $match->score2 ? 'font-bold text-green-700' : '' }}">{{ $match->pair1->displayName() }}</td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-gray-800">
                                    @if(!is_null($match->score1))
                                        {{ $match->score1 }} – {{ $match->score2 }}
                                    @else
                                        <span class="text-gray-400 font-normal">TBD</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center {{ $match->score2 > $match->score1 ? 'font-bold text-green-700' : '' }}">{{ $match->pair2->displayName() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No doubles matches yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
