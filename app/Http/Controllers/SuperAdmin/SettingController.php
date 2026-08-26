<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SettingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Super Admin Protection
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        abort_unless(
            $user &&
                (bool) $user->is_super_user,
            403,
            'You do not have access to system settings.'
        );


        /*
        |--------------------------------------------------------------------------
        | Access Control Summary
        |--------------------------------------------------------------------------
        */

        $rolesCount =
            Role::query()
            ->count();


        $permissionsCount =
            Permission::query()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | User Summary
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
        | Company Summary
        |--------------------------------------------------------------------------
        */

        $totalCompanies =
            Company::withTrashed()
            ->count();


        $activeCompanies =
            Company::query()
            ->where(
                'status',
                true
            )
            ->count();


        $inactiveCompanies =
            Company::query()
            ->where(
                'status',
                false
            )
            ->count();


        $archivedCompanies =
            Company::onlyTrashed()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Branch Summary
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
        | Activity Summary
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
        | Notification Summary
        |--------------------------------------------------------------------------
        */

        $totalNotifications =
            $user
            ->notifications()
            ->count();


        $unreadNotifications =
            $user
            ->unreadNotifications()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Settings Sections
        |--------------------------------------------------------------------------
        */

        $sections = [

            [
                'key' =>
                'profile',

                'title' =>
                'My Profile',

                'description' =>
                'Manage administrator profile and password.',

                'icon' =>
                'bi-person-circle',

                'href' =>
                '/profile',
            ],

            [
                'key' =>
                'roles',

                'title' =>
                'Roles',

                'description' =>
                'Manage application roles and access groups.',

                'icon' =>
                'bi-person-badge',

                'href' =>
                '/super-admin/roles',

                'count' =>
                $rolesCount,
            ],

            [
                'key' =>
                'permissions',

                'title' =>
                'Permissions',

                'description' =>
                'Manage granular system permissions.',

                'icon' =>
                'bi-shield-check',

                'href' =>
                '/super-admin/permissions',

                'count' =>
                $permissionsCount,
            ],

            [
                'key' =>
                'users',

                'title' =>
                'Users',

                'description' =>
                'Manage company users and access assignments.',

                'icon' =>
                'bi-people',

                'href' =>
                '/super-admin/users',

                'count' =>
                $totalUsers,
            ],

            [
                'key' =>
                'activity',

                'title' =>
                'Activity Logs',

                'description' =>
                'Review administrative and security activity.',

                'icon' =>
                'bi-clock-history',

                'href' =>
                '/super-admin/activity-logs',

                'count' =>
                $totalActivities,
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'SuperAdmin/Settings/Index',
            [
                'summary' => [

                    'access' => [

                        'roles' =>
                        $rolesCount,

                        'permissions' =>
                        $permissionsCount,
                    ],


                    'users' => [

                        'total' =>
                        $totalUsers,

                        'active' =>
                        $activeUsers,

                        'inactive' =>
                        $inactiveUsers,
                    ],


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


                'sections' =>
                $sections,
            ]
        );
    }
}
