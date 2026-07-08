<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\DoublePair;
use App\Models\Season;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index(Club $club)
    {
        $seasons = $club->seasons()->orderBy('year', 'desc')->get();

        return view('admin.seasons.index', compact('club', 'seasons'));
    }

    public function create(Club $club)
    {
        return view('admin.seasons.create', compact('club'));
    }

    public function store(Request $request, Club $club)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $season = $club->seasons()->create($data);

        return redirect()->route('admin.seasons.show', [$club, $season])->with('success', 'Season created.');
    }

    public function show(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $pairs = DoublePair::where('season_id', $season->id)->with(['player1', 'player2'])->get();
        $players = $club->users()->orderBy('name')->get();
        $singlesMatches = $season->singlesMatches()->with(['player1', 'player2'])->latest('played_at')->get();
        $doublesMatches = $season->doublesMatches()->with(['pair1.player1', 'pair1.player2', 'pair2.player1', 'pair2.player2'])->latest('played_at')->get();

        return view('admin.seasons.show', compact('club', 'season', 'pairs', 'players', 'singlesMatches', 'doublesMatches'));
    }

    public function activate(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $club->seasons()->update(['active' => false]);
        $season->update(['active' => true]);

        return back()->with('success', 'Season activated.');
    }

    public function destroy(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $season->delete();

        return redirect()->route('admin.seasons.index', $club)->with('success', 'Season deleted.');
    }

    public function randomizePairs(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        DoublePair::where('season_id', $season->id)->delete();

        $players = $club->doublesPlayers()->get()->shuffle();

        foreach ($players->chunk(2) as $chunk) {
            if ($chunk->count() === 2) {
                DoublePair::create([
                    'season_id' => $season->id,
                    'player1_id' => $chunk->first()->id,
                    'player2_id' => $chunk->last()->id,
                ]);
            }
        }

        return back()->with('success', 'Pairs randomized from doubles players.');
    }

    public function editPair(Club $club, Season $season, DoublePair $pair)
    {
        abort_unless($season->club_id === $club->id, 404);

        $players = $club->users()->orderBy('name')->get();

        return view('admin.seasons.edit_pair', compact('club', 'season', 'pair', 'players'));
    }

    public function updatePair(Request $request, Club $club, Season $season, DoublePair $pair)
    {
        abort_unless($season->club_id === $club->id, 404);

        $data = $request->validate([
            'player1_id' => 'required|exists:users,id|different:player2_id',
            'player2_id' => 'required|exists:users,id',
        ]);

        $pair->update($data);

        return redirect()->route('admin.seasons.show', [$club, $season])->with('success', 'Pair updated.');
    }

    public function destroyPair(Club $club, Season $season, DoublePair $pair)
    {
        abort_unless($season->club_id === $club->id, 404);

        $pair->delete();

        return back()->with('success', 'Pair removed.');
    }

    public function storePair(Request $request, Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

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
