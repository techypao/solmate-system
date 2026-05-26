<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class EnsureUserIsNotArchived
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || ! $user->isArchivedCustomer()) {
            return $next($request);
        }

        $message = User::archivedAccountMessage();

        if ($request->expectsJson() || $request->is('api/*')) {
            $currentToken = $user->currentAccessToken();

            if ($currentToken instanceof PersonalAccessToken) {
                $currentToken->delete();
            }

            return response()->json([
                'message' => $message,
            ], 403);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors([
                'email' => $message,
            ]);
    }
}