<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Current Super Administrator
        |--------------------------------------------------------------------------
        */

        $admin =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Super Admin Protection
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $admin &&
                (bool) $admin->is_super_user,
            403,
            'You do not have access to the Super Admin dashboard.'
        );


        /*
        |--------------------------------------------------------------------------
        | Company Statistics
        |--------------------------------------------------------------------------
        */

        $companyQuery =
            Company::query();


        $totalCompanies =
            (clone $companyQuery)
            ->count();


        $activeCompanies =
            (clone $companyQuery)
            ->where(
                'status',
                true
            )
            ->count();


        $inactiveCompanies =
            (clone $companyQuery)
            ->where(
                'status',
                false
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Archived Company Statistics
        |--------------------------------------------------------------------------
        */

        $archivedCompanies =
            Company::onlyTrashed()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Branch Statistics
        |--------------------------------------------------------------------------
        */

        $branchQuery =
            Store::query();


        $totalBranches =
            (clone $branchQuery)
            ->count();


        $activeBranches =
            (clone $branchQuery)
            ->where(
                'status',
                true
            )
            ->count();


        $inactiveBranches =
            (clone $branchQuery)
            ->where(
                'status',
                false
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | User Statistics
        |--------------------------------------------------------------------------
        */

        $userQuery =
            User::query()
            ->where(
                'is_super_user',
                false
            );


        $totalUsers =
            (clone $userQuery)
            ->count();


        $activeUsers =
            (clone $userQuery)
            ->where(
                'status',
                true
            )
            ->count();


        $inactiveUsers =
            (clone $userQuery)
            ->where(
                'status',
                false
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Notification Statistics
        |--------------------------------------------------------------------------
        */

        $unreadNotifications =
            $admin
            ->unreadNotifications()
            ->count();


        $totalNotifications =
            $admin
            ->notifications()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Activity Statistics
        |--------------------------------------------------------------------------
        */

        $totalActivities =
            ActivityLog::query()
            ->count();


        $todayActivities =
            ActivityLog::query()
            ->whereDate(
                'created_at',
                today()
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Recent Companies
        |--------------------------------------------------------------------------
        */

        $recentCompanies =
            Company::query()
            ->withCount(
                'stores'
            )
            ->latest(
                'id'
            )
            ->limit(
                5
            )
            ->get([
                'id',
                'company_name',
                'company_code',
                'owner_name',
                'status',
                'created_at',
            ])
            ->map(
                function ($company) {

                    return [
                        'id' =>
                        $company->id,

                        'company_name' =>
                        $company->company_name,

                        'company_code' =>
                        $company->company_code,

                        'owner_name' =>
                        $company->owner_name,

                        'status' =>
                        (bool) $company->status,

                        'stores_count' =>
                        $company->stores_count,

                        'created_at' =>
                        $company->created_at,
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Recent Branches
        |--------------------------------------------------------------------------
        */

        $recentBranches =
            Store::query()
            ->with([
                'company:id,company_name,company_code',
            ])
            ->latest(
                'id'
            )
            ->limit(
                5
            )
            ->get([
                'id',
                'company_id',
                'store_code',
                'store_name',
                'city',
                'status',
                'created_at',
            ])
            ->map(
                function ($store) {

                    return [
                        'id' =>
                        $store->id,

                        'company_id' =>
                        $store->company_id,

                        'store_code' =>
                        $store->store_code,

                        'store_name' =>
                        $store->store_name,

                        'city' =>
                        $store->city,

                        'status' =>
                        (bool) $store->status,

                        'created_at' =>
                        $store->created_at,

                        'company' =>
                        $store->company
                            ? [
                                'id' =>
                                $store->company->id,

                                'company_name' =>
                                $store->company->company_name,

                                'company_code' =>
                                $store->company->company_code,
                            ]
                            : null,
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Recent Users
        |--------------------------------------------------------------------------
        */

        $recentUsers =
            User::query()
            ->with([
                'company:id,company_name,company_code',
                'roles:id,name',
            ])
            ->where(
                'is_super_user',
                false
            )
            ->latest(
                'id'
            )
            ->limit(
                5
            )
            ->get([
                'id',
                'company_id',
                'name',
                'email',
                'mobile',
                'status',
                'created_at',
            ])
            ->map(
                function ($user) {

                    return [
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

                        'created_at' =>
                        $user->created_at,

                        'company' =>
                        $user->company
                            ? [
                                'id' =>
                                $user->company->id,

                                'company_name' =>
                                $user->company->company_name,

                                'company_code' =>
                                $user->company->company_code,
                            ]
                            : null,

                        'roles' =>
                        $user->roles
                            ->map(
                                fn($role) => [
                                    'id' =>
                                    $role->id,

                                    'name' =>
                                    $role->name,
                                ]
                            )
                            ->values(),
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Recent Activity
        |--------------------------------------------------------------------------
        */

        $recentActivities =
            ActivityLog::query()
            ->with([
                'user:id,name,email',
            ])
            ->latest(
                'id'
            )
            ->limit(
                8
            )
            ->get()
            ->map(
                function ($activity) {

                    return [
                        'id' =>
                        $activity->id,

                        'module' =>
                        $activity->module,

                        'action' =>
                        $activity->action,

                        'description' =>
                        $activity->description,

                        'subject_type' =>
                        $activity->subject_type,

                        'subject_id' =>
                        $activity->subject_id,

                        'ip_address' =>
                        $activity->ip_address,

                        'created_at' =>
                        $activity->created_at,

                        'user' =>
                        $activity->user
                            ? [
                                'id' =>
                                $activity->user->id,

                                'name' =>
                                $activity->user->name,

                                'email' =>
                                $activity->user->email,
                            ]
                            : null,
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Recent Notifications
        |--------------------------------------------------------------------------
        */

        $recentNotifications =
            $admin
            ->notifications()
            ->latest()
            ->limit(
                5
            )
            ->get()
            ->map(
                function ($notification) {

                    $data =
                        $notification->data
                        ?? [];


                    return [
                        'id' =>
                        $notification->id,

                        'title' =>
                        $data['title']
                            ?? 'Notification',

                        'message' =>
                        $data['message']
                            ?? '',

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
                        $data['fallback_url']
                            ?? null,

                        /*
                            |--------------------------------------------------------------------------
                            | Status
                            |--------------------------------------------------------------------------
                            */

                        'read' =>
                        $notification->read_at
                            !== null,

                        'read_at' =>
                        $notification
                            ->read_at
                            ?->toISOString(),

                        'created_at' =>
                        $notification
                            ->created_at
                            ?->toISOString(),
                    ];
                }
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'SuperAdmin/Dashboard',
            [
                /*
                |--------------------------------------------------------------------------
                | Statistics
                |--------------------------------------------------------------------------
                */

                'stats' => [

                    'companies' => [
                        'total' =>
                        $totalCompanies,

                        'active' =>
                        $activeCompanies,

                        'inactive' =>
                        $inactiveCompanies,

                        'archived' =>
                        $archivedCompanies,
                    ],


                    'branches' => [
                        'total' =>
                        $totalBranches,

                        'active' =>
                        $activeBranches,

                        'inactive' =>
                        $inactiveBranches,
                    ],


                    'users' => [
                        'total' =>
                        $totalUsers,

                        'active' =>
                        $activeUsers,

                        'inactive' =>
                        $inactiveUsers,
                    ],


                    'activity' => [
                        'total' =>
                        $totalActivities,

                        'today' =>
                        $todayActivities,
                    ],


                    'notifications' => [
                        'total' =>
                        $totalNotifications,

                        'unread' =>
                        $unreadNotifications,
                    ],
                ],


                /*
                |--------------------------------------------------------------------------
                | Recent Records
                |--------------------------------------------------------------------------
                */

                'recentCompanies' =>
                $recentCompanies,

                'recentBranches' =>
                $recentBranches,

                'recentUsers' =>
                $recentUsers,

                'recentActivities' =>
                $recentActivities,

                'recentNotifications' =>
                $recentNotifications,
            ]
        );
    }
}
