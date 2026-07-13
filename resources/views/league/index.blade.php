<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-bold text-lg text-blue-100">
                🎾 {{ $club->name }}
                @if($season) <span class="text-blue-300 font-normal">— {{ $season->name }}</span> @endif
            </h2>
            <div class="flex items-center gap-3 flex-wrap">
                @if($seasons->count() > 1)
                <select onchange="window.location='{{ url('clubs/'.$club->id.'/season') }}/'+this.value+'/singles'"
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
                {{-- Navigation tabs --}}
                <div class="flex gap-2 mb-6 flex-wrap">
                    <a href="{{ route('club.singles', [$club, $season]) }}"
                       class="px-4 py-2 rounded-md text-sm font-medium bg-teal-600 text-white hover:bg-teal-700 transition-colors">
                        Singles
                    </a>
                    <a href="{{ route('club.doubles', [$club, $season]) }}"
                       class="px-4 py-2 rounded-md text-sm font-medium bg-amber-500 text-white hover:bg-amber-600 transition-colors">
                        Doubles
                    </a>
                    <a href="{{ route('club.schedule', $club) }}"
                       class="px-4 py-2 rounded-md text-sm font-medium bg-blue-700 text-blue-100 hover:bg-blue-600 transition-colors">
                        📅 Schedule
                    </a>
                    <a href="{{ route('club.export', [$club, $season]) }}"
                       class="px-4 py-2 rounded-md text-sm font-medium bg-blue-800 text-blue-200 hover:bg-blue-700 transition-colors">
                        📥 Export
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Singles preview --}}
                    <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                        <div class="bg-teal-600 px-5 py-3.5 flex justify-between items-center">
                            <h3 class="text-white font-bold">Singles Standings</h3>
                            <a href="{{ route('club.singles', [$club, $season]) }}" class="text-teal-100 text-xs hover:text-white">View all →</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm min-w-[300px]">
                                <thead class="bg-blue-800 text-blue-200 uppercase text-xs">
                                    <tr><th class="px-4 py-2 text-left">#</th><th class="px-4 py-2 text-left">Player</th><th class="px-3 py-2 text-center">W</th><th class="px-3 py-2 text-center">L</th><th class="px-3 py-2 text-center">Pts</th></tr>
                                </thead>
                                <tbody class="divide-y divide-blue-800">
                                    @forelse(array_slice($singlesStandings, 0, 5) as $i => $row)
                                        <tr class="{{ $i === 0 ? 'bg-blue-800/60' : '' }}">
                                            <td class="px-4 py-2 text-blue-400">{{ $i+1 }}</td>
                                            <td class="px-4 py-2 text-white font-medium">{{ $i === 0 ? '🏆 ' : '' }}{{ $row['player']->name }}</td>
                                            <td class="px-3 py-2 text-center text-emerald-400">{{ $row['won'] }}</td>
                                            <td class="px-3 py-2 text-center text-red-400">{{ $row['lost'] }}</td>
                                            <td class="px-3 py-2 text-center font-bold text-teal-300">{{ $row['points'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-4 py-4 text-center text-blue-400">No matches yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Doubles preview --}}
                    <div class="bg-blue-900 rounded-lg shadow overflow-hidden">
                        <div class="bg-amber-500 px-5 py-3.5 flex justify-between items-center">
                            <h3 class="text-white font-bold">Doubles Standings</h3>
                            <a href="{{ route('club.doubles', [$club, $season]) }}" class="text-amber-100 text-xs hover:text-white">View all →</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm min-w-[300px]">
                                <thead class="bg-blue-800 text-blue-200 uppercase text-xs">
                                    <tr><th class="px-4 py-2 text-left">#</th><th class="px-4 py-2 text-left">Pair</th><th class="px-3 py-2 text-center">W</th><th class="px-3 py-2 text-center">L</th><th class="px-3 py-2 text-center">Pts</th></tr>
                                </thead>
                                <tbody class="divide-y divide-blue-800">
                                    @forelse(array_slice($doublesStandings, 0, 5) as $i => $row)
                                        <tr class="{{ $i === 0 ? 'bg-blue-800/60' : '' }}">
                                            <td class="px-4 py-2 text-blue-400">{{ $i+1 }}</td>
                                            <td class="px-4 py-2 text-white font-medium">{{ $i === 0 ? '🏆 ' : '' }}{{ $row['pair']->displayName() }}</td>
                                            <td class="px-3 py-2 text-center text-emerald-400">{{ $row['won'] }}</td>
                                            <td class="px-3 py-2 text-center text-red-400">{{ $row['lost'] }}</td>
                                            <td class="px-3 py-2 text-center font-bold text-amber-400">{{ $row['points'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-4 py-4 text-center text-blue-400">No matches yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
