<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\SuperAdminNotificationService;
use App\Support\AppResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $permissions =
            Permission::query()
            ->withCount(
                'roles'
            )
            ->orderBy(
                'name'
            )
            ->get()
            ->map(
                function ($permission) {

                    $parts =
                        explode(
                            '.',
                            $permission->name
                        );


                    return [

                        'id' =>
                        $permission->id,

                        'name' =>
                        $permission->name,

                        'module' =>
                        $parts[0]
                            ?? 'other',

                        'action' =>
                        collect(
                            $parts
                        )
                            ->skip(1)
                            ->implode('.'),

                        'guard_name' =>
                        $permission->guard_name,

                        'roles_count' =>
                        $permission->roles_count,
                    ];
                }
            );


        return Inertia::render(
            'SuperAdmin/Permissions/Index',
            [
                'permissions' =>
                $permissions,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render(
            'SuperAdmin/Permissions/Create'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'module' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'action' => [
                    'required',
                    'string',
                    'max:100',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $module =
            $this->normalize(
                $validated['module']
            );


        $action =
            $this->normalize(
                $validated['action']
            );


        $name =
            $module
            . '.'
            . $action;


        /*
        |--------------------------------------------------------------------------
        | Duplicate Protection
        |--------------------------------------------------------------------------
        */

        $exists =
            Permission::query()
            ->where(
                'name',
                $name
            )
            ->where(
                'guard_name',
                'web'
            )
            ->exists();


        if (
            $exists
        ) {

            return AppResponse::error(
                'This permission already exists.',
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        $permission =
            Permission::create([

                'name' =>
                $name,

                'guard_name' =>
                'web',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'permission',

            action: 'created',

            description: "Created permission {$permission->name}",

            subject: $permission,

            newValues: [

                'permission_name' =>
                $permission->name,

                'module' =>
                $module,

                'action' =>
                $action,

                'guard_name' =>
                $permission->guard_name,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        SuperAdminNotificationService::send(
            title: 'Permission Created',

            message: "Permission {$permission->name} has been created.",

            category: 'security',

            icon: 'bi-shield-plus',

            targetType: 'permission',

            targetId: $permission->id,

            fallbackUrl: route(
                'super-admin.permissions.index'
            ),

            level: 'success',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'super-admin.permissions.index',
            'Permission created successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Permission $permission
    ) {
        /*
        |--------------------------------------------------------------------------
        | Parse Permission
        |--------------------------------------------------------------------------
        */

        $parts =
            explode(
                '.',
                $permission->name
            );


        /*
        |--------------------------------------------------------------------------
        | Role Usage
        |--------------------------------------------------------------------------
        */

        $rolesCount =
            $permission
            ->roles()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'SuperAdmin/Permissions/Edit',
            [
                'permissionData' => [

                    'id' =>
                    $permission->id,

                    'name' =>
                    $permission->name,

                    'module' =>
                    $parts[0]
                        ?? '',

                    'action' =>
                    collect(
                        $parts
                    )
                        ->skip(1)
                        ->implode('.'),

                    'guard_name' =>
                    $permission->guard_name,

                    'roles_count' =>
                    $rolesCount,

                    'can_edit' =>
                    $rolesCount === 0,
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Permission $permission
    ) {
        /*
        |--------------------------------------------------------------------------
        | Prevent Editing Permission In Use
        |--------------------------------------------------------------------------
        */

        $rolesCount =
            $permission
            ->roles()
            ->count();


        if (
            $rolesCount > 0
        ) {

            return AppResponse::error(
                "This permission cannot be renamed because it is assigned to {$rolesCount} role(s)."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'module' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'action' => [
                    'required',
                    'string',
                    'max:100',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Capture Old Values
        |--------------------------------------------------------------------------
        */

        $oldName =
            $permission->name;


        $oldParts =
            explode(
                '.',
                $oldName
            );


        $oldModule =
            $oldParts[0]
            ?? '';


        $oldAction =
            collect(
                $oldParts
            )
            ->skip(1)
            ->implode('.');


        /*
        |--------------------------------------------------------------------------
        | New Name
        |--------------------------------------------------------------------------
        */

        $module =
            $this->normalize(
                $validated['module']
            );


        $action =
            $this->normalize(
                $validated['action']
            );


        $name =
            $module
            . '.'
            . $action;


        /*
        |--------------------------------------------------------------------------
        | Duplicate Protection
        |--------------------------------------------------------------------------
        */

        $exists =
            Permission::query()
            ->where(
                'name',
                $name
            )
            ->where(
                'guard_name',
                $permission->guard_name
            )
            ->where(
                'id',
                '!=',
                $permission->id
            )
            ->exists();


        if (
            $exists
        ) {

            return AppResponse::error(
                'This permission already exists.',
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | No Actual Change
        |--------------------------------------------------------------------------
        */

        if (
            $oldName ===
            $name
        ) {

            return AppResponse::info(
                'No permission changes were detected.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $permission->update([
            'name' =>
            $name,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'permission',

            action: 'updated',

            description: "Renamed permission {$oldName} to {$permission->name}",

            subject: $permission,

            oldValues: [

                'permission_name' =>
                $oldName,

                'module' =>
                $oldModule,

                'action' =>
                $oldAction,
            ],

            newValues: [

                'permission_name' =>
                $permission->name,

                'module' =>
                $module,

                'action' =>
                $action,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Security Notification
        |--------------------------------------------------------------------------
        */

        SuperAdminNotificationService::send(
            title: 'Permission Renamed',

            message: "{$oldName} was renamed to {$permission->name}.",

            category: 'security',

            icon: 'bi-shield-exclamation',

            targetType: 'permission',

            targetId: $permission->id,

            fallbackUrl: route(
                'super-admin.permissions.index'
            ),

            level: 'warning',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'super-admin.permissions.index',
            'Permission updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Permission $permission
    ) {
        /*
        |--------------------------------------------------------------------------
        | Check Role Usage
        |--------------------------------------------------------------------------
        */

        $rolesCount =
            $permission
            ->roles()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Prevent Delete When Used
        |--------------------------------------------------------------------------
        */

        if (
            $rolesCount > 0
        ) {

            return AppResponse::error(
                "This permission cannot be deleted because it is assigned to {$rolesCount} role(s)."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Capture Before Delete
        |--------------------------------------------------------------------------
        */

        $permissionId =
            $permission->id;


        $permissionName =
            $permission->name;


        $guardName =
            $permission->guard_name;


        $parts =
            explode(
                '.',
                $permissionName
            );


        $module =
            $parts[0]
            ?? '';


        $action =
            collect(
                $parts
            )
            ->skip(1)
            ->implode('.');


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'permission',

            action: 'deleted',

            description: "Deleted permission {$permissionName}",

            subject: $permission,

            oldValues: [

                'permission_name' =>
                $permissionName,

                'module' =>
                $module,

                'action' =>
                $action,

                'guard_name' =>
                $guardName,
            ],

            newValues: [
                'record_state' =>
                'Deleted',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Delete Permission
        |--------------------------------------------------------------------------
        */

        $permission->delete();


        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();


        /*
        |--------------------------------------------------------------------------
        | Security Notification
        |--------------------------------------------------------------------------
        */

        SuperAdminNotificationService::send(
            title: 'Permission Deleted',

            message: "Permission {$permissionName} has been permanently deleted.",

            category: 'security',

            icon: 'bi-shield-x',

            targetType: 'permission',

            targetId: $permissionId,

            fallbackUrl: route(
                'super-admin.permissions.index'
            ),

            level: 'danger',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'super-admin.permissions.index',
            'Permission deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize
    |--------------------------------------------------------------------------
    */

    private function normalize(
        string $value
    ): string {

        return strtolower(
            trim(
                preg_replace(
                    '/[^a-zA-Z0-9]+/',
                    '-',
                    trim(
                        $value
                    )
                ),
                '-'
            )
        );
    }
}
