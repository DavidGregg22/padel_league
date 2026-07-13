<?php

namespace App\Exports;

use App\Models\Season;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DoublesMatchesSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private Season $season) {}

    public function title(): string
    {
        return 'Doubles Matches';
    }

    public function headings(): array
    {
        return ['Date', 'Pair 1', 'Pair 2', 'Sets', 'Sets Won P1', 'Sets Won P2', 'Winner'];
    }

    public function array(): array
    {
        $matches = $this->season->doublesMatches()
            ->with(['pair1.player1', 'pair1.player2', 'pair2.player1', 'pair2.player2'])
            ->latest('played_at')->get();

        return $matches->map(fn ($m) => [
            $m->played_at?->format('Y-m-d') ?? '',
            $m->pair1->displayName(),
            $m->pair2->displayName(),
            $m->setsDisplay(),
            $m->score1,
            $m->score2,
            $m->score1 > $m->score2 ? $m->pair1->displayName() : ($m->score2 > $m->score1 ? $m->pair2->displayName() : 'Draw'),
        ])->toArray();
    }
}
