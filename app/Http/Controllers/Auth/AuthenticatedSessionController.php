<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }


    /**
     * Handle an incoming authentication request.
     */
    public function store(
        LoginRequest $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Authenticate
        |--------------------------------------------------------------------------
        */

        $request->authenticate();


        /*
        |--------------------------------------------------------------------------
        | Regenerate Session
        |--------------------------------------------------------------------------
        */

        $request->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user = $request->user();


        /*
        |--------------------------------------------------------------------------
        | Update Last Login
        |--------------------------------------------------------------------------
        */

        $loginTime = now();


        $user->update([
            'last_login_at' => $loginTime,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'authentication',

            action: 'login',

            description: "User {$user->name} signed in",

            subject: $user,

            newValues: [
                'email' =>
                $user->email,

                'login_at' =>
                $loginTime
                    ->toDateTimeString(),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect After Login
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'login.redirect'
            );
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(
        Request $request
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        |
        | Capture the authenticated user before logout so that we can
        | write the logout activity before destroying the session.
        |
        */

        $user = $request->user();


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        if ($user) {

            $logoutTime = now();


            ActivityLogger::log(
                module: 'authentication',

                action: 'logout',

                description: "User {$user->name} signed out",

                subject: $user,

                newValues: [
                    'email' =>
                    $user->email,

                    'logout_at' =>
                    $logoutTime
                        ->toDateTimeString(),
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Auth::guard('web')
            ->logout();


        /*
        |--------------------------------------------------------------------------
        | Invalidate Session
        |--------------------------------------------------------------------------
        |
        | This removes active_company_id, active_store_id and all other
        | session data belonging to the logged-in user.
        |
        */

        $request->session()
            ->invalidate();


        /*
        |--------------------------------------------------------------------------
        | Regenerate CSRF Token
        |--------------------------------------------------------------------------
        */

        $request->session()
            ->regenerateToken();


        /*
        |--------------------------------------------------------------------------
        | Redirect to Login
        |--------------------------------------------------------------------------
        |
        | The Super Admin application uses Inertia/React while the login
        | screen is currently a normal Blade page.
        |
        | Inertia::location() forces a full browser navigation instead of
        | trying to load the Blade login page as an Inertia response.
        |
        */

        return Inertia::location(
            route('login')
        );
    }
}
