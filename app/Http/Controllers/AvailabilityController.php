<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Club;
use App\Services\PlaytomicService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AvailabilityController extends Controller
{
    /** Show scheduling page with month calendar. */
    public function index(Request $request, Club $club)
    {
        $season = $club->activeSeason();
        $user = auth()->user();

        // Determine which month to show (default: current)
        $now = Carbon::today();
        $requestedMonth = $request->query('month'); // format: Y-m
        if ($requestedMonth && preg_match('/^\d{4}-\d{2}$/', $requestedMonth)) {
            $monthStart = Carbon::createFromFormat('Y-m', $requestedMonth)->startOfMonth();
        } else {
            $monthStart = $now->copy()->startOfMonth();
        }

        // Can't go before current month
        $currentMonthStart = $now->copy()->startOfMonth();
        if ($monthStart->lt($currentMonthStart)) {
            $monthStart = $currentMonthStart;
        }

        // Can't go more than 12 months forward
        $maxMonth = $now->copy()->addMonths(11)->startOfMonth();
        if ($monthStart->gt($maxMonth)) {
            $monthStart = $maxMonth;
        }

        $monthEnd = $monthStart->copy()->endOfMonth();

        // Build calendar dates for the month
        $dates = collect();
        $day = $monthStart->copy();
        while ($day->lte($monthEnd)) {
            $dates->push($day->toDateString());
            $day->addDay();
        }

        // Pad start to align with day of week (Monday = 0)
        $startPadding = ($monthStart->dayOfWeekIso - 1);

        // Current user's availability for this month
        $myAvailability = Availability::where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->whereBetween('available_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->pluck('available_date')
            ->map(fn ($d) => $d->toDateString())
            ->toArray();

        // Everyone's availability for this month
        $allAvailability = Availability::where('club_id', $club->id)
            ->whereBetween('available_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with('user')
            ->get()
            ->groupBy(fn ($a) => $a->available_date->toDateString());

        // Suggested matches
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
                    $available = $allAvailability->get($date, collect());
                    if ($available->contains('user_id', $fixture['player1']->id) && $available->contains('user_id', $fixture['player2']->id)) {
                        $suggestions[] = [
                            'type' => 'singles',
                            'date' => $date,
                            'label' => $fixture['player1']->name.' vs '.$fixture['player2']->name,
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
                    $available = $allAvailability->get($date, collect());
                    $availableIds = $available->pluck('user_id')->toArray();
                    $needed = [
                        $fixture['pair1']->player1_id, $fixture['pair1']->player2_id,
                        $fixture['pair2']->player1_id, $fixture['pair2']->player2_id,
                    ];
                    if (count(array_intersect($needed, $availableIds)) === 4) {
                        $suggestions[] = [
                            'type' => 'doubles',
                            'date' => $date,
                            'label' => $fixture['pair1']->displayName().' vs '.$fixture['pair2']->displayName(),
                        ];
                    }
                }
            }
        }

        // Nav: prev/next month
        $prevMonth = $monthStart->copy()->subMonth();
        $nextMonth = $monthStart->copy()->addMonth();
        $canGoPrev = $prevMonth->gte($currentMonthStart);
        $canGoNext = $nextMonth->lte($maxMonth);

        return view('league.schedule', compact(
            'club', 'season', 'dates', 'startPadding', 'myAvailability',
            'allAvailability', 'suggestions', 'monthStart', 'now',
            'prevMonth', 'nextMonth', 'canGoPrev', 'canGoNext'
        ));
    }

    /** Toggle a date on/off. */
    public function toggle(Request $request, Club $club)
    {
        $data = $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $existing = Availability::where('club_id', $club->id)
            ->where('user_id', auth()->id())
            ->where('available_date', $data['date'])
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            Availability::create([
                'club_id' => $club->id,
                'user_id' => auth()->id(),
                'available_date' => $data['date'],
            ]);
        }

        return back();
    }

    /** Fetch Playtomic court availability for a date. */
    public function courts(Request $request, Club $club)
    {
        $date = $request->query('date', now()->toDateString());

        if (! $club->playtomic_tenant_id) {
            return response()->json(['error' => 'Playtomic not configured for this club.', 'slots' => []]);
        }

        $slots = PlaytomicService::getSlots($club->playtomic_tenant_id, $date);

        return response()->json(['date' => $date, 'slots' => $slots]);
    }
}
