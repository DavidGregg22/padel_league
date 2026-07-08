<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = ['club_id', 'name', 'year', 'active'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function doublePairs()
    {
        return $this->hasMany(DoublePair::class);
    }

    public function singlesMatches()
    {
        return $this->hasMany(SinglesMatch::class);
    }

    public function doublesMatches()
    {
        return $this->hasMany(DoublesMatch::class);
    }

    public function singlesStandings(): array
    {
        $playerIds = $this->club->users()->pluck('users.id');
        $standings = [];

        foreach ($playerIds as $playerId) {
            $player = User::find($playerId);

            $played = SinglesMatch::where('season_id', $this->id)
                ->where(fn ($q) => $q->where('player1_id', $playerId)->orWhere('player2_id', $playerId))
                ->whereNotNull('score1')->count();

            if ($played === 0) {
                continue;
            }

            $won = SinglesMatch::where('season_id', $this->id)
                ->where(fn ($q) => $q->where('player1_id', $playerId)->whereColumn('score1', '>', 'score2')
                    ->orWhere('player2_id', $playerId)->whereColumn('score2', '>', 'score1'))
                ->whereNotNull('score1')->count();

            $drawn = SinglesMatch::where('season_id', $this->id)
                ->where(fn ($q) => $q->where('player1_id', $playerId)->orWhere('player2_id', $playerId))
                ->whereNotNull('score1')->whereColumn('score1', 'score2')->count();

            $standings[] = [
                'player' => $player,
                'played' => $played,
                'won' => $won,
                'drawn' => $drawn,
                'lost' => $played - $won - $drawn,
                'points' => ($won * 3) + $drawn,
            ];
        }

        usort($standings, fn ($a, $b) => $b['points'] <=> $a['points']);

        return $standings;
    }

    public function doublesStandings(): array
    {
        $pairs = DoublePair::where('season_id', $this->id)->with(['player1', 'player2'])->get();
        $standings = [];

        foreach ($pairs as $pair) {
            $played = DoublesMatch::where('season_id', $this->id)
                ->where(fn ($q) => $q->where('pair1_id', $pair->id)->orWhere('pair2_id', $pair->id))
                ->whereNotNull('score1')->count();

            $won = DoublesMatch::where('season_id', $this->id)
                ->where(fn ($q) => $q->where('pair1_id', $pair->id)->whereColumn('score1', '>', 'score2')
                    ->orWhere('pair2_id', $pair->id)->whereColumn('score2', '>', 'score1'))
                ->whereNotNull('score1')->count();

            $drawn = DoublesMatch::where('season_id', $this->id)
                ->where(fn ($q) => $q->where('pair1_id', $pair->id)->orWhere('pair2_id', $pair->id))
                ->whereNotNull('score1')->whereColumn('score1', 'score2')->count();

            $standings[] = [
                'pair' => $pair,
                'played' => $played,
                'won' => $won,
                'drawn' => $drawn,
                'lost' => $played - $won - $drawn,
                'points' => ($won * 3) + $drawn,
            ];
        }

        usort($standings, fn ($a, $b) => $b['points'] <=> $a['points']);

        return $standings;
    }

    /**
     * Generate all round-robin singles fixtures and mark which are played.
     */
    public function singlesFixtures(): array
    {
        $players = $this->club->users()->orderBy('name')->get();
        $matches = $this->singlesMatches()->whereNotNull('score1')->get();
        $fixtures = [];

        for ($i = 0; $i < $players->count(); $i++) {
            for ($j = $i + 1; $j < $players->count(); $j++) {
                $p1 = $players[$i];
                $p2 = $players[$j];

                $match = $matches->first(fn ($m) => ($m->player1_id === $p1->id && $m->player2_id === $p2->id)
                    || ($m->player1_id === $p2->id && $m->player2_id === $p1->id));

                $fixtures[] = [
                    'player1' => $p1,
                    'player2' => $p2,
                    'match' => $match,
                    'played' => (bool) $match,
                ];
            }
        }

        return $fixtures;
    }

    /**
     * Generate all round-robin doubles fixtures and mark which are played.
     */
    public function doublesFixtures(): array
    {
        $pairs = DoublePair::where('season_id', $this->id)->with(['player1', 'player2'])->get();
        $matches = $this->doublesMatches()->whereNotNull('score1')->get();
        $fixtures = [];

        for ($i = 0; $i < $pairs->count(); $i++) {
            for ($j = $i + 1; $j < $pairs->count(); $j++) {
                $pr1 = $pairs[$i];
                $pr2 = $pairs[$j];

                $match = $matches->first(fn ($m) => ($m->pair1_id === $pr1->id && $m->pair2_id === $pr2->id)
                    || ($m->pair1_id === $pr2->id && $m->pair2_id === $pr1->id));

                $fixtures[] = [
                    'pair1' => $pr1,
                    'pair2' => $pr2,
                    'match' => $match,
                    'played' => (bool) $match,
                ];
            }
        }

        return $fixtures;
    }
}
