<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;

class LoginRedirectController extends Controller
{
    public function handle(): RedirectResponse
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Validate User
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return redirect()->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Clear Old Business Context
        |--------------------------------------------------------------------------
        |
        | This prevents a previous company / branch session from remaining
        | when another user logs in.
        |
        */

        session()->forget([
            'active_company_id',
            'active_company_name',
            'active_company_code',

            'active_store_id',
            'active_store_name',
            'active_store_code',

            'super_admin_business_context',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Super Admin does not automatically enter a company or branch.
        |
        */

        if ($user->is_super_user) {
            return redirect()->route(
                'super-admin.dashboard'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Check User Status
        |--------------------------------------------------------------------------
        */

        if (!(bool) $user->status) {
            abort(
                403,
                'Your user account is currently inactive.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company = Company::query()
            ->whereKey($user->company_id)
            ->first();

        if (!$company) {
            abort(
                403,
                'Company information could not be found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Check Company Status
        |--------------------------------------------------------------------------
        */

        if (!(bool) $company->status) {
            abort(
                403,
                'Your company is currently inactive.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Save Company Context
        |--------------------------------------------------------------------------
        |
        | Company information is available even when no branch is selected.
        |
        */

        $this->setCompanyContext($company);

        /*
        |--------------------------------------------------------------------------
        | Company Admin
        |--------------------------------------------------------------------------
        |
        | Company Admin:
        |
        | 0 active branches
        |      -> Company dashboard
        |
        | 1 active branch
        |      -> Automatically enter branch
        |
        | 2+ active branches
        |      -> Branch selection
        |
        */

        if ($user->hasRole('Company Admin')) {

            $stores = Store::query()
                ->where(
                    'company_id',
                    $company->id
                )
                ->where(
                    'status',
                    1
                )
                ->orderBy(
                    'store_name'
                )
                ->get();

            /*
            |--------------------------------------------------------------------------
            | No Active Branch
            |--------------------------------------------------------------------------
            */

            if ($stores->isEmpty()) {

                $this->clearStoreContext();

                /*
                |--------------------------------------------------------------------------
                | Company Dashboard
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                |
                | This route must work without active_store_id.
                |
                | If your route name is different, change only this route name.
                |
                */

                return redirect()->route(
                    'business.company.dashboard'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | One Active Branch
            |--------------------------------------------------------------------------
            */

            if ($stores->count() === 1) {

                $store = $stores->first();

                $this->setStoreContext(
                    $company,
                    $store
                );

                return redirect()->route(
                    'business.dashboard'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Multiple Active Branches
            |--------------------------------------------------------------------------
            */

            $this->clearStoreContext();

            return redirect()->route(
                'branch.select'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Branch Admin / Store Manager / Normal User
        |--------------------------------------------------------------------------
        |
        | These users can access only branches explicitly assigned through
        | user_stores.
        |
        */

        $stores = $user->stores()
            ->where(
                'stores.status',
                1
            )
            ->where(
                'stores.company_id',
                $company->id
            )
            ->orderBy(
                'stores.store_name'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | No Assigned Branch
        |--------------------------------------------------------------------------
        */

        if ($stores->isEmpty()) {

            $this->clearStoreContext();

            abort(
                403,
                'No active branch is assigned to this user.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | One Assigned Branch
        |--------------------------------------------------------------------------
        */

        if ($stores->count() === 1) {

            $store = $stores->first();

            $this->setStoreContext(
                $company,
                $store
            );

            return redirect()->route(
                'business.dashboard'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Multiple Assigned Branches
        |--------------------------------------------------------------------------
        */

        $this->clearStoreContext();

        return redirect()->route(
            'branch.select'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Set Company Context
    |--------------------------------------------------------------------------
    */

    private function setCompanyContext(
        Company $company
    ): void {

        session([
            'active_company_id' =>
            $company->id,

            'active_company_name' =>
            $company->company_name,

            'active_company_code' =>
            $company->company_code,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Set Branch Context
    |--------------------------------------------------------------------------
    */

    private function setStoreContext(
        Company $company,
        Store $store
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Safety Check
        |--------------------------------------------------------------------------
        */

        if (
            (int) $store->company_id !==
            (int) $company->id
        ) {
            abort(
                403,
                'The selected branch does not belong to this company.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $this->setCompanyContext(
            $company
        );

        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        */

        session([
            'active_store_id' =>
            $store->id,

            'active_store_name' =>
            $store->store_name,

            'active_store_code' =>
            $store->store_code,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Ensure Normal Business Context
        |--------------------------------------------------------------------------
        */

        session()->forget(
            'super_admin_business_context'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Clear Branch Context
    |--------------------------------------------------------------------------
    */

    private function clearStoreContext(): void
    {
        session()->forget([
            'active_store_id',
            'active_store_name',
            'active_store_code',
            'super_admin_business_context',
        ]);
    }
}
