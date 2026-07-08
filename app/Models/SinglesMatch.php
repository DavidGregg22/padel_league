<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinglesMatch extends Model
{
    protected $fillable = ['season_id', 'player1_id', 'player2_id', 'score1', 'score2', 'sets', 'played_at'];

    protected $casts = [
        'played_at' => 'datetime',
        'sets' => 'array',
    ];

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

    /**
     * Parse a sets string like "6-4, 3-6, 7-5" into [['p1'=>6,'p2'=>4], ...]
     */
    public static function parseSetsString(string $input): array
    {
        $sets = [];
        foreach (preg_split('/[\s,]+/', trim($input)) as $part) {
            if (preg_match('/^(\d+)-(\d+)$/', $part, $m)) {
                $sets[] = ['p1' => (int) $m[1], 'p2' => (int) $m[2]];
            }
        }

        return $sets;
    }

    /**
     * Validate that each set score is a valid padel/tennis set.
     * Valid scores: 6-0..6-4, 7-5, 7-6, 6-6 (unfinished tiebreak = draw).
     * Returns an error message string or null if valid.
     */
    public static function validateSets(array $sets): ?string
    {
        if (empty($sets)) {
            return 'Enter at least one set (e.g. 6-4, 7-5).';
        }

        foreach ($sets as $i => $set) {
            $p1 = $set['p1'];
            $p2 = $set['p2'];
            $high = max($p1, $p2);
            $low = min($p1, $p2);

            // 6-6: unfinished tiebreak (draw)
            if ($p1 === 6 && $p2 === 6) {
                continue;
            }

            if ($p1 === $p2) {
                return "Set ".($i + 1).": invalid tie ({$p1}-{$p2}). Only 6-6 allowed for unfinished sets.";
            }

            // Tiebreak: 7-6
            if ($high === 7 && $low === 6) {
                continue;
            }

            // Normal set: winner has 6, loser has 0-4
            if ($high === 6 && $low <= 4) {
                continue;
            }

            // 7-5: winner broke at 6-5
            if ($high === 7 && $low === 5) {
                continue;
            }

            return "Set ".($i + 1).": invalid score ({$p1}-{$p2}). Valid: 6-0 to 6-4, 7-5, 7-6, or 6-6.";
        }

        return null;
    }

    /**
     * From parsed sets, count how many sets each side won.
     * A set is won if you have more games (handles 6-4, 7-5, 7-6 style).
     */
    public static function computeSetScores(array $sets): array
    {
        $s1 = 0;
        $s2 = 0;
        foreach ($sets as $set) {
            if ($set['p1'] > $set['p2']) {
                $s1++;
            } elseif ($set['p2'] > $set['p1']) {
                $s2++;
            }
        }

        return [$s1, $s2];
    }

    /** Display like "6-4, 3-6, 7-5" */
    public function setsDisplay(): string
    {
        if (! $this->sets) {
            return '';
        }

        return implode(', ', array_map(fn ($s) => $s['p1'].'-'.$s['p2'], $this->sets));
    }

    public function result(): string
    {
        if (is_null($this->score1)) {
            return 'Pending';
        }
        if ($this->score1 > $this->score2) {
            return 'Player 1 wins';
        }
        if ($this->score2 > $this->score1) {
            return 'Player 2 wins';
        }

        return 'Draw';
    }
}
