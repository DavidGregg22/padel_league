<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClubMemberMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $club = $request->route('club');

        if (! $club || ! $request->user()?->belongsToClub($club)) {
            abort(403, 'You are not a member of this club.');
        }

        return $next($request);
    }
}
