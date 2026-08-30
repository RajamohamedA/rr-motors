<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessContextMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        if (
            !$user
        ) {

            return redirect()
                ->route(
                    'login'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | User Must Be Active
        |--------------------------------------------------------------------------
        |
        | Super Admin is handled separately below because the platform account
        | may enter a company through Super Admin business context.
        |
        */

        if (
            !(bool) $user->is_super_user &&
            !(bool) $user->status
        ) {

            abort(
                403,
                'Your user account is currently inactive.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Active Company Context Required
        |--------------------------------------------------------------------------
        */

        $companyId =
            session(
                'active_company_id'
            );


        if (
            !$companyId
        ) {

            return redirect()
                ->route(
                    'login.redirect'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()

            ->whereKey(
                $companyId
            )

            ->first();


        if (
            !$company
        ) {

            $this->clearBusinessContext(
                $request
            );


            abort(
                403,
                'The selected company is no longer available.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $company->status
        ) {

            $this->clearBusinessContext(
                $request
            );


            abort(
                403,
                'The selected company is currently inactive.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Active Branch Context Required
        |--------------------------------------------------------------------------
        |
        | business.context is intended for operational business routes.
        |
        | Company-only routes such as:
        |
        | /company/dashboard
        | /company/profile
        | /company/users
        | /company/roles
        | /company/branches
        |
        | should NOT use this middleware.
        |
        */

        $storeId =
            session(
                'active_store_id'
            );


        if (
            !$storeId
        ) {

            /*
            |--------------------------------------------------------------------------
            | Company Admin
            |--------------------------------------------------------------------------
            |
            | Company Admin may legitimately be in company-only context without
            | a branch selected.
            |
            */

            if (
                !$user->is_super_user &&
                $user->hasRole(
                    'Company Admin'
                )
            ) {

                return redirect()
                    ->route(
                        'branch.select'
                    );
            }


            return redirect()
                ->route(
                    'login.redirect'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Branch
        |--------------------------------------------------------------------------
        */

        $store =
            Store::query()

            ->whereKey(
                $storeId
            )

            ->where(
                'company_id',
                $company->id
            )

            ->first();


        if (
            !$store
        ) {

            $this->clearStoreContext(
                $request
            );


            return redirect()
                ->route(
                    'login.redirect'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Branch Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $store->status
        ) {

            $this->clearStoreContext(
                $request
            );


            /*
            |--------------------------------------------------------------------------
            | Company Admin
            |--------------------------------------------------------------------------
            */

            if (
                !$user->is_super_user &&
                $user->hasRole(
                    'Company Admin'
                )
            ) {

                return redirect()
                    ->route(
                        'branch.select'
                    );
            }


            return redirect()
                ->route(
                    'login.redirect'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Super Admin Business Context
        |--------------------------------------------------------------------------
        |
        | Super Admin may enter any active company + active branch through the
        | platform panel.
        |
        */

        if (
            (bool) $user->is_super_user
        ) {

            /*
            |--------------------------------------------------------------------------
            | Must Be Explicit Super Admin Business Context
            |--------------------------------------------------------------------------
            */

            if (
                !session(
                    'super_admin_business_context',
                    false
                )
            ) {

                $this->clearBusinessContext(
                    $request
                );


                return redirect()
                    ->route(
                        'super-admin.dashboard'
                    );
            }


            return $next(
                $request
            );
        }


        /*
        |--------------------------------------------------------------------------
        | User Company Isolation
        |--------------------------------------------------------------------------
        */

        if (
            (int) $user->company_id !==
            (int) $company->id
        ) {

            $this->clearBusinessContext(
                $request
            );


            abort(
                403,
                'You do not have access to this company.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Admin
        |--------------------------------------------------------------------------
        |
        | Company Admin can access any ACTIVE branch of their own company.
        |
        */

        if (
            $user->hasRole(
                'Company Admin'
            )
        ) {

            return $next(
                $request
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Normal Employee Branch Access
        |--------------------------------------------------------------------------
        |
        | Store Manager
        | Sales User
        | Purchase User
        | Inventory User
        | Accounts User
        | Read Only
        | Company Custom Roles
        |
        | must have explicit user_stores assignment.
        |
        */

        $hasBranchAccess =
            $user
            ->stores()

            ->where(
                'stores.id',
                $store->id
            )

            ->where(
                'stores.company_id',
                $company->id
            )

            ->where(
                'stores.status',
                true
            )

            ->exists();


        if (
            !$hasBranchAccess
        ) {

            $this->clearStoreContext(
                $request
            );


            abort(
                403,
                'You no longer have access to this branch.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Continue
        |--------------------------------------------------------------------------
        */

        return $next(
            $request
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Store Context
    |--------------------------------------------------------------------------
    */

    private function clearStoreContext(
        Request $request
    ): void {

        $request
            ->session()
            ->forget([
                'active_store_id',
                'active_store_name',
                'active_store_code',
                'super_admin_business_context',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Complete Business Context
    |--------------------------------------------------------------------------
    */

    private function clearBusinessContext(
        Request $request
    ): void {

        $request
            ->session()
            ->forget([
                'active_company_id',
                'active_company_name',
                'active_company_code',

                'active_store_id',
                'active_store_name',
                'active_store_code',

                'super_admin_business_context',
            ]);
    }
}
