<?php

namespace App\Support;

use Illuminate\Http\RedirectResponse;

class AppResponse
{
    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    public static function success(
        string $message
    ): RedirectResponse {

        return back()->with(
            'app_message',
            [
                'id' =>
                uniqid(
                    'msg_',
                    true
                ),

                'type' =>
                'success',

                'message' =>
                $message,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Error
    |--------------------------------------------------------------------------
    */

    public static function error(
        string $message,
        bool $withInput = false
    ): RedirectResponse {

        $response =
            back()->with(
                'app_message',
                [
                    'id' =>
                    uniqid(
                        'msg_',
                        true
                    ),

                    'type' =>
                    'error',

                    'message' =>
                    $message,
                ]
            );


        if (
            $withInput
        ) {

            $response->withInput();
        }


        return $response;
    }


    /*
    |--------------------------------------------------------------------------
    | Warning
    |--------------------------------------------------------------------------
    */

    public static function warning(
        string $message
    ): RedirectResponse {

        return back()->with(
            'app_message',
            [
                'id' =>
                uniqid(
                    'msg_',
                    true
                ),

                'type' =>
                'warning',

                'message' =>
                $message,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Information
    |--------------------------------------------------------------------------
    */

    public static function info(
        string $message
    ): RedirectResponse {

        return back()->with(
            'app_message',
            [
                'id' =>
                uniqid(
                    'msg_',
                    true
                ),

                'type' =>
                'info',

                'message' =>
                $message,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Success Redirect To Route
    |--------------------------------------------------------------------------
    */

    public static function successRoute(
        string $route,
        string $message,
        mixed $parameters = []
    ): RedirectResponse {

        return redirect()
            ->route(
                $route,
                $parameters
            )
            ->with(
                'app_message',
                [
                    'id' =>
                    uniqid(
                        'msg_',
                        true
                    ),

                    'type' =>
                    'success',

                    'message' =>
                    $message,
                ]
            );
    }
}
