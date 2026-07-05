<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\SinglesMatch;
use App\Models\DoublesMatch;
use App\Models\DoublePair;
use App\Models\User;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    // ── Singles ──────────────────────────────────────────────

    public function createSingles(Season $season)
    {
        $players = User::where('is_admin', false)->orderBy('name')->get();
        return view('admin.matches.create_singles', compact('season', 'players'));
    }

    public function storeSingles(Request $request, Season $season)
    {
        $data = $request->validate([
            'player1_id' => 'required|exists:users,id|different:player2_id',
            'player2_id' => 'required|exists:users,id',
            'score1'     => 'required|integer|min:0|max:9',
            'score2'     => 'required|integer|min:0|max:9',
            'played_at'  => 'nullable|date',
        ]);

        SinglesMatch::create(array_merge($data, ['season_id' => $season->id]));
        return redirect()->route('admin.seasons.show', $season)->with('success', 'Singles match added.');
    }

    public function editSingles(Season $season, SinglesMatch $match)
    {
        $players = User::where('is_admin', false)->orderBy('name')->get();
        return view('admin.matches.edit_singles', compact('season', 'match', 'players'));
    }

    public function updateSingles(Request $request, Season $season, SinglesMatch $match)
    {
        $data = $request->validate([
            'player1_id' => 'required|exists:users,id|different:player2_id',
            'player2_id' => 'required|exists:users,id',
            'score1'     => 'required|integer|min:0|max:9',
            'score2'     => 'required|integer|min:0|max:9',
            'played_at'  => 'nullable|date',
        ]);

        $match->update($data);
        return redirect()->route('admin.seasons.show', $season)->with('success', 'Singles match updated.');
    }

    public function destroySingles(Season $season, SinglesMatch $match)
    {
        $match->delete();
        return redirect()->route('admin.seasons.show', $season)->with('success', 'Singles match deleted.');
    }

    // ── Doubles ──────────────────────────────────────────────

    public function createDoubles(Season $season)
    {
        $pairs = DoublePair::where('season_id', $season->id)->with(['player1', 'player2'])->get();
        return view('admin.matches.create_doubles', compact('season', 'pairs'));
    }

    public function storeDoubles(Request $request, Season $season)
    {
        $data = $request->validate([
            'pair1_id'  => 'required|exists:double_pairs,id|different:pair2_id',
            'pair2_id'  => 'required|exists:double_pairs,id',
            'score1'    => 'required|integer|min:0|max:9',
            'score2'    => 'required|integer|min:0|max:9',
            'played_at' => 'nullable|date',
        ]);

        DoublesMatch::create(array_merge($data, ['season_id' => $season->id]));
        return redirect()->route('admin.seasons.show', $season)->with('success', 'Doubles match added.');
    }

    public function editDoubles(Season $season, DoublesMatch $match)
    {
        $pairs = DoublePair::where('season_id', $season->id)->with(['player1', 'player2'])->get();
        return view('admin.matches.edit_doubles', compact('season', 'match', 'pairs'));
    }

    public function updateDoubles(Request $request, Season $season, DoublesMatch $match)
    {
        $data = $request->validate([
            'pair1_id'  => 'required|exists:double_pairs,id|different:pair2_id',
            'pair2_id'  => 'required|exists:double_pairs,id',
            'score1'    => 'required|integer|min:0|max:9',
            'score2'    => 'required|integer|min:0|max:9',
            'played_at' => 'nullable|date',
        ]);

        $match->update($data);
        return redirect()->route('admin.seasons.show', $season)->with('success', 'Doubles match updated.');
    }

    public function destroyDoubles(Season $season, DoublesMatch $match)
    {
        $match->delete();
        return redirect()->route('admin.seasons.show', $season)->with('success', 'Doubles match deleted.');
    }
}
