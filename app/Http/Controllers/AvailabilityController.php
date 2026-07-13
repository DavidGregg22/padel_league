<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Club;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /** Show scheduling page: my availability + suggested matches. */
    public function index(Club $club)
    {
        $season = $club->activeSeason();
        $user = auth()->user();

        // Next 14 days
        $dates = collect();
        for ($i = 0; $i < 14; $i++) {
            $dates->push(now()->addDays($i)->toDateString());
        }

        // Current user's availability
        $myAvailability = Availability::where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->whereBetween('available_date', [now()->toDateString(), now()->addDays(13)->toDateString()])
            ->pluck('available_date')
            ->map(fn ($d) => $d->toDateString())
            ->toArray();

        // Everyone's availability for the next 14 days
        $allAvailability = Availability::where('club_id', $club->id)
            ->whereBetween('available_date', [now()->toDateString(), now()->addDays(13)->toDateString()])
            ->with('user')
            ->get()
            ->groupBy(fn ($a) => $a->available_date->toDateString());

        // Find suggested matches (pending fixtures where both sides are available on same date)
        $suggestions = [];

        if ($season) {
            // Singles
            $singlesFixtures = $season->singlesFixtures();
            foreach ($singlesFixtures as $fixture) {
                if ($fixture['played']) {
                    continue;
                }
                foreach ($dates as $date) {
                    $available = $allAvailability->get($date, collect());
                    $p1Free = $available->contains('user_id', $fixture['player1']->id);
                    $p2Free = $available->contains('user_id', $fixture['player2']->id);
                    if ($p1Free && $p2Free) {
                        $suggestions[] = [
                            'type' => 'singles',
                            'date' => $date,
                            'label' => $fixture['player1']->name.' vs '.$fixture['player2']->name,
                        ];
                    }
                }
            }

            // Doubles
            $doublesFixtures = $season->doublesFixtures();
            foreach ($doublesFixtures as $fixture) {
                if ($fixture['played']) {
                    continue;
                }
                foreach ($dates as $date) {
                    $available = $allAvailability->get($date, collect());
                    $availableIds = $available->pluck('user_id')->toArray();
                    // All 4 players in both pairs must be free
                    $needed = [
                        $fixture['pair1']->player1_id,
                        $fixture['pair1']->player2_id,
                        $fixture['pair2']->player1_id,
                        $fixture['pair2']->player2_id,
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

        return view('league.schedule', compact('club', 'season', 'dates', 'myAvailability', 'allAvailability', 'suggestions'));
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
            $status = 'removed';
        } else {
            Availability::create([
                'club_id' => $club->id,
                'user_id' => auth()->id(),
                'available_date' => $data['date'],
            ]);
            $status = 'added';
        }

        return back()->with('success', "Availability {$status} for ".date('D j M', strtotime($data['date'])));
    }
}
