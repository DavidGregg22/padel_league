<?php

namespace App\Exports;

use App\Models\Season;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SinglesMatchesSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private Season $season) {}

    public function title(): string
    {
        return 'Singles Matches';
    }

    public function headings(): array
    {
        return ['Date', 'Player 1', 'Player 2', 'Sets', 'Sets Won P1', 'Sets Won P2', 'Winner'];
    }

    public function array(): array
    {
        $matches = $this->season->singlesMatches()->with(['player1', 'player2'])->latest('played_at')->get();

        return $matches->map(fn ($m) => [
            $m->played_at?->format('Y-m-d') ?? '',
            $m->player1->name,
            $m->player2->name,
            $m->setsDisplay(),
            $m->score1,
            $m->score2,
            $m->score1 > $m->score2 ? $m->player1->name : ($m->score2 > $m->score1 ? $m->player2->name : 'Draw'),
        ])->toArray();
    }
}
