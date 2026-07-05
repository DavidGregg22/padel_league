<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoublesMatch extends Model
{
    protected $fillable = ['season_id', 'pair1_id', 'pair2_id', 'score1', 'score2', 'played_at'];

    protected $casts = ['played_at' => 'datetime'];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function pair1()
    {
        return $this->belongsTo(DoublePair::class, 'pair1_id');
    }

    public function pair2()
    {
        return $this->belongsTo(DoublePair::class, 'pair2_id');
    }
}
