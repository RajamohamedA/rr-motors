<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /*
    |--------------------------------------------------------------------------
    | Root View
    |--------------------------------------------------------------------------
    */

    protected $rootView = 'app';


    /*
    |--------------------------------------------------------------------------
    | Asset Version
    |--------------------------------------------------------------------------
    */

    public function version(
        Request $request
    ): ?string {

        return parent::version(
            $request
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Shared Props
    |--------------------------------------------------------------------------
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


                        /*
                        |--------------------------------------------------------------------------
                        | Roles
                        |--------------------------------------------------------------------------
                        */

                        'roles' =>
                        fn() =>
                        $user
                            ->getRoleNames()
                            ->values()
                            ->all(),


                        /*
                        |--------------------------------------------------------------------------
                        | Permissions
                        |--------------------------------------------------------------------------
                        */

                        'permissions' =>
                        fn() =>
                        $user
                            ->getAllPermissions()
                            ->pluck(
                                'name'
                            )
                            ->values()
                            ->all(),
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
            | IMPORTANT:
            |
            | All controllers should use:
            |
            | return back()->with(
            |     'app_message',
            |     [
            |         'id' => (string) Str::uuid(),
            |         'type' => 'success',
            |         'message' => 'Operation completed successfully.',
            |     ]
            | );
            |
            | Available in React:
            |
            | const { app_message } = usePage().props;
            |
            */

            'app_message' =>
            fn() =>
            $request
                ->session()
                ->get(
                    'app_message'
                ),


            /*
            |--------------------------------------------------------------------------
            | Legacy Generic Message
            |--------------------------------------------------------------------------
            |
            | Keep this temporarily while older controllers are converted
            | to app_message.
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
    | Super Admin Notifications
    |--------------------------------------------------------------------------
    */

    private function getNotifications(
        $user
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Guest / Business User
        |--------------------------------------------------------------------------
        |
        | The notification dropdown currently belongs only to Super Admin.
        |
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
                            | ID
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
                            | Read State
                            |--------------------------------------------------------------------------
                            */

                        'read' =>
                        $notification
                            ->read_at
                            !== null,

                        'read_at' =>
                        $notification
                            ->read_at
                            ?->toISOString(),


                        /*
                            |--------------------------------------------------------------------------
                            | Created Time
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
        | Return Notification Data
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
