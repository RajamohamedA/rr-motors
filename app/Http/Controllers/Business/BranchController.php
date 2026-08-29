<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use App\Notifications\BranchApprovalRequested;
use App\Support\AppResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        $this->ensureCompanyAdmin(
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | Active Company
        |--------------------------------------------------------------------------
        */

        $company =
            $this->getActiveCompany(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        $branches =
            Store::query()

            ->where(
                'company_id',
                $company->id
            )

            ->when(
                $request->filled(
                    'search'
                ),
                function (
                    $query
                ) use (
                    $request
                ) {

                    $search =
                        trim(
                            $request
                                ->string(
                                    'search'
                                )
                                ->toString()
                        );


                    $query->where(
                        function (
                            $q
                        ) use (
                            $search
                        ) {

                            $q->where(
                                'store_name',
                                'like',
                                "%{$search}%"
                            )

                                ->orWhere(
                                    'store_code',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'city',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'phone',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->orderBy(
                'branch_number'
            )

            ->paginate(
                12
            )

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [

            'total' =>
            Store::query()
                ->where(
                    'company_id',
                    $company->id
                )
                ->count(),

            'active' =>
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

            'pending' =>
            Store::query()
                ->where(
                    'company_id',
                    $company->id
                )
                ->where(
                    'status',
                    false
                )
                ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Business/Branches/Index',
            [

                'company' => [

                    'id' =>
                    $company->id,

                    'company_name' =>
                    $company->company_name,

                    'company_code' =>
                    $company->company_code,
                ],

                'branches' =>
                $branches,

                'summary' =>
                $summary,

                'filters' => [

                    'search' =>
                    $request->input(
                        'search',
                        ''
                    ),
                ],

                'activeStoreId' =>
                $request
                    ->session()
                    ->get(
                        'active_store_id'
                    ),
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

        $user =
            $request->user();


        $this->ensureCompanyAdmin(
            $user
        );


        $company =
            $this->getActiveCompany(
                $user
            );


        return Inertia::render(
            'Business/Branches/Create',
            [

                'company' => [

                    'id' =>
                    $company->id,

                    'company_name' =>
                    $company->company_name,

                    'company_code' =>
                    $company->company_code,
                ],
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

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        $this->ensureCompanyAdmin(
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company =
            $this->getActiveCompany(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        |
        | Intentionally NOT accepted:
        |
        | company_id
        | branch_number
        | store_code
        | status
        |
        */

        $validated =
            $request->validate([

                'store_name' => [

                    'required',
                    'string',
                    'max:255',

                    Rule::unique(
                        'stores',
                        'store_name'
                    )->where(
                        fn(
                            $query
                        ) =>
                        $query->where(
                            'company_id',
                            $company->id
                        )
                    ),
                ],

                'contact_person' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'address' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'city' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'state' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'country' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'pincode' => [
                    'nullable',
                    'string',
                    'max:20',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Create Branch
        |--------------------------------------------------------------------------
        */

        $store =
            DB::transaction(
                function () use (
                    $validated,
                    $company
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Company
                    |--------------------------------------------------------------------------
                    |
                    | Both Super Admin and Business Portal use this same concept.
                    |
                    | Locking the company row serializes branch-number generation
                    | for this company.
                    |
                    */

                    $lockedCompany =
                        Company::query()
                        ->where(
                            'id',
                            $company->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Re-check Company Status
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !(bool) $lockedCompany->status
                    ) {

                        abort(
                            409,
                            'Company is inactive.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Ensure Company Code Exists
                    |--------------------------------------------------------------------------
                    */

                    if (
                        empty($lockedCompany->company_code)
                    ) {

                        $lockedCompany->company_code =
                            Company::generateCompanyCode(
                                $lockedCompany->company_name
                            );


                        $lockedCompany->save();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Last Branch Number For This Company
                    |--------------------------------------------------------------------------
                    */

                    $lastBranchNumber =
                        Store::query()

                        ->where(
                            'company_id',
                            $lockedCompany->id
                        )

                        ->max(
                            'branch_number'
                        )

                        ?? 0;


                    /*
                    |--------------------------------------------------------------------------
                    | Next Branch Number
                    |--------------------------------------------------------------------------
                    */

                    $nextBranchNumber =
                        ((int) $lastBranchNumber)
                        + 1;


                    /*
                    |--------------------------------------------------------------------------
                    | Company-Based Branch Code
                    |--------------------------------------------------------------------------
                    |
                    | Example:
                    |
                    | Company code:
                    | RAJA
                    |
                    | Branches:
                    |
                    | RAJA-B001
                    | RAJA-B002
                    | RAJA-B003
                    |
                    */

                    $storeCode =
                        strtoupper(
                            $lockedCompany->company_code
                        )

                        . '-B'

                        . str_pad(
                            $nextBranchNumber,
                            3,
                            '0',
                            STR_PAD_LEFT
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Safety Duplicate Check
                    |--------------------------------------------------------------------------
                    */

                    if (
                        Store::query()
                        ->where(
                            'company_id',
                            $lockedCompany->id
                        )
                        ->where(
                            'store_code',
                            $storeCode
                        )
                        ->exists()
                    ) {

                        abort(
                            409,
                            'Unable to generate a unique branch code. Please try again.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Create Pending Branch
                    |--------------------------------------------------------------------------
                    */

                    return Store::create([

                        ...$validated,

                        'company_id' =>
                        $lockedCompany->id,

                        'branch_number' =>
                        $nextBranchNumber,

                        'store_code' =>
                        $storeCode,

                        /*
                        |--------------------------------------------------------------------------
                        | Company Admin Cannot Activate Branch
                        |--------------------------------------------------------------------------
                        */

                        'status' =>
                        false,
                    ]);
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Notify Super Admin
        |--------------------------------------------------------------------------
        */

        $this->notifySuperAdmins(
            $company,
            $store
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'business.branches.index',
            "Branch {$store->store_code} created successfully and is waiting for Super Admin approval."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Store $store
    ): Response {

        $user =
            $request->user();


        $this->ensureCompanyAdmin(
            $user
        );


        $company =
            $this->getActiveCompany(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Company Isolation
        |--------------------------------------------------------------------------
        */

        $this->ensureBranchBelongsToCompany(
            $store,
            $company
        );


        return Inertia::render(
            'Business/Branches/Show',
            [

                'company' =>
                $this->companyData(
                    $company
                ),

                'branch' =>
                $this->branchData(
                    $store,
                    $request
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
        Store $store
    ): Response {

        $user =
            $request->user();


        $this->ensureCompanyAdmin(
            $user
        );


        $company =
            $this->getActiveCompany(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Company Isolation
        |--------------------------------------------------------------------------
        */

        $this->ensureBranchBelongsToCompany(
            $store,
            $company
        );


        return Inertia::render(
            'Business/Branches/Edit',
            [

                'company' =>
                $this->companyData(
                    $company
                ),

                'branch' =>
                $this->branchData(
                    $store,
                    $request
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
        Store $store
    ): RedirectResponse {

        $user =
            $request->user();


        $this->ensureCompanyAdmin(
            $user
        );


        $company =
            $this->getActiveCompany(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Company Isolation
        |--------------------------------------------------------------------------
        */

        $this->ensureBranchBelongsToCompany(
            $store,
            $company
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        |
        | Company Admin cannot update:
        |
        | company_id
        | branch_number
        | store_code
        | status
        |
        */

        $validated =
            $request->validate([

                'store_name' => [

                    'required',
                    'string',
                    'max:255',

                    Rule::unique(
                        'stores',
                        'store_name'
                    )
                        ->where(
                            fn(
                                $query
                            ) =>
                            $query->where(
                                'company_id',
                                $company->id
                            )
                        )
                        ->ignore(
                            $store->id
                        ),
                ],

                'contact_person' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'address' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'city' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'state' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'country' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'pincode' => [
                    'nullable',
                    'string',
                    'max:20',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Update Allowed Fields Only
        |--------------------------------------------------------------------------
        */

        $store->update(
            $validated
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'business.branches.index',
            'Branch details updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Notify Super Admins
    |--------------------------------------------------------------------------
    */

    private function notifySuperAdmins(
        Company $company,
        Store $store
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Active Super Admins
        |--------------------------------------------------------------------------
        */

        $superAdmins =
            User::query()
            ->where(
                'is_super_user',
                true
            )
            ->where(
                'status',
                true
            )
            ->get();


        if (
            $superAdmins->isEmpty()
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Send Approval Request
        |--------------------------------------------------------------------------
        */

        Notification::send(
            $superAdmins,
            new BranchApprovalRequested(
                $company,
                $store
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Active Company
    |--------------------------------------------------------------------------
    */

    private function getActiveCompany(
        User $user
    ): Company {

        /*
        |--------------------------------------------------------------------------
        | User Must Belong To A Company
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


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        if (
            !$user
        ) {

            abort(
                401
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Super Admin Uses Super Admin Portal
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $user->is_super_user
        ) {

            abort(
                403,
                'Use the Super Admin branch management area.'
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
                'Only Company Administrators can manage company branches.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Branch Belongs To Company
    |--------------------------------------------------------------------------
    */

    private function ensureBranchBelongsToCompany(
        Store $store,
        Company $company
    ): void {

        abort_unless(
            (int) $store->company_id ===
                (int) $company->id,
            404
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
    | Branch Data
    |--------------------------------------------------------------------------
    */

    private function branchData(
        Store $store,
        Request $request
    ): array {

        return [

            'id' =>
            $store->id,

            'company_id' =>
            $store->company_id,

            'branch_number' =>
            $store->branch_number,

            'store_code' =>
            $store->store_code,

            'store_name' =>
            $store->store_name,

            'contact_person' =>
            $store->contact_person,

            'phone' =>
            $store->phone,

            'email' =>
            $store->email,

            'address' =>
            $store->address,

            'city' =>
            $store->city,

            'state' =>
            $store->state,

            'country' =>
            $store->country,

            'pincode' =>
            $store->pincode,

            'status' =>
            (bool) $store->status,

            'status_label' =>
            $store->status
                ? 'Active'
                : 'Pending Approval',

            'is_current' =>
            (int) $request
                ->session()
                ->get(
                    'active_store_id'
                )
                ===
                (int) $store->id,

            'created_at' =>
            $store->created_at
                ?->toISOString(),

            'updated_at' =>
            $store->updated_at
                ?->toISOString(),
        ];
    }
}
