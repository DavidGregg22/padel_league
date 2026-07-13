<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-bold text-lg text-blue-100">🎾 Doubles — {{ $season->name }}</h2>
            <a href="{{ route('club.league', $club) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← Overview</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 space-y-6">

            {{-- Tab nav --}}
            <div class="flex gap-2">
                <a href="{{ route('club.singles', [$club, $season]) }}" class="px-4 py-2 rounded-md text-sm font-medium bg-blue-800 text-blue-300 hover:text-white hover:bg-blue-700 transition-colors">Singles</a>
                <span class="px-4 py-2 rounded-md text-sm font-medium bg-amber-500 text-white">Doubles</span>
            </div>

            {{-- Standings --}}
            <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                <div class="bg-amber-500 px-5 py-3.5"><h3 class="text-white font-bold">Standings</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[340px]">
                        <thead class="bg-blue-800 text-blue-200 uppercase text-xs">
                            <tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">Pair</th><th class="px-3 py-3 text-center">P</th><th class="px-3 py-3 text-center">W</th><th class="px-3 py-3 text-center">D</th><th class="px-3 py-3 text-center">L</th><th class="px-3 py-3 text-center font-bold">Pts</th></tr>
                        </thead>
                        <tbody class="divide-y divide-blue-800">
                            @forelse($standings as $i => $row)
                                <tr class="{{ $i === 0 ? 'bg-blue-800/60' : 'hover:bg-blue-800/40' }}">
                                    <td class="px-4 py-3 text-blue-400">{{ $i+1 }}</td>
                                    <td class="px-4 py-3 font-medium text-white">{{ $i === 0 ? '🏆 ' : '' }}{{ $row['pair']->displayName() }}</td>
                                    <td class="px-3 py-3 text-center text-blue-300">{{ $row['played'] }}</td>
                                    <td class="px-3 py-3 text-center text-emerald-400 font-medium">{{ $row['won'] }}</td>
                                    <td class="px-3 py-3 text-center text-amber-400">{{ $row['drawn'] }}</td>
                                    <td class="px-3 py-3 text-center text-red-400">{{ $row['lost'] }}</td>
                                    <td class="px-3 py-3 text-center font-bold text-amber-400">{{ $row['points'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-6 text-center text-blue-400">No pairs assigned yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pairs --}}
            @if($pairs->count())
            <div class="bg-blue-900 rounded-lg shadow p-5">
                <h3 class="font-bold text-blue-100 mb-3">Teams</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($pairs as $pair)
                        <div class="border border-blue-700 rounded-lg px-4 py-3 text-sm bg-blue-800/50">
                            <div class="font-medium text-white">{{ $pair->player1->name }}</div>
                            <div class="text-blue-400 text-xs my-0.5">&</div>
                            <div class="font-medium text-white">{{ $pair->player2->name }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Order of Play --}}
            <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                <div class="bg-amber-600/30 border-b border-amber-700 px-5 py-3.5 flex justify-between items-center">
                    <h3 class="font-bold text-amber-300">Order of Play</h3>
                    <span class="text-amber-200 text-xs">{{ $fixturesPlayed }} / {{ count($fixtures) }} played</span>
                </div>

                @if(count($fixtures) > 0)
                <div class="px-5 pt-3">
                    <div class="w-full bg-blue-800 rounded-full h-2">
                        <div class="bg-amber-400 h-2 rounded-full" style="width: {{ round($fixturesPlayed / count($fixtures) * 100) }}%"></div>
                    </div>
                </div>
                @endif

                <div class="p-5 space-y-2">
                    @forelse($fixtures as $fixture)
                        <div class="flex flex-col bg-blue-800/50 rounded-lg px-4 py-3 {{ $fixture['played'] ? 'opacity-70' : '' }}"
                             x-data="{ showForm: false }">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 flex-1 min-w-0">
                                    <span class="font-medium text-white text-sm truncate">{{ $fixture['pair1']->displayName() }}</span>
                                    <span class="text-blue-400 text-xs shrink-0">vs</span>
                                    <span class="font-medium text-white text-sm truncate">{{ $fixture['pair2']->displayName() }}</span>
                                </div>
                                <div class="shrink-0 ml-3">
                                    @if($fixture['played'])
                                        <span class="text-xs font-mono font-bold text-emerald-400">{{ $fixture['match']->score1 }}–{{ $fixture['match']->score2 }}</span>
                                        @if($fixture['match']->sets)
                                            <span class="text-xs text-blue-400 ml-1">({{ $fixture['match']->setsDisplay() }})</span>
                                        @endif
                                    @else
                                        @if(auth()->user()->isClubAdmin($club))
                                            <button @click="showForm = !showForm" class="text-xs text-amber-400 bg-amber-900/50 px-2 py-1 rounded font-medium hover:bg-amber-900 transition-colors">
                                                Log Score
                                            </button>
                                        @else
                                            <span class="text-xs text-amber-400 bg-amber-900/40 px-2 py-0.5 rounded font-medium">Pending</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @if(!$fixture['played'] && auth()->user()->isClubAdmin($club))
                            <div x-show="showForm" x-cloak class="mt-3 pt-3 border-t border-blue-700">
                                <form method="POST" action="{{ route('admin.matches.doubles.store', [$club, $season]) }}" class="flex flex-wrap gap-2 items-end">
                                    @csrf
                                    <input type="hidden" name="pair1_id" value="{{ $fixture['pair1']->id }}">
                                    <input type="hidden" name="pair2_id" value="{{ $fixture['pair2']->id }}">
                                    <input type="hidden" name="played_at" value="{{ date('Y-m-d') }}">
                                    <div class="flex-1 min-w-[120px]">
                                        <label class="block text-xs text-blue-400 mb-1">Sets (e.g. 6-4, 7-5)</label>
                                        <input type="text" name="sets_input" required placeholder="6-4, 7-5"
                                               class="w-full text-sm bg-blue-800 border-blue-700 text-white placeholder-blue-500 rounded-md px-3 py-1.5 focus:ring-teal-500 focus:border-teal-500">
                                    </div>
                                    <button type="submit" class="bg-amber-500 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-amber-600 transition-colors">Save</button>
                                </form>
                            </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-blue-400 text-sm text-center py-4">No pairs assigned yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent Results --}}
            @if($matches->count())
            <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                <div class="bg-blue-800 px-5 py-3.5"><h3 class="font-bold text-blue-100">Recent Results</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[400px]">
                        <thead class="bg-blue-800/50 text-blue-200 uppercase text-xs"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-center">Pair 1</th><th class="px-4 py-3 text-center">Score</th><th class="px-4 py-3 text-center">Pair 2</th></tr></thead>
                        <tbody class="divide-y divide-blue-800">
                            @foreach($matches as $match)
                            <tr class="hover:bg-blue-800/40">
                                <td class="px-4 py-3 text-blue-400 whitespace-nowrap">{{ $match->played_at?->format('d M') ?? '—' }}</td>
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
