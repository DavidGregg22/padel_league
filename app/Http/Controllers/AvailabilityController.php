<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Club;
use App\Services\PlaytomicService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AvailabilityController extends Controller
{
    public function index(Request $request, Club $club)
    {
        $season = $club->activeSeason();
        $user = auth()->user();

        $now = Carbon::today();
        $requestedMonth = $request->query('month');
        if ($requestedMonth && preg_match('/^\d{4}-\d{2}$/', $requestedMonth)) {
            $monthStart = Carbon::createFromFormat('Y-m', $requestedMonth)->startOfMonth();
        } else {
            $monthStart = $now->copy()->startOfMonth();
        }

        $currentMonthStart = $now->copy()->startOfMonth();
        if ($monthStart->lt($currentMonthStart)) {
            $monthStart = $currentMonthStart;
        }

        $maxMonth = $now->copy()->addMonths(11)->startOfMonth();
        if ($monthStart->gt($maxMonth)) {
            $monthStart = $maxMonth;
        }

        $monthEnd = $monthStart->copy()->endOfMonth();

        $dates = collect();
        $day = $monthStart->copy();
        while ($day->lte($monthEnd)) {
            $dates->push($day->toDateString());
            $day->addDay();
        }

        $startPadding = ($monthStart->dayOfWeekIso - 1);

        // My availability (with times)
        $myAvailability = Availability::where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->whereBetween('available_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->groupBy(fn ($a) => $a->available_date->toDateString());

        // My dates (for calendar highlighting)
        $myDates = $myAvailability->keys()->toArray();

        // Everyone's availability
        $allAvailability = Availability::where('club_id', $club->id)
            ->whereBetween('available_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with('user')
            ->get()
            ->groupBy(fn ($a) => $a->available_date->toDateString());

        // Suggestions
        $suggestions = [];
        if ($season) {
            $singlesFixtures = $season->singlesFixtures();
            foreach ($singlesFixtures as $fixture) {
                if ($fixture['played']) {
                    continue;
                }
                foreach ($dates as $date) {
                    if (Carbon::parse($date)->lt($now)) {
                        continue;
                    }
                    $daySlots = $allAvailability->get($date, collect());
                    $p1Slots = $daySlots->where('user_id', $fixture['player1']->id);
                    $p2Slots = $daySlots->where('user_id', $fixture['player2']->id);
                    if ($p1Slots->isNotEmpty() && $p2Slots->isNotEmpty()) {
                        // Find overlapping times
                        $overlap = $this->findOverlap($p1Slots, $p2Slots);
                        $suggestions[] = [
                            'type' => 'singles',
                            'date' => $date,
                            'label' => $fixture['player1']->name.' vs '.$fixture['player2']->name,
                            'times' => $overlap,
                        ];
                    }
                }
            }

            $doublesFixtures = $season->doublesFixtures();
            foreach ($doublesFixtures as $fixture) {
                if ($fixture['played']) {
                    continue;
                }
                foreach ($dates as $date) {
                    if (Carbon::parse($date)->lt($now)) {
                        continue;
                    }
                    $daySlots = $allAvailability->get($date, collect());
                    $needed = [
                        $fixture['pair1']->player1_id, $fixture['pair1']->player2_id,
                        $fixture['pair2']->player1_id, $fixture['pair2']->player2_id,
                    ];
                    $allFree = true;
                    $playerSlots = [];
                    foreach ($needed as $pid) {
                        $slots = $daySlots->where('user_id', $pid);
                        if ($slots->isEmpty()) {
                            $allFree = false;
                            break;
                        }
                        $playerSlots[] = $slots;
                    }
                    if ($allFree) {
                        $overlap = $this->findMultiOverlap($playerSlots);
                        $suggestions[] = [
                            'type' => 'doubles',
                            'date' => $date,
                            'label' => $fixture['pair1']->displayName().' vs '.$fixture['pair2']->displayName(),
                            'times' => $overlap,
                        ];
                    }
                }
            }
        }

        $prevMonth = $monthStart->copy()->subMonth();
        $nextMonth = $monthStart->copy()->addMonth();
        $canGoPrev = $prevMonth->gte($currentMonthStart);
        $canGoNext = $nextMonth->lte($maxMonth);

        return view('league.schedule', compact(
            'club', 'season', 'dates', 'startPadding', 'myAvailability', 'myDates',
            'allAvailability', 'suggestions', 'monthStart', 'now',
            'prevMonth', 'nextMonth', 'canGoPrev', 'canGoNext'
        ));
    }

    /** Add a time slot for a date. */
    public function store(Request $request, Club $club)
    {
        $data = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        Availability::firstOrCreate([
            'club_id' => $club->id,
            'user_id' => auth()->id(),
            'available_date' => $data['date'],
            'start_time' => $data['start_time'],
        ], [
            'end_time' => $data['end_time'],
        ]);

        return back();
    }

    /** Remove a specific availability slot. */
    public function destroy(Request $request, Club $club)
    {
        $data = $request->validate([
            'availability_id' => 'required|exists:availabilities,id',
        ]);

        Availability::where('id', $data['availability_id'])
            ->where('user_id', auth()->id())
            ->where('club_id', $club->id)
            ->delete();

        return back();
    }

    /** Update an existing time slot. */
    public function update(Request $request, Club $club)
    {
        $data = $request->validate([
            'availability_id' => 'required|exists:availabilities,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        Availability::where('id', $data['availability_id'])
            ->where('user_id', auth()->id())
            ->where('club_id', $club->id)
            ->update([
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
            ]);

        return back();
    }

    /** Old toggle method — now redirects to date view. */
    public function toggle(Request $request, Club $club)
    {
        $data = $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        // If they have slots for this date, remove all. Otherwise redirect to add.
        $existing = Availability::where('club_id', $club->id)
            ->where('user_id', auth()->id())
            ->where('available_date', $data['date'])
            ->get();

        if ($existing->isNotEmpty()) {
            Availability::where('club_id', $club->id)
                ->where('user_id', auth()->id())
                ->where('available_date', $data['date'])
                ->delete();
        } else {
            // Default: mark as "all day" (no specific time)
            Availability::create([
                'club_id' => $club->id,
                'user_id' => auth()->id(),
                'available_date' => $data['date'],
                'start_time' => null,
                'end_time' => null,
            ]);
        }

        return back();
    }

    /** Fetch Playtomic court availability. */
    public function courts(Request $request, Club $club)
    {
        $date = $request->query('date', now()->toDateString());

        if (! $club->playtomic_tenant_id) {
            return response()->json(['error' => 'Playtomic not configured.', 'slots' => []]);
        }

        $slots = PlaytomicService::getSlots($club->playtomic_tenant_id, $date);

        return response()->json(['date' => $date, 'slots' => $slots]);
    }

    /** Find overlapping time windows between two players' slots. */
    private function findOverlap($slotsA, $slotsB): string
    {
        // If either has an "all day" slot (null times), they overlap
        if ($slotsA->whereNull('start_time')->isNotEmpty() || $slotsB->whereNull('start_time')->isNotEmpty()) {
            $specific = $slotsA->whereNotNull('start_time')->merge($slotsB->whereNotNull('start_time'));
            if ($specific->isEmpty()) {
                return 'All day';
            }

            return $specific->map(fn ($s) => substr($s->start_time, 0, 5).'–'.substr($s->end_time, 0, 5))->join(', ');
        }

        // Find actual overlapping ranges
        $overlaps = [];
        foreach ($slotsA as $a) {
            foreach ($slotsB as $b) {
                $start = max($a->start_time, $b->start_time);
                $end = min($a->end_time, $b->end_time);
                if ($start < $end) {
                    $overlaps[] = substr($start, 0, 5).'–'.substr($end, 0, 5);
                }
            }
        }

        return $overlaps ? implode(', ', array_unique($overlaps)) : 'Check times';
    }

    private function findMultiOverlap(array $playerSlots): string
    {
        // If any player has "all day", treat as available all times
        foreach ($playerSlots as $slots) {
            if ($slots->whereNull('start_time')->isEmpty() && $slots->whereNotNull('start_time')->isEmpty()) {
                return '';
            }
        }

        // Simple: if all have at least one slot, show "Available"
        return 'Available';
    }
}
