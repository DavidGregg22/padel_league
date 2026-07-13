<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-bold text-lg text-blue-100">📅 Schedule — {{ $club->name }}</h2>
            <a href="{{ route('club.league', $club) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-900/50 text-emerald-300 border border-emerald-700 px-4 py-3 rounded-md text-sm">{{ session('success') }}</div>
            @endif

            {{-- Who's available when --}}
            <div class="bg-blue-900 rounded-lg shadow p-5">
                <h3 class="font-bold text-blue-100 mb-4">Who's Free</h3>
                <div class="space-y-3">
                    @php $hasAny = false; @endphp
                    @foreach($dates as $date)
                        @php $available = $allAvailability->get($date, collect()); @endphp
                        @if($available->count() > 0)
                            @php $hasAny = true; @endphp
                            <div class="bg-blue-800/50 rounded-lg px-4 py-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-white">{{ date('D j M', strtotime($date)) }}</span>
                                    <span class="text-xs text-blue-400">{{ $available->count() }} available</span>
                                </div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($available as $a)
                                        <span class="text-xs bg-teal-900/50 text-teal-300 px-2 py-0.5 rounded">{{ $a->user->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @if(!$hasAny)
                        <p class="text-blue-400 text-sm text-center py-4">No one has marked availability yet. Be the first!</p>
                    @endif
                </div>
            </div>

            {{-- Suggested Matches --}}
            @if(count($suggestions) > 0)
            <div class="bg-blue-900 rounded-lg shadow p-5">
                <h3 class="font-bold text-blue-100 mb-1">🎯 Ready to Schedule</h3>
                <p class="text-blue-400 text-xs mb-4">These pending matches have all players available on the same date.</p>
                <div class="space-y-2">
                    @foreach($suggestions as $s)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-blue-800/50 rounded-lg px-4 py-3 gap-2">
                            <div class="flex-1 min-w-0">
                                <span class="text-sm text-white font-medium block sm:inline">{{ $s['label'] }}</span>
                                <span class="text-xs {{ $s['type'] === 'singles' ? 'text-teal-400' : 'text-amber-400' }} sm:ml-2">{{ ucfirst($s['type']) }}</span>
                            </div>
                            <span class="text-xs text-emerald-400 bg-emerald-900/50 px-2 py-0.5 rounded shrink-0 self-start sm:self-auto">
                                {{ date('D j M', strtotime($s['date'])) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- My Availability --}}
            <div class="bg-blue-900 rounded-lg shadow p-5">
                <h3 class="font-bold text-blue-100 mb-1">My Availability</h3>
                <p class="text-blue-400 text-xs mb-4">Tap dates you're free to play. Others can see when you're available.</p>

                <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
                    @foreach($dates as $date)
                        @php
                            $isAvailable = in_array($date, $myAvailability);
                            $dayName = date('D', strtotime($date));
                            $dayNum = date('j', strtotime($date));
                            $monthName = date('M', strtotime($date));
                        @endphp
                        <form method="POST" action="{{ route('club.schedule.toggle', $club) }}">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <button type="submit"
                                class="w-full rounded-lg py-3 px-1 text-center transition-colors min-h-[72px]
                                       {{ $isAvailable
                                           ? 'bg-teal-600 text-white border-2 border-teal-400'
                                           : 'bg-blue-800 text-blue-400 border-2 border-blue-700 hover:border-blue-500 active:bg-blue-700' }}">
                                <div class="text-xs font-medium">{{ $dayName }}</div>
                                <div class="text-lg font-bold leading-tight">{{ $dayNum }}</div>
                                <div class="text-xs">{{ $monthName }}</div>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
