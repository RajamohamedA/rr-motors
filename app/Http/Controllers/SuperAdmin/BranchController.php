<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use App\Services\ActivityLogger;
use App\Services\SuperAdminNotificationService;
use App\Support\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BranchController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $branches =
            Store::query()
            ->with([
                'company:id,company_name,company_code,status',
            ])
            ->orderBy(
                'company_id'
            )
            ->orderBy(
                'branch_number'
            )
            ->get()
            ->map(
                function ($store) {

                    return [

                        'id' =>
                        $store->id,

                        'company_id' =>
                        $store->company_id,

                        'company' => [

                            'id' =>
                            $store->company?->id,

                            'company_name' =>
                            $store->company?->company_name,

                            'company_code' =>
                            $store->company?->company_code,

                            'status' =>
                            (bool) $store->company?->status,

                        ],

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

                        'city' =>
                        $store->city,

                        'state' =>
                        $store->state,

                        'status' =>
                        (bool) $store->status,

                    ];
                }
            );


        return Inertia::render(
            'SuperAdmin/Branches/Index',
            [
                'branches' =>
                $branches,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        Company $company
    ) {

        return Inertia::render(
            'SuperAdmin/Branches/Create',
            [
                'company' => [

                    'id' =>
                    $company->id,

                    'company_name' =>
                    $company->company_name,

                    'company_code' =>
                    $company->company_code,

                    'status' =>
                    (bool) $company->status,

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
        Request $request,
        Company $company
    ) {

        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'store_name' => [
                    'required',
                    'string',
                    'max:255',
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

                'status' => [
                    'required',
                    'boolean',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $company->status
        ) {

            return AppResponse::error(
                'You cannot create a branch for an inactive company.',
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Create Branch
        |--------------------------------------------------------------------------
        */

        $store =
            DB::transaction(
                function () use (
                    $company,
                    $validated
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Company
                    |--------------------------------------------------------------------------
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
                    | Re-check Company Status Inside Transaction
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
                    | Next Branch Number
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


                    $nextBranchNumber =
                        $lastBranchNumber + 1;


                    /*
                    |--------------------------------------------------------------------------
                    | Branch Code
                    |--------------------------------------------------------------------------
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
                    | Create
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

                    ]);
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'branch',

            action: 'created',

            description: "Created branch {$store->store_name} ({$store->store_code}) for {$company->company_name}",

            subject: $store,

            newValues: $this->branchAuditValues(
                $store
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        SuperAdminNotificationService::send(
            title: 'Branch Created',

            message: "{$store->store_name} ({$store->store_code}) has been created for {$company->company_name}.",

            category: 'branch',

            icon: 'bi-shop-window',

            targetType: 'branch',

            targetId: $store->id,

            fallbackUrl: route(
                'super-admin.branches.index'
            ),

            level: 'success',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'super-admin.companies.show',
            "Branch {$store->store_code} created successfully.",
            $company
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Company $company,
        Store $store
    ) {

        /*
        |--------------------------------------------------------------------------
        | Protection
        |--------------------------------------------------------------------------
        */

        $this->ensureBranchBelongsToCompany(
            $company,
            $store
        );


        return Inertia::render(
            'SuperAdmin/Branches/Edit',
            [
                'company' => [

                    'id' =>
                    $company->id,

                    'company_name' =>
                    $company->company_name,

                    'company_code' =>
                    $company->company_code,

                    'status' =>
                    (bool) $company->status,

                ],

                'store' =>
                $store,
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
        Company $company,
        Store $store
    ) {

        /*
        |--------------------------------------------------------------------------
        | Protection
        |--------------------------------------------------------------------------
        */

        $this->ensureBranchBelongsToCompany(
            $company,
            $store
        );


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'store_name' => [
                    'required',
                    'string',
                    'max:255',
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

                'status' => [
                    'required',
                    'boolean',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Capture Old Values
        |--------------------------------------------------------------------------
        */

        $oldValues =
            $this->branchAuditValues(
                $store
            );


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $store->update(
            $validated
        );


        $store->refresh();


        /*
        |--------------------------------------------------------------------------
        | Capture New Values
        |--------------------------------------------------------------------------
        */

        $newValues =
            $this->branchAuditValues(
                $store
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
        | No Changes
        |--------------------------------------------------------------------------
        */

        if (
            empty($changedNewValues)
        ) {

            return AppResponse::info(
                'No changes were detected for this branch.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'branch',

            action: 'updated',

            description: "Updated branch {$store->store_name} ({$store->store_code})",

            subject: $store,

            oldValues: $changedOldValues,

            newValues: $changedNewValues
        );


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        SuperAdminNotificationService::send(
            title: 'Branch Updated',

            message: "{$store->store_name} ({$store->store_code}) of {$company->company_name} has been updated.",

            category: 'branch',

            icon: 'bi-pencil-square',

            targetType: 'branch',

            targetId: $store->id,

            fallbackUrl: route(
                'super-admin.branches.index'
            ),

            level: 'info',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'super-admin.companies.show',
            'Branch updated successfully.',
            $company
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate
    |--------------------------------------------------------------------------
    */

    public function activate(
        Company $company,
        Store $store
    ) {

        /*
        |--------------------------------------------------------------------------
        | Protection
        |--------------------------------------------------------------------------
        */

        $this->ensureBranchBelongsToCompany(
            $company,
            $store
        );


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $company->status
        ) {

            return AppResponse::error(
                'The company is inactive. Activate the company before activating this branch.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Already Active
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $store->status
        ) {

            return AppResponse::info(
                'Branch is already active.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Activate
        |--------------------------------------------------------------------------
        */

        $store->update([
            'status' =>
            true,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'branch',

            action: 'activated',

            description: "Activated branch {$store->store_name} ({$store->store_code})",

            subject: $store,

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
            title: 'Branch Activated',

            message: "{$store->store_name} ({$store->store_code}) of {$company->company_name} has been activated.",

            category: 'branch',

            icon: 'bi-shop-window',

            targetType: 'branch',

            targetId: $store->id,

            fallbackUrl: route(
                'super-admin.branches.index'
            ),

            level: 'success',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::success(
            'Branch activated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Deactivate
    |--------------------------------------------------------------------------
    */

    public function deactivate(
        Company $company,
        Store $store
    ) {

        /*
        |--------------------------------------------------------------------------
        | Protection
        |--------------------------------------------------------------------------
        */

        $this->ensureBranchBelongsToCompany(
            $company,
            $store
        );


        /*
        |--------------------------------------------------------------------------
        | Prevent Current Working Branch Deactivation
        |--------------------------------------------------------------------------
        */

        if (
            (int) session(
                'active_store_id'
            ) ===
            (int) $store->id
        ) {

            return AppResponse::error(
                'You cannot deactivate the branch currently being used. Exit the branch first.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Already Inactive
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $store->status
        ) {

            return AppResponse::info(
                'Branch is already inactive.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Deactivate
        |--------------------------------------------------------------------------
        */

        $store->update([
            'status' =>
            false,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'branch',

            action: 'deactivated',

            description: "Deactivated branch {$store->store_name} ({$store->store_code})",

            subject: $store,

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
            title: 'Branch Deactivated',

            message: "{$store->store_name} ({$store->store_code}) of {$company->company_name} has been deactivated.",

            category: 'branch',

            icon: 'bi-shop',

            targetType: 'branch',

            targetId: $store->id,

            fallbackUrl: route(
                'super-admin.branches.index'
            ),

            level: 'warning',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::success(
            'Branch deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Branch Audit Values
    |--------------------------------------------------------------------------
    */

    private function branchAuditValues(
        Store $store
    ): array {

        return [

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

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Detect Changed Values
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
    | Ensure Branch Belongs To Company
    |--------------------------------------------------------------------------
    */

    private function ensureBranchBelongsToCompany(
        Company $company,
        Store $store
    ): void {

        abort_unless(
            (int) $store->company_id ===
                (int) $company->id,
            404
        );
    }
}
