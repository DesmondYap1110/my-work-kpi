<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Setting the company up - teams, positions, members, tags, KPIs and the
 * weighting between them - belongs to the system administrator alone.
 *
 * Hiding those entries from the sidebar is presentation; this is the rule.
 * It is applied as a route-group middleware so that a page, its form posts and
 * its AJAX list are all covered by the same single decision, rather than by a
 * check remembered separately in each controller.
 */
class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::user();

        if (! $staff || ! $staff->isAdmin()) {
            abort(403, 'Only the system administrator can manage this.');
        }

        return $next($request);
    }
}
