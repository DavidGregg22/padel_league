<?php

namespace App\Http\Controllers;

use App\Exports\SeasonExport;
use App\Models\Club;
use App\Models\Season;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function season(Club $club, Season $season)
    {
        abort_unless($season->club_id === $club->id, 404);

        $filename = str_replace(' ', '_', $club->name).'_'.$season->name.'_'.$season->year.'.xlsx';

        return Excel::download(new SeasonExport($season), $filename);
    }
}
