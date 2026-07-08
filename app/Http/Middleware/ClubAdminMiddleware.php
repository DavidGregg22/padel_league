<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClubAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $club = $request->route('club');

        if (! $club || ! $request->user()?->isClubAdmin($club)) {
            abort(403, 'Club admins only.');
        }

        return $next($request);
    }
}
