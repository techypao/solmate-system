<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$permissions): mixed
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login')
                ->with('status', 'Please log in to continue.');
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdminUser()) {
            return $this->deny($request);
        }

        if ($permissions === [] || $user->hasAnyAdminPermission($permissions)) {
            return $next($request);
        }

        return $this->deny($request);
    }

    private function deny(Request $request): mixed
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'You do not have permission to access that admin area.');
    }
}
