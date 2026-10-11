<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;

use App\Services\ActivityLogger;

use App\Services\SuperAdminNotificationService;

use App\Support\AppResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Str;

use Illuminate\Validation\Rule;

use Illuminate\Validation\ValidationException;

use Inertia\Inertia;

use Spatie\Permission\Models\Permission;

use Spatie\Permission\Models\Role;

use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller

{

    /*

    |--------------------------------------------------------------------------

    | Index

    |--------------------------------------------------------------------------

    */

    public function index()

    {

        $roles =

            Role::query()

            ->withCount([

                'users',

                'permissions',

            ])

            ->orderBy(

                'name'

            )

            ->get()

            ->map(

                function ($role) {

                    return [

                        'id' =>

                        $role->id,

                        'name' =>

                        $role->name,

                        'guard_name' =>

                        $role->guard_name,

                        'users_count' =>

                        $role->users_count,

                        'permissions_count' =>

                        $role->permissions_count,

                        'created_at' =>

                        $role->created_at,

                    ];
                }

            );

        return Inertia::render(

            'SuperAdmin/Roles/Index',

            [

                'roles' =>

                $roles,

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

            'SuperAdmin/Roles/Create',

            [

                'permissions' =>

                $this->permissionGroups(),

            ]

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

                'name' => [

                    'required',

                    'string',

                    'max:100',

                    'unique:roles,name',

                ],

                'permissions' => [

                    'nullable',

                    'array',

                ],

                'permissions.*' => [

                    'string',

                    'exists:permissions,name',

                ],

            ]);

        /*

        |--------------------------------------------------------------------------

        | Normalize Role Name

        |--------------------------------------------------------------------------

        */

        $roleName =

            $this->normalizeRoleName(

                $validated['name']

            );

        $this->ensureRoleNameIsUnique(

            $roleName

        );

        /*

        |--------------------------------------------------------------------------

        | Create Role

        |--------------------------------------------------------------------------

        */

        $role =

            Role::create([

                'name' =>

                $roleName,

                'guard_name' =>

                'web',

            ]);

        /*

        |--------------------------------------------------------------------------

        | Assign Permissions

        |--------------------------------------------------------------------------

        */

        $role->syncPermissions(

            $validated['permissions']

                ?? []

        );

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

        | Reload Permissions

        |--------------------------------------------------------------------------

        */

        $role->load([

            'permissions:id,name',

        ]);

        /*

        |--------------------------------------------------------------------------

        | Activity Log

        |--------------------------------------------------------------------------

        */

        ActivityLogger::log(

            module: 'role',

            action: 'created',

            description: "Created role {$role->name}",

            subject: $role,

            newValues: [

                'role_name' =>

                $role->name,

                'permissions' =>

                $this->permissionNames(

                    $role

                ),

            ]

        );

        /*

        |--------------------------------------------------------------------------

        | Notification

        |--------------------------------------------------------------------------

        */

        SuperAdminNotificationService::send(

            title: 'Role Created',

            message: "Role {$role->name} has been created.",

            category: 'security',

            icon: 'bi-person-badge',

            targetType: 'role',

            targetId: $role->id,

            fallbackUrl: route(

                'super-admin.roles.index'

            ),

            level: 'success',

        );

        /*

        |--------------------------------------------------------------------------

        | Response

        |--------------------------------------------------------------------------

        */

        return AppResponse::successRoute(

            'super-admin.roles.show',

            'Role created successfully.',

            $role

        );
    }

    /*

    |--------------------------------------------------------------------------

    | Show

    |--------------------------------------------------------------------------

    */

    public function show(

        Role $role

    ) {

        /*

        |--------------------------------------------------------------------------

        | Load Permissions

        |--------------------------------------------------------------------------

        */

        $role->load([

            'permissions:id,name',

        ]);

        /*

        |--------------------------------------------------------------------------

        | Group Permissions

        |--------------------------------------------------------------------------

        */

        $groupedPermissions =

            $role->permissions

            ->sortBy(

                'name'

            )

            ->groupBy(

                function ($permission) {

                    return explode(

                        '.',

                        $permission->name

                    )[0];
                }

            )

            ->map(

                function (

                    $permissions,

                    $module

                ) {

                    return [

                        'module' =>

                        $module,

                        'permissions' =>

                        $permissions

                            ->map(

                                function ($permission) {

                                    $parts =

                                        explode(

                                            '.',

                                            $permission->name

                                        );

                                    return [

                                        'name' =>

                                        $permission->name,

                                        'action' =>

                                        collect(

                                            $parts

                                        )

                                            ->skip(1)

                                            ->implode('.'),

                                    ];
                                }

                            )

                            ->values(),

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

            'SuperAdmin/Roles/Show',

            [

                'roleData' => [

                    'id' =>

                    $role->id,

                    'name' =>

                    $role->name,

                    'users_count' =>

                    $role

                        ->users()

                        ->count(),

                    'permissions_count' =>

                    $role->permissions

                        ->count(),

                    'permission_groups' =>

                    $groupedPermissions,

                ],

            ]

        );
    }

    /*

    |--------------------------------------------------------------------------

    | Edit

    |--------------------------------------------------------------------------

    */

    public function edit(

        Role $role

    ) {

        return Inertia::render(

            'SuperAdmin/Roles/Edit',

            [

                'roleData' => [

                    'id' =>

                    $role->id,

                    'name' =>

                    $role->name,

                    'permissions' =>

                    $role

                        ->permissions()

                        ->pluck(

                            'name'

                        )

                        ->sort()

                        ->values(),

                ],

                'permissions' =>

                $this->permissionGroups(),

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

        Role $role

    ) {

        /*

        |--------------------------------------------------------------------------

        | Validate

        |--------------------------------------------------------------------------

        */

        $validated =

            $request->validate([

                'name' => [

                    'required',

                    'string',

                    'max:100',

                    Rule::unique(

                        'roles',

                        'name'

                    )->ignore(

                        $role->id

                    ),

                ],

                'permissions' => [

                    'nullable',

                    'array',

                ],

                'permissions.*' => [

                    'string',

                    'exists:permissions,name',

                ],

            ]);

        /*

        |--------------------------------------------------------------------------

        | Existing Values

        |--------------------------------------------------------------------------

        */

        $role->load([

            'permissions:id,name',

        ]);

        $oldName =

            $role->name;

        $oldPermissions =

            $role->permissions

            ->pluck(

                'name'

            )

            ->sort()

            ->values();

        /*

        |--------------------------------------------------------------------------

        | Normalize Role Name

        |--------------------------------------------------------------------------

        */

        $newName =

            $this->isSystemRole(

                $role->name

            )

            ? $this->normalizeRoleName(

                $role->name

            )

            : $this->normalizeRoleName(

                $validated['name']

            );

        $this->ensureRoleNameIsUnique(

            $newName,

            (int) $role->id

        );

        /*

        |--------------------------------------------------------------------------

        | Update Role

        |--------------------------------------------------------------------------

        */

        $role->update([

            'name' =>

            $newName,

        ]);

        /*

        |--------------------------------------------------------------------------

        | Sync Permissions

        |--------------------------------------------------------------------------

        */

        $role->syncPermissions(

            $validated['permissions']

                ?? []

        );

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

        | Reload

        |--------------------------------------------------------------------------

        */

        $role->refresh();

        $role->load([

            'permissions:id,name',

        ]);

        $newPermissions =

            $role->permissions

            ->pluck(

                'name'

            )

            ->sort()

            ->values();

        /*

        |--------------------------------------------------------------------------

        | Permission Changes

        |--------------------------------------------------------------------------

        */

        $permissionsAdded =

            $newPermissions

            ->diff(

                $oldPermissions

            )

            ->values();

        $permissionsRemoved =

            $oldPermissions

            ->diff(

                $newPermissions

            )

            ->values();

        /*

        |--------------------------------------------------------------------------

        | Audit Changes

        |--------------------------------------------------------------------------

        */

        $oldValues =

            [];

        $newValues =

            [];

        if (

            $oldName !==

            $role->name

        ) {

            $oldValues['role_name'] =

                $oldName;

            $newValues['role_name'] =

                $role->name;
        }

        if (

            $permissionsAdded

            ->isNotEmpty()

        ) {

            $oldValues['permissions_added'] =

                '—';

            $newValues['permissions_added'] =

                $permissionsAdded

                ->implode(', ');
        }

        if (

            $permissionsRemoved

            ->isNotEmpty()

        ) {

            $oldValues['permissions_removed'] =

                $permissionsRemoved

                ->implode(', ');

            $newValues['permissions_removed'] =

                '—';
        }

        /*

        |--------------------------------------------------------------------------

        | No Changes

        |--------------------------------------------------------------------------

        */

        if (

            empty($newValues)

        ) {

            return AppResponse::info(

                'No changes were detected for this role.'

            );
        }

        /*

        |--------------------------------------------------------------------------

        | Activity Log

        |--------------------------------------------------------------------------

        */

        ActivityLogger::log(

            module: 'role',

            action: 'updated',

            description: "Updated role {$role->name}",

            subject: $role,

            oldValues: $oldValues,

            newValues: $newValues

        );

        /*

        |--------------------------------------------------------------------------

        | Security Notification

        |--------------------------------------------------------------------------

        */

        $changes =

            [];

        if (

            $oldName !==

            $role->name

        ) {

            $changes[] =

                "renamed from {$oldName} to {$role->name}";
        }

        if (

            $permissionsAdded

            ->isNotEmpty()

        ) {

            $changes[] =

                $permissionsAdded->count()

                . ' permission(s) added';
        }

        if (

            $permissionsRemoved

            ->isNotEmpty()

        ) {

            $changes[] =

                $permissionsRemoved->count()

                . ' permission(s) removed';
        }

        SuperAdminNotificationService::send(

            title: 'Role Access Changed',

            message: "{$role->name}: "

                . implode(

                    ', ',

                    $changes

                )

                . '.',

            category: 'security',

            icon: 'bi-shield-exclamation',

            targetType: 'role',

            targetId: $role->id,

            fallbackUrl: route(

                'super-admin.roles.index'

            ),

            level: 'warning',

        );

        /*

        |--------------------------------------------------------------------------

        | Response

        |--------------------------------------------------------------------------

        */

        return AppResponse::successRoute(

            'super-admin.roles.show',

            'Role updated successfully.',

            $role

        );
    }

    /*

    |--------------------------------------------------------------------------

    | Delete

    |--------------------------------------------------------------------------

    */

    public function destroy(

        Role $role

    ) {

        if ($this->isSystemRole($role->name)) {

            return AppResponse::error(

                'System roles cannot be deleted.'

            );
        }

        /*

        |--------------------------------------------------------------------------

        | Prevent Delete When Assigned

        |--------------------------------------------------------------------------

        */

        $usersCount =

            $role

            ->users()

            ->count();

        if (

            $usersCount > 0

        ) {

            return AppResponse::error(

                "This role cannot be deleted because it is assigned to {$usersCount} user(s)."

            );
        }

        /*

        |--------------------------------------------------------------------------

        | Load Existing Permissions

        |--------------------------------------------------------------------------

        */

        $role->load([

            'permissions:id,name',

        ]);

        /*

        |--------------------------------------------------------------------------

        | Capture Existing Data

        |--------------------------------------------------------------------------

        */

        $roleId =

            $role->id;

        $roleName =

            $role->name;

        $oldValues = [

            'role_name' =>

            $roleName,

            'permissions' =>

            $this->permissionNames(

                $role

            ),

        ];

        /*

        |--------------------------------------------------------------------------

        | Activity Log

        |--------------------------------------------------------------------------

        */

        ActivityLogger::log(

            module: 'role',

            action: 'deleted',

            description: "Deleted role {$roleName}",

            subject: $role,

            oldValues: $oldValues,

            newValues: [

                'record_state' =>

                'Deleted',

            ]

        );

        /*

        |--------------------------------------------------------------------------

        | Delete

        |--------------------------------------------------------------------------

        */

        $role->delete();

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

            title: 'Role Deleted',

            message: "Role {$roleName} has been permanently deleted.",

            category: 'security',

            icon: 'bi-shield-x',

            targetType: 'role',

            targetId: $roleId,

            fallbackUrl: route(

                'super-admin.roles.index'

            ),

            level: 'danger',

        );

        /*

        |--------------------------------------------------------------------------

        | Response

        |--------------------------------------------------------------------------

        */

        return AppResponse::successRoute(

            'super-admin.roles.index',

            'Role deleted successfully.'

        );
    }

    /*

    |--------------------------------------------------------------------------

    | Permission Groups

    |--------------------------------------------------------------------------

    */

    private function permissionGroups(): array

    {

        return Permission::query()

            ->orderBy(

                'name'

            )

            ->get()

            ->groupBy(

                function ($permission) {

                    return explode(

                        '.',

                        $permission->name

                    )[0];
                }

            )

            ->map(

                function (

                    $permissions,

                    $module

                ) {

                    return [

                        'module' =>

                        $module,

                        'permissions' =>

                        $permissions

                            ->map(

                                fn($permission) => [

                                    'id' =>

                                    $permission->id,

                                    'name' =>

                                    $permission->name,

                                    'action' =>

                                    collect(

                                        explode(

                                            '.',

                                            $permission->name

                                        )

                                    )

                                        ->skip(1)

                                        ->implode('.'),

                                ]

                            )

                            ->values(),

                    ];
                }

            )

            ->values()

            ->all();
    }

    /*

    |--------------------------------------------------------------------------

    | Permission Names

    |--------------------------------------------------------------------------

    */

    private function permissionNames(

        Role $role

    ): string {

        return $role->permissions

            ->pluck(

                'name'

            )

            ->sort()

            ->values()

            ->implode(', ');
    }

    private function normalizeRoleName(string $name): string

    {

        $name = Str::squish($name);

        return match (Str::lower($name)) {

            'super admin' => 'Super Admin',

            'company admin' => 'Company Admin',

            default => Str::title($name),
        };
    }

    private function isSystemRole(string $name): bool

    {

        return in_array(

            Str::lower(Str::squish($name)),

            [

                'super admin',

                'company admin',

            ],

            true

        );
    }

    private function ensureRoleNameIsUnique(

        string $name,

        ?int $ignoreRoleId = null

    ): void {

        $query = Role::query()

            ->where('guard_name', 'web')

            ->whereRaw(

                'LOWER(name) = ?',

                [Str::lower($name)]

            );

        if ($ignoreRoleId !== null) {

            $query->where(

                $query->getModel()->getKeyName(),

                '!=',

                $ignoreRoleId

            );
        }

        if ($query->exists()) {

            throw ValidationException::withMessages([

                'name' => 'A role with this name already exists.',

            ]);
        }
    }
}
