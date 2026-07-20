<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-bold text-lg text-blue-100">📅 Schedule — {{ $club->name }}</h2>
            <a href="{{ route('club.league', $club) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 space-y-6">

            {{-- Who's Free --}}
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
                                    <span class="text-xs text-blue-400">{{ $available->groupBy('user_id')->count() }} {{ $available->groupBy('user_id')->count() === 1 ? 'player' : 'players' }}</span>
                                </div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($available->groupBy('user_id') as $userId => $slots)
                                        @php
                                            $name = $slots->first()->user->name;
                                            $timeStr = $slots->whereNotNull('start_time')->map(fn($s) => substr($s->start_time, 0, 5).'–'.substr($s->end_time, 0, 5))->join(', ');
                                        @endphp
                                        <span class="text-xs bg-teal-900/50 text-teal-300 px-2 py-0.5 rounded">
                                            {{ $name }}@if($timeStr) <span class="text-teal-400">({{ $timeStr }})</span>@endif
                                        </span>
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
                                <div>
                                    <span class="text-xs {{ $s['type'] === 'singles' ? 'text-teal-400' : 'text-amber-400' }}">{{ ucfirst($s['type']) }}</span>
                                    @if($s['times'])
                                        <span class="text-xs text-blue-300 ml-1">· {{ $s['times'] }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-emerald-400 bg-emerald-900/50 px-2 py-0.5 rounded">{{ date('D j M', strtotime($s['date'])) }}</span>
                                    @if($club->playtomic_tenant_id)
                                        <a href="https://playtomic.io/tenant/{{ $club->playtomic_tenant_id }}?date={{ $s['date'] }}"
                                           target="_blank" rel="noopener"
                                           class="text-xs text-blue-300 bg-blue-700 px-2 py-0.5 rounded hover:bg-blue-600 transition-colors">
                                            Book →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Calendar + Add Time --}}
            <div class="bg-blue-900 rounded-lg shadow p-5 sm:p-6" x-data="{ selectedDate: null }">
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
                        <div class="text-center text-xs font-semibold text-blue-400 uppercase">{{ $day }}</div>
                    @endforeach
                </div>

                {{-- Calendar grid --}}
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem;">
                    @for($i = 0; $i < $startPadding; $i++)
                        <div style="height: 3.5rem;"></div>
                    @endfor

                    @foreach($dates as $date)
                        @php
                            $isAvailable = in_array($date, $myDates);
                            $isPast = \Carbon\Carbon::parse($date)->lt($now);
                            $dayNum = date('j', strtotime($date));
                            $availableCount = ($allAvailability->get($date, collect()))->groupBy('user_id')->count();
                            $isToday = $date === $now->toDateString();
                        @endphp

                        @if($isPast)
                            <div class="flex flex-col items-center justify-center rounded-lg opacity-25" style="height: 3.5rem;">
                                <span class="text-sm text-blue-500">{{ $dayNum }}</span>
                            </div>
                        @else
                            <button @click="selectedDate = selectedDate === '{{ $date }}' ? null : '{{ $date }}'"
                                style="width: 100%; height: 3.5rem;"
                                class="flex flex-col items-center justify-center rounded-lg transition-all
                                       {{ $isAvailable
                                           ? 'bg-teal-600 text-white ring-2 ring-teal-400 shadow-md'
                                           : 'bg-blue-800/80 text-blue-200 ring-1 ring-blue-700 hover:ring-blue-500 active:bg-blue-700' }}
                                       {{ $isToday && !$isAvailable ? '!ring-2 !ring-amber-400' : '' }}
                                       {{ $isToday && $isAvailable ? '!ring-amber-400' : '' }}">
                                <span class="text-sm font-bold">{{ $dayNum }}</span>
                                @if($availableCount > 0)
                                    <span class="text-[10px] mt-0.5 {{ $isAvailable ? 'text-teal-200' : 'text-teal-400' }}">{{ $availableCount }} free</span>
                                @endif
                            </button>
                        @endif
                    @endforeach
                </div>

                {{-- Selected date panel --}}
                <template x-if="selectedDate">
                    <div class="mt-5 pt-5 border-t border-blue-800">
                        <h4 class="text-white font-medium mb-3" x-text="'Availability for ' + new Date(selectedDate + 'T12:00').toLocaleDateString('en-GB', {weekday:'long', day:'numeric', month:'short'})"></h4>

                        {{-- My existing slots for selected date --}}
                        @foreach($dates as $date)
                            <div x-show="selectedDate === '{{ $date }}'" x-cloak>
                                @php $mySlots = $myAvailability->get($date, collect()); @endphp
                                @if($mySlots->isNotEmpty())
                                    <p class="text-xs text-blue-400 mb-2">Your time slots:</p>
                                    <div class="space-y-2 mb-4">
                                        @foreach($mySlots as $slot)
                                            <div class="bg-teal-900/40 rounded px-3 py-2">
                                                <form method="POST" action="{{ route('club.schedule.update', $club) }}" class="flex flex-wrap gap-2 items-center">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="availability_id" value="{{ $slot->id }}">
                                                    @if($slot->start_time)
                                                        <input type="time" name="start_time" value="{{ substr($slot->start_time, 0, 5) }}"
                                                               class="text-sm bg-blue-800 border-blue-700 text-white rounded px-2 py-1 w-24 focus:ring-teal-500 focus:border-teal-500">
                                                        <span class="text-blue-400 text-xs">–</span>
                                                        <input type="time" name="end_time" value="{{ substr($slot->end_time, 0, 5) }}"
                                                               class="text-sm bg-blue-800 border-blue-700 text-white rounded px-2 py-1 w-24 focus:ring-teal-500 focus:border-teal-500">
                                                        <button type="submit" class="text-xs text-teal-400 hover:text-teal-300 font-medium">Save</button>
                                                    @else
                                                        <span class="text-sm text-teal-300 flex-1">All day</span>
                                                    @endif
                                                </form>
                                                <form method="POST" action="{{ route('club.schedule.destroy', $club) }}" class="inline mt-1">
                                                    @csrf @method('DELETE')
                                                    <input type="hidden" name="availability_id" value="{{ $slot->id }}">
                                                    <button class="text-xs text-red-400 hover:text-red-300">Remove</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Add another time slot --}}
                                <p class="text-xs text-blue-400 mb-2">{{ $mySlots->isNotEmpty() ? 'Add another time:' : 'Add a time slot:' }}</p>
                                <form method="POST" action="{{ route('club.schedule.store', $club) }}" class="flex flex-wrap gap-2 items-end">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <div class="flex-1 min-w-[90px]">
                                        <label class="block text-xs text-blue-400 mb-1">From</label>
                                        <input type="time" name="start_time" required
                                               class="w-full text-sm bg-blue-800 border-blue-700 text-white rounded-md px-2 py-1.5 focus:ring-teal-500 focus:border-teal-500">
                                    </div>
                                    <div class="flex-1 min-w-[90px]">
                                        <label class="block text-xs text-blue-400 mb-1">To</label>
                                        <input type="time" name="end_time" required
                                               class="w-full text-sm bg-blue-800 border-blue-700 text-white rounded-md px-2 py-1.5 focus:ring-teal-500 focus:border-teal-500">
                                    </div>
                                    <button type="submit" class="bg-teal-600 text-white px-3 py-1.5 rounded-md text-sm font-medium hover:bg-teal-700 transition-colors">Add</button>
                                </form>

                                {{-- Quick: mark all day --}}
                                @if($mySlots->isEmpty())
                                <div class="mt-2">
                                    <form method="POST" action="{{ route('club.schedule.toggle', $club) }}">
                                        @csrf
                                        <input type="hidden" name="date" value="{{ $date }}">
                                        <button type="submit" class="text-xs text-blue-400 hover:text-blue-200 underline">Or mark as free all day</button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </template>

                {{-- Legend --}}
                <div class="mt-5 pt-4 border-t border-blue-800 flex flex-wrap gap-x-5 gap-y-2 text-xs text-blue-400">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-teal-600 ring-2 ring-teal-400 inline-block"></span> You're free</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-800 ring-1 ring-blue-700 inline-block"></span> Tap to set times</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-800 ring-2 ring-amber-400 inline-block"></span> Today</span>
                </div>
            </div>

            {{-- end of content --}}

        </div>
    </div>
</x-app-layout>
