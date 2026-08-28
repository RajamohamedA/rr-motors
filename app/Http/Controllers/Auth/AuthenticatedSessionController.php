<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Company;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;
class AuthenticatedSessionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Page
    |--------------------------------------------------------------------------
    */

    public function create(): InertiaResponse
    {
        return Inertia::render(
            'Auth/Login',
            [
                'canResetPassword' =>
                Route::has(
                    'password.request'
                ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function store(
        LoginRequest $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Authenticate Credentials
        |--------------------------------------------------------------------------
        */

        $request->authenticate();


        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Safety
        |--------------------------------------------------------------------------
        */

        if (
            !$user
        ) {

            return $this->rejectLogin(
                $request,
                'Unable to sign in. Please try again.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Super Admin is platform-level and does not require a company.
        |
        | But the Super Admin account itself must still be active.
        |
        */

        if (
            (bool) $user->is_super_user
        ) {

            if (
                !(bool) $user->status
            ) {

                return $this->rejectLogin(
                    $request,
                    'Your Super Admin account is inactive. Please contact the system administrator.'
                );
            }


            return $this->completeLogin(
                $request
            );
        }


        /*
        |--------------------------------------------------------------------------
        | User Account Status
        |--------------------------------------------------------------------------
        |
        | New business registrations are created with status = false.
        |
        | Super Admin activates the account after subscription/payment.
        |
        */

        if (
            !(bool) $user->status
        ) {

            return $this->rejectLogin(
                $request,
                'Your RR ERP account is pending activation. Please complete your subscription/payment and wait for Super Admin approval.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Assignment Required
        |--------------------------------------------------------------------------
        */

        if (
            !$user->company_id
        ) {

            return $this->rejectLogin(
                $request,
                'No company is assigned to your account. Please contact the administrator.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Load Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()
            ->find(
                $user->company_id
            );


        /*
        |--------------------------------------------------------------------------
        | Company Missing
        |--------------------------------------------------------------------------
        */

        if (
            !$company
        ) {

            return $this->rejectLogin(
                $request,
                'Your assigned company could not be found. Please contact the administrator.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Activation / Subscription
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $company->status
        ) {

            return $this->rejectLogin(
                $request,
                'Your company subscription is pending activation. Please complete the subscription/payment and wait for Super Admin approval.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Successful Company Login
        |--------------------------------------------------------------------------
        */

        return $this->completeLogin(
            $request
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Complete Successful Login
    |--------------------------------------------------------------------------
    */

    private function completeLogin(
        LoginRequest $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Regenerate Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Login Time
        |--------------------------------------------------------------------------
        */

        $loginTime =
            now();


        /*
        |--------------------------------------------------------------------------
        | Update Last Login
        |--------------------------------------------------------------------------
        */

        $user->update([
            'last_login_at' =>
            $loginTime,
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
        | Redirect Through Login Redirect Controller
        |--------------------------------------------------------------------------
        |
        | LoginRedirectController decides:
        |
        | - Super Admin
        | - Company Admin
        | - Store Manager
        | - Sales/Purchase User
        | - Branch selection
        |
        */

        return redirect()
            ->route(
                'login.redirect'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject Login
    |--------------------------------------------------------------------------
    |
    | Credentials are correct, but the account is not currently allowed
    | to enter RR ERP.
    |
    */

    private function rejectLogin(
        LoginRequest $request,
        string $message
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Auth::guard(
            'web'
        )->logout();


        /*
        |--------------------------------------------------------------------------
        | Invalidate Existing Session
        |--------------------------------------------------------------------------
        |
        | Clears:
        |
        | active_company_id
        | active_store_id
        | and any other session context.
        |
        */

        $request
            ->session()
            ->invalidate();


        /*
        |--------------------------------------------------------------------------
        | Regenerate CSRF Token
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerateToken();


        /*
        |--------------------------------------------------------------------------
        | Return To React Login Page
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'login'
            )
            ->withInput([
                'email' =>
                $request->input(
                    'email'
                ),
            ])
            ->with(
                'app_message',
                [

                    /*
                    |--------------------------------------------------------------------------
                    | Unique Message ID
                    |--------------------------------------------------------------------------
                    |
                    | Allows React to show the same warning repeatedly.
                    |
                    */

                    'id' =>
                    uniqid(
                        'msg_',
                        true
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | Message Type
                    |--------------------------------------------------------------------------
                    */

                    'type' =>
                    'warning',

                    /*
                    |--------------------------------------------------------------------------
                    | Message
                    |--------------------------------------------------------------------------
                    */

                    'message' =>
                    $message,
                ]
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Log Logout Activity
        |--------------------------------------------------------------------------
        */

        if (
            $user
        ) {

            $logoutTime =
                now();


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

        Auth::guard(
            'web'
        )->logout();


        /*
        |--------------------------------------------------------------------------
        | Invalidate Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->invalidate();


        /*
        |--------------------------------------------------------------------------
        | Regenerate CSRF Token
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerateToken();


        /*
        |--------------------------------------------------------------------------
        | Redirect To React Login Page
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'login'
            );
    }
}
