<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-bold text-lg text-blue-100">📋 Order of Play — {{ $season->name }}</h2>
            <a href="{{ route('club.league', $club) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← Standings</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 space-y-6">

            {{-- Singles Fixtures --}}
            <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                <div class="bg-teal-600 px-5 py-3.5 flex justify-between items-center">
                    <h3 class="text-white font-bold">Singles Fixtures</h3>
                    <span class="text-teal-100 text-sm">{{ $singlesPlayed }} / {{ count($singlesFixtures) }} played</span>
                </div>

                {{-- Progress bar --}}
                @if(count($singlesFixtures) > 0)
                <div class="px-5 pt-3">
                    <div class="w-full bg-blue-800 rounded-full h-2">
                        <div class="bg-teal-500 h-2 rounded-full" style="width: {{ (count($singlesFixtures) > 0) ? round($singlesPlayed / count($singlesFixtures) * 100) : 0 }}%"></div>
                    </div>
                </div>
                @endif

                <div class="p-5 space-y-2">
                    @forelse($singlesFixtures as $fixture)
                        <div class="flex items-center justify-between bg-blue-800/50 rounded-lg px-4 py-3 {{ $fixture['played'] ? 'opacity-70' : '' }}">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <span class="font-medium text-white text-sm truncate">{{ $fixture['player1']->name }}</span>
                                <span class="text-blue-400 text-xs shrink-0">vs</span>
                                <span class="font-medium text-white text-sm truncate">{{ $fixture['player2']->name }}</span>
                            </div>
                            <div class="shrink-0 ml-3">
                                @if($fixture['played'])
                                    <span class="text-xs font-mono font-bold text-emerald-400">
                                        {{ $fixture['match']->score1 }}–{{ $fixture['match']->score2 }}
                                    </span>
                                    @if($fixture['match']->sets)
                                        <span class="text-xs text-blue-400 ml-1">({{ $fixture['match']->setsDisplay() }})</span>
                                    @endif
                                @else
                                    <span class="text-xs text-amber-400 bg-amber-900/40 px-2 py-0.5 rounded font-medium">Pending</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-blue-400 text-sm text-center py-4">No members in this club yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Doubles Fixtures --}}
            <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                <div class="bg-amber-500 px-5 py-3.5 flex justify-between items-center">
                    <h3 class="text-white font-bold">Doubles Fixtures</h3>
                    <span class="text-amber-100 text-sm">{{ $doublesPlayed }} / {{ count($doublesFixtures) }} played</span>
                </div>

                @if(count($doublesFixtures) > 0)
                <div class="px-5 pt-3">
                    <div class="w-full bg-blue-800 rounded-full h-2">
                        <div class="bg-amber-400 h-2 rounded-full" style="width: {{ (count($doublesFixtures) > 0) ? round($doublesPlayed / count($doublesFixtures) * 100) : 0 }}%"></div>
                    </div>
                </div>
                @endif

                <div class="p-5 space-y-2">
                    @forelse($doublesFixtures as $fixture)
                        <div class="flex items-center justify-between bg-blue-800/50 rounded-lg px-4 py-3 {{ $fixture['played'] ? 'opacity-70' : '' }}">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <span class="font-medium text-white text-sm truncate">{{ $fixture['pair1']->displayName() }}</span>
                                <span class="text-blue-400 text-xs shrink-0">vs</span>
                                <span class="font-medium text-white text-sm truncate">{{ $fixture['pair2']->displayName() }}</span>
                            </div>
                            <div class="shrink-0 ml-3">
                                @if($fixture['played'])
                                    <span class="text-xs font-mono font-bold text-emerald-400">
                                        {{ $fixture['match']->score1 }}–{{ $fixture['match']->score2 }}
                                    </span>
                                    @if($fixture['match']->sets)
                                        <span class="text-xs text-blue-400 ml-1">({{ $fixture['match']->setsDisplay() }})</span>
                                    @endif
                                @else
                                    <span class="text-xs text-amber-400 bg-amber-900/40 px-2 py-0.5 rounded font-medium">Pending</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-blue-400 text-sm text-center py-4">No pairs assigned yet.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
