<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([

            /*
            |--------------------------------------------------------------------------
            | Authenticated User
            |--------------------------------------------------------------------------
            */

            'auth' => function () {

                $user = auth()->user();

                if (!$user) {
                    return null;
                }

                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                        'is_super_user' => (bool) $user->is_super_user,
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | Roles
                    |--------------------------------------------------------------------------
                    */

                    'roles' => $user->getRoleNames()->values(),

                    /*
                    |--------------------------------------------------------------------------
                    | Permissions
                    |--------------------------------------------------------------------------
                    */

                    'permissions' => $user->getAllPermissions()
                        ->pluck('name')
                        ->values(),
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
                    ->get('active_company');
            },


            /*
            |--------------------------------------------------------------------------
            | Active Branch
            |--------------------------------------------------------------------------
            */

            'activeStore' => function () {

                return request()
                    ->attributes
                    ->get('active_store');
            },


            /*
            |--------------------------------------------------------------------------
            | Available store
            |--------------------------------------------------------------------------
            */
            'availableStores' => function () {

                $user = auth()->user();

                if (!$user) {
                    return [];
                }

                $companyId = session('active_company_id');

                if (!$companyId) {
                    return [];
                }

                /*
                |--------------------------------------------------------------------------
                | Super Admin
                |--------------------------------------------------------------------------
                */

                if ($user->is_super_user) {

                    return \App\Models\Store::query()
                        ->where('company_id', $companyId)
                        ->where('status', 1)
                        ->orderBy('store_name')
                        ->get([
                            'id',
                            'store_code',
                            'store_name',
                            'company_id',
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Company Owner
                |--------------------------------------------------------------------------
                */

                if ($user->hasRole('company-owner')) {

                    return $user->company
                        ->stores()
                        ->where('status', 1)
                        ->orderBy('store_name')
                        ->get([
                            'id',
                            'store_code',
                            'store_name',
                            'company_id',
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Normal User
                |--------------------------------------------------------------------------
                */

                return $user->stores()
                    ->where('stores.company_id', $companyId)
                    ->where('stores.status', 1)
                    ->orderBy('store_name')
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
