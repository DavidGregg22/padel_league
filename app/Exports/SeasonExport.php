<?php

namespace App\Exports;

use App\Models\Season;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SeasonExport implements WithMultipleSheets
{
    public function __construct(private Season $season) {}

    public function sheets(): array
    {
        return [
            'Singles Standings' => new SinglesStandingsSheet($this->season),
            'Doubles Standings' => new DoublesStandingsSheet($this->season),
            'Singles Matches' => new SinglesMatchesSheet($this->season),
            'Doubles Matches' => new DoublesMatchesSheet($this->season),
        ];
    }
}
