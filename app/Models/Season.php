<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = ['name', 'year', 'active'];

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

    public function singlesStandings()
    {
        $players = User::all();
        $standings = [];

        foreach ($players as $player) {
            $won = SinglesMatch::where('season_id', $this->id)
                ->where(function ($q) use ($player) {
                    $q->where('player1_id', $player->id)->whereColumn('score1', '>', 'score2')
                      ->orWhere('player2_id', $player->id)->whereColumn('score2', '>', 'score1');
                })->whereNotNull('score1')->count();

            $drawn = SinglesMatch::where('season_id', $this->id)
                ->where(function ($q) use ($player) {
                    $q->where('player1_id', $player->id)->orWhere('player2_id', $player->id);
                })->whereNotNull('score1')->whereColumn('score1', 'score2')->count();

            $played = SinglesMatch::where('season_id', $this->id)
                ->where(function ($q) use ($player) {
                    $q->where('player1_id', $player->id)->orWhere('player2_id', $player->id);
                })->whereNotNull('score1')->count();

            $lost = $played - $won - $drawn;
            $points = ($won * 3) + $drawn;

            if ($played > 0) {
                $standings[] = [
                    'player' => $player,
                    'played' => $played,
                    'won'    => $won,
                    'drawn'  => $drawn,
                    'lost'   => $lost,
                    'points' => $points,
                ];
            }
        }

        usort($standings, fn($a, $b) => $b['points'] <=> $a['points']);
        return $standings;
    }

    public function doublesStandings()
    {
        $pairs = DoublePair::where('season_id', $this->id)->with(['player1', 'player2'])->get();
        $standings = [];

        foreach ($pairs as $pair) {
            $won = DoublesMatch::where('season_id', $this->id)
                ->where(function ($q) use ($pair) {
                    $q->where('pair1_id', $pair->id)->whereColumn('score1', '>', 'score2')
                      ->orWhere('pair2_id', $pair->id)->whereColumn('score2', '>', 'score1');
                })->whereNotNull('score1')->count();

            $drawn = DoublesMatch::where('season_id', $this->id)
                ->where(function ($q) use ($pair) {
                    $q->where('pair1_id', $pair->id)->orWhere('pair2_id', $pair->id);
                })->whereNotNull('score1')->whereColumn('score1', 'score2')->count();

            $played = DoublesMatch::where('season_id', $this->id)
                ->where(function ($q) use ($pair) {
                    $q->where('pair1_id', $pair->id)->orWhere('pair2_id', $pair->id);
                })->whereNotNull('score1')->count();

            $lost = $played - $won - $drawn;
            $points = ($won * 3) + $drawn;

            $standings[] = [
                'pair'   => $pair,
                'played' => $played,
                'won'    => $won,
                'drawn'  => $drawn,
                'lost'   => $lost,
                'points' => $points,
            ];
        }

        usort($standings, fn($a, $b) => $b['points'] <=> $a['points']);
        return $standings;
    }
}
