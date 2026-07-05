<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🎾 Padel League
                @if($season) — {{ $season->name }} @endif
            </h2>
            <div class="flex items-center gap-3">
                @if($seasons->count() > 1)
                <form method="GET" action="{{ route('home') }}">
                    <select name="redirect" onchange="window.location='/season/'+this.value" class="text-sm border-gray-300 rounded-md shadow-sm">
                        @foreach($seasons as $s)
                            <option value="{{ $s->id }}" {{ $season && $season->id === $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->year }}){{ $s->active ? ' ★' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.seasons.index') }}" class="text-sm bg-indigo-600 text-white px-3 py-1 rounded-md hover:bg-indigo-700">Admin</a>
                    @endif
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(!$season)
                <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                    No active season yet. An admin needs to create and activate a season.
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    {{-- Singles Table --}}
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="bg-indigo-600 px-6 py-4">
                            <h3 class="text-white font-bold text-lg">Singles Standings</h3>
                        </div>
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
                                    <tr class="{{ $i === 0 ? 'bg-yellow-50' : '' }} hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-400 font-medium">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ $i === 0 ? '🏆 ' : '' }}{{ $row['player']->name }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-600">{{ $row['played'] }}</td>
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

                    {{-- Doubles Table --}}
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="bg-green-600 px-6 py-4">
                            <h3 class="text-white font-bold text-lg">Doubles Standings</h3>
                        </div>
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
                                    <tr class="{{ $i === 0 ? 'bg-yellow-50' : '' }} hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-400 font-medium">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ $i === 0 ? '🏆 ' : '' }}{{ $row['pair']->displayName() }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-600">{{ $row['played'] }}</td>
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

                <div class="mt-6 text-center">
                    <a href="{{ route('league.season', $season) }}" class="text-indigo-600 hover:underline text-sm">
                        View full season details including match results →
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
