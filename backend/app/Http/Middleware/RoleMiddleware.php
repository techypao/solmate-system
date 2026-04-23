<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // If not authenticated at all
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login')
                ->with('status', 'Please log in to continue.');
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole($role)) {
            return $next($request);
        }

        // Authenticated but wrong role
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Web: redirect to the user's own home with a flash message
        $message = 'You do not have permission to access that page.';

        if ($user->role === User::ROLE_CUSTOMER) {
            return redirect()->route('home')->with('status', $message);
        }

        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->route('dashboard')->with('status', $message);
        }

        return redirect()->route('login')->with('status', $message);
    }
}