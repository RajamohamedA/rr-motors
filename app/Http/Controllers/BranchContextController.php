<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Store;
use App\Support\AppResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchContextController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Super Admin Enters Company / Branch
    |--------------------------------------------------------------------------
    */

    public function enter(
        Company $company,
        Store $store
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            auth()->user();


        /*
        |--------------------------------------------------------------------------
        | Super Admin Only
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user &&
                (bool) $user->is_super_user,
            403,
            'Only the Super Administrator can enter a company from the platform panel.'
        );


        /*
        |--------------------------------------------------------------------------
        | Branch Must Belong To Company
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $store->company_id ===
                (int) $company->id,
            404,
            'The selected branch does not belong to this company.'
        );


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $company->status
        ) {

            return AppResponse::error(
                'The company is inactive. Activate the company before entering this branch.'
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

            return AppResponse::error(
                'The branch is inactive. Activate the branch before entering it.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Set Super Admin Business Context
        |--------------------------------------------------------------------------
        */

        session([

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            'active_company_id' =>
            $company->id,

            'active_company_name' =>
            $company->company_name,

            'active_company_code' =>
            $company->company_code,


            /*
            |--------------------------------------------------------------------------
            | Branch
            |--------------------------------------------------------------------------
            */

            'active_store_id' =>
            $store->id,

            'active_store_name' =>
            $store->store_name,

            'active_store_code' =>
            $store->store_code,


            /*
            |--------------------------------------------------------------------------
            | Super Admin Context Flag
            |--------------------------------------------------------------------------
            */

            'super_admin_business_context' =>
            true,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Business Dashboard
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'business.dashboard'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Company Admin / Normal User Selects Branch
    |--------------------------------------------------------------------------
    */

    public function select(
        Store $store
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            auth()->user();


        abort_unless(
            $user,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | Load Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()
            ->find(
                $store->company_id
            );


        /*
        |--------------------------------------------------------------------------
        | Company Must Exist
        |--------------------------------------------------------------------------
        */

        if (
            !$company
        ) {

            return AppResponse::error(
                'The company assigned to this branch could not be found.'
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

            return AppResponse::error(
                'The company is inactive. Please contact the administrator.'
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

            return AppResponse::error(
                'This branch is inactive. Please select another active branch.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $user->is_super_user
        ) {

            session([

                /*
                |--------------------------------------------------------------------------
                | Company
                |--------------------------------------------------------------------------
                */

                'active_company_id' =>
                $company->id,

                'active_company_name' =>
                $company->company_name,

                'active_company_code' =>
                $company->company_code,


                /*
                |--------------------------------------------------------------------------
                | Branch
                |--------------------------------------------------------------------------
                */

                'active_store_id' =>
                $store->id,

                'active_store_name' =>
                $store->store_name,

                'active_store_code' =>
                $store->store_code,


                /*
                |--------------------------------------------------------------------------
                | Super Admin Context
                |--------------------------------------------------------------------------
                */

                'super_admin_business_context' =>
                true,
            ]);


            return redirect()
                ->route(
                    'business.dashboard'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | User Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $user->status
        ) {

            return AppResponse::error(
                'Your account is inactive. Please contact the administrator.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | User Must Belong To Company
        |--------------------------------------------------------------------------
        */

        if (
            (int) $user->company_id !==
            (int) $company->id
        ) {

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
        | Company Admin may access any ACTIVE branch belonging
        | to their own company.
        |
        */

        if (
            $user->hasRole(
                'Company Admin'
            )
        ) {

            abort_unless(
                (int) $store->company_id ===
                    (int) $user->company_id,
                403,
                'You do not have access to this branch.'
            );
        } else {

            /*
            |--------------------------------------------------------------------------
            | Branch Admin / Normal Business User
            |--------------------------------------------------------------------------
            |
            | Non Company-Admin users must have explicit branch assignment
            | through user_stores.
            |
            */

            $hasAccess =
                $user
                ->stores()
                ->where(
                    'stores.id',
                    $store->id
                )
                ->where(
                    'stores.company_id',
                    $user->company_id
                )
                ->where(
                    'stores.status',
                    true
                )
                ->exists();


            if (
                !$hasAccess
            ) {

                return AppResponse::error(
                    'You do not have access to this branch.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Set Business Context
        |--------------------------------------------------------------------------
        */

        session([

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            'active_company_id' =>
            $company->id,

            'active_company_name' =>
            $company->company_name,

            'active_company_code' =>
            $company->company_code,


            /*
            |--------------------------------------------------------------------------
            | Branch
            |--------------------------------------------------------------------------
            */

            'active_store_id' =>
            $store->id,

            'active_store_name' =>
            $store->store_name,

            'active_store_code' =>
            $store->store_code,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Remove Super Admin Context Flag
        |--------------------------------------------------------------------------
        */

        session()
            ->forget(
                'super_admin_business_context'
            );


        /*
        |--------------------------------------------------------------------------
        | Business Dashboard
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'business.dashboard'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Exit Branch / Company Context
    |--------------------------------------------------------------------------
    */

    public function exit(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        abort_unless(
            $user,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | Clear Complete Business Context
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget([

                /*
                |--------------------------------------------------------------------------
                | Company
                |--------------------------------------------------------------------------
                */

                'active_company_id',
                'active_company_name',
                'active_company_code',


                /*
                |--------------------------------------------------------------------------
                | Branch
                |--------------------------------------------------------------------------
                */

                'active_store_id',
                'active_store_name',
                'active_store_code',


                /*
                |--------------------------------------------------------------------------
                | Super Admin Context
                |--------------------------------------------------------------------------
                */

                'super_admin_business_context',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $user->is_super_user
        ) {

            return redirect()
                ->route(
                    'super-admin.dashboard'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | User Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $user->status
        ) {

            abort(
                403,
                'Your account is currently inactive.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Admin
        |--------------------------------------------------------------------------
        |
        | Company Admin is leaving only the current branch workspace.
        |
        | They remain logged in and return to Company Dashboard.
        |
        */

        if (
            $user->hasRole(
                'Company Admin'
            )
        ) {

            /*
            |--------------------------------------------------------------------------
            | Load Company
            |--------------------------------------------------------------------------
            */

            $company =
                Company::query()
                ->find(
                    $user->company_id
                );


            /*
            |--------------------------------------------------------------------------
            | Company Must Exist
            |--------------------------------------------------------------------------
            */

            if (
                !$company
            ) {

                abort(
                    404,
                    'Company could not be found.'
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

                abort(
                    403,
                    'Your company is currently inactive.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Restore Company-Only Context
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | No branch is selected here.
            |
            */

            session([
                'active_company_id' =>
                $company->id,

                'active_company_name' =>
                $company->company_name,

                'active_company_code' =>
                $company->company_code,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Make Sure Branch Context Is Empty
            |--------------------------------------------------------------------------
            */

            session()->forget([
                'active_store_id',
                'active_store_name',
                'active_store_code',
                'super_admin_business_context',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Company Dashboard
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'business.company.dashboard'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Branch Admin / Normal User Active Branch Count
        |--------------------------------------------------------------------------
        */

        $activeStores =
            $user
            ->stores()
            ->where(
                'stores.company_id',
                $user->company_id
            )
            ->where(
                'stores.status',
                true
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | No Active Assigned Branch
        |--------------------------------------------------------------------------
        */

        if (
            $activeStores === 0
        ) {

            abort(
                403,
                'No active branch is assigned to this user.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Multiple Active Branches
        |--------------------------------------------------------------------------
        */

        if (
            $activeStores > 1
        ) {

            /*
            |--------------------------------------------------------------------------
            | Restore Company Context
            |--------------------------------------------------------------------------
            |
            | This allows branch-selection UI to display the company identity.
            |
            */

            $company =
                Company::query()
                ->find(
                    $user->company_id
                );


            if (
                $company &&
                (bool) $company->status
            ) {

                session([
                    'active_company_id' =>
                    $company->id,

                    'active_company_name' =>
                    $company->company_name,

                    'active_company_code' =>
                    $company->company_code,
                ]);
            }


            return redirect()
                ->route(
                    'branch.select'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Single Branch User
        |--------------------------------------------------------------------------
        |
        | LoginRedirectController will automatically restore the user's only
        | active assigned branch and redirect them back to Business Dashboard.
        |
        */

        return redirect()
            ->route(
                'login.redirect'
            );
    }
}
