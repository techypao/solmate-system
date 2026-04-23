<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSessionTimeout
{
    /**
     * Inactivity timeout in minutes.
     * Configure via SESSION_TIMEOUT in .env (default: 30).
     */
    private int $timeoutMinutes;

    public function __construct()
    {
        $this->timeoutMinutes = (int) env('SESSION_TIMEOUT', 30);
    }

    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::check()) {
            $lastActivity = $request->session()->get('_last_activity');
            $now = time();

            if ($lastActivity !== null && ($now - $lastActivity) > ($this->timeoutMinutes * 60)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('status', 'Your session expired due to inactivity. Please log in again.');
            }

            // Refresh last-activity timestamp on every authenticated request.
            $request->session()->put('_last_activity', $now);
        }

        $response = $next($request);

        // Prevent the browser from caching authenticated pages so that
        // pressing Back after logout does not reveal stale protected content.
        if (Auth::check()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
