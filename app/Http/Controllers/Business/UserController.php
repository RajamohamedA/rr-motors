<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use App\Support\AppResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
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


        $role =
            trim(
                $request
                    ->string('role')
                    ->toString()
            );


        $status =
            $request->input(
                'status'
            );


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        $users =
            User::query()

            ->where(
                'company_id',
                $company->id
            )

            ->where(
                'is_super_user',
                false
            )

            ->with([
                'roles:id,company_id,name,status',
                'stores:id,company_id,store_name,store_code,status',
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
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'mobile',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            /*
                |--------------------------------------------------------------------------
                | Company Scoped Role Filter
                |--------------------------------------------------------------------------
                */

            ->when(
                $role !== '',
                function (
                    $query
                ) use (
                    $role,
                    $company
                ) {

                    $query->whereHas(
                        'roles',
                        function (
                            $roleQuery
                        ) use (
                            $role,
                            $company
                        ) {

                            $roleQuery
                                ->where(
                                    'roles.name',
                                    $role
                                )
                                ->where(
                                    function (
                                        $q
                                    ) use (
                                        $company
                                    ) {

                                        /*
                                            |--------------------------------------------------------------------------
                                            | Company Admin Is Global
                                            |--------------------------------------------------------------------------
                                            */

                                        $q->where(
                                            function (
                                                $globalQuery
                                            ) {

                                                $globalQuery
                                                    ->whereNull(
                                                        'roles.company_id'
                                                    )
                                                    ->where(
                                                        'roles.name',
                                                        'Company Admin'
                                                    );
                                            }
                                        )

                                            /*
                                                |--------------------------------------------------------------------------
                                                | Custom Company Roles
                                                |--------------------------------------------------------------------------
                                                */

                                            ->orWhere(
                                                'roles.company_id',
                                                $company->id
                                            );
                                    }
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

        $users->through(
            function (
                User $user
            ) use (
                $currentUser
            ) {

                $isCompanyAdmin =
                    $user->hasRole(
                        'Company Admin'
                    );


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

                    'role' =>
                    $user->roles
                        ->pluck('name')
                        ->first(),

                    'roles' =>
                    $user->roles
                        ->pluck('name')
                        ->values()
                        ->all(),

                    /*
                    |--------------------------------------------------------------------------
                    | Company Admin Has All Branch Access
                    |--------------------------------------------------------------------------
                    */

                    'all_branches' =>
                    $isCompanyAdmin,

                    'branches' =>
                    $isCompanyAdmin
                        ? []
                        : $user->stores
                        ->map(
                            fn(
                                Store $store
                            ) => [

                                'id' =>
                                $store->id,

                                'store_name' =>
                                $store->store_name,

                                'store_code' =>
                                $store->store_code,

                                'status' =>
                                (bool) $store->status,
                            ]
                        )
                        ->values()
                        ->all(),

                    'is_current_user' =>
                    (int) $currentUser->id ===
                        (int) $user->id,

                    'is_company_admin' =>
                    $isCompanyAdmin,

                    'created_at' =>
                    $user->created_at
                        ?->toISOString(),

                    'updated_at' =>
                    $user->updated_at
                        ?->toISOString(),
                ];
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $baseQuery =
            User::query()
            ->where(
                'company_id',
                $company->id
            )
            ->where(
                'is_super_user',
                false
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

            'branches' =>
            Store::query()
                ->where(
                    'company_id',
                    $company->id
                )
                ->where(
                    'status',
                    true
                )
                ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Filter Roles
        |--------------------------------------------------------------------------
        */

        $roles =
            $this->getFilterRoles(
                $company
            );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Business/Users/Index',
            [

                'company' =>
                $this->companyData(
                    $company
                ),

                'users' =>
                $users,

                'summary' =>
                $summary,

                'roles' =>
                $roles,

                'filters' => [

                    'search' =>
                    $search,

                    'role' =>
                    $role,

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
            'Business/Users/Create',
            [

                'company' =>
                $this->companyData(
                    $company
                ),

                'roles' =>
                $this->getAssignableRoles(
                    $company
                ),

                'branches' =>
                $this->getCompanyBranches(
                    $company
                ),
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
    ): RedirectResponse {

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
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',

                    Rule::unique(
                        'users',
                        'email'
                    ),
                ],

                'mobile' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8),
                ],

                'role' => [
                    'required',
                    'string',
                ],

                'store_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'store_ids.*' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'stores',
                        'id'
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
                                'status',
                                true
                            )
                    ),
                ],

                'status' => [
                    'required',
                    'boolean',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Company Role
        |--------------------------------------------------------------------------
        */

        $role =
            $this->getAllowedRole(
                $validated['role'],
                $company
            );


        /*
        |--------------------------------------------------------------------------
        | Create User
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $validated,
                $company,
                $role
            ) {

                $storeIds =
                    array_values(
                        array_unique(
                            array_map(
                                'intval',
                                $validated['store_ids']
                            )
                        )
                    );


                $user =
                    User::create([

                        'company_id' =>
                        $company->id,

                        /*
                        |--------------------------------------------------------------------------
                        | Primary Branch
                        |--------------------------------------------------------------------------
                        */

                        'store_id' =>
                        $storeIds[0]
                            ?? null,

                        'name' =>
                        trim(
                            $validated['name']
                        ),

                        'email' =>
                        strtolower(
                            trim(
                                $validated['email']
                            )
                        ),

                        'mobile' =>
                        !empty($validated['mobile'])
                            ? trim(
                                $validated['mobile']
                            )
                            : null,

                        'password' =>
                        Hash::make(
                            $validated['password']
                        ),

                        'status' =>
                        (bool) $validated['status'],

                        /*
                        |--------------------------------------------------------------------------
                        | Never Super Admin
                        |--------------------------------------------------------------------------
                        */

                        'is_super_user' =>
                        false,
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Company Role
                |--------------------------------------------------------------------------
                */

                $user->syncRoles([
                    $role,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Branch Access
                |--------------------------------------------------------------------------
                */

                $user
                    ->stores()
                    ->sync(
                        $storeIds
                    );
            }
        );


        return AppResponse::successRoute(
            'business.users.index',
            'User created successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        User $user
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


        $this->ensureUserBelongsToCompany(
            $user,
            $company
        );


        $user->load([
            'roles:id,company_id,name,status',
            'stores:id,company_id,store_name,store_code,status',
        ]);


        return Inertia::render(
            'Business/Users/Show',
            [

                'company' =>
                $this->companyData(
                    $company
                ),

                'managedUser' =>
                $this->userData(
                    $user,
                    $currentUser
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
        User $user
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


        $this->ensureUserBelongsToCompany(
            $user,
            $company
        );


        /*
        |--------------------------------------------------------------------------
        | Protect Company Admin
        |--------------------------------------------------------------------------
        */

        if (
            $user->hasRole(
                'Company Admin'
            )
        ) {

            abort(
                403,
                'Company Admin accounts can only be managed by Super Admin.'
            );
        }


        $user->load([
            'roles:id,company_id,name,status',
            'stores:id,company_id,store_name,store_code,status',
        ]);


        return Inertia::render(
            'Business/Users/Edit',
            [

                'company' =>
                $this->companyData(
                    $company
                ),

                'managedUser' =>
                $this->userData(
                    $user,
                    $currentUser
                ),

                'roles' =>
                $this->getAssignableRoles(
                    $company
                ),

                'branches' =>
                $this->getCompanyBranches(
                    $company
                ),
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
    ): RedirectResponse {

        $currentUser =
            $request->user();


        $this->ensureCompanyAdmin(
            $currentUser
        );


        $company =
            $this->getCompany(
                $currentUser
            );


        $this->ensureUserBelongsToCompany(
            $user,
            $company
        );


        /*
        |--------------------------------------------------------------------------
        | Protect Company Admin
        |--------------------------------------------------------------------------
        */

        if (
            $user->hasRole(
                'Company Admin'
            )
        ) {

            return AppResponse::error(
                'Company Admin accounts can only be managed by Super Admin.'
            );
        }


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
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',

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

                'password' => [
                    'nullable',
                    'confirmed',
                    Password::min(8),
                ],

                'role' => [
                    'required',
                    'string',
                ],

                'store_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'store_ids.*' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'stores',
                        'id'
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
                                'status',
                                true
                            )
                    ),
                ],

                'status' => [
                    'required',
                    'boolean',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Company Role
        |--------------------------------------------------------------------------
        */

        $role =
            $this->getAllowedRole(
                $validated['role'],
                $company
            );


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $validated,
                $user,
                $role
            ) {

                $storeIds =
                    array_values(
                        array_unique(
                            array_map(
                                'intval',
                                $validated['store_ids']
                            )
                        )
                    );


                $data = [

                    'name' =>
                    trim(
                        $validated['name']
                    ),

                    'email' =>
                    strtolower(
                        trim(
                            $validated['email']
                        )
                    ),

                    'mobile' =>
                    !empty($validated['mobile'])
                        ? trim(
                            $validated['mobile']
                        )
                        : null,

                    'store_id' =>
                    $storeIds[0]
                        ?? null,

                    'status' =>
                    (bool) $validated['status'],
                ];


                /*
                |--------------------------------------------------------------------------
                | Password Only When Entered
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($validated['password'])
                ) {

                    $data['password'] =
                        Hash::make(
                            $validated['password']
                        );
                }


                $user->update(
                    $data
                );


                /*
                |--------------------------------------------------------------------------
                | Company Role
                |--------------------------------------------------------------------------
                */

                $user->syncRoles([
                    $role,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Branch Access
                |--------------------------------------------------------------------------
                */

                $user
                    ->stores()
                    ->sync(
                        $storeIds
                    );
            }
        );


        return AppResponse::successRoute(
            'business.users.index',
            'User updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        User $user
    ): RedirectResponse {

        $currentUser =
            $request->user();


        $this->ensureCompanyAdmin(
            $currentUser
        );


        $company =
            $this->getCompany(
                $currentUser
            );


        $this->ensureUserBelongsToCompany(
            $user,
            $company
        );


        /*
        |--------------------------------------------------------------------------
        | Cannot Change Own Status
        |--------------------------------------------------------------------------
        */

        if (
            (int) $user->id ===
            (int) $currentUser->id
        ) {

            return AppResponse::error(
                'You cannot change the status of your own account.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Admin Protected
        |--------------------------------------------------------------------------
        */

        if (
            $user->hasRole(
                'Company Admin'
            )
        ) {

            return AppResponse::error(
                'Company Admin status can only be managed by Super Admin.'
            );
        }


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
        | Update
        |--------------------------------------------------------------------------
        */

        $user->update([
            'status' =>
            (bool) $validated['status'],
        ]);


        return AppResponse::success(
            $user->status
                ? 'User activated successfully.'
                : 'User deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Company
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
    | Company Admin Security
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
        | Super Admin Uses Super Admin Area
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $user->is_super_user
        ) {

            abort(
                403,
                'Use the Super Admin user management area.'
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
                'Only Company Administrators can manage users.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | User Company Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureUserBelongsToCompany(
        User $user,
        Company $company
    ): void {

        abort_unless(
            (int) $user->company_id ===
                (int) $company->id
                &&
                !(bool) $user->is_super_user,
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Assignable Company Roles
    |--------------------------------------------------------------------------
    |
    | Only ACTIVE custom roles belonging to this company.
    |
    | Global roles such as:
    |
    | Super Admin
    | Company Admin
    |
    | are automatically excluded because company_id is NULL.
    |
    */

    private function getAssignableRoles(
        Company $company
    ): array {

        return Role::query()

            ->where(
                'company_id',
                $company->id
            )

            ->where(
                'guard_name',
                'web'
            )

            ->where(
                'status',
                true
            )

            ->orderBy(
                'name'
            )

            ->get([
                'id',
                'company_id',
                'name',
                'description',
            ])

            ->map(
                fn(
                    Role $role
                ) => [

                    'id' =>
                    $role->id,

                    'name' =>
                    $role->name,

                    'description' =>
                    $role->description,
                ]
            )

            ->values()

            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Filter Roles
    |--------------------------------------------------------------------------
    |
    | Used by Users & Access index.
    |
    | Include:
    |
    | Company Admin
    | +
    | this company's custom roles
    |
    */

    private function getFilterRoles(
        Company $company
    ): array {

        return Role::query()

            ->where(
                'guard_name',
                'web'
            )

            ->where(
                function (
                    $query
                ) use (
                    $company
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Global Company Admin
                    |--------------------------------------------------------------------------
                    */

                    $query->where(
                        function (
                            $globalQuery
                        ) {

                            $globalQuery
                                ->whereNull(
                                    'company_id'
                                )
                                ->where(
                                    'name',
                                    'Company Admin'
                                );
                        }
                    )

                        /*
                        |--------------------------------------------------------------------------
                        | Company's Custom Roles
                        |--------------------------------------------------------------------------
                        */

                        ->orWhere(
                            'company_id',
                            $company->id
                        );
                }
            )

            ->orderBy(
                'name'
            )

            ->get([
                'id',
                'company_id',
                'name',
            ])

            ->map(
                fn(
                    Role $role
                ) => [

                    'id' =>
                    $role->id,

                    'name' =>
                    $role->name,
                ]
            )

            ->values()

            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Company Role
    |--------------------------------------------------------------------------
    |
    | Important:
    |
    | Never search only by role name.
    |
    | Two different companies can both have:
    |
    | Sales Staff
    | Cashier
    | Branch Manager
    |
    |--------------------------------------------------------------------------
    */

    private function getAllowedRole(
        string $roleName,
        Company $company
    ): Role {

        $role =
            Role::query()

            ->where(
                'company_id',
                $company->id
            )

            ->where(
                'name',
                $roleName
            )

            ->where(
                'guard_name',
                'web'
            )

            ->where(
                'status',
                true
            )

            ->first();


        if (
            !$role
        ) {

            abort(
                422,
                'The selected role is invalid, inactive, or does not belong to this company.'
            );
        }


        return $role;
    }


    /*
    |--------------------------------------------------------------------------
    | Active Company Branches
    |--------------------------------------------------------------------------
    */

    private function getCompanyBranches(
        Company $company
    ): array {

        return Store::query()

            ->where(
                'company_id',
                $company->id
            )

            ->where(
                'status',
                true
            )

            ->orderBy(
                'branch_number'
            )

            ->get([
                'id',
                'store_name',
                'store_code',
                'branch_number',
            ])

            ->map(
                fn(
                    Store $store
                ) => [

                    'id' =>
                    $store->id,

                    'store_name' =>
                    $store->store_name,

                    'store_code' =>
                    $store->store_code,

                    'branch_number' =>
                    $store->branch_number,
                ]
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
    | User Data
    |--------------------------------------------------------------------------
    */

    private function userData(
        User $user,
        User $currentUser
    ): array {

        $user->loadMissing([
            'roles:id,company_id,name,status',
            'stores:id,company_id,store_name,store_code,status',
        ]);


        $isCompanyAdmin =
            $user->hasRole(
                'Company Admin'
            );


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

            'role' =>
            $user->roles
                ->pluck('name')
                ->first(),

            'roles' =>
            $user->roles
                ->pluck('name')
                ->values()
                ->all(),

            /*
            |--------------------------------------------------------------------------
            | Company Admin Has All Branch Access
            |--------------------------------------------------------------------------
            */

            'all_branches' =>
            $isCompanyAdmin,

            /*
            |--------------------------------------------------------------------------
            | Edit Form Branch IDs
            |--------------------------------------------------------------------------
            */

            'store_ids' =>
            $user->stores
                ->pluck('id')
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
            | Branch Information
            |--------------------------------------------------------------------------
            */

            'branches' =>
            $user->stores
                ->map(
                    fn(
                        Store $store
                    ) => [

                        'id' =>
                        $store->id,

                        'store_name' =>
                        $store->store_name,

                        'store_code' =>
                        $store->store_code,

                        'status' =>
                        (bool) $store->status,
                    ]
                )
                ->values()
                ->all(),

            'is_current_user' =>
            (int) $currentUser->id ===
                (int) $user->id,

            'is_company_admin' =>
            $isCompanyAdmin,

            'created_at' =>
            $user->created_at
                ?->toISOString(),

            'updated_at' =>
            $user->updated_at
                ?->toISOString(),
        ];
    }
}
