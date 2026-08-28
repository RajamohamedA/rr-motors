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
    public function index(Request $request): Response
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            abort(401);
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Super Admin should use Super Admin dashboard instead.
        |
        */

        if ($user->is_super_user) {
            abort(
                403,
                'Super Admin should use the Super Admin dashboard.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Company Admin Only
        |--------------------------------------------------------------------------
        */

        if (!$user->hasRole('Company Admin')) {
            abort(
                403,
                'Only Company Admin can access the company dashboard.'
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
                404,
                'Company not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Company Status
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
        | Store Summary
        |--------------------------------------------------------------------------
        */

        $storesQuery = Store::query()
            ->where(
                'company_id',
                $company->id
            );

        $totalBranches = (clone $storesQuery)
            ->count();

        $activeBranches = (clone $storesQuery)
            ->where(
                'status',
                1
            )
            ->count();

        $pendingBranches = (clone $storesQuery)
            ->where(
                'status',
                0
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | User Summary
        |--------------------------------------------------------------------------
        */

        $usersQuery = User::query()
            ->where(
                'company_id',
                $company->id
            )
            ->where(
                'is_super_user',
                false
            );

        $totalUsers = (clone $usersQuery)
            ->count();

        $activeUsers = (clone $usersQuery)
            ->where(
                'status',
                1
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Branch List
        |--------------------------------------------------------------------------
        */

        $branches = Store::query()
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
            ->map(function ($store) {

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
                    optional(
                        $store->created_at
                    )->format(
                        'd M Y'
                    ),
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Business/CompanyDashboard',
            [
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

                'branchSummary' => [
                    'total' =>
                    $totalBranches,

                    'active' =>
                    $activeBranches,

                    'pending' =>
                    $pendingBranches,
                ],

                'userSummary' => [
                    'total' =>
                    $totalUsers,

                    'active' =>
                    $activeUsers,
                ],

                'branches' =>
                $branches,
            ]
        );
    }
}
