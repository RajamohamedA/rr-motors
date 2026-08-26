<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SuperAdminNotificationService;
use App\Support\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $users =
            User::query()
            ->with([
                'company:id,company_name,company_code',
                'stores:id,store_code,store_name,company_id',
                'roles:id,name',
            ])
            ->where(
                'is_super_user',
                false
            )
            ->orderBy(
                'name'
            )
            ->get()
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

                        'company_id' =>
                        $user->company_id,

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

                        'stores' =>
                        $user->stores
                            ->map(
                                fn($store) => [

                                    'id' =>
                                    $store->id,

                                    'store_code' =>
                                    $store->store_code,

                                    'store_name' =>
                                    $store->store_name,
                                ]
                            )
                            ->values(),

                        'roles' =>
                        $user->roles
                            ->pluck(
                                'name'
                            )
                            ->values(),

                        'status' =>
                        (bool) $user->status,

                        'last_login_at' =>
                        $user->last_login_at,
                    ];
                }
            );


        return Inertia::render(
            'SuperAdmin/Users/Index',
            [
                'users' =>
                $users,
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
        $companies =
            Company::query()
            ->where(
                'status',
                1
            )
            ->orderBy(
                'company_name'
            )
            ->get([
                'id',
                'company_name',
                'company_code',
            ]);


        $roles =
            Role::query()
            ->orderBy(
                'name'
            )
            ->get([
                'id',
                'name',
            ]);


        return Inertia::render(
            'SuperAdmin/Users/Create',
            [
                'companies' =>
                $companies,

                'roles' =>
                $roles,
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
        
        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],

                'mobile' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'company_id' => [
                    'required',
                    'exists:companies,id',
                ],

                'store_ids' => [
                    'nullable',
                    'array',
                ],

                'store_ids.*' => [
                    'integer',
                    'exists:stores,id',
                ],

                'role' => [
                    'required',
                    'exists:roles,name',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Branch IDs
        |--------------------------------------------------------------------------
        */

        $storeIds =
            collect(
                $validated['store_ids']
                    ?? []
            )
            ->map(
                fn($id) =>
                (int) $id
            )
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Validate Branch Scope
        |--------------------------------------------------------------------------
        */

        if (
            !$this->storesBelongToCompany(
                (int) $validated['company_id'],
                $storeIds
            )
        ) {

            return AppResponse::error(
                'One or more selected branches do not belong to the selected company.',
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()
            ->whereKey(
                $validated['company_id']
            )
            ->firstOrFail();


        if (
            !(bool) $company->status
        ) {

            return AppResponse::error(
                'The selected company is inactive. Activate the company before creating this user.',
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Create User
        |--------------------------------------------------------------------------
        */

        $user =
            User::create([

                'company_id' =>
                $validated['company_id'],

                'store_id' =>
                $storeIds->count() === 1
                    ? $storeIds->first()
                    : null,

                'name' =>
                $validated['name'],

                'email' =>
                $validated['email'],

                'mobile' =>
                $validated['mobile']
                    ?? null,

                'password' =>
                Hash::make(
                    $validated['password']
                ),

                'status' =>
                $validated['status'],

                'is_super_user' =>
                false,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Branch Assignments
        |--------------------------------------------------------------------------
        */

        $user
            ->stores()
            ->sync(
                $storeIds->all()
            );


        /*
        |--------------------------------------------------------------------------
        | Role
        |--------------------------------------------------------------------------
        */

        $user->syncRoles([
            $validated['role'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Audit Relations
        |--------------------------------------------------------------------------
        */

        $this->loadUserAuditRelations(
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'user',

            action: 'created',

            description: "Created user {$user->name} ({$user->email})",

            subject: $user,

            newValues: $this->userAuditValues(
                $user
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        SuperAdminNotificationService::send(
            title: 'User Created',

            message: "{$user->name} ({$user->email}) has been created.",

            category: 'user',

            icon: 'bi-person-plus',

            targetType: 'user',

            targetId: $user->id,

            fallbackUrl: route(
                'super-admin.users.index'
            ),

            level: 'success',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'super-admin.users.index',
            'User created successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        User $user
    ) {
        $this->protectSuperAdminUser(
            $user,
            'Super Administrator cannot be edited here.'
        );


        $companies =
            Company::query()
            ->where(
                'status',
                1
            )
            ->orderBy(
                'company_name'
            )
            ->get([
                'id',
                'company_name',
                'company_code',
            ]);


        $stores =
            Store::query()
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'status',
                1
            )
            ->orderBy(
                'branch_number'
            )
            ->orderBy(
                'store_name'
            )
            ->get([
                'id',
                'store_code',
                'store_name',
            ]);


        $roles =
            Role::query()
            ->orderBy(
                'name'
            )
            ->get([
                'id',
                'name',
            ]);


        return Inertia::render(
            'SuperAdmin/Users/Edit',
            [
                'userData' => [

                    'id' =>
                    $user->id,

                    'name' =>
                    $user->name,

                    'email' =>
                    $user->email,

                    'mobile' =>
                    $user->mobile,

                    'company_id' =>
                    $user->company_id,

                    'store_ids' =>
                    $user
                        ->stores()
                        ->pluck(
                            'stores.id'
                        )
                        ->map(
                            fn($id) =>
                            (int) $id
                        )
                        ->values(),

                    'role' =>
                    $user->roles
                        ->pluck(
                            'name'
                        )
                        ->first(),

                    'status' =>
                    (bool) $user->status,
                ],

                'companies' =>
                $companies,

                'stores' =>
                $stores,

                'roles' =>
                $roles,
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
        User $user
    ) {
        $this->protectSuperAdminUser(
            $user
        );


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
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',

                    Rule::unique(
                        'users',
                        'email'
                    )->ignore(
                        $user->id
                    ),
                ],

                'mobile' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'company_id' => [
                    'required',
                    'exists:companies,id',
                ],

                'store_ids' => [
                    'nullable',
                    'array',
                ],

                'store_ids.*' => [
                    'integer',
                    'exists:stores,id',
                ],

                'role' => [
                    'required',
                    'exists:roles,name',
                ],

                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'confirmed',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Branch IDs
        |--------------------------------------------------------------------------
        */

        $storeIds =
            collect(
                $validated['store_ids']
                    ?? []
            )
            ->map(
                fn($id) =>
                (int) $id
            )
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Validate Branch Scope
        |--------------------------------------------------------------------------
        */

        if (
            !$this->storesBelongToCompany(
                (int) $validated['company_id'],
                $storeIds
            )
        ) {

            return AppResponse::error(
                'One or more selected branches do not belong to the selected company.',
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()
            ->find(
                $validated['company_id']
            );


        if (
            !$company ||
            !(bool) $company->status
        ) {

            return AppResponse::error(
                'The selected company is inactive. Activate the company before assigning this user.',
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Old State
        |--------------------------------------------------------------------------
        */

        $this->loadUserAuditRelations(
            $user
        );


        $oldValues =
            $this->userAuditValues(
                $user
            );


        $oldRole =
            $oldValues['role']
            ?? null;

        $oldCompany =
            $oldValues['company']
            ?? null;

        $oldBranches =
            $oldValues['branches']
            ?? null;

        $oldStatus =
            (bool) $user->status;


        /*
        |--------------------------------------------------------------------------
        | Update Data
        |--------------------------------------------------------------------------
        */

        $updateData = [

            'company_id' =>
            $validated['company_id'],

            'store_id' =>
            $storeIds->count() === 1
                ? $storeIds->first()
                : null,

            'name' =>
            $validated['name'],

            'email' =>
            $validated['email'],

            'mobile' =>
            $validated['mobile']
                ?? null,

            'status' =>
            $validated['status'],
        ];


        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        */

        $passwordChanged =
            !empty($validated['password']);


        if (
            $passwordChanged
        ) {

            $updateData['password'] =
                Hash::make(
                    $validated['password']
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $user->update(
            $updateData
        );


        $user
            ->stores()
            ->sync(
                $storeIds->all()
            );


        $user->syncRoles([
            $validated['role'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Reload
        |--------------------------------------------------------------------------
        */

        $user->refresh();


        $this->loadUserAuditRelations(
            $user
        );


        $newValues =
            $this->userAuditValues(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Detect Changes
        |--------------------------------------------------------------------------
        */

        [
            $changedOldValues,
            $changedNewValues,
        ] =
            $this->changedAuditValues(
                $oldValues,
                $newValues
            );


        /*
        |--------------------------------------------------------------------------
        | Password Change
        |--------------------------------------------------------------------------
        */

        if (
            $passwordChanged
        ) {

            $changedOldValues['password'] =
                '********';

            $changedNewValues['password'] =
                'Changed';
        }


        /*
        |--------------------------------------------------------------------------
        | No Changes
        |--------------------------------------------------------------------------
        */

        if (
            empty($changedNewValues)
        ) {

            return AppResponse::info(
                'No changes were detected for this user.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'user',

            action: 'updated',

            description: "Updated user {$user->name} ({$user->email})",

            subject: $user,

            oldValues: $changedOldValues,

            newValues: $changedNewValues
        );


        /*
        |--------------------------------------------------------------------------
        | Security Changes
        |--------------------------------------------------------------------------
        */

        $newRole =
            $newValues['role']
            ?? null;

        $newCompany =
            $newValues['company']
            ?? null;

        $newBranches =
            $newValues['branches']
            ?? null;

        $newStatus =
            (bool) $user->status;


        $securityChanges =
            [];


        if (
            $oldRole !==
            $newRole
        ) {

            $securityChanges[] =
                'role changed';
        }


        if (
            $oldCompany !==
            $newCompany
        ) {

            $securityChanges[] =
                'company assignment changed';
        }


        if (
            $oldBranches !==
            $newBranches
        ) {

            $securityChanges[] =
                'branch access changed';
        }


        if (
            $oldStatus !==
            $newStatus
        ) {

            $securityChanges[] =
                $newStatus
                ? 'account activated'
                : 'account deactivated';
        }


        if (
            $passwordChanged
        ) {

            $securityChanges[] =
                'password changed';
        }


        /*
        |--------------------------------------------------------------------------
        | Security Notification
        |--------------------------------------------------------------------------
        */

        if (
            !empty($securityChanges)
        ) {

            SuperAdminNotificationService::send(
                title: 'User Access Changed',

                message: "{$user->name} ({$user->email}): "
                    . implode(
                        ', ',
                        $securityChanges
                    )
                    . '.',

                category: 'security',

                icon: 'bi-person-lock',

                targetType: 'user',

                targetId: $user->id,

                fallbackUrl: route(
                    'super-admin.users.index'
                ),

                level: 'warning',
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'super-admin.users.index',
            'User updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        User $user
    ) {
        $this->protectSuperAdminUser(
            $user,
            'Super Administrator accounts are managed separately.'
        );


        $user->load([
            'company:id,company_name,company_code',

            'stores:id,company_id,store_code,store_name,city,state,status',

            'roles:id,name',
        ]);


        $permissions =
            $user
            ->getAllPermissions()
            ->sortBy(
                'name'
            );


        $permissionGroups =
            $permissions
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


        return Inertia::render(
            'SuperAdmin/Users/Show',
            [
                'userData' => [

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

                    'last_login_at' =>
                    $user->last_login_at,

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

                    'stores' =>
                    $user->stores
                        ->map(
                            fn($store) => [

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
                            ]
                        )
                        ->values(),

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

                    'permissions_count' =>
                    $permissions->count(),

                    'permission_groups' =>
                    $permissionGroups,
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        User $user
    ) {
        $this->protectSuperAdminUser(
            $user,
            'Super Administrator accounts cannot be deleted here.'
        );


        /*
        |--------------------------------------------------------------------------
        | Prevent Self Delete
        |--------------------------------------------------------------------------
        */

        if (
            auth()->id() ===
            $user->id
        ) {

            return AppResponse::error(
                'You cannot delete your own account.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Load Existing State
        |--------------------------------------------------------------------------
        */

        $this->loadUserAuditRelations(
            $user
        );


        $oldValues =
            $this->userAuditValues(
                $user
            );


        $userId =
            $user->id;

        $userName =
            $user->name;

        $userEmail =
            $user->email;


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'user',

            action: 'deleted',

            description: "Deleted user {$userName} ({$userEmail})",

            subject: $user,

            oldValues: $oldValues,

            newValues: [
                'record_state' =>
                'Deleted',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Remove Assignments
        |--------------------------------------------------------------------------
        */

        $user
            ->stores()
            ->detach();


        $user->syncRoles([]);


        /*
        |--------------------------------------------------------------------------
        | Delete
        |--------------------------------------------------------------------------
        */

        $user->delete();


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        SuperAdminNotificationService::send(
            title: 'User Deleted',

            message: "{$userName} ({$userEmail}) has been deleted.",

            category: 'user',

            icon: 'bi-person-dash',

            targetType: 'user',

            targetId: $userId,

            fallbackUrl: route(
                'super-admin.users.index'
            ),

            level: 'danger',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'super-admin.users.index',
            'User deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate
    |--------------------------------------------------------------------------
    */

    public function activate(
        User $user
    ) {
        $this->protectSuperAdminUser(
            $user,
            'Super Administrator accounts are managed separately.'
        );


        /*
        |--------------------------------------------------------------------------
        | Already Active
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $user->status
        ) {

            return AppResponse::info(
                'User is already active.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            $user->company_id
        ) {

            $company =
                Company::find(
                    $user->company_id
                );


            if (
                !$company ||
                !(bool) $company->status
            ) {

                return AppResponse::error(
                    'The user company is inactive. Activate the company before activating this user.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Activate
        |--------------------------------------------------------------------------
        */

        $user->update([
            'status' =>
            true,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'user',

            action: 'activated',

            description: "Activated user {$user->name} ({$user->email})",

            subject: $user,

            oldValues: [
                'status' =>
                false,
            ],

            newValues: [
                'status' =>
                true,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        SuperAdminNotificationService::send(
            title: 'User Activated',

            message: "{$user->name} ({$user->email}) has been activated.",

            category: 'user',

            icon: 'bi-person-check',

            targetType: 'user',

            targetId: $user->id,

            fallbackUrl: route(
                'super-admin.users.index'
            ),

            level: 'success',
        );


        return AppResponse::success(
            'User activated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Deactivate
    |--------------------------------------------------------------------------
    */

    public function deactivate(
        User $user
    ) {
        $this->protectSuperAdminUser(
            $user,
            'Super Administrator accounts are managed separately.'
        );


        /*
        |--------------------------------------------------------------------------
        | Prevent Self Deactivation
        |--------------------------------------------------------------------------
        */

        if (
            auth()->id() ===
            $user->id
        ) {

            return AppResponse::error(
                'You cannot deactivate your own account.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Already Inactive
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $user->status
        ) {

            return AppResponse::info(
                'User is already inactive.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Deactivate
        |--------------------------------------------------------------------------
        */

        $user->update([
            'status' =>
            false,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'user',

            action: 'deactivated',

            description: "Deactivated user {$user->name} ({$user->email})",

            subject: $user,

            oldValues: [
                'status' =>
                true,
            ],

            newValues: [
                'status' =>
                false,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        SuperAdminNotificationService::send(
            title: 'User Deactivated',

            message: "{$user->name} ({$user->email}) has been deactivated.",

            category: 'user',

            icon: 'bi-person-x',

            targetType: 'user',

            targetId: $user->id,

            fallbackUrl: route(
                'super-admin.users.index'
            ),

            level: 'warning',
        );


        return AppResponse::success(
            'User deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Branch Options
    |--------------------------------------------------------------------------
    */

    public function branchOptions(
        Company $company
    ) {
        $branches =
            $company
            ->stores()
            ->where(
                'status',
                1
            )
            ->orderBy(
                'branch_number'
            )
            ->orderBy(
                'store_name'
            )
            ->get([
                'id',
                'store_code',
                'store_name',
            ]);


        return response()->json([
            'branches' =>
            $branches,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Stores Belong To Company
    |--------------------------------------------------------------------------
    */

    private function storesBelongToCompany(
        int $companyId,
        $storeIds
    ): bool {

        if (
            $storeIds->isEmpty()
        ) {

            return true;
        }


        $validStoreCount =
            Store::query()
            ->where(
                'company_id',
                $companyId
            )
            ->whereIn(
                'id',
                $storeIds->all()
            )
            ->count();


        return $validStoreCount ===
            $storeIds->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Load Audit Relationships
    |--------------------------------------------------------------------------
    */

    private function loadUserAuditRelations(
        User $user
    ): void {

        $user->load([
            'company:id,company_name,company_code',

            'stores:id,company_id,store_code,store_name',

            'roles:id,name',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | User Audit Values
    |--------------------------------------------------------------------------
    */

    private function userAuditValues(
        User $user
    ): array {

        return [

            'name' =>
            $user->name,

            'email' =>
            $user->email,

            'mobile' =>
            $user->mobile,

            'company' =>
            $user->company
                ? sprintf(
                    '%s (%s)',
                    $user->company->company_name,
                    $user->company->company_code
                )
                : null,

            'branches' =>
            $user->stores
                ->map(
                    fn($store) =>
                    sprintf(
                        '%s (%s)',
                        $store->store_name,
                        $store->store_code
                    )
                )
                ->sort()
                ->values()
                ->implode(', '),

            'role' =>
            $user->roles
                ->pluck(
                    'name'
                )
                ->sort()
                ->values()
                ->implode(', '),

            'status' =>
            (bool) $user->status,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Detect Changed Audit Values
    |--------------------------------------------------------------------------
    */

    private function changedAuditValues(
        array $oldValues,
        array $newValues
    ): array {

        $changedOld =
            [];

        $changedNew =
            [];


        foreach (
            $newValues as
            $key => $newValue
        ) {

            $oldValue =
                $oldValues[$key]
                ?? null;


            if (
                $oldValue !=
                $newValue
            ) {

                $changedOld[$key] =
                    $oldValue;

                $changedNew[$key] =
                    $newValue;
            }
        }


        return [
            $changedOld,
            $changedNew,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Protect Super Admin Account
    |--------------------------------------------------------------------------
    */

    private function protectSuperAdminUser(
        User $user,
        string $message =
        'Super Administrator accounts are managed separately.'
    ): void {

        abort_if(
            (bool) $user->is_super_user,
            403,
            $message
        );
    }
}
