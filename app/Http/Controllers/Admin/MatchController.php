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
            'sets_input' => 'required|string|max:200',
            'played_at'  => 'nullable|date',
        ]);

        $sets = SinglesMatch::parseSetsString($data['sets_input']);
        if (empty($sets)) {
            return back()->withInput()->withErrors(['sets_input' => 'Enter sets like: 6-4, 3-6, 7-5']);
        }

        [$s1, $s2] = SinglesMatch::computeSetScores($sets);

        SinglesMatch::create([
            'season_id'  => $season->id,
            'player1_id' => $data['player1_id'],
            'player2_id' => $data['player2_id'],
            'sets'       => $sets,
            'score1'     => $s1,
            'score2'     => $s2,
            'played_at'  => $data['played_at'] ?? null,
        ]);

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
            'sets_input' => 'required|string|max:200',
            'played_at'  => 'nullable|date',
        ]);

        $sets = SinglesMatch::parseSetsString($data['sets_input']);
        if (empty($sets)) {
            return back()->withInput()->withErrors(['sets_input' => 'Enter sets like: 6-4, 3-6, 7-5']);
        }

        [$s1, $s2] = SinglesMatch::computeSetScores($sets);

        $match->update([
            'player1_id' => $data['player1_id'],
            'player2_id' => $data['player2_id'],
            'sets'       => $sets,
            'score1'     => $s1,
            'score2'     => $s2,
            'played_at'  => $data['played_at'] ?? null,
        ]);

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
            'pair1_id'   => 'required|exists:double_pairs,id|different:pair2_id',
            'pair2_id'   => 'required|exists:double_pairs,id',
            'sets_input' => 'required|string|max:200',
            'played_at'  => 'nullable|date',
        ]);

        $sets = SinglesMatch::parseSetsString($data['sets_input']);
        if (empty($sets)) {
            return back()->withInput()->withErrors(['sets_input' => 'Enter sets like: 6-4, 3-6, 7-5']);
        }

        [$s1, $s2] = SinglesMatch::computeSetScores($sets);

        DoublesMatch::create([
            'season_id' => $season->id,
            'pair1_id'  => $data['pair1_id'],
            'pair2_id'  => $data['pair2_id'],
            'sets'      => $sets,
            'score1'    => $s1,
            'score2'    => $s2,
            'played_at' => $data['played_at'] ?? null,
        ]);

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
            'pair1_id'   => 'required|exists:double_pairs,id|different:pair2_id',
            'pair2_id'   => 'required|exists:double_pairs,id',
            'sets_input' => 'required|string|max:200',
            'played_at'  => 'nullable|date',
        ]);

        $sets = SinglesMatch::parseSetsString($data['sets_input']);
        if (empty($sets)) {
            return back()->withInput()->withErrors(['sets_input' => 'Enter sets like: 6-4, 3-6, 7-5']);
        }

        [$s1, $s2] = SinglesMatch::computeSetScores($sets);

        $match->update([
            'pair1_id'  => $data['pair1_id'],
            'pair2_id'  => $data['pair2_id'],
            'sets'      => $sets,
            'score1'    => $s1,
            'score2'    => $s2,
            'played_at' => $data['played_at'] ?? null,
        ]);

        return redirect()->route('admin.seasons.show', $season)->with('success', 'Doubles match updated.');
    }

    public function destroyDoubles(Season $season, DoublesMatch $match)
    {
        $match->delete();
        return redirect()->route('admin.seasons.show', $season)->with('success', 'Doubles match deleted.');
    }
}
