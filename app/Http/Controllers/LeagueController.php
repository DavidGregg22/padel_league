<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\DoublePair;
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

    /** Overview page — quick standings preview for both. */
    public function index(Club $club)
    {
        $season = $club->activeSeason();
        $seasons = $club->seasons()->orderBy('year', 'desc')->get();
        $singlesStandings = $season ? $season->singlesStandings() : [];
        $doublesStandings = $season ? $season->doublesStandings() : [];

        return view('league.index', compact('club', 'season', 'seasons', 'singlesStandings', 'doublesStandings'));
    }

    /** Dedicated singles page — standings, fixtures, results. */
    public function singles(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $standings = $season->singlesStandings();
        $fixtures = $season->singlesFixtures();
        $fixturesPlayed = count(array_filter($fixtures, fn ($f) => $f['played']));
        $matches = $season->singlesMatches()->with(['player1', 'player2'])->latest('played_at')->get();

        return view('league.singles', compact('club', 'season', 'standings', 'fixtures', 'fixturesPlayed', 'matches'));
    }

    /** Dedicated doubles page — standings, pairs, fixtures, results. */
    public function doubles(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $standings = $season->doublesStandings();
        $pairs = DoublePair::where('season_id', $season->id)->with(['player1', 'player2'])->get();
        $fixtures = $season->doublesFixtures();
        $fixturesPlayed = count(array_filter($fixtures, fn ($f) => $f['played']));
        $matches = $season->doublesMatches()->with(['pair1.player1', 'pair1.player2', 'pair2.player1', 'pair2.player2'])->latest('played_at')->get();

        return view('league.doubles', compact('club', 'season', 'standings', 'pairs', 'fixtures', 'fixturesPlayed', 'matches'));
    }

    /** Combined fixtures page (kept for backward compat). */
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
