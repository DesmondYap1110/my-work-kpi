<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Matches the legacy /adm portal's behaviour: only staff belonging to
 * position_id === 1 may access this application at all. Applied as a
 * route-group middleware rather than scattered per-controller checks,
 * so it's a single, extensible authorization point.
 */
class EnsureIsAdminPosition
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::user();

        if (! $staff || ! $staff->isAdmin() || ! $staff->staffstatus) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'uname' => 'You are not authorized to access this portal.',
            ]);
        }

        return $next($request);
    }
}
