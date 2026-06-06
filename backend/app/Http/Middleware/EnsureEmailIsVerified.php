<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasVerifiedEmail() || $user->role === 'admin') {
            return $next($request);
        }

        $user->tokens()->delete();
        DB::table('sessions')->where('user_id', $user->id)->delete();

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Please verify your email before continuing.',
            ], 403);
        }

        return redirect('/login')->withErrors([
            'email' => 'Please verify your email before logging in.',
        ]);
    }
}
