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
                'roles:id,company_id,name,description,status,is_system_role',
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
                                                function (
                                                    $systemQuery
                                                ) {

                                                    $systemQuery
                                                        ->whereNull(
                                                            'roles.company_id'
                                                        )
                                                        ->where(
                                                            'roles.is_system_role',
                                                            true
                                                        )
                                                        ->whereNotIn(
                                                            'roles.name',
                                                            [
                                                                'Super Admin',
                                                                'Company Admin',
                                                            ]
                                                        );
                                                }
                                            )

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

                    /*
                    |--------------------------------------------------------------------------
                    | Primary Role
                    |--------------------------------------------------------------------------
                    */

                    'role' =>
                    $user->roles
                        ->first()
                        ?->name,

                    'role_type' =>
                    $this->getRoleType(
                        $user->roles
                            ->first()
                    ),

                    'is_system_role' =>
                    (bool) (
                        $user->roles
                        ->first()
                        ?->is_system_role
                        ?? false
                    ),

                    'role_description' =>
                    $user->roles
                        ->first()
                        ?->description,

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
            'roles:id,company_id,name,description,status,is_system_role',
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
            &&
            !(bool) $currentUser->is_super_user
        ) {

            abort(
                403,
                'Company Admin accounts can only be managed by Super Admin.'
            );
        }


        $user->load([
            'roles:id,company_id,name,description,status,is_system_role',
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
            &&
            !(bool) $currentUser->is_super_user
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
            &&
            !(bool) $currentUser->is_super_user
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

        $currentUser =
            auth()->user();


        $isSuperAdmin =
            (bool) (
                $currentUser?->is_super_user
                ?? false
            );


        return Role::query()

            ->where(
                'guard_name',
                'web'
            )

            ->where(
                'status',
                true
            )

            ->where(
                'name',
                '!=',
                'Super Admin'
            )

            ->where(
                function (
                    $query
                ) use (
                    $company,
                    $isSuperAdmin
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Company Admin
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $isSuperAdmin
                    ) {

                        $query->orWhere(
                            function (
                                $companyAdminQuery
                            ) {

                                $companyAdminQuery
                                    ->whereNull(
                                        'company_id'
                                    )
                                    ->where(
                                        'name',
                                        'Company Admin'
                                    );
                            }
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Global System Roles
                    |--------------------------------------------------------------------------
                    */

                    $query->orWhere(
                        function (
                            $systemQuery
                        ) {

                            $systemQuery
                                ->whereNull(
                                    'company_id'
                                )
                                ->where(
                                    'is_system_role',
                                    true
                                )
                                ->whereNotIn(
                                    'name',
                                    [
                                        'Super Admin',
                                        'Company Admin',
                                    ]
                                );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Company Custom Roles
                    |--------------------------------------------------------------------------
                    */

                    $query->orWhere(
                        function (
                            $companyQuery
                        ) use (
                            $company
                        ) {

                            $companyQuery
                                ->where(
                                    'company_id',
                                    $company->id
                                )
                                ->where(
                                    'is_system_role',
                                    false
                                );
                        }
                    );
                }
            )

            ->orderByDesc(
                'is_system_role'
            )

            ->orderBy(
                'name'
            )

            ->get([
                'id',
                'company_id',
                'name',
                'description',
                'guard_name',
                'status',
                'is_system_role',
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

                    'type' =>
                    (bool) $role->is_system_role
                        ? 'system'
                        : 'company',

                    'is_system_role' =>
                    (bool) $role->is_system_role,

                    'company_id' =>
                    $role->company_id,
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
            ->where('guard_name', 'web')
            ->where(function ($query) use ($company) {
                $query->where(function ($companyAdminQuery) {
                    $companyAdminQuery
                        ->whereNull('company_id')
                        ->where('name', 'Company Admin');
                })
                    ->orWhere(function ($systemQuery) {
                        $systemQuery
                            ->whereNull('company_id')
                            ->where('is_system_role', true)
                            ->whereNotIn('name', [
                                'Super Admin',
                                'Company Admin',
                            ]);
                    })
                    ->orWhere(function ($companyQuery) use ($company) {
                        $companyQuery
                            ->where('company_id', $company->id)
                            ->where('is_system_role', false);
                    });
            })
            ->orderByDesc('is_system_role')
            ->orderBy('name')
            ->get([
                'id',
                'company_id',
                'name',
                'is_system_role',
            ])
            ->map(fn(Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'type' => (bool) $role->is_system_role ? 'system' : 'company',
            ])
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

        $currentUser =
            auth()->user();


        $isSuperAdmin =
            (bool) (
                $currentUser?->is_super_user
                ?? false
            );


        /*
        |--------------------------------------------------------------------------
        | Never Assign Super Admin Here
        |--------------------------------------------------------------------------
        */

        if (
            $roleName ===
            'Super Admin'
        ) {

            abort(
                422,
                'The Super Admin role cannot be assigned from company user management.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Admin Only By Super Admin
        |--------------------------------------------------------------------------
        */

        if (
            $roleName ===
            'Company Admin'
            &&
            !$isSuperAdmin
        ) {

            abort(
                422,
                'Company Admin accounts can only be assigned by Super Admin.'
            );
        }


        $role =
            Role::query()

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

            ->where(
                function (
                    $query
                ) use (
                    $company,
                    $isSuperAdmin
                ) {

                    if (
                        $isSuperAdmin
                    ) {

                        $query->orWhere(
                            function (
                                $companyAdminQuery
                            ) {

                                $companyAdminQuery
                                    ->whereNull(
                                        'company_id'
                                    )
                                    ->where(
                                        'name',
                                        'Company Admin'
                                    );
                            }
                        );
                    }


                    $query->orWhere(
                        function (
                            $systemQuery
                        ) {

                            $systemQuery
                                ->whereNull(
                                    'company_id'
                                )
                                ->where(
                                    'is_system_role',
                                    true
                                )
                                ->whereNotIn(
                                    'name',
                                    [
                                        'Super Admin',
                                        'Company Admin',
                                    ]
                                );
                        }
                    );


                    $query->orWhere(
                        function (
                            $companyQuery
                        ) use (
                            $company
                        ) {

                            $companyQuery
                                ->where(
                                    'company_id',
                                    $company->id
                                )
                                ->where(
                                    'is_system_role',
                                    false
                                );
                        }
                    );
                }
            )

            ->first();


        if (
            !$role
        ) {

            abort(
                422,
                'The selected role is invalid, inactive, protected, or does not belong to this company.'
            );
        }


        return $role;
    }


    /*
    |--------------------------------------------------------------------------
    | Role Type
    |--------------------------------------------------------------------------
    |
    | Global predefined employee roles have:
    |
    | company_id = NULL
    | is_system_role = true
    |
    | Company-created custom roles have:
    |
    | company_id = current company
    | is_system_role = false
    |
    */

    private function getRoleType(
        ?Role $role
    ): ?string {

        if (
            !$role
        ) {

            return null;
        }


        return (bool) $role->is_system_role
            ? 'system'
            : 'company';
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
            'roles:id,company_id,name,description,status,is_system_role',
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

            /*
            |--------------------------------------------------------------------------
            | Primary Role
            |--------------------------------------------------------------------------
            */

            'role' =>
            $user->roles
                ->first()
                ?->name,

            'role_type' =>
            $this->getRoleType(
                $user->roles
                    ->first()
            ),

            'is_system_role' =>
            (bool) (
                $user->roles
                ->first()
                ?->is_system_role
                ?? false
            ),

            'role_description' =>
            $user->roles
                ->first()
                ?->description,

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
