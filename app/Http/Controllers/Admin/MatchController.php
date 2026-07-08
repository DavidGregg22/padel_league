<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\DoublePair;
use App\Models\DoublesMatch;
use App\Models\Season;
use App\Models\SinglesMatch;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    // ── Singles ──────────────────────────────────────────────

    public function createSingles(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $players = $club->users()->orderBy('name')->get();

        return view('admin.matches.create_singles', compact('club', 'season', 'players'));
    }

    public function storeSingles(Request $request, Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $data = $request->validate([
            'player1_id' => 'required|exists:users,id|different:player2_id',
            'player2_id' => 'required|exists:users,id',
            'sets_input' => 'required|string|max:200',
            'played_at' => 'nullable|date',
        ]);

        $sets = SinglesMatch::parseSetsString($data['sets_input']);
        $error = SinglesMatch::validateSets($sets);
        if ($error) {
            return back()->withInput()->withErrors(['sets_input' => $error]);
        }

        [$s1, $s2] = SinglesMatch::computeSetScores($sets);

        SinglesMatch::create([
            'season_id' => $season->id,
            'player1_id' => $data['player1_id'],
            'player2_id' => $data['player2_id'],
            'sets' => $sets,
            'score1' => $s1,
            'score2' => $s2,
            'played_at' => $data['played_at'] ?? null,
        ]);

        return redirect()->route('admin.seasons.show', [$club, $season])->with('success', 'Singles match added.');
    }

    public function editSingles(Club $club, Season $season, SinglesMatch $match)
    {
        abort_unless($season->club_id === $club->id, 404);

        $players = $club->users()->orderBy('name')->get();

        return view('admin.matches.edit_singles', compact('club', 'season', 'match', 'players'));
    }

    public function updateSingles(Request $request, Club $club, Season $season, SinglesMatch $match)
    {
        abort_unless($season->club_id === $club->id, 404);

        $data = $request->validate([
            'player1_id' => 'required|exists:users,id|different:player2_id',
            'player2_id' => 'required|exists:users,id',
            'sets_input' => 'required|string|max:200',
            'played_at' => 'nullable|date',
        ]);

        $sets = SinglesMatch::parseSetsString($data['sets_input']);
        $error = SinglesMatch::validateSets($sets);
        if ($error) {
            return back()->withInput()->withErrors(['sets_input' => $error]);
        }

        [$s1, $s2] = SinglesMatch::computeSetScores($sets);

        $match->update([
            'player1_id' => $data['player1_id'],
            'player2_id' => $data['player2_id'],
            'sets' => $sets,
            'score1' => $s1,
            'score2' => $s2,
            'played_at' => $data['played_at'] ?? null,
        ]);

        return redirect()->route('admin.seasons.show', [$club, $season])->with('success', 'Singles match updated.');
    }

    public function destroySingles(Club $club, Season $season, SinglesMatch $match)
    {
        abort_unless($season->club_id === $club->id, 404);

        $match->delete();

        return redirect()->route('admin.seasons.show', [$club, $season])->with('success', 'Singles match deleted.');
    }

    // ── Doubles ──────────────────────────────────────────────

    public function createDoubles(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $pairs = DoublePair::where('season_id', $season->id)->with(['player1', 'player2'])->get();

        return view('admin.matches.create_doubles', compact('club', 'season', 'pairs'));
    }

    public function storeDoubles(Request $request, Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $data = $request->validate([
            'pair1_id' => 'required|exists:double_pairs,id|different:pair2_id',
            'pair2_id' => 'required|exists:double_pairs,id',
            'sets_input' => 'required|string|max:200',
            'played_at' => 'nullable|date',
        ]);

        $sets = SinglesMatch::parseSetsString($data['sets_input']);
        $error = SinglesMatch::validateSets($sets);
        if ($error) {
            return back()->withInput()->withErrors(['sets_input' => $error]);
        }

        [$s1, $s2] = SinglesMatch::computeSetScores($sets);

        DoublesMatch::create([
            'season_id' => $season->id,
            'pair1_id' => $data['pair1_id'],
            'pair2_id' => $data['pair2_id'],
            'sets' => $sets,
            'score1' => $s1,
            'score2' => $s2,
            'played_at' => $data['played_at'] ?? null,
        ]);

        return redirect()->route('admin.seasons.show', [$club, $season])->with('success', 'Doubles match added.');
    }

    public function editDoubles(Club $club, Season $season, DoublesMatch $match)
    {
        abort_unless($season->club_id === $club->id, 404);

        $pairs = DoublePair::where('season_id', $season->id)->with(['player1', 'player2'])->get();

        return view('admin.matches.edit_doubles', compact('club', 'season', 'match', 'pairs'));
    }

    public function updateDoubles(Request $request, Club $club, Season $season, DoublesMatch $match)
    {
        abort_unless($season->club_id === $club->id, 404);

        $data = $request->validate([
            'pair1_id' => 'required|exists:double_pairs,id|different:pair2_id',
            'pair2_id' => 'required|exists:double_pairs,id',
            'sets_input' => 'required|string|max:200',
            'played_at' => 'nullable|date',
        ]);

        $sets = SinglesMatch::parseSetsString($data['sets_input']);
        $error = SinglesMatch::validateSets($sets);
        if ($error) {
            return back()->withInput()->withErrors(['sets_input' => $error]);
        }

        [$s1, $s2] = SinglesMatch::computeSetScores($sets);

        $match->update([
            'pair1_id' => $data['pair1_id'],
            'pair2_id' => $data['pair2_id'],
            'sets' => $sets,
            'score1' => $s1,
            'score2' => $s2,
            'played_at' => $data['played_at'] ?? null,
        ]);

        return redirect()->route('admin.seasons.show', [$club, $season])->with('success', 'Doubles match updated.');
    }

    public function destroyDoubles(Club $club, Season $season, DoublesMatch $match)
    {
        abort_unless($season->club_id === $club->id, 404);

        $match->delete();

        return redirect()->route('admin.seasons.show', [$club, $season])->with('success', 'Doubles match deleted.');
    }
}
