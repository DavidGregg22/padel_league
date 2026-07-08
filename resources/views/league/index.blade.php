<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-bold text-lg text-blue-100">
                🎾 {{ $club->name }}
                @if($season) <span class="text-blue-300 font-normal">— {{ $season->name }}</span> @endif
            </h2>
            <div class="flex items-center gap-3 flex-wrap">
                @if($seasons->count() > 1)
                <select onchange="window.location='{{ url('clubs/'.$club->id.'/season') }}/'+this.value"
                        class="text-sm bg-blue-800 border-blue-700 text-blue-100 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    @foreach($seasons as $s)
                        <option value="{{ $s->id }}" {{ $season && $season->id === $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->year }}){{ $s->active ? ' ★' : '' }}
                        </option>
                    @endforeach
                </select>
                @endif
                @if(auth()->user()->isClubAdmin($club))
                    <a href="{{ route('admin.seasons.index', $club) }}"
                       class="text-sm bg-teal-600 text-white px-3 py-1.5 rounded-md hover:bg-teal-700 transition-colors">
                        Admin
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(!$season)
                <div class="bg-blue-900 rounded-lg shadow p-8 text-center text-blue-300">
                    No active season yet. A club admin needs to create and activate a season.
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Singles --}}
                    <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                        <div class="bg-teal-600 px-5 py-3.5"><h3 class="text-white font-bold">Singles Standings</h3></div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm min-w-[340px]">
                                <thead class="bg-blue-800 text-blue-200 uppercase text-xs">
                                    <tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">Player</th><th class="px-3 py-3 text-center">P</th><th class="px-3 py-3 text-center">W</th><th class="px-3 py-3 text-center">D</th><th class="px-3 py-3 text-center">L</th><th class="px-3 py-3 text-center font-bold">Pts</th></tr>
                                </thead>
                                <tbody class="divide-y divide-blue-800">
                                    @forelse($singlesStandings as $i => $row)
                                        <tr class="{{ $i === 0 ? 'bg-blue-800/60' : 'hover:bg-blue-800/40' }}">
                                            <td class="px-4 py-3 text-blue-400">{{ $i+1 }}</td>
                                            <td class="px-4 py-3 font-medium text-white">{{ $i === 0 ? '🏆 ' : '' }}{{ $row['player']->name }}</td>
                                            <td class="px-3 py-3 text-center text-blue-300">{{ $row['played'] }}</td>
                                            <td class="px-3 py-3 text-center text-emerald-400 font-medium">{{ $row['won'] }}</td>
                                            <td class="px-3 py-3 text-center text-amber-400">{{ $row['drawn'] }}</td>
                                            <td class="px-3 py-3 text-center text-red-400">{{ $row['lost'] }}</td>
                                            <td class="px-3 py-3 text-center font-bold text-teal-300">{{ $row['points'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="px-4 py-6 text-center text-blue-400">No matches played yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Doubles --}}
                    <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                        <div class="bg-amber-500 px-5 py-3.5"><h3 class="text-white font-bold">Doubles Standings</h3></div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm min-w-[340px]">
                                <thead class="bg-blue-800 text-blue-200 uppercase text-xs">
                                    <tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">Pair</th><th class="px-3 py-3 text-center">P</th><th class="px-3 py-3 text-center">W</th><th class="px-3 py-3 text-center">D</th><th class="px-3 py-3 text-center">L</th><th class="px-3 py-3 text-center font-bold">Pts</th></tr>
                                </thead>
                                <tbody class="divide-y divide-blue-800">
                                    @forelse($doublesStandings as $i => $row)
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
                                        <tr><td colspan="7" class="px-4 py-6 text-center text-blue-400">No matches played yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mt-5 text-center">
                    <a href="{{ route('club.season', [$club, $season]) }}" class="text-teal-400 hover:text-teal-300 text-sm font-medium">
                        View full season details →
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
