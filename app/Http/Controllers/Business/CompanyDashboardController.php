<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;

use Illuminate\Http\Request;

use Inertia\Inertia;
use Inertia\Response;


class CompanyDashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Company Dashboard
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

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

            abort(
                401
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Resolve Company
        |--------------------------------------------------------------------------
        |
        | Super Admin:
        |   Uses active company selected in business context.
        |
        | Company Admin:
        |   Uses company assigned to authenticated user.
        |
        */

        $company =
            $this->resolveCompany(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Company Status
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $company->status
        ) {

            abort(
                403,
                $user->is_super_user
                    ? 'The selected company is currently inactive.'
                    : 'Your company is currently inactive.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Store Summary
        |--------------------------------------------------------------------------
        */

        $storesQuery =
            Store::query()

            ->where(
                'company_id',
                $company->id
            );


        $totalBranches =
            (clone $storesQuery)
            ->count();


        $activeBranches =
            (clone $storesQuery)

            ->where(
                'status',
                true
            )

            ->count();


        $pendingBranches =
            (clone $storesQuery)

            ->where(
                'status',
                false
            )

            ->count();


        /*
        |--------------------------------------------------------------------------
        | User Summary
        |--------------------------------------------------------------------------
        */

        $usersQuery =
            User::query()

            ->where(
                'company_id',
                $company->id
            )

            ->where(
                'is_super_user',
                false
            );


        $totalUsers =
            (clone $usersQuery)
            ->count();


        $activeUsers =
            (clone $usersQuery)

            ->where(
                'status',
                true
            )

            ->count();


        $inactiveUsers =
            (clone $usersQuery)

            ->where(
                'status',
                false
            )

            ->count();


        /*
        |--------------------------------------------------------------------------
        | Branch List
        |--------------------------------------------------------------------------
        */

        $branches =
            Store::query()

            ->where(
                'company_id',
                $company->id
            )

            ->orderByDesc(
                'status'
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
                'status',
                'created_at',
            ])

            ->map(
                function (
                    Store $store
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

                        'status' =>
                        (bool) $store->status,

                        'status_label' =>
                        $store->status
                            ? 'Active'
                            : 'Pending Approval',

                        'created_at' =>
                        $store->created_at
                            ?->format(
                                'd M Y'
                            ),
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Current Branch
        |--------------------------------------------------------------------------
        */

        $activeStoreId =
            (int) session(
                'active_store_id'
            );


        $activeBranch =
            null;


        if (
            $activeStoreId
        ) {

            $store =
                Store::query()

                ->where(
                    'company_id',
                    $company->id
                )

                ->whereKey(
                    $activeStoreId
                )

                ->first();


            if (
                $store
            ) {

                $activeBranch = [

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

                    'status' =>
                    (bool) $store->status,
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Business/CompanyDashboard',
            [

                /*
                |--------------------------------------------------------------------------
                | Access Context
                |--------------------------------------------------------------------------
                */

                'context' => [

                    'is_super_admin' =>
                    (bool) $user->is_super_user,

                    'is_company_admin' =>
                    $user->hasRole(
                        'Company Admin'
                    ),

                    'is_super_admin_context' =>
                    (bool) session(
                        'super_admin_business_context',
                        false
                    ),
                ],


                /*
                |--------------------------------------------------------------------------
                | Company
                |--------------------------------------------------------------------------
                */

                'company' => [

                    'id' =>
                    $company->id,

                    'company_name' =>
                    $company->company_name,

                    'company_code' =>
                    $company->company_code,

                    'owner_name' =>
                    $company->owner_name,

                    'email' =>
                    $company->email,

                    'phone' =>
                    $company->phone,

                    'city' =>
                    $company->city,

                    'state' =>
                    $company->state,

                    'status' =>
                    (bool) $company->status,
                ],


                /*
                |--------------------------------------------------------------------------
                | Active Branch
                |--------------------------------------------------------------------------
                */

                'activeBranch' =>
                $activeBranch,


                /*
                |--------------------------------------------------------------------------
                | Branch Summary
                |--------------------------------------------------------------------------
                */

                'branchSummary' => [

                    'total' =>
                    $totalBranches,

                    'active' =>
                    $activeBranches,

                    'pending' =>
                    $pendingBranches,
                ],


                /*
                |--------------------------------------------------------------------------
                | User Summary
                |--------------------------------------------------------------------------
                */

                'userSummary' => [

                    'total' =>
                    $totalUsers,

                    'active' =>
                    $activeUsers,

                    'inactive' =>
                    $inactiveUsers,
                ],


                /*
                |--------------------------------------------------------------------------
                | Branches
                |--------------------------------------------------------------------------
                */

                'branches' =>
                $branches,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Accessible Company
    |--------------------------------------------------------------------------
    */

    private function resolveCompany(
        Request $request
    ): Company {

        $user =
            $request->user();


        if (
            !$user
        ) {

            abort(
                401
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

            $companyId =
                (int) session(
                    'active_company_id'
                );


            if (
                !$companyId
            ) {

                abort(
                    403,
                    'No active company is selected.'
                );
            }


            return Company::query()

                ->whereKey(
                    $companyId
                )

                ->firstOrFail();
        }


        /*
        |--------------------------------------------------------------------------
        | Company Admin
        |--------------------------------------------------------------------------
        */

        if (
            !$user->hasRole(
                'Company Admin'
            )
        ) {

            abort(
                403,
                'Only Company Admin can access the company dashboard.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Own Company
        |--------------------------------------------------------------------------
        */

        $companyId =
            (int) $user->company_id;


        if (
            !$companyId
        ) {

            abort(
                403,
                'No company is assigned to this account.'
            );
        }


        return Company::query()

            ->whereKey(
                $companyId
            )

            ->firstOrFail();
    }
}
