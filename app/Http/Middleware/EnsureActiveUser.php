<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Existing sessions must not bypass a later account deactivation.
        // Keep logout available so inactive users can end their session.
        if ($user && !(bool) $user->status && !$request->routeIs('logout')) {
            abort(403, 'Your account is inactive.');
        }

        return $next($request);
    }
}
