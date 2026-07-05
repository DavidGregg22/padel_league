<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinglesMatch extends Model
{
    protected $fillable = ['season_id', 'player1_id', 'player2_id', 'score1', 'score2', 'played_at'];

    protected $casts = ['played_at' => 'datetime'];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function player1()
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function result(): string
    {
        if (is_null($this->score1)) return 'Pending';
        if ($this->score1 > $this->score2) return 'Player 1 wins';
        if ($this->score2 > $this->score1) return 'Player 2 wins';
        return 'Draw';
    }
}
