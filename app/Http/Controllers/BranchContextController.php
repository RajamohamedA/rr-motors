<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;

class BranchContextController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Super Admin enters a branch
    |--------------------------------------------------------------------------
    */

    public function enter(Company $company, Store $store): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($user->is_super_user, 403);

        abort_unless(
            (int) $store->company_id === (int) $company->id,
            404
        );

        session([
            'active_company_id' => $company->id,
            'active_store_id' => $store->id,
        ]);

        return redirect()->route('business.dashboard');
    }


    /*
    |--------------------------------------------------------------------------
    | Company Owner / Normal User selects a branch
    |--------------------------------------------------------------------------
    */

    public function select(Store $store): RedirectResponse
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($user->is_super_user) {

            abort_unless(
                $store->company_id !== null,
                404
            );

            session([
                'active_company_id' => $store->company_id,
                'active_store_id' => $store->id,
            ]);

            return redirect()->route('business.dashboard');
        }


        /*
        |--------------------------------------------------------------------------
        | Company Owner
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('company-owner')) {

            abort_unless(
                (int) $store->company_id === (int) $user->company_id,
                403
            );
        } else {

            /*
            |--------------------------------------------------------------------------
            | Normal User
            |--------------------------------------------------------------------------
            |
            | Must have this branch assigned in user_stores.
            |
            */

            $hasAccess = $user->stores()
                ->where('stores.id', $store->id)
                ->where('stores.company_id', $user->company_id)
                ->where('stores.status', 1)
                ->exists();

            abort_unless($hasAccess, 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Set Active Context
        |--------------------------------------------------------------------------
        */

        session([
            'active_company_id' => $store->company_id,
            'active_store_id' => $store->id,
        ]);

        return redirect()->route('business.dashboard');
    }


    /*
    |--------------------------------------------------------------------------
    | Exit Branch
    |--------------------------------------------------------------------------
    */

    public function exit(): RedirectResponse
    {
        session()->forget([
            'active_company_id',
            'active_store_id',
        ]);

        return redirect()->route('dashboard');
    }
}
