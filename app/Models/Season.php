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

            $standings[] = [
                'player' => $player,
                'played' => $played,
                'won' => $won,
                'lost' => $played - $won,
                'points' => $won * 3,
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

            $standings[] = [
                'pair' => $pair,
                'played' => $played,
                'won' => $won,
                'lost' => $played - $won,
                'points' => $won * 3,
            ];
        }

        usort($standings, fn ($a, $b) => $b['points'] <=> $a['points']);

        return $standings;
    }
}
