<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     */
    protected $rootView = 'app';


    /**
     * Determine the current asset version.
     */
    public function version(
        Request $request
    ): ?string {

        return parent::version(
            $request
        );
    }


    /**
     * Define the props that are shared by default.
     */
    public function share(
        Request $request
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Shared Inertia Props
        |--------------------------------------------------------------------------
        */

        return [

            ...parent::share(
                $request
            ),


            /*
            |--------------------------------------------------------------------------
            | Authentication
            |--------------------------------------------------------------------------
            */

            'auth' => [

                'user' =>
                $user
                    ? [

                        'id' =>
                        $user->id,

                        'name' =>
                        $user->name,

                        'email' =>
                        $user->email,

                        'mobile' =>
                        $user->mobile,

                        'status' =>
                        (bool) $user->status,

                        'is_super_user' =>
                        (bool) $user->is_super_user,

                        'company_id' =>
                        $user->company_id,

                        'store_id' =>
                        $user->store_id,

                        'last_login_at' =>
                        $user->last_login_at,

                    ]
                    : null,
            ],


            /*
            |--------------------------------------------------------------------------
            | Active Business Context
            |--------------------------------------------------------------------------
            */

            'business' => [

                'company_id' =>
                fn() =>
                $request
                    ->session()
                    ->get(
                        'active_company_id'
                    ),

                'store_id' =>
                fn() =>
                $request
                    ->session()
                    ->get(
                        'active_store_id'
                    ),
            ],


            /*
            |--------------------------------------------------------------------------
            | Global Application Message
            |--------------------------------------------------------------------------
            |
            | All controllers should use App\Support\AppResponse.
            |
            | Example:
            |
            | AppResponse::success(...)
            | AppResponse::error(...)
            | AppResponse::warning(...)
            | AppResponse::info(...)
            |
            */

            'appMessage' =>
            fn() =>
            $request
                ->session()
                ->get(
                    'app_message'
                ),


            /*
            |--------------------------------------------------------------------------
            | Generic Message
            |--------------------------------------------------------------------------
            |
            | Keep this temporarily if any older module still uses:
            |
            | ->with('message', '...')
            |
            | You can remove it later after all modules use AppResponse.
            |
            */

            'message' =>
            fn() =>
            $request
                ->session()
                ->get(
                    'message'
                ),


            /*
            |--------------------------------------------------------------------------
            | Super Admin Notifications
            |--------------------------------------------------------------------------
            */

            'notifications' =>
            fn() =>
            $this->getNotifications(
                $user
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Super Admin Notifications
    |--------------------------------------------------------------------------
    */

    private function getNotifications(
        $user
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Guest / Business User
        |--------------------------------------------------------------------------
        */

        if (
            !$user ||
            !(bool) $user->is_super_user
        ) {

            return [

                'unread_count' =>
                0,

                'items' =>
                [],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Latest Notifications
        |--------------------------------------------------------------------------
        */

        $items =
            $user
            ->notifications()
            ->latest()
            ->limit(
                8
            )
            ->get()
            ->map(
                function (
                    $notification
                ) {

                    $data =
                        $notification->data
                        ?? [];


                    return [

                        /*
                            |--------------------------------------------------------------------------
                            | Notification ID
                            |--------------------------------------------------------------------------
                            */

                        'id' =>
                        $notification->id,


                        /*
                            |--------------------------------------------------------------------------
                            | Content
                            |--------------------------------------------------------------------------
                            */

                        'title' =>
                        $data['title']
                            ?? 'Notification',

                        'message' =>
                        $data['message']
                            ?? '',


                        /*
                            |--------------------------------------------------------------------------
                            | Classification
                            |--------------------------------------------------------------------------
                            */

                        'category' =>
                        $data['category']
                            ?? 'system',

                        'level' =>
                        $data['level']
                            ?? 'info',

                        'icon' =>
                        $data['icon']
                            ?? 'bi-bell',


                        /*
                            |--------------------------------------------------------------------------
                            | Navigation
                            |--------------------------------------------------------------------------
                            */

                        'target_type' =>
                        $data['target_type']
                            ?? null,

                        'target_id' =>
                        $data['target_id']
                            ?? null,

                        'fallback_url' =>
                        $data['fallback_url']
                            ?? null,


                        /*
                            |--------------------------------------------------------------------------
                            | Read Status
                            |--------------------------------------------------------------------------
                            */

                        'read' =>
                        $notification->read_at
                            !== null,

                        'read_at' =>
                        $notification
                            ->read_at
                            ?->toISOString(),


                        /*
                            |--------------------------------------------------------------------------
                            | Time
                            |--------------------------------------------------------------------------
                            */

                        'created_at' =>
                        $notification
                            ->created_at
                            ?->toISOString(),
                    ];
                }
            )
            ->values()
            ->all();


        /*
        |--------------------------------------------------------------------------
        | Unread Count
        |--------------------------------------------------------------------------
        */

        $unreadCount =
            $user
            ->unreadNotifications()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [

            'unread_count' =>
            $unreadCount,

            'items' =>
            $items,
        ];
    }
}
