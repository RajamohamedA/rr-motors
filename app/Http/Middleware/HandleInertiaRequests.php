<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\Store;
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

                'company_logo' => fn() => $this->getCompanyLogo($request),
                'assigned_branch_ids' => function () use ($request) {
                    $user = $request->user();
                    $companyId = $request->session()->get('active_company_id');
                    if (!$user || !$user->status || !$companyId) {
                        return [];
                    }
                    if ($user->is_super_user) {
                        return Store::where('company_id', $companyId)->pluck('id')->all();
                    }
                    if ((int) $companyId !== (int) $user->company_id) {
                        return [];
                    }
                    // Include pending assignments to distinguish approval from membership.
                    return $user->stores()->where('stores.company_id', $companyId)
                        ->pluck('stores.id')->all();
                },

                /*
                |--------------------------------------------------------------------------
                | Company
                |--------------------------------------------------------------------------
                */

                'company_id' =>
                fn() =>
                $request
                    ->session()
                    ->get(
                        'active_company_id'
                    ),

                'company_name' =>
                fn() =>
                $request
                    ->session()
                    ->get(
                        'active_company_name'
                    ),

                'company_code' =>
                fn() =>
                $request
                    ->session()
                    ->get(
                        'active_company_code'
                    ),


                /*
                |--------------------------------------------------------------------------
                | Branch
                |--------------------------------------------------------------------------
                */

                'store_id' =>
                fn() =>
                $request
                    ->session()
                    ->get(
                        'active_store_id'
                    ),

                'store_name' =>
                fn() =>
                $request
                    ->session()
                    ->get(
                        'active_store_name'
                    ),

                'store_code' =>
                fn() =>
                $request
                    ->session()
                    ->get(
                        'active_store_code'
                    ),


                /*
                |--------------------------------------------------------------------------
                | Available Branches
                |--------------------------------------------------------------------------
                |
                | Used by Business Topbar branch switcher.
                |
                | Super Admin:
                | - all active branches in current company context
                |
                | Company Admin:
                | - only explicitly assigned active branches
                |
                | Branch Admin / Normal User:
                | - only explicitly assigned active branches
                |
                */

                'available_branches' =>
                fn() =>
                $this->getAvailableBranches(
                    $request
                ),


                /*
                |--------------------------------------------------------------------------
                | Super Admin Business Context
                |--------------------------------------------------------------------------
                */

                'is_super_admin_context' =>
                fn() =>
                (bool) $request
                    ->session()
                    ->get(
                        'super_admin_business_context',
                        false
                    ),
            ],


            /*
            |--------------------------------------------------------------------------
            | Global Application Message
            |--------------------------------------------------------------------------
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
            | Notifications
            |--------------------------------------------------------------------------
            |
            | Every authenticated user receives their own Laravel database
            | notifications.
            |
            */

            'notifications' =>
            fn() =>
            $this->getNotifications(
                $user,
                $request
            ),
        ];
    }


    /** Return the public-disk path; the sidebar adds /storage/. */
    private function getCompanyLogo(Request $request): ?string
    {
        $user = $request->user();
        $companyId = $request->session()->get('active_company_id');

        if (!$user || !(bool) $user->status || !$companyId) {
            return null;
        }

        if (
            !$user->is_super_user &&
            (int) $companyId !== (int) $user->company_id
        ) {
            return null;
        }

        return Company::query()
            ->whereKey($companyId)
            ->where('status', true)
            ->value('logo');
    }

    /*
    |--------------------------------------------------------------------------
    | Available Branches
    |--------------------------------------------------------------------------
    */

    private function getAvailableBranches(
        Request $request
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        if (
            !$user || !(bool) $user->status
        ) {

            return [];
        }


        /*
        |--------------------------------------------------------------------------
        | Active Company Context
        |--------------------------------------------------------------------------
        */

        $activeCompanyId =
            $request
            ->session()
            ->get(
                'active_company_id'
            );


        /*
        |--------------------------------------------------------------------------
        | No Company Context
        |--------------------------------------------------------------------------
        */

        if (
            !$activeCompanyId
        ) {

            return [];
        }


        /*
        |--------------------------------------------------------------------------
        | Current Branch
        |--------------------------------------------------------------------------
        */

        $activeStoreId =
            $request
            ->session()
            ->get(
                'active_store_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $user->is_super_user
        ) {

            return Store::query()
                ->where(
                    'company_id',
                    $activeCompanyId
                )
                ->where(
                    'status',
                    1
                )
                ->orderBy(
                    'store_name'
                )
                ->get([
                    'id',
                    'store_code',
                    'store_name',
                    'city',
                    'state',
                ])
                ->map(
                    function (
                        Store $store
                    ) use (
                        $activeStoreId
                    ) {

                        return [

                            'id' =>
                            $store->id,

                            'store_code' =>
                            $store->store_code,

                            'store_name' =>
                            $store->store_name,

                            'city' =>
                            $store->city,

                            'state' =>
                            $store->state,

                            'is_current' =>
                            (int) $activeStoreId ===
                                (int) $store->id,
                        ];
                    }
                )
                ->values()
                ->all();
        }


        // Company Admin and Store Manager require explicit membership.
        if ((int) $activeCompanyId !== (int) $user->company_id) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Branch Admin / Normal Business User
        |--------------------------------------------------------------------------
        */

        return $user
            ->stores()
            ->where(
                'stores.company_id',
                $user->company_id
            )
            ->where(
                'stores.status',
                1
            )
            ->orderBy(
                'stores.store_name'
            )
            ->get([
                'stores.id',
                'stores.store_code',
                'stores.store_name',
                'stores.city',
                'stores.state',
            ])
            ->map(
                function (
                    $store
                ) use (
                    $activeStoreId
                ) {

                    return [

                        'id' =>
                        $store->id,

                        'store_code' =>
                        $store->store_code,

                        'store_name' =>
                        $store->store_name,

                        'city' =>
                        $store->city,

                        'state' =>
                        $store->state,

                        'is_current' =>
                        (int) $activeStoreId ===
                            (int) $store->id,
                    ];
                }
            )
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Laravel notifications are already attached to the recipient user.
    |
    | Therefore:
    |
    | Super Admin
    |     -> sees notifications sent to Super Admin
    |
    | Company Admin
    |     -> sees notifications sent to that Company Admin
    |
    | Branch Admin
    |     -> sees notifications sent to that Branch Admin
    |
    | Normal User
    |     -> sees notifications sent to that user
    |
    */

    private function getNotifications(
        $user,
        Request $request
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Guest
        |--------------------------------------------------------------------------
        */

        if (
            !$user
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
        | Business Context
        |--------------------------------------------------------------------------
        */

        $activeCompanyId =
            $request
            ->session()
            ->get(
                'active_company_id'
            );


        $activeStoreId =
            $request
            ->session()
            ->get(
                'active_store_id'
            );


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
                10
            )
            ->get()
            ->map(
                function (
                    $notification
                ) use (
                    $activeCompanyId,
                    $activeStoreId
                ) {

                    /*
                        |--------------------------------------------------------------------------
                        | Notification Data
                        |--------------------------------------------------------------------------
                        */

                    $data =
                        $notification->data
                        ?? [];


                    /*
                        |--------------------------------------------------------------------------
                        | Notification Company / Branch
                        |--------------------------------------------------------------------------
                        |
                        | Optional values.
                        |
                        | Notifications that don't contain these values continue
                        | to work normally.
                        |
                        */

                    $notificationCompanyId =
                        $data['company_id']
                        ?? null;


                    $notificationStoreId =
                        $data['store_id']
                        ?? null;


                    /*
                        |--------------------------------------------------------------------------
                        | Navigation URL
                        |--------------------------------------------------------------------------
                        */

                    $fallbackUrl =
                        $data['fallback_url']
                        ?? $data['url']
                        ?? null;


                    /*
                        |--------------------------------------------------------------------------
                        | Return
                        |--------------------------------------------------------------------------
                        */

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
                            | Company / Branch Context
                            |--------------------------------------------------------------------------
                            */

                        'company_id' =>
                        $notificationCompanyId,

                        'store_id' =>
                        $notificationStoreId,

                        'belongs_to_active_company' =>
                        $notificationCompanyId === null
                            ||
                            (
                                (int) $notificationCompanyId ===
                                (int) $activeCompanyId
                            ),

                        'belongs_to_active_branch' =>
                        $notificationStoreId === null
                            ||
                            (
                                (int) $notificationStoreId ===
                                (int) $activeStoreId
                            ),


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
                        $fallbackUrl,


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
        |
        | Count unread notifications belonging to the logged-in user.
        |
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
