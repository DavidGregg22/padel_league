<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\DoublePair;
use App\Models\User;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Season::orderBy('year', 'desc')->get();
        return view('admin.seasons.index', compact('seasons'));
    }

    public function create()
    {
        return view('admin.seasons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $season = Season::create($data);
        return redirect()->route('admin.seasons.show', $season)->with('success', 'Season created.');
    }

    public function show(Season $season)
    {
        $pairs = DoublePair::where('season_id', $season->id)->with(['player1', 'player2'])->get();
        $players = User::where('is_admin', false)->get();
        $singlesMatches = $season->singlesMatches()->with(['player1', 'player2'])->latest('played_at')->get();
        $doublesMatches = $season->doublesMatches()->with(['pair1.player1', 'pair1.player2', 'pair2.player1', 'pair2.player2'])->latest('played_at')->get();
        return view('admin.seasons.show', compact('season', 'pairs', 'players', 'singlesMatches', 'doublesMatches'));
    }

    public function activate(Season $season)
    {
        Season::query()->update(['active' => false]);
        $season->update(['active' => true]);
        return back()->with('success', 'Season activated.');
    }

    public function destroy(Season $season)
    {
        $season->delete();
        return redirect()->route('admin.seasons.index')->with('success', 'Season deleted.');
    }

    public function randomizePairs(Season $season)    {
        // Remove existing pairs for this season
        DoublePair::where('season_id', $season->id)->delete();

        $players = User::where('is_admin', false)->get()->shuffle();

        // Pair them up (if odd number, last player sits out)
        $pairs = $players->chunk(2);

        foreach ($pairs as $pair) {
            if ($pair->count() === 2) {
                DoublePair::create([
                    'season_id' => $season->id,
                    'player1_id' => $pair->first()->id,
                    'player2_id' => $pair->last()->id,
                ]);
            }
        }

        return back()->with('success', 'Pairs randomized for this season.');
    }
}
