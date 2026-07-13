<?php

namespace App\Exports;

use App\Models\Season;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SinglesStandingsSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private Season $season) {}

    public function title(): string
    {
        return 'Singles Standings';
    }

    public function headings(): array
    {
        return ['#', 'Player', 'Played', 'Won', 'Drawn', 'Lost', 'Points'];
    }

    public function array(): array
    {
        $standings = $this->season->singlesStandings();

        return array_map(fn ($row, $i) => [
            $i + 1,
            $row['player']->name,
            $row['played'],
            $row['won'],
            $row['drawn'],
            $row['lost'],
            $row['points'],
        ], $standings, array_keys($standings));
    }
}
