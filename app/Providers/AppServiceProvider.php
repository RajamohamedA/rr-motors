<?php

namespace App\Providers;

use App\Contracts\GstProviderInterface;
use App\Models\Store;
use App\Services\Gst\ApiGstProvider;
use App\Services\Gst\MockGstProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | GST Provider Binding
        |--------------------------------------------------------------------------
        |
        | GST_PROVIDER=mock
        |
        | or
        |
        | GST_PROVIDER=api
        |
        | CustomerController and GstLookupService do not need to know
        | which provider is being used.
        |
        */

        $this->app->bind(
            GstProviderInterface::class,
            function () {

                $provider =
                    config(
                        'gst.provider',
                        'mock'
                    );


                return match ($provider) {

                    'mock' =>
                    app(
                        MockGstProvider::class
                    ),

                    'api' =>
                    app(
                        ApiGstProvider::class
                    ),

                    default =>
                    throw new RuntimeException(
                        "Unsupported GST provider: {$provider}"
                    ),
                };
            }
        );
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use the trusted account flag, not a role name that companies can edit.
        // Other users must continue through their normal permission checks.
        Gate::before(function ($user, string $ability): ?bool {
            return (bool) $user->is_super_user && (bool) $user->status
                ? true
                : null;
        });

        Inertia::share([

            /*
            |--------------------------------------------------------------------------
            | Authenticated User
            |--------------------------------------------------------------------------
            */

            'auth' => function () {

                $user =
                    auth()->user();


                if (
                    !$user
                ) {

                    return null;
                }


                return [

                    'user' => [

                        'id' =>
                        $user->id,

                        'name' =>
                        $user->name,

                        'email' =>
                        $user->email,

                        'mobile' =>
                        $user->mobile,

                        'company_id' =>
                        $user->company_id,

                        'store_id' =>
                        $user->store_id,

                        'status' =>
                        (bool) $user->status,

                        'is_super_user' =>
                        (bool) $user->is_super_user,

                        /*
                        |--------------------------------------------------------------------------
                        | Roles
                        |--------------------------------------------------------------------------
                        |
                        | These are also included inside auth.user because
                        | Business Sidebar and page-level permission checks
                        | commonly read:
                        |
                        | auth.user.roles
                        |
                        */

                        'roles' =>
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
                        $user
                            ->getAllPermissions()
                            ->pluck(
                                'name'
                            )
                            ->values()
                            ->all(),
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Legacy Top-Level Roles
                    |--------------------------------------------------------------------------
                    |
                    | Keep these for existing React pages that may still use:
                    |
                    | auth.roles
                    |
                    */

                    'roles' =>
                    $user
                        ->getRoleNames()
                        ->values()
                        ->all(),


                    /*
                    |--------------------------------------------------------------------------
                    | Legacy Top-Level Permissions
                    |--------------------------------------------------------------------------
                    */

                    'permissions' =>
                    $user
                        ->getAllPermissions()
                        ->pluck(
                            'name'
                        )
                        ->values()
                        ->all(),
                ];
            },


            /*
            |--------------------------------------------------------------------------
            | Active Company
            |--------------------------------------------------------------------------
            */

            'activeCompany' => function () {

                return request()
                    ->attributes
                    ->get(
                        'active_company'
                    );
            },


            /*
            |--------------------------------------------------------------------------
            | Active Branch
            |--------------------------------------------------------------------------
            */

            'activeStore' => function () {

                return request()
                    ->attributes
                    ->get(
                        'active_store'
                    );
            },


            /*
            |--------------------------------------------------------------------------
            | Available Stores
            |--------------------------------------------------------------------------
            */

            'availableStores' => function () {

                $user =
                    auth()->user();


                if (
                    !$user
                ) {

                    return [];
                }


                $companyId =
                    (int) session(
                        'active_company_id'
                    );


                if (
                    !$companyId
                ) {

                    return [];
                }


                /*
                |--------------------------------------------------------------------------
                | Super Admin
                |--------------------------------------------------------------------------
                |
                | Super Admin can access all active branches of the
                | currently selected company.
                |
                */

                if (
                    $user->is_super_user
                ) {

                    return Store::query()

                        ->where(
                            'company_id',
                            $companyId
                        )

                        ->where(
                            'status',
                            true
                        )

                        ->orderBy(
                            'store_name'
                        )

                        ->get([
                            'id',
                            'store_code',
                            'store_name',
                            'company_id',
                        ]);
                }


                // All company users, including Company Admin, may only see
                // explicitly assigned branches within their own company.
                if ((int) $user->company_id !== $companyId) {
                    return [];
                }

                return $user
                    ->stores()

                    ->where(
                        'stores.company_id',
                        $companyId
                    )

                    ->where(
                        'stores.status',
                        true
                    )

                    ->orderBy(
                        'stores.store_name'
                    )

                    ->get([
                        'stores.id',
                        'stores.store_code',
                        'stores.store_name',
                        'stores.company_id',
                    ]);
            },

        ]);
    }
}
