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
        // Check if the user is authenticated and has the required role
        if (Auth::check()) {
    /** @var User $user */
    $user = Auth::user();

    if ($user->hasRole($role)) {
        return $next($request);
    }
}

        // If not authorized, return a 403 Forbidden response
        return response()->json(['message' => 'Forbidden'], 403);
    }
}