<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinglesMatch extends Model
{
    protected $fillable = ['season_id', 'player1_id', 'player2_id', 'score1', 'score2', 'sets', 'played_at'];

    protected $casts = [
        'played_at' => 'datetime',
        'sets'      => 'array',
    ];

    public function season()    { return $this->belongsTo(Season::class); }
    public function player1()   { return $this->belongsTo(User::class, 'player1_id'); }
    public function player2()   { return $this->belongsTo(User::class, 'player2_id'); }

    /**
     * Parse a sets string like "6-4, 3-6, 7-5" into [['p1'=>6,'p2'=>4], ...]
     * A set is won by whoever wins more games (tennis: need 6+ with 2 clear, or 7 in tiebreak).
     * We keep it simple: most games wins the set.
     */
    public static function parseSetsString(string $input): array
    {
        $sets = [];
        foreach (preg_split('/[\s,]+/', trim($input)) as $part) {
            if (preg_match('/^(\d+)-(\d+)$/', $part, $m)) {
                $sets[] = ['p1' => (int)$m[1], 'p2' => (int)$m[2]];
            }
        }
        return $sets;
    }

    /**
     * From parsed sets, count how many sets each side won.
     * A set is won if you have more games (handles 6-4, 7-5, 7-6 style).
     */
    public static function computeSetScores(array $sets): array
    {
        $s1 = 0; $s2 = 0;
        foreach ($sets as $set) {
            if ($set['p1'] > $set['p2']) $s1++;
            elseif ($set['p2'] > $set['p1']) $s2++;
        }
        return [$s1, $s2];
    }

    /** Display like "6-4, 3-6, 7-5" */
    public function setsDisplay(): string
    {
        if (!$this->sets) return '';
        return implode(', ', array_map(fn($s) => $s['p1'].'-'.$s['p2'], $this->sets));
    }

    public function result(): string
    {
        if (is_null($this->score1)) return 'Pending';
        if ($this->score1 > $this->score2) return 'Player 1 wins';
        if ($this->score2 > $this->score1) return 'Player 2 wins';
        return 'Draw';
    }
}
