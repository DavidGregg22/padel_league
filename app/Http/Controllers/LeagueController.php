<?php

namespace App\Http\Controllers;

use App\Models\Season;

class LeagueController extends Controller
{
    public function index()
    {
        $season = Season::where('active', true)->first();
        $singlesStandings = $season ? $season->singlesStandings() : [];
        $doublesStandings = $season ? $season->doublesStandings() : [];
        $seasons = Season::orderBy('year', 'desc')->get();

        return view('league.index', compact('season', 'singlesStandings', 'doublesStandings', 'seasons'));
    }

    public function season(Season $season)
    {
        $singlesStandings = $season->singlesStandings();
        $doublesStandings = $season->doublesStandings();
        $singlesMatches = $season->singlesMatches()->with(['player1', 'player2'])->latest('played_at')->get();
        $doublesMatches = $season->doublesMatches()->with(['pair1.player1', 'pair1.player2', 'pair2.player1', 'pair2.player2'])->latest('played_at')->get();

        return view('league.season', compact('season', 'singlesStandings', 'doublesStandings', 'singlesMatches', 'doublesMatches'));
    }
}
