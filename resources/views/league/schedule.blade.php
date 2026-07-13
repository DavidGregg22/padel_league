<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-bold text-lg text-blue-100">📅 Schedule — {{ $club->name }}</h2>
            <a href="{{ route('club.league', $club) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 space-y-6">

            {{-- Who's Free (always at top) --}}
            <div class="bg-blue-900 rounded-lg shadow p-5">
                <h3 class="font-bold text-blue-100 mb-3">Who's Free</h3>
                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    @php $hasAny = false; @endphp
                    @foreach($dates as $date)
                        @if(\Carbon\Carbon::parse($date)->lt($now)) @continue @endif
                        @php $available = $allAvailability->get($date, collect()); @endphp
                        @if($available->count() > 0)
                            @php $hasAny = true; @endphp
                            <div class="bg-blue-800/50 rounded-lg px-4 py-3">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-sm font-medium text-white">{{ date('D j M', strtotime($date)) }}</span>
                                    <span class="text-xs text-blue-400">{{ $available->count() }} {{ $available->count() === 1 ? 'player' : 'players' }}</span>
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
                        <p class="text-blue-400 text-sm text-center py-4">No availability marked this month yet.</p>
                    @endif
                </div>
            </div>

            {{-- Suggested Matches with Court Availability --}}
            @if(count($suggestions) > 0)
            <div class="bg-blue-900 rounded-lg shadow p-5">
                <h3 class="font-bold text-blue-100 mb-1">🎯 Ready to Play</h3>
                <p class="text-blue-400 text-xs mb-3">Pending matches where all players are free on the same day.</p>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    @foreach($suggestions as $s)
                        <div class="bg-blue-800/50 rounded-lg px-4 py-3">
                            <div class="text-sm text-white font-medium">{{ $s['label'] }}</div>
                            <div class="flex items-center justify-between mt-1.5">
                                <span class="text-xs {{ $s['type'] === 'singles' ? 'text-teal-400' : 'text-amber-400' }}">{{ ucfirst($s['type']) }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-emerald-400 bg-emerald-900/50 px-2 py-0.5 rounded">{{ date('D j M', strtotime($s['date'])) }}</span>
                                    @if($club->playtomic_tenant_id)
                                        <a href="https://playtomic.io/tenant/{{ $club->playtomic_tenant_id }}?date={{ $s['date'] }}"
                                           target="_blank" rel="noopener"
                                           class="text-xs text-blue-300 bg-blue-700 px-2 py-0.5 rounded hover:bg-blue-600 transition-colors">
                                            Book Court →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Calendar --}}
            <div class="bg-blue-900 rounded-lg shadow p-5 sm:p-6">
                <div class="flex items-center justify-between mb-6">
                    @if($canGoPrev)
                        <a href="{{ route('club.schedule', $club) }}?month={{ $prevMonth->format('Y-m') }}"
                           class="text-blue-300 hover:text-white p-2 rounded-md bg-blue-800 hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @else
                        <span class="p-2 text-blue-800"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></span>
                    @endif

                    <h3 class="font-bold text-white text-xl">{{ $monthStart->format('F Y') }}</h3>

                    @if($canGoNext)
                        <a href="{{ route('club.schedule', $club) }}?month={{ $nextMonth->format('Y-m') }}"
                           class="text-blue-300 hover:text-white p-2 rounded-md bg-blue-800 hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="p-2 text-blue-800"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                    @endif
                </div>

                {{-- Day headers --}}
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem;" class="mb-2">
                    @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
                        <div class="text-center text-xs font-semibold text-blue-400 uppercase tracking-wide py-1">{{ $day }}</div>
                    @endforeach
                </div>

                {{-- Calendar grid --}}
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem;">
                    @for($i = 0; $i < $startPadding; $i++)
                        <div style="height: 3.5rem;"></div>
                    @endfor

                    @foreach($dates as $date)
                        @php
                            $isAvailable = in_array($date, $myAvailability);
                            $isPast = \Carbon\Carbon::parse($date)->lt($now);
                            $dayNum = date('j', strtotime($date));
                            $availableCount = $allAvailability->get($date, collect())->count();
                            $isToday = $date === $now->toDateString();
                        @endphp

                        @if($isPast)
                            <div class="flex flex-col items-center justify-center rounded-lg opacity-25" style="height: 3.5rem;">
                                <span class="text-sm text-blue-500">{{ $dayNum }}</span>
                            </div>
                        @else
                            <form method="POST" action="{{ route('club.schedule.toggle', $club) }}" style="height: 3.5rem;">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <button type="submit" style="width: 100%; height: 100%;"
                                    class="flex flex-col items-center justify-center rounded-lg transition-all
                                           {{ $isAvailable
                                               ? 'bg-teal-600 text-white ring-2 ring-teal-400 shadow-md'
                                               : 'bg-blue-800/80 text-blue-200 ring-1 ring-blue-700 hover:ring-blue-500 hover:bg-blue-800 active:bg-blue-700' }}
                                           {{ $isToday && !$isAvailable ? '!ring-2 !ring-amber-400' : '' }}
                                           {{ $isToday && $isAvailable ? '!ring-amber-400' : '' }}">
                                    <span class="text-sm font-bold">{{ $dayNum }}</span>
                                    @if($availableCount > 0)
                                        <span class="text-[10px] mt-0.5 {{ $isAvailable ? 'text-teal-200' : 'text-teal-400' }}">{{ $availableCount }} free</span>
                                    @endif
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>

                {{-- Legend --}}
                <div class="mt-5 pt-4 border-t border-blue-800 flex flex-wrap gap-x-5 gap-y-2 text-xs text-blue-400">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-teal-600 ring-2 ring-teal-400 inline-block"></span> You're free</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-800 ring-1 ring-blue-700 inline-block"></span> Tap to mark free</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-800 ring-2 ring-amber-400 inline-block"></span> Today</span>
                </div>
            </div>

            {{-- Court Availability (Playtomic) --}}
            @if($club->playtomic_tenant_id)
            <div class="bg-blue-900 rounded-lg shadow p-5" x-data="courtChecker()">
                <h3 class="font-bold text-blue-100 mb-3">🏟️ Court Availability</h3>
                <div class="flex flex-wrap gap-3 items-end mb-4">
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs text-blue-400 mb-1">Check date</label>
                        <input type="date" x-model="selectedDate" :min="today"
                               class="w-full bg-blue-800 border-blue-700 text-white text-sm rounded-md px-3 py-2 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <button @click="fetchCourts()" :disabled="loading"
                            class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700 transition-colors disabled:opacity-50">
                        <span x-show="!loading">Check Courts</span>
                        <span x-show="loading">Loading...</span>
                    </button>
                </div>

                <div x-show="slots.length > 0" x-cloak>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <template x-for="slot in slots" :key="slot.time + slot.court">
                            <div class="flex items-center justify-between bg-blue-800/50 rounded-lg px-4 py-2.5">
                                <div>
                                    <span class="text-sm text-white font-medium" x-text="slot.time"></span>
                                    <span class="text-xs text-blue-400 ml-2" x-text="slot.duration + ' min'"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-blue-300" x-text="slot.court"></span>
                                    <span class="text-xs text-amber-400" x-text="slot.price"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <a :href="'https://playtomic.io/tenant/{{ $club->playtomic_tenant_id }}?date=' + selectedDate"
                       target="_blank" rel="noopener"
                       class="mt-3 inline-block text-sm text-teal-400 bg-teal-900/50 px-3 py-1.5 rounded-md hover:bg-teal-900 transition-colors">
                        Book on Playtomic →
                    </a>
                </div>

                <p x-show="checked && slots.length === 0" x-cloak class="text-blue-400 text-sm py-4 text-center">
                    No courts available for that date.
                </p>
            </div>

            <script>
            function courtChecker() {
                return {
                    selectedDate: '{{ now()->toDateString() }}',
                    today: '{{ now()->toDateString() }}',
                    slots: [],
                    loading: false,
                    checked: false,
                    fetchCourts() {
                        this.loading = true;
                        this.checked = false;
                        fetch('{{ route("club.schedule.courts", $club) }}?date=' + this.selectedDate)
                            .then(r => r.json())
                            .then(data => {
                                this.slots = data.slots || [];
                                this.checked = true;
                                this.loading = false;
                            })
                            .catch(() => {
                                this.slots = [];
                                this.checked = true;
                                this.loading = false;
                            });
                    }
                }
            }
            </script>
            @endif

        </div>
    </div>
</x-app-layout>
