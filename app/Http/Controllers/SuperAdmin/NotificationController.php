<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use App\Support\AppResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Open Notification
    |--------------------------------------------------------------------------
    |
    | POST /super-admin/notifications/{id}/read
    |
    | Marks notification as read and redirects to the related resource.
    |
    */

    public function read(
        Request $request,
        string $id
    ): RedirectResponse {

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
            'You do not have access to notifications.'
        );


        /*
        |--------------------------------------------------------------------------
        | Find Notification
        |--------------------------------------------------------------------------
        */

        $notification =
            $user
            ->notifications()
            ->where(
                'id',
                $id
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Mark As Read
        |--------------------------------------------------------------------------
        */

        if (
            $notification->read_at === null
        ) {

            $notification->markAsRead();
        }


        /*
        |--------------------------------------------------------------------------
        | Notification Data
        |--------------------------------------------------------------------------
        */

        $data =
            $notification->data
            ?? [];


        $targetType =
            $data['target_type']
            ?? null;


        $targetId =
            $data['target_id']
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | Fallback URL
        |--------------------------------------------------------------------------
        */

        $fallbackUrl =
            $data['fallback_url']
            ?? route(
                'super-admin.dashboard'
            );


        /*
        |--------------------------------------------------------------------------
        | Resolve Destination
        |--------------------------------------------------------------------------
        */

        $destination =
            $this->resolveTargetUrl(
                targetType: $targetType,

                targetId: $targetId,

                fallbackUrl: $fallbackUrl
            );


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()->to(
            $destination
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Single Notification As Read
    |--------------------------------------------------------------------------
    |
    | PATCH /super-admin/notifications/{id}/read
    |
    */

    public function markRead(
        Request $request,
        string $id
    ): RedirectResponse {

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
            'You do not have access to notifications.'
        );


        /*
        |--------------------------------------------------------------------------
        | Find Notification
        |--------------------------------------------------------------------------
        */

        $notification =
            $user
            ->notifications()
            ->where(
                'id',
                $id
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Already Read
        |--------------------------------------------------------------------------
        */

        if (
            $notification->read_at !== null
        ) {

            return AppResponse::info(
                'Notification is already marked as read.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Mark Read
        |--------------------------------------------------------------------------
        */

        $notification->markAsRead();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::success(
            'Notification marked as read.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark All Notifications As Read
    |--------------------------------------------------------------------------
    |
    | PATCH /super-admin/notifications/read-all
    |
    */

    public function markAllRead(
        Request $request
    ): RedirectResponse {

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
            'You do not have access to notifications.'
        );


        /*
        |--------------------------------------------------------------------------
        | Unread Count
        |--------------------------------------------------------------------------
        */

        $unreadCount =
            $user
            ->unreadNotifications()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Nothing To Mark
        |--------------------------------------------------------------------------
        */

        if (
            $unreadCount === 0
        ) {

            return AppResponse::info(
                'There are no unread notifications.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Mark All Read
        |--------------------------------------------------------------------------
        */

        $user
            ->unreadNotifications()
            ->update([
                'read_at' =>
                now(),
            ]);


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::success(
            $unreadCount === 1
                ? '1 notification marked as read.'
                : "{$unreadCount} notifications marked as read."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Target URL
    |--------------------------------------------------------------------------
    */

    private function resolveTargetUrl(
        ?string $targetType,
        int|string|null $targetId,
        string $fallbackUrl
    ): string {

        /*
        |--------------------------------------------------------------------------
        | No Target
        |--------------------------------------------------------------------------
        */

        if (
            !$targetType ||
            $targetId === null ||
            $targetId === ''
        ) {

            return $fallbackUrl;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $targetType =
            strtolower(
                trim(
                    $targetType
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Resolve
        |--------------------------------------------------------------------------
        */

        return match ($targetType) {

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            'company' =>
            $this->resolveCompanyUrl(
                $targetId,
                $fallbackUrl
            ),


            /*
            |--------------------------------------------------------------------------
            | Branch / Store
            |--------------------------------------------------------------------------
            */

            'branch',
            'store' =>
            $this->resolveBranchUrl(
                $targetId,
                $fallbackUrl
            ),


            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            'user' =>
            $this->resolveUserUrl(
                $targetId,
                $fallbackUrl
            ),


            /*
            |--------------------------------------------------------------------------
            | Role
            |--------------------------------------------------------------------------
            */

            'role' =>
            $this->resolveRoleUrl(
                $targetId,
                $fallbackUrl
            ),


            /*
            |--------------------------------------------------------------------------
            | Permission
            |--------------------------------------------------------------------------
            */

            'permission' =>
            $this->resolvePermissionUrl(
                $targetId,
                $fallbackUrl
            ),


            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            'activity',
            'activity_log',
            'activity-log' =>
            route(
                'super-admin.activity-logs.index'
            ),


            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */

            'setting',
            'settings' =>
            route(
                'super-admin.settings.index'
            ),


            /*
            |--------------------------------------------------------------------------
            | Unknown
            |--------------------------------------------------------------------------
            */

            default =>
            $fallbackUrl,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    private function resolveCompanyUrl(
        int|string $targetId,
        string $fallbackUrl
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Normal Query Intentionally Excludes Archived Companies
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()
            ->find(
                $targetId
            );


        if (
            !$company
        ) {

            return $fallbackUrl;
        }


        return route(
            'super-admin.companies.show',
            $company
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Branch
    |--------------------------------------------------------------------------
    |
    | Your current routes do NOT contain:
    |
    | super-admin.branches.show
    |
    | Therefore branch notifications open the parent company page.
    |
    */

    private function resolveBranchUrl(
        int|string $targetId,
        string $fallbackUrl
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Find Branch
        |--------------------------------------------------------------------------
        */

        $store =
            Store::query()
            ->find(
                $targetId
            );


        if (
            !$store
        ) {

            return $fallbackUrl;
        }


        /*
        |--------------------------------------------------------------------------
        | Find Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()
            ->find(
                $store->company_id
            );


        if (
            !$company
        ) {

            return $fallbackUrl;
        }


        /*
        |--------------------------------------------------------------------------
        | Open Parent Company
        |--------------------------------------------------------------------------
        */

        return route(
            'super-admin.companies.show',
            $company
        );
    }


    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    private function resolveUserUrl(
        int|string $targetId,
        string $fallbackUrl
    ): string {

        $targetUser =
            User::query()
            ->find(
                $targetId
            );


        /*
        |--------------------------------------------------------------------------
        | Deleted User
        |--------------------------------------------------------------------------
        */

        if (
            !$targetUser
        ) {

            return $fallbackUrl;
        }


        /*
        |--------------------------------------------------------------------------
        | Super Admin Protection
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $targetUser->is_super_user
        ) {

            return $fallbackUrl;
        }


        /*
        |--------------------------------------------------------------------------
        | User Show
        |--------------------------------------------------------------------------
        */

        return route(
            'super-admin.users.show',
            $targetUser
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Role
    |--------------------------------------------------------------------------
    */

    private function resolveRoleUrl(
        int|string $targetId,
        string $fallbackUrl
    ): string {

        $role =
            Role::query()
            ->find(
                $targetId
            );


        /*
        |--------------------------------------------------------------------------
        | Deleted Role
        |--------------------------------------------------------------------------
        */

        if (
            !$role
        ) {

            return $fallbackUrl;
        }


        /*
        |--------------------------------------------------------------------------
        | Role Show
        |--------------------------------------------------------------------------
        */

        return route(
            'super-admin.roles.show',
            $role
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Permission
    |--------------------------------------------------------------------------
    */

    private function resolvePermissionUrl(
        int|string $targetId,
        string $fallbackUrl
    ): string {

        $permission =
            Permission::query()
            ->find(
                $targetId
            );


        /*
        |--------------------------------------------------------------------------
        | Deleted Permission
        |--------------------------------------------------------------------------
        */

        if (
            !$permission
        ) {

            return $fallbackUrl;
        }


        /*
        |--------------------------------------------------------------------------
        | Permission Edit
        |--------------------------------------------------------------------------
        */

        return route(
            'super-admin.permissions.edit',
            $permission
        );
    }
}
