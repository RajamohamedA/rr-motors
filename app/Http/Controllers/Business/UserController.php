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
                'roles:id,name',
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

            ->when(
                $role !== '',
                function (
                    $query
                ) use (
                    $role
                ) {

                    $query->role(
                        $role
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

            ->orderBy('name')

            ->paginate(15)

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
                        ->values(),

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
                        ->values(),

                    'is_current_user' =>
                    (int) $currentUser->id ===
                        (int) $user->id,

                    'created_at' =>
                    $user->created_at
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

            /*
            |--------------------------------------------------------------------------
            | Number Of Active Company Branches
            |--------------------------------------------------------------------------
            */

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
        | Roles For Filter
        |--------------------------------------------------------------------------
        */

        $roles =
            $this->getAssignableRoles();


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
                $this->getAssignableRoles(),

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
                    'nullable',
                    'array',
                ],

                'store_ids.*' => [
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
        | Role Security
        |--------------------------------------------------------------------------
        */

        $role =
            $this->getAllowedRole(
                $validated['role']
            );


        /*
        |--------------------------------------------------------------------------
        | Company Admin Role Protection
        |--------------------------------------------------------------------------
        |
        | Company Admin cannot create another Company Admin.
        |
        */

        if (
            $role->name ===
            'Company Admin'
        ) {

            abort(
                403,
                'Company Admin accounts can only be managed by Super Admin.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Branch Required
        |--------------------------------------------------------------------------
        */

        if (
            empty($validated['store_ids'])
        ) {

            return back()
                ->withErrors([
                    'store_ids' =>
                    'Select at least one branch for this user.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $validated,
                $company,
                $role
            ) {

                $user =
                    User::create([

                        'company_id' =>
                        $company->id,

                        /*
                        |--------------------------------------------------------------------------
                        | Primary Store
                        |--------------------------------------------------------------------------
                        */

                        'store_id' =>
                        $validated['store_ids'][0]
                            ?? null,

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
                        (bool) $validated['status'],

                        /*
                        |--------------------------------------------------------------------------
                        | Never Allow Business Portal Super Admin
                        |--------------------------------------------------------------------------
                        */

                        'is_super_user' =>
                        false,
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Role
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
                        $validated['store_ids']
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
            'roles:id,name',
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


        $user->load([
            'roles:id,name',
            'stores:id',
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
                $this->getAssignableRoles(),

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

            abort(
                403,
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


        $role =
            $this->getAllowedRole(
                $validated['role']
            );


        if (
            $role->name ===
            'Company Admin'
        ) {

            abort(
                403,
                'You cannot assign the Company Admin role.'
            );
        }


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

                $data = [

                    'name' =>
                    $validated['name'],

                    'email' =>
                    $validated['email'],

                    'mobile' =>
                    $validated['mobile']
                        ?? null,

                    'store_id' =>
                    $validated['store_ids'][0]
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


                $user->syncRoles([
                    $role,
                ]);


                $user
                    ->stores()
                    ->sync(
                        $validated['store_ids']
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


        $validated =
            $request->validate([

                'status' => [
                    'required',
                    'boolean',
                ],
            ]);


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

            abort(401);
        }


        if (
            (bool) $user->is_super_user
        ) {

            abort(
                403,
                'Use the Super Admin user management area.'
            );
        }


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
                (int) $company->id &&
                !(bool) $user->is_super_user,
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Assignable Roles
    |--------------------------------------------------------------------------
    */

    private function getAssignableRoles(): array
    {
        return Role::query()

            /*
            |--------------------------------------------------------------------------
            | Never Expose Super Admin Roles
            |--------------------------------------------------------------------------
            */

            ->whereNotIn(
                'name',
                [
                    'Super Admin',
                    'super-admin',
                    'super_admin',
                    'Company Admin',
                ]
            )

            ->orderBy('name')

            ->get([
                'id',
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
    | Validate Role
    |--------------------------------------------------------------------------
    */

    private function getAllowedRole(
        string $roleName
    ): Role {

        $role =
            Role::query()
            ->where(
                'name',
                $roleName
            )
            ->first();


        if (
            !$role
        ) {

            abort(
                422,
                'The selected role is invalid.'
            );
        }


        $blockedRoles = [

            'Super Admin',
            'super-admin',
            'super_admin',
            'Company Admin',
        ];


        if (
            in_array(
                $role->name,
                $blockedRoles,
                true
            )
        ) {

            abort(
                403,
                'This role cannot be assigned from the Company Portal.'
            );
        }


        return $role;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Branches
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
            'roles:id,name',
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

            'all_branches' =>
            $isCompanyAdmin,

            'store_ids' =>
            $user->stores
                ->pluck('id')
                ->values(),

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
                ->values(),

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
