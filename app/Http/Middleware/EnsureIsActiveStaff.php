<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * The gate into the portal: any active staff member may come in.
 *
 * This used to demand the administrator position, which made the portal a
 * single-user tool. Ordinary staff now sign in to see their own KPI and their
 * own tasks - what they may *administer* is a separate question, answered by
 * EnsureIsAdmin on the routes that set things up.
 *
 * A member who has been deactivated is signed out rather than merely refused,
 * so a session that was live when the switch was flipped does not survive it.
 */
class EnsureIsActiveStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::user();

        if (! $staff || ! $staff->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'You are not authorized to access this portal.',
            ]);
        }

        return $next($request);
    }
}
