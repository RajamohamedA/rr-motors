<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Must Be Authenticated
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | Must Be Active
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (bool) $user->status,
            403,
            'Your account is inactive.'
        );


        /*
        |--------------------------------------------------------------------------
        | Must Be Super Admin
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (bool) $user->is_super_user,
            403,
            'You do not have Super Administrator access.'
        );


        return $next($request);
    }
}
