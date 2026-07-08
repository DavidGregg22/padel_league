<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoublePair extends Model
{
    protected $fillable = ['season_id', 'player1_id', 'player2_id', 'pair_name'];

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

    public function displayName(): string
    {
        return $this->pair_name ?? ($this->player1->name.' & '.$this->player2->name);
    }
}
