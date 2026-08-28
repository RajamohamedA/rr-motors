<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Forgot Password Page
    |--------------------------------------------------------------------------
    */

    public function create(): Response
    {
        return Inertia::render(
            'Auth/ForgotPassword'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Send Password Reset Link
    |--------------------------------------------------------------------------
    |
    | @throws ValidationException
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Validate Email
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'email' => [
                    'required',
                    'email',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Send Reset Link
        |--------------------------------------------------------------------------
        */

        $status =
            Password::sendResetLink([
                'email' =>
                $validated['email'],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Reset Link Sent
        |--------------------------------------------------------------------------
        */

        if (
            $status ===
            Password::RESET_LINK_SENT
        ) {

            return back()
                ->with(
                    'app_message',
                    [

                        'id' =>
                        (string) Str::uuid(),

                        'type' =>
                        'success',

                        'message' =>
                        __($status),
                    ]
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Failed To Send Reset Link
        |--------------------------------------------------------------------------
        */

        return back()
            ->withInput([
                'email' =>
                $validated['email'],
            ])
            ->withErrors([
                'email' =>
                __($status),
            ])
            ->with(
                'app_message',
                [

                    'id' =>
                    (string) Str::uuid(),

                    'type' =>
                    'error',

                    'message' =>
                    'We could not send the password reset link. Please check the email address and try again.',
                ]
            );
    }
}
