<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoublePair;
use App\Models\Season;
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

    public function randomizePairs(Season $season)
    {
        DoublePair::where('season_id', $season->id)->delete();

        $players = User::where('is_admin', false)->get()->shuffle();

        foreach ($players->chunk(2) as $pair) {
            if ($pair->count() === 2) {
                DoublePair::create([
                    'season_id' => $season->id,
                    'player1_id' => $pair->first()->id,
                    'player2_id' => $pair->last()->id,
                ]);
            }
        }

        return back()->with('success', 'Pairs randomized.');
    }

    public function editPair(Season $season, DoublePair $pair)
    {
        $players = User::where('is_admin', false)->get();

        return view('admin.seasons.edit_pair', compact('season', 'pair', 'players'));
    }

    public function updatePair(Request $request, Season $season, DoublePair $pair)
    {
        $data = $request->validate([
            'player1_id' => 'required|exists:users,id|different:player2_id',
            'player2_id' => 'required|exists:users,id',
        ]);

        $pair->update($data);

        return redirect()->route('admin.seasons.show', $season)->with('success', 'Pair updated.');
    }

    public function destroyPair(Season $season, DoublePair $pair)
    {
        $pair->delete();

        return back()->with('success', 'Pair removed.');
    }

    public function storePair(Request $request, Season $season)
    {
        $data = $request->validate([
            'player1_id' => 'required|exists:users,id|different:player2_id',
            'player2_id' => 'required|exists:users,id',
        ]);

        DoublePair::create([
            'season_id' => $season->id,
            'player1_id' => $data['player1_id'],
            'player2_id' => $data['player2_id'],
        ]);

        return back()->with('success', 'Pair added.');
    }
}
