<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFrontendApiRequestsAreStateful
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->shouldInferFrontendHeaders($request)) {
            $request->headers->set('referer', rtrim($request->getSchemeAndHttpHost(), '/').'/');
        }

        return $next($request);
    }

    private function shouldInferFrontendHeaders(Request $request): bool
    {
        if ($request->headers->has('referer') || $request->headers->has('origin')) {
            return false;
        }

        $sessionCookieName = (string) config('session.cookie');

        return filled($request->cookies->get($sessionCookieName))
            || filled($request->cookies->get('XSRF-TOKEN'));
    }
}
