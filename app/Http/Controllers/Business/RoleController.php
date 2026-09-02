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

    public function index(
        Request $request
    ): Response {

        $currentUser =
            $request->user();


        $this->ensureCompanyAdmin(
            $currentUser
        );


        $company =
            $this->getCompany(
                $currentUser
            );


        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $search =
            trim(
                $request
                    ->string('search')
                    ->toString()
            );


        $status =
            $request->input(
                'status'
            );


        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $roles =
            Role::query()

            ->where(
                'company_id',
                $company->id
            )

            ->withCount([
                'permissions',
                'users',
            ])

            ->when(
                $search !== '',
                function (
                    $query
                ) use (
                    $search
                ) {

                    $query->where(
                        function (
                            $q
                        ) use (
                            $search
                        ) {

                            $q->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )

                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->when(
                $status !== null &&
                    $status !== '',
                function (
                    $query
                ) use (
                    $status
                ) {

                    $query->where(
                        'status',
                        (bool) ((int) $status)
                    );
                }
            )

            ->orderBy(
                'name'
            )

            ->paginate(
                15
            )

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Transform
        |--------------------------------------------------------------------------
        */

        $roles->through(
            fn(
                Role $role
            ) => [

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
                $role->created_at
                    ?->toISOString(),

                'updated_at' =>
                $role->updated_at
                    ?->toISOString(),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $baseQuery =
            Role::query()

            ->where(
                'company_id',
                $company->id
            );


        $summary = [

            'total' => (clone $baseQuery)
                ->count(),

            'active' => (clone $baseQuery)

                ->where(
                    'status',
                    true
                )

                ->count(),

            'inactive' => (clone $baseQuery)

                ->where(
                    'status',
                    false
                )

                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Only Assignable Permissions
            |--------------------------------------------------------------------------
            */

            'permissions' =>
            $this
                ->getAssignablePermissions()
                ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Business/Roles/Index',
            [

                'company' =>
                $this->companyData(
                    $company
                ),

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

    public function create(
        Request $request
    ): Response {

        $currentUser =
            $request->user();


        $this->ensureCompanyAdmin(
            $currentUser
        );


        $company =
            $this->getCompany(
                $currentUser
            );


        return Inertia::render(
            'Business/Roles/Create',
            [

                'company' =>
                $this->companyData(
                    $company
                ),

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

    public function store(
        Request $request
    ) {

        $currentUser =
            $request->user();


        $this->ensureCompanyAdmin(
            $currentUser
        );


        $company =
            $this->getCompany(
                $currentUser
            );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:125',

                    Rule::unique(
                        'roles',
                        'name'
                    )->where(
                        fn(
                            $query
                        ) =>
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
        | Create
        |--------------------------------------------------------------------------
        */

        $role =
            DB::transaction(
                function () use (
                    $validated,
                    $company,
                    $permissions
                ) {

                    $role =
                        new Role();


                    $role->company_id =
                        $company->id;


                    $role->name =
                        trim(
                            $validated['name']
                        );


                    $role->description =
                        !empty($validated['description'])
                        ? trim(
                            $validated['description']
                        )
                        : null;


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


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'business.roles.show',
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
        Request $request,
        Role $role
    ): Response {

        $currentUser =
            $request->user();


        $this->ensureCompanyAdmin(
            $currentUser
        );


        $company =
            $this->getCompany(
                $currentUser
            );


        $this->ensureRoleBelongsToCompany(
            $role,
            $company
        );


        /*
        |--------------------------------------------------------------------------
        | Load
        |--------------------------------------------------------------------------
        */

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
                $this->companyData(
                    $company
                ),

                'managedRole' =>
                $this->roleData(
                    $role
                ),
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

        $currentUser =
            $request->user();


        $this->ensureCompanyAdmin(
            $currentUser
        );


        $company =
            $this->getCompany(
                $currentUser
            );


        $this->ensureRoleBelongsToCompany(
            $role,
            $company
        );


        /*
        |--------------------------------------------------------------------------
        | Load
        |--------------------------------------------------------------------------
        */

        $role->load([
            'permissions:id,name,guard_name',
        ]);


        return Inertia::render(
            'Business/Roles/Edit',
            [

                'company' =>
                $this->companyData(
                    $company
                ),

                'managedRole' =>
                $this->roleData(
                    $role
                ),

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

        $currentUser =
            $request->user();


        $this->ensureCompanyAdmin(
            $currentUser
        );


        $company =
            $this->getCompany(
                $currentUser
            );


        $this->ensureRoleBelongsToCompany(
            $role,
            $company
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:125',

                    Rule::unique(
                        'roles',
                        'name'
                    )->where(
                        fn(
                            $query
                        ) =>
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
                        ->ignore(
                            $role->id
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
                    trim(
                        $validated['name']
                    ),

                    'description' =>
                    !empty($validated['description'])
                        ? trim(
                            $validated['description']
                        )
                        : null,

                    'status' =>
                    (bool) $validated['status'],
                ]);


                /*
                |--------------------------------------------------------------------------
                | Sync Permissions
                |--------------------------------------------------------------------------
                */

                $role->syncPermissions(
                    $permissions
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'business.roles.show',
            'Role updated successfully.',
            $role
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

        $currentUser =
            $request->user();


        $this->ensureCompanyAdmin(
            $currentUser
        );


        $company =
            $this->getCompany(
                $currentUser
            );


        $this->ensureRoleBelongsToCompany(
            $role,
            $company
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'status' => [
                    'required',
                    'boolean',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Prevent Disable When In Use
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $validated['status'] &&
            $role
            ->users()
            ->exists()
        ) {

            return AppResponse::error(
                'This role is assigned to users. Remove the role from those users before deactivating it.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

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

        $currentUser =
            $request->user();


        $this->ensureCompanyAdmin(
            $currentUser
        );


        $company =
            $this->getCompany(
                $currentUser
            );


        $this->ensureRoleBelongsToCompany(
            $role,
            $company
        );


        /*
        |--------------------------------------------------------------------------
        | Prevent Delete When In Use
        |--------------------------------------------------------------------------
        */

        if (
            $role
            ->users()
            ->exists()
        ) {

            return AppResponse::error(
                'This role is assigned to one or more users and cannot be deleted.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Delete
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $role
            ) {

                $role->syncPermissions(
                    []
                );


                $role->delete();
            }
        );


        return AppResponse::successRoute(
            'business.roles.index',
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


            $company =
                Company::query()

                ->whereKey(
                    $companyId
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
                    'The selected company is inactive or unavailable.'
                );
            }


            return $company;
        }


        /*
        |--------------------------------------------------------------------------
        | Company Admin
        |--------------------------------------------------------------------------
        */

        if (
            !$user->company_id
        ) {

            abort(
                403,
                'No company is assigned to this account.'
            );
        }


        $company =
            Company::query()

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


            return;
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
                'Only Company Administrators can manage roles.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Role Company Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureRoleBelongsToCompany(
        Role $role,
        Company $company
    ): void {

        abort_unless(
            $role->company_id !== null &&
                (int) $role->company_id ===
                (int) $company->id,
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Blocked Permission Rule
    |--------------------------------------------------------------------------
    |
    | Custom business roles are operational roles.
    |
    | Company administration remains exclusively under Company Admin.
    |
    */

    private function isBlockedCompanyRolePermission(
        string $permission
    ): bool {

        $currentUser =
            auth()->user();


        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Super Admin may assign any normal web permission while working
        | inside a selected company context.
        |
        */

        if (
            (bool) (
                $currentUser?->is_super_user
                ?? false
            )
        ) {

            return str_starts_with(
                $permission,
                'super-admin.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Admin
        |--------------------------------------------------------------------------
        |
        | Company-created operational roles cannot receive company/user/role
        | administration permissions.
        |
        */

        $blockedPrefixes = [

            'company.',
            'users.',
            'roles.',
            'super-admin.',
        ];


        foreach (
            $blockedPrefixes as $prefix
        ) {

            if (
                str_starts_with(
                    $permission,
                    $prefix
                )
            ) {

                return true;
            }
        }


        $blockedPermissions = [

            'store.show',
            'store.create',
            'store.edit',
            'store.delete',
        ];


        return in_array(
            $permission,
            $blockedPermissions,
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Assignable Permissions
    |--------------------------------------------------------------------------
    */

    private function getAssignablePermissions()
    {

        return Permission::query()

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
                'guard_name',
            ])

            ->reject(
                fn(
                    Permission $permission
                ) =>
                $this
                    ->isBlockedCompanyRolePermission(
                        $permission->name
                    )
            )

            ->values();
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


        /*
        |--------------------------------------------------------------------------
        | Normalize IDs
        |--------------------------------------------------------------------------
        */

        $permissionIds =
            collect(
                $permissionIds
            )

            ->map(
                fn(
                    $id
                ) =>
                (int) $id
            )

            ->unique()

            ->values();


        /*
        |--------------------------------------------------------------------------
        | Load Permissions
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
        | Invalid ID Protection
        |--------------------------------------------------------------------------
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
        | Administrative Permission Protection
        |--------------------------------------------------------------------------
        */

        foreach (
            $permissions as $permission
        ) {

            if (
                $this
                ->isBlockedCompanyRolePermission(
                    $permission->name
                )
            ) {

                abort(
                    403,
                    "Permission {$permission->name} cannot be assigned to a custom company role."
                );
            }
        }


        return $permissions;
    }


    /*
    |--------------------------------------------------------------------------
    | Permission Groups
    |--------------------------------------------------------------------------
    */

    private function getPermissionGroups(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Module Labels
        |--------------------------------------------------------------------------
        */

        $moduleLabels = [

            'dashboard' =>
            'Dashboard',

            'store' =>
            'Branches',

            'godown' =>
            'Godowns',

            'brand' =>
            'Brands',

            'unit' =>
            'Units',

            'category' =>
            'Categories',

            'product' =>
            'Products',

            'supplier' =>
            'Suppliers',

            'customer' =>
            'Customers',

            'purchase' =>
            'Purchases',

            'purchase-return' =>
            'Purchase Returns',

            'purchase-report' =>
            'Purchase Reports',

            'sales' =>
            'Sales',

            'sales-return' =>
            'Sales Returns',

            'sales-report' =>
            'Sales Reports',

            'quotation' =>
            'Quotations',

            'current-stock' =>
            'Current Stock',

            'stock-ledger' =>
            'Stock Ledger',

            'stock-adjustment' =>
            'Stock Adjustment',

            'stock-transfer' =>
            'Stock Transfer',

            'stock' =>
            'Stock',

            'accounts' =>
            'Accounts',

            'customer-payment' =>
            'Customer Payments',

            'customer-ledger' =>
            'Customer Ledger',

            'reports' =>
            'Reports',
        ];


        /*
        |--------------------------------------------------------------------------
        | Action Labels
        |--------------------------------------------------------------------------
        */

        $actionLabels = [

            'view' =>
            'View',

            'show' =>
            'Show',

            'create' =>
            'Create',

            'edit' =>
            'Edit',

            'delete' =>
            'Delete',

            'status' =>
            'Status',

            'approve' =>
            'Approve',

            'print' =>
            'Print',

            'pdf' =>
            'PDF',

            'excel' =>
            'Excel',

            'adjust' =>
            'Adjust',

            'convert-sale' =>
            'Convert to Sale',
        ];


        /*
        |--------------------------------------------------------------------------
        | Get Assignable Permissions
        |--------------------------------------------------------------------------
        */

        $permissions =
            $this->getAssignablePermissions();


        /*
        |--------------------------------------------------------------------------
        | Groups
        |--------------------------------------------------------------------------
        */

        $groups = [];


        foreach (
            $permissions as $permission
        ) {

            /*
            |--------------------------------------------------------------------------
            | Split At Last Dot
            |--------------------------------------------------------------------------
            */

            $lastDotPosition =
                strrpos(
                    $permission->name,
                    '.'
                );


            if (
                $lastDotPosition ===
                false
            ) {

                $module =
                    $permission->name;


                $action =
                    'view';
            } else {

                $module =
                    substr(
                        $permission->name,
                        0,
                        $lastDotPosition
                    );


                $action =
                    substr(
                        $permission->name,
                        $lastDotPosition + 1
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Create Module
            |--------------------------------------------------------------------------
            */

            if (
                !isset(
                    $groups[$module]
                )
            ) {

                $groups[$module] = [

                    'module' =>
                    $module,

                    'label' =>
                    $moduleLabels[$module]
                        ??
                        str(
                            $module
                        )
                        ->replace(
                            [
                                '-',
                                '_',
                            ],
                            ' '
                        )
                        ->title()
                        ->toString(),

                    'permissions' =>
                    [],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Permission
            |--------------------------------------------------------------------------
            */

            $groups[$module]['permissions'][] = [

                'id' =>
                $permission->id,

                'name' =>
                $permission->name,

                'action' =>
                $action,

                'label' =>
                $actionLabels[$action]
                    ??
                    str(
                        $action
                    )
                    ->replace(
                        [
                            '-',
                            '_',
                        ],
                        ' '
                    )
                    ->title()
                    ->toString(),
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Action Order
        |--------------------------------------------------------------------------
        */

        $actionOrder = [

            'view' =>
            10,

            'show' =>
            20,

            'create' =>
            30,

            'edit' =>
            40,

            'delete' =>
            50,

            'approve' =>
            60,

            'adjust' =>
            70,

            'convert-sale' =>
            80,

            'status' =>
            90,

            'print' =>
            100,

            'pdf' =>
            110,

            'excel' =>
            120,
        ];


        /*
        |--------------------------------------------------------------------------
        | Sort Permissions
        |--------------------------------------------------------------------------
        */

        foreach (
            $groups as &$group
        ) {

            usort(
                $group['permissions'],
                function (
                    array $a,
                    array $b
                ) use (
                    $actionOrder
                ) {

                    $aOrder =
                        $actionOrder[$a['action']]
                        ?? 999;


                    $bOrder =
                        $actionOrder[$b['action']]
                        ?? 999;


                    return $aOrder
                        <=>
                        $bOrder;
                }
            );
        }


        unset(
            $group
        );


        /*
        |--------------------------------------------------------------------------
        | Module Order
        |--------------------------------------------------------------------------
        */

        $moduleOrder = [

            'dashboard',

            'customer',
            'supplier',
            'product',
            'category',
            'brand',
            'unit',
            'godown',

            'purchase',
            'purchase-return',

            'sales',
            'sales-return',
            'quotation',

            'current-stock',
            'stock-ledger',
            'stock-adjustment',
            'stock-transfer',
            'stock',

            'customer-payment',
            'customer-ledger',
            'accounts',

            'purchase-report',
            'sales-report',
            'reports',

            /*
            |--------------------------------------------------------------------------
            | Only store.view survives the blocked branch-management rules.
            |--------------------------------------------------------------------------
            */

            'store',
        ];


        /*
        |--------------------------------------------------------------------------
        | Sort Modules
        |--------------------------------------------------------------------------
        */

        uasort(
            $groups,
            function (
                array $a,
                array $b
            ) use (
                $moduleOrder
            ) {

                $aPosition =
                    array_search(
                        $a['module'],
                        $moduleOrder,
                        true
                    );


                $bPosition =
                    array_search(
                        $b['module'],
                        $moduleOrder,
                        true
                    );


                $aPosition =
                    $aPosition === false
                    ? 999
                    : $aPosition;


                $bPosition =
                    $bPosition === false
                    ? 999
                    : $bPosition;


                if (
                    $aPosition ===
                    $bPosition
                ) {

                    return strcmp(
                        $a['label'],
                        $b['label']
                    );
                }


                return $aPosition
                    <=>
                    $bPosition;
            }
        );


        return array_values(
            $groups
        );
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
            $role->permissions
                ->count(),

            'users_count' =>
            $role->users_count,

            /*
            |--------------------------------------------------------------------------
            | Edit
            |--------------------------------------------------------------------------
            */

            'permission_ids' =>
            $role->permissions

                ->pluck(
                    'id'
                )

                ->map(
                    fn(
                        $id
                    ) =>
                    (int) $id
                )

                ->values()

                ->all(),

            /*
            |--------------------------------------------------------------------------
            | Show
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
