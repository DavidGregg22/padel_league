<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-bold text-lg text-blue-100">📅 Schedule — {{ $club->name }}</h2>
            <a href="{{ route('club.league', $club) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium">← Back</a>
        </div>
    </x-slot>

    <div class="py-6" x-data="schedule()">
        <div class="max-w-4xl mx-auto px-4 space-y-8">

            {{-- Month Calendar --}}
            <div class="bg-blue-900 rounded-lg shadow p-5">
                {{-- Month nav --}}
                <div class="flex items-center justify-between mb-4">
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
                        <div style="height: 3rem;"></div>
                    @endfor

                    @foreach($dates as $date)
                        @php
                            $isPast = \Carbon\Carbon::parse($date)->lt($now);
                            $dayNum = date('j', strtotime($date));
                            $isToday = $date === $now->toDateString();
                            $hasAvail = in_array($date, $myDates);
                        @endphp

                        @if($isPast)
                            <div class="flex items-center justify-center rounded-lg opacity-25" style="height: 3rem;">
                                <span class="text-sm text-blue-500">{{ $dayNum }}</span>
                            </div>
                        @else
                            <button @click="selectDate('{{ $date }}')" style="height: 3rem;"
                                :class="selectedDate === '{{ $date }}' ? 'bg-slate-800 text-white ring-2 ring-teal-400' : '{{ $hasAvail ? "bg-teal-700 text-white ring-1 ring-teal-500" : "bg-blue-800 text-blue-200 hover:bg-blue-700" }}'"
                                class="w-full flex items-center justify-center rounded-lg transition-all text-sm font-bold {{ $isToday ? '!ring-2 !ring-amber-400' : '' }}">
                                {{ $dayNum }}
                            </button>
                        @endif
                    @endforeach
                </div>

                {{-- Legend --}}
                <div class="mt-4 pt-3 border-t border-blue-800 flex flex-wrap gap-x-5 gap-y-1 text-xs text-blue-400">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-slate-800 ring-2 ring-teal-400 inline-block"></span> Selected</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-teal-700 ring-1 ring-teal-500 inline-block"></span> You're free</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-800 ring-2 ring-amber-400 inline-block"></span> Today</span>
                </div>
            </div>

            {{-- Time slots grid (5 columns) --}}
            <div class="bg-blue-900 rounded-lg shadow p-5" id="times">
                <h3 class="font-bold text-blue-100 mb-4">
                    Times for <span x-text="formatDate(selectedDate)"></span>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem;">
                    @php
                        $timeSlots = [];
                        for ($h = 7; $h <= 22; $h++) {
                            $timeSlots[] = sprintf('%02d:00', $h);
                            $timeSlots[] = sprintf('%02d:30', $h);
                        }
                    @endphp

                    @foreach($timeSlots as $time)
                        @php $timeFormatted = date('g:i a', strtotime($time)); @endphp
                        <button @click="toggleSlot('{{ $time }}')"
                                :class="isMySlot('{{ $time }}') ? 'bg-slate-800 text-white ring-2 ring-teal-400' : (getSlotCount('{{ $time }}') > 0 ? 'bg-blue-800 text-white' : 'bg-blue-900/50 text-blue-400 border border-blue-700')"
                                class="relative rounded-xl px-1 py-3 text-center transition-all hover:ring-1 hover:ring-blue-500 min-h-[70px] flex flex-col items-center justify-center">
                            <div x-show="getSlotCount('{{ $time }}') > 0" class="flex flex-wrap justify-center gap-0.5 mb-1">
                                <template x-for="name in getSlotNames('{{ $time }}')" :key="name">
                                    <span class="w-5 h-5 rounded-full bg-teal-700 text-[9px] font-bold text-teal-200 flex items-center justify-center ring-1 ring-teal-500"
                                          x-text="getInitials(name)"></span>
                                </template>
                            </div>
                            <span class="text-xs sm:text-sm font-bold">{{ $timeFormatted }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Who's free on selected date --}}
            <div class="bg-blue-900 rounded-lg shadow p-5" x-show="Object.keys(playersForDate).length > 0" x-cloak>
                <h3 class="font-bold text-blue-100 mb-3">
                    Who's free on <span x-text="formatDate(selectedDate)"></span>
                </h3>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    <template x-for="(times, name) in playersForDate" :key="name">
                        <div class="flex items-center justify-between bg-blue-800/50 rounded-lg px-4 py-2.5">
                            <span class="text-sm text-white font-medium" x-text="name"></span>
                            <span class="text-xs text-teal-400" x-text="times"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Suggested Matches --}}
            @if(count($suggestions) > 0)
            <div class="bg-blue-900 rounded-lg shadow p-5">
                <h3 class="font-bold text-blue-100 mb-1">🎯 Ready to Play</h3>
                <p class="text-blue-400 text-xs mb-3">All players free on the same day.</p>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($suggestions as $s)
                        <div class="bg-blue-800/50 rounded-lg px-4 py-3">
                            <div class="text-sm text-white font-medium">{{ $s['label'] }}</div>
                            <div class="flex items-center justify-between mt-1.5">
                                <div>
                                    <span class="text-xs {{ $s['type'] === 'singles' ? 'text-teal-400' : 'text-amber-400' }}">{{ ucfirst($s['type']) }}</span>
                                    @if($s['times'])<span class="text-xs text-blue-300 ml-1">· {{ $s['times'] }}</span>@endif
                                </div>
                                <span class="text-xs text-emerald-400 bg-emerald-900/50 px-2 py-0.5 rounded">{{ date('D j M', strtotime($s['date'])) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    <script>
    function schedule() {
        return {
            selectedDate: '{{ $selectedDay }}',
            availability: {!! json_encode($availabilityJson) !!},
            myAvailability: {!! json_encode($myAvailabilityJson) !!},

            selectDate(date) {
                this.selectedDate = date;
            },

            formatDate(date) {
                if (!date) return '';
                return new Date(date + 'T12:00').toLocaleDateString('en-GB', {weekday:'long', day:'numeric', month:'short'});
            },

            getSlotCount(time) {
                return this.getSlotNames(time).length;
            },

            getSlotNames(time) {
                let dateSlots = this.availability[this.selectedDate] || [];
                let names = [];
                dateSlots.forEach(player => {
                    let isAvail = player.slots.some(s => {
                        if (!s.start) return true;
                        return time >= s.start && time < s.end;
                    });
                    if (isAvail) names.push(player.name);
                });
                return names;
            },

            getInitials(name) {
                return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
            },

            isMySlot(time) {
                let mySlots = this.myAvailability[this.selectedDate] || [];
                return mySlots.some(s => {
                    if (!s.start) return true;
                    return time >= s.start && time < s.end;
                });
            },

            toggleSlot(time) {
                let endTime = this.addMinutes(time, 30);
                if (this.isMySlot(time)) {
                    let mySlots = this.myAvailability[this.selectedDate] || [];
                    let slot = mySlots.find(s => {
                        if (!s.start) return true;
                        return time >= s.start && time < s.end;
                    });
                    if (slot) this.removeSlot(slot.id);
                } else {
                    this.addSlot(time, endTime);
                }
            },

            addMinutes(time, mins) {
                let [h, m] = time.split(':').map(Number);
                m += mins;
                if (m >= 60) { h++; m -= 60; }
                return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
            },

            addSlot(start, end) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("club.schedule.store", $club) }}';
                form.innerHTML = '@csrf<input name="date" value="' + this.selectedDate + '"><input name="start_time" value="' + start + '"><input name="end_time" value="' + end + '">';
                document.body.appendChild(form);
                form.submit();
            },

            removeSlot(id) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("club.schedule.destroy", $club) }}';
                form.innerHTML = '@csrf @method("DELETE")<input name="availability_id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            },

            get playersForDate() {
                let dateSlots = this.availability[this.selectedDate] || [];
                let result = {};
                dateSlots.forEach(player => {
                    let times = player.slots.map(s => s.start ? s.start + '–' + s.end : 'All day').join(', ');
                    result[player.name] = times;
                });
                return result;
            }
        }
    }
    </script>
</x-app-layout>
