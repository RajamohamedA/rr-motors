<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Support\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): Response
    {
        $currentUser = $request->user();

        $this->ensureCompanyAdmin($currentUser);

        $company = $this->getCompany($currentUser);

        $search = trim(
            $request->string('search')->toString()
        );

        $status = $request->input('status');

        $roles = Role::query()
            ->where('company_id', $company->id)

            ->withCount([
                'permissions',
                'users',
            ])

            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        );
                    });
                }
            )

            ->when(
                $status !== null &&
                    $status !== '',
                fn($query) =>
                $query->where(
                    'status',
                    (bool) $status
                )
            )

            ->orderBy('name')

            ->paginate(15)

            ->withQueryString();

        $roles->through(
            fn(Role $role) => [
                'id' =>
                $role->id,

                'name' =>
                $role->name,

                'description' =>
                $role->description,

                'guard_name' =>
                $role->guard_name,

                'status' =>
                (bool) $role->status,

                'permissions_count' =>
                $role->permissions_count,

                'users_count' =>
                $role->users_count,

                'created_at' =>
                $role->created_at?->toISOString(),

                'updated_at' =>
                $role->updated_at?->toISOString(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $baseQuery = Role::query()
            ->where(
                'company_id',
                $company->id
            );

        $summary = [
            'total' => (clone $baseQuery)->count(),

            'active' => (clone $baseQuery)
                ->where('status', true)
                ->count(),

            'inactive' => (clone $baseQuery)
                ->where('status', false)
                ->count(),

            'permissions' =>
            Permission::query()->count(),
        ];

        return Inertia::render(
            'Business/Roles/Index',
            [
                'company' =>
                $this->companyData($company),

                'roles' =>
                $roles,

                'summary' =>
                $summary,

                'filters' => [
                    'search' =>
                    $search,

                    'status' =>
                    $status,
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(Request $request): Response
    {
        $currentUser = $request->user();

        $this->ensureCompanyAdmin($currentUser);

        $company = $this->getCompany($currentUser);

        return Inertia::render(
            'Business/Roles/Create',
            [
                'company' =>
                $this->companyData($company),

                'permissionGroups' =>
                $this->getPermissionGroups(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $currentUser = $request->user();

        $this->ensureCompanyAdmin($currentUser);

        $company = $this->getCompany($currentUser);

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:125',

                Rule::unique('roles', 'name')
                    ->where(
                        fn($query) =>
                        $query
                            ->where(
                                'company_id',
                                $company->id
                            )
                            ->where(
                                'guard_name',
                                'web'
                            )
                    ),
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'status' => [
                'required',
                'boolean',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'required',
                'integer',
                Rule::exists(
                    'permissions',
                    'id'
                ),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate Permissions
        |--------------------------------------------------------------------------
        */

        $permissions =
            $this->resolvePermissions(
                $validated['permissions'] ?? []
            );

        /*
        |--------------------------------------------------------------------------
        | Create Role
        |--------------------------------------------------------------------------
        */

        $role = DB::transaction(
            function () use (
                $validated,
                $company,
                $permissions
            ) {
                /*
                |--------------------------------------------------------------------------
                | Important
                |--------------------------------------------------------------------------
                |
                | We create the role directly instead of Role::create([
                | 'name' => ...
                | ]) alone because this role belongs to this company.
                |
                */

                $role = new Role();

                $role->company_id =
                    $company->id;

                $role->name =
                    trim($validated['name']);

                $role->description =
                    $validated['description']
                    ?? null;

                $role->guard_name =
                    'web';

                $role->status =
                    (bool) $validated['status'];

                $role->save();

                /*
                |--------------------------------------------------------------------------
                | Permissions
                |--------------------------------------------------------------------------
                */

                $role->syncPermissions(
                    $permissions
                );

                return $role;
            }
        );

        return redirect()
            ->route(
                'business.roles.show',
                $role
            )
            ->with(
                'success',
                'Role created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Role $role
    ): Response {
        $currentUser = $request->user();

        $this->ensureCompanyAdmin($currentUser);

        $company = $this->getCompany($currentUser);

        $this->ensureRoleBelongsToCompany(
            $role,
            $company
        );

        $role->load([
            'permissions:id,name,guard_name',
        ]);

        $role->loadCount([
            'users',
        ]);

        return Inertia::render(
            'Business/Roles/Show',
            [
                'company' =>
                $this->companyData($company),

                'managedRole' =>
                $this->roleData($role),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request,
        Role $role
    ): Response {
        $currentUser = $request->user();

        $this->ensureCompanyAdmin($currentUser);

        $company = $this->getCompany($currentUser);

        $this->ensureRoleBelongsToCompany(
            $role,
            $company
        );

        $role->load([
            'permissions:id,name,guard_name',
        ]);

        return Inertia::render(
            'Business/Roles/Edit',
            [
                'company' =>
                $this->companyData($company),

                'managedRole' =>
                $this->roleData($role),

                'permissionGroups' =>
                $this->getPermissionGroups(),
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
        $currentUser = $request->user();

        $this->ensureCompanyAdmin($currentUser);

        $company = $this->getCompany($currentUser);

        $this->ensureRoleBelongsToCompany(
            $role,
            $company
        );

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:125',

                Rule::unique('roles', 'name')
                    ->where(
                        fn($query) =>
                        $query
                            ->where(
                                'company_id',
                                $company->id
                            )
                            ->where(
                                'guard_name',
                                'web'
                            )
                    )
                    ->ignore($role->id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'status' => [
                'required',
                'boolean',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'required',
                'integer',

                Rule::exists(
                    'permissions',
                    'id'
                ),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Resolve Permissions
        |--------------------------------------------------------------------------
        */

        $permissions =
            $this->resolvePermissions(
                $validated['permissions']
                    ?? []
            );

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $role,
                $validated,
                $permissions
            ) {
                $role->update([
                    'name' =>
                    trim($validated['name']),

                    'description' =>
                    $validated['description']
                        ?? null,

                    'status' =>
                    (bool) $validated['status'],
                ]);

                $role->syncPermissions(
                    $permissions
                );
            }
        );

        return redirect()
            ->route(
                'business.roles.show',
                $role
            )
            ->with(
                'success',
                'Role updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        Role $role
    ) {
        $currentUser = $request->user();

        $this->ensureCompanyAdmin($currentUser);

        $company = $this->getCompany($currentUser);

        $this->ensureRoleBelongsToCompany(
            $role,
            $company
        );

        $validated = $request->validate([
            'status' => [
                'required',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent Disabling Used Role
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $validated['status'] &&
            $role->users()->exists()
        ) {
            return AppResponse::error(
                'This role is assigned to users. Remove the role from those users before deactivating it.'
            );
        }

        $role->update([
            'status' =>
            (bool) $validated['status'],
        ]);

        return AppResponse::success(
            $role->status
                ? 'Role activated successfully.'
                : 'Role deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Role $role
    ) {
        $currentUser = $request->user();

        $this->ensureCompanyAdmin($currentUser);

        $company = $this->getCompany($currentUser);

        $this->ensureRoleBelongsToCompany(
            $role,
            $company
        );

        /*
        |--------------------------------------------------------------------------
        | Protect Used Roles
        |--------------------------------------------------------------------------
        */

        if (
            $role->users()->exists()
        ) {
            return AppResponse::error(
                'This role is assigned to one or more users and cannot be deleted.'
            );
        }

        DB::transaction(
            function () use ($role) {
                /*
                |--------------------------------------------------------------------------
                | Remove Permission Relationships
                |--------------------------------------------------------------------------
                */

                $role->syncPermissions([]);

                /*
                |--------------------------------------------------------------------------
                | Delete
                |--------------------------------------------------------------------------
                */

                $role->delete();
            }
        );

        return redirect()
            ->route(
                'business.roles.index'
            )
            ->with(
                'success',
                'Role deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Company
    |--------------------------------------------------------------------------
    */

    private function getCompany(
        User $user
    ): Company {
        if (
            !$user->company_id
        ) {
            abort(
                403,
                'No company is assigned to this account.'
            );
        }

        $company = Company::query()
            ->whereKey(
                $user->company_id
            )
            ->where(
                'status',
                true
            )
            ->first();

        if (
            !$company
        ) {
            abort(
                403,
                'Your company is inactive or unavailable.'
            );
        }

        return $company;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Admin Protection
    |--------------------------------------------------------------------------
    */

    private function ensureCompanyAdmin(
        ?User $user
    ): void {
        if (
            !$user
        ) {
            abort(401);
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin Must Use Super Admin Portal
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $user->is_super_user
        ) {
            abort(
                403,
                'Use the Super Admin role management area.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Company Admin Only
        |--------------------------------------------------------------------------
        */

        if (
            !$user->hasRole(
                'Company Admin'
            )
        ) {
            abort(
                403,
                'Only Company Administrators can manage roles.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Company Role Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureRoleBelongsToCompany(
        Role $role,
        Company $company
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Global Roles Have company_id = NULL
        |--------------------------------------------------------------------------
        |
        | This automatically protects:
        |
        | Super Admin
        | Company Admin
        | Any other platform/global role
        |
        */

        abort_unless(
            $role->company_id !== null &&
                (int) $role->company_id ===
                (int) $company->id,
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Allowed Permissions
    |--------------------------------------------------------------------------
    */

    private function resolvePermissions(
        array $permissionIds
    ) {
        if (
            empty($permissionIds)
        ) {
            return collect();
        }

        $permissionIds = collect(
            $permissionIds
        )
            ->map(
                fn($id) =>
                (int) $id
            )
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Only Web Permissions
        |--------------------------------------------------------------------------
        */

        $permissions =
            Permission::query()
            ->where(
                'guard_name',
                'web'
            )
            ->whereIn(
                'id',
                $permissionIds
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Security Check
        |--------------------------------------------------------------------------
        |
        | If somebody manually modifies the request and sends an unknown
        | permission ID, reject the request.
        |
        */

        if (
            $permissions->count() !==
            $permissionIds->count()
        ) {
            abort(
                422,
                'One or more selected permissions are invalid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Block Platform Permissions
        |--------------------------------------------------------------------------
        |
        | Add any Super Admin/platform-only permission prefixes here.
        |
        */

        $blockedPrefixes = [
            'super-admin.',
            'company.manage',
            'company.delete',
        ];

        foreach (
            $permissions as $permission
        ) {
            foreach (
                $blockedPrefixes as $prefix
            ) {
                if (
                    str_starts_with(
                        $permission->name,
                        $prefix
                    )
                ) {
                    abort(
                        403,
                        "Permission {$permission->name} cannot be assigned from the Company Portal."
                    );
                }
            }
        }

        return $permissions;
    }


    /*
    |--------------------------------------------------------------------------
    | Permission Groups
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | product.view
    | product.create
    | product.edit
    |
    | becomes:
    |
    | Product
    |   View
    |   Create
    |   Edit
    |
    |--------------------------------------------------------------------------
    */

    private function getPermissionGroups(): array
    {
        $permissions =
            Permission::query()
            ->where(
                'guard_name',
                'web'
            )
            ->orderBy(
                'name'
            )
            ->get([
                'id',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Remove Platform Permissions
        |--------------------------------------------------------------------------
        */

        $permissions =
            $permissions
            ->reject(
                function (
                    Permission $permission
                ) {
                    return
                        str_starts_with(
                            $permission->name,
                            'super-admin.'
                        )
                        ||
                        in_array(
                            $permission->name,
                            [
                                'company.manage',
                                'company.delete',
                            ],
                            true
                        );
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Group
        |--------------------------------------------------------------------------
        */

        return $permissions
            ->groupBy(
                function (
                    Permission $permission
                ) {
                    $parts =
                        explode(
                            '.',
                            $permission->name
                        );

                    return $parts[0]
                        ?? 'other';
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

                        'label' =>
                        str($module)
                            ->replace(
                                ['-', '_'],
                                ' '
                            )
                            ->title()
                            ->toString(),

                        'permissions' =>
                        $permissions
                            ->map(
                                function (
                                    Permission $permission
                                ) {
                                    $parts =
                                        explode(
                                            '.',
                                            $permission->name
                                        );

                                    $action =
                                        $parts[1]
                                        ?? $permission->name;

                                    return [
                                        'id' =>
                                        $permission->id,

                                        'name' =>
                                        $permission->name,

                                        'action' =>
                                        $action,

                                        'label' =>
                                        str($action)
                                            ->replace(
                                                ['-', '_'],
                                                ' '
                                            )
                                            ->title()
                                            ->toString(),
                                    ];
                                }
                            )
                            ->values()
                            ->all(),
                    ];
                }
            )
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Company Data
    |--------------------------------------------------------------------------
    */

    private function companyData(
        Company $company
    ): array {
        return [
            'id' =>
            $company->id,

            'company_name' =>
            $company->company_name,

            'company_code' =>
            $company->company_code,

            'status' =>
            (bool) $company->status,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Role Data
    |--------------------------------------------------------------------------
    */

    private function roleData(
        Role $role
    ): array {
        $role->loadMissing([
            'permissions:id,name,guard_name',
        ]);

        $role->loadCount([
            'users',
        ]);

        return [
            'id' =>
            $role->id,

            'company_id' =>
            $role->company_id,

            'name' =>
            $role->name,

            'description' =>
            $role->description,

            'guard_name' =>
            $role->guard_name,

            'status' =>
            (bool) $role->status,

            'permissions_count' =>
            $role->permissions->count(),

            'users_count' =>
            $role->users_count,

            /*
            |--------------------------------------------------------------------------
            | Edit Form
            |--------------------------------------------------------------------------
            */

            'permission_ids' =>
            $role->permissions
                ->pluck('id')
                ->map(
                    fn($id) =>
                    (int) $id
                )
                ->values()
                ->all(),

            /*
            |--------------------------------------------------------------------------
            | Show Page
            |--------------------------------------------------------------------------
            */

            'permissions' =>
            $role->permissions
                ->map(
                    fn(
                        Permission $permission
                    ) => [
                        'id' =>
                        $permission->id,

                        'name' =>
                        $permission->name,
                    ]
                )
                ->values()
                ->all(),

            'created_at' =>
            $role->created_at
                ?->toISOString(),

            'updated_at' =>
            $role->updated_at
                ?->toISOString(),
        ];
    }
}
