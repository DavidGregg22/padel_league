<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Season;

class LeagueController extends Controller
{
    /** Landing — show the user's clubs, or redirect if only one. */
    public function home()
    {
        $clubs = auth()->user()->clubs()->orderBy('name')->get();

        if ($clubs->count() === 1) {
            return redirect()->route('club.league', $clubs->first());
        }

        return view('league.home', compact('clubs'));
    }

    /** Main standings page for a club. */
    public function index(Club $club)
    {
        $season = $club->activeSeason();
        $seasons = $club->seasons()->orderBy('year', 'desc')->get();
        $singlesStandings = $season ? $season->singlesStandings() : [];
        $doublesStandings = $season ? $season->doublesStandings() : [];

        return view('league.index', compact('club', 'season', 'seasons', 'singlesStandings', 'doublesStandings'));
    }

    /** Full season detail for a club. */
    public function season(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $singlesStandings = $season->singlesStandings();
        $doublesStandings = $season->doublesStandings();
        $singlesMatches = $season->singlesMatches()->with(['player1', 'player2'])->latest('played_at')->get();
        $doublesMatches = $season->doublesMatches()->with(['pair1.player1', 'pair1.player2', 'pair2.player1', 'pair2.player2'])->latest('played_at')->get();

        return view('league.season', compact('club', 'season', 'singlesStandings', 'doublesStandings', 'singlesMatches', 'doublesMatches'));
    }

    /** Order of play — round-robin fixtures. */
    public function fixtures(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $singlesFixtures = $season->singlesFixtures();
        $doublesFixtures = $season->doublesFixtures();

        $singlesPlayed = count(array_filter($singlesFixtures, fn ($f) => $f['played']));
        $doublesPlayed = count(array_filter($doublesFixtures, fn ($f) => $f['played']));

        return view('league.fixtures', compact('club', 'season', 'singlesFixtures', 'doublesFixtures', 'singlesPlayed', 'doublesPlayed'));
    }
}
