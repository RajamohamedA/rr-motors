<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LoginRedirectController extends Controller
{
    public function handle(): RedirectResponse
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($user->is_super_user) {

            return redirect()->route(
                'super-admin.dashboard'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Owner
        |--------------------------------------------------------------------------
        |
        | Company Owner can access all active branches
        | belonging to their own company.
        |
        */

        if ($user->hasRole('company-owner')) {

            $stores = $user->company
                ->stores()
                ->where('status', 1)
                ->orderBy('store_name')
                ->get();


            /*
            |--------------------------------------------------------------------------
            | No Branch
            |--------------------------------------------------------------------------
            */

            if ($stores->isEmpty()) {

                abort(
                    403,
                    'No active branch is available for this company.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | One Branch
            |--------------------------------------------------------------------------
            */

            if ($stores->count() === 1) {

                $store = $stores->first();

                session([
                    'active_company_id' =>
                    $user->company_id,

                    'active_store_id' =>
                    $store->id,
                ]);

                return redirect()->route(
                    'business.dashboard'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Multiple Branches
            |--------------------------------------------------------------------------
            */

            return redirect()->route(
                'branch.select'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Normal User
        |--------------------------------------------------------------------------
        |
        | Normal users can access only branches assigned
        | through user_stores.
        |
        */

        $stores = $user->stores()
            ->where(
                'stores.status',
                1
            )
            ->where(
                'stores.company_id',
                $user->company_id
            )
            ->orderBy(
                'store_name'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | No Assigned Branch
        |--------------------------------------------------------------------------
        */

        if ($stores->isEmpty()) {

            abort(
                403,
                'No active branch is assigned to this user.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | One Branch
        |--------------------------------------------------------------------------
        */

        if ($stores->count() === 1) {

            $store = $stores->first();

            session([
                'active_company_id' =>
                $user->company_id,

                'active_store_id' =>
                $store->id,
            ]);

            return redirect()->route(
                'business.dashboard'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Multiple Branches
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'branch.select'
        );
    }
}
