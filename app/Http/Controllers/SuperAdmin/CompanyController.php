<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Notifications\SuperAdminAlert;
use App\Services\ActivityLogger;
use App\Support\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CompanyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $companies =
            Company::withTrashed()
            ->withCount('stores')
            ->orderBy('company_name')
            ->get()
            ->map(
                function ($company) {

                    return [

                        'id' =>
                        $company->id,

                        'company_name' =>
                        $company->company_name,

                        'company_code' =>
                        $company->company_code,

                        'owner_name' =>
                        $company->owner_name,

                        'gst_number' =>
                        $company->gst_number,

                        'pan_number' =>
                        $company->pan_number,

                        'email' =>
                        $company->email,

                        'phone' =>
                        $company->phone,

                        'city' =>
                        $company->city,

                        'state' =>
                        $company->state,

                        'logo' =>
                        $company->logo,

                        'status' =>
                        (bool) $company->status,

                        'stores_count' =>
                        $company->stores_count,

                        'deleted_at' =>
                        $company->deleted_at,

                    ];
                }
            );


        return Inertia::render(
            'SuperAdmin/Companies/Index',
            [
                'companies' =>
                $companies,
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
            'SuperAdmin/Companies/Create'
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

                'company_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'owner_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'gst_number' => [
                    'nullable',
                    'string',
                    'max:50',
                    'unique:companies,gst_number',
                ],

                'pan_number' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'website' => [
                    'nullable',
                    'string',
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

                'logo' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Generate Company Code
        |--------------------------------------------------------------------------
        */

        $validated['company_code'] =
            Company::generateCompanyCode(
                $validated['company_name']
            );


        /*
        |--------------------------------------------------------------------------
        | Upload Logo
        |--------------------------------------------------------------------------
        */

        if (
            $request->hasFile(
                'logo'
            )
        ) {

            $validated['logo'] =
                $request
                ->file('logo')
                ->store(
                    'companies',
                    'public'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Create Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::create(
                $validated
            );


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'company',

            action: 'created',

            description: "Created company {$company->company_name}",

            subject: $company,

            newValues: $this->companyAuditValues(
                $company
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        $this->notifySuperAdmins(
            title: 'Company Created',

            message: "{$company->company_name} ({$company->company_code}) has been created.",

            category: 'company',

            icon: 'bi-buildings',

            targetType: 'company',

            targetId: $company->id,

            fallbackUrl: route(
                'super-admin.companies.index'
            ),

            level: 'success',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'super-admin.companies.index',
            'Company created successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Company $company
    ) {
        $company->load([
            'stores' => function ($query) {

                $query->orderBy(
                    'store_name'
                );
            },
        ]);


        return Inertia::render(
            'SuperAdmin/Companies/Show',
            [
                'company' =>
                $company,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Company $company
    ) {
        return Inertia::render(
            'SuperAdmin/Companies/Edit',
            [
                'company' =>
                $company,
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
        Company $company
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'company_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'owner_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'gst_number' => [
                    'nullable',
                    'string',
                    'max:50',

                    Rule::unique(
                        'companies',
                        'gst_number'
                    )->ignore(
                        $company->id
                    ),
                ],

                'pan_number' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'website' => [
                    'nullable',
                    'string',
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

                'logo' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Old Values
        |--------------------------------------------------------------------------
        */

        $oldValues =
            $this->companyAuditValues(
                $company
            );


        /*
        |--------------------------------------------------------------------------
        | Replace Logo
        |--------------------------------------------------------------------------
        */

        if (
            $request->hasFile(
                'logo'
            )
        ) {

            if (
                $company->logo &&
                Storage::disk(
                    'public'
                )->exists(
                    $company->logo
                )
            ) {

                Storage::disk(
                    'public'
                )->delete(
                    $company->logo
                );
            }


            $validated['logo'] =
                $request
                ->file('logo')
                ->store(
                    'companies',
                    'public'
                );
        } else {

            unset(
                $validated['logo']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $company->update(
            $validated
        );


        $company->refresh();


        /*
        |--------------------------------------------------------------------------
        | New Values
        |--------------------------------------------------------------------------
        */

        $newValues =
            $this->companyAuditValues(
                $company
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
                'No changes were detected for this company.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'company',

            action: 'updated',

            description: "Updated company {$company->company_name}",

            subject: $company,

            oldValues: $changedOldValues,

            newValues: $changedNewValues
        );


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        $this->notifySuperAdmins(
            title: 'Company Updated',

            message: "{$company->company_name} ({$company->company_code}) has been updated.",

            category: 'company',

            icon: 'bi-pencil-square',

            targetType: 'company',

            targetId: $company->id,

            fallbackUrl: route(
                'super-admin.companies.index'
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
            'Company updated successfully.',
            $company
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate
    |--------------------------------------------------------------------------
    */

    public function activate(
        Company $company
    ) {
        /*
        |--------------------------------------------------------------------------
        | Already Active
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $company->status
        ) {

            return AppResponse::info(
                'Company is already active.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Activate
        |--------------------------------------------------------------------------
        */

        $company->update([
            'status' =>
            true,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'company',

            action: 'activated',

            description: "Activated company {$company->company_name}",

            subject: $company,

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

        $this->notifySuperAdmins(
            title: 'Company Activated',

            message: "{$company->company_name} ({$company->company_code}) has been activated.",

            category: 'company',

            icon: 'bi-building-check',

            targetType: 'company',

            targetId: $company->id,

            fallbackUrl: route(
                'super-admin.companies.index'
            ),

            level: 'success',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::success(
            'Company activated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Deactivate
    |--------------------------------------------------------------------------
    */

    public function deactivate(
        Company $company
    ) {
        /*
        |--------------------------------------------------------------------------
        | Already Inactive
        |--------------------------------------------------------------------------
        */

        if (
            !(bool) $company->status
        ) {

            return AppResponse::info(
                'Company is already inactive.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Deactivate
        |--------------------------------------------------------------------------
        */

        $company->update([
            'status' =>
            false,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'company',

            action: 'deactivated',

            description: "Deactivated company {$company->company_name}",

            subject: $company,

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

        $this->notifySuperAdmins(
            title: 'Company Deactivated',

            message: "{$company->company_name} ({$company->company_code}) has been deactivated.",

            category: 'company',

            icon: 'bi-building-x',

            targetType: 'company',

            targetId: $company->id,

            fallbackUrl: route(
                'super-admin.companies.index'
            ),

            level: 'warning',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::success(
            'Company deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Archive / Soft Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Company $company
    ) {
        /*
        |--------------------------------------------------------------------------
        | Capture State
        |--------------------------------------------------------------------------
        */

        $wasActive =
            (bool) $company->status;


        $companyId =
            $company->id;


        $companyName =
            $company->company_name;


        $companyCode =
            $company->company_code;


        /*
        |--------------------------------------------------------------------------
        | Deactivate
        |--------------------------------------------------------------------------
        */

        $company->update([
            'status' =>
            false,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Archive
        |--------------------------------------------------------------------------
        */

        $company->delete();


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'company',

            action: 'archived',

            description: "Archived company {$companyName}",

            subject: $company,

            oldValues: [
                'record_state' =>
                'Active',

                'status' =>
                $wasActive,
            ],

            newValues: [
                'record_state' =>
                'Archived',

                'status' =>
                false,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        $this->notifySuperAdmins(
            title: 'Company Archived',

            message: "{$companyName} ({$companyCode}) has been moved to archive.",

            category: 'company',

            icon: 'bi-archive',

            targetType: 'company',

            targetId: $companyId,

            fallbackUrl: route(
                'super-admin.companies.index'
            ),

            level: 'danger',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::successRoute(
            'super-admin.companies.index',
            'Company moved to archive successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Restore
    |--------------------------------------------------------------------------
    */

    public function restore(
        $id
    ) {
        /*
        |--------------------------------------------------------------------------
        | Find Archived Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::withTrashed()
            ->findOrFail(
                $id
            );


        /*
        |--------------------------------------------------------------------------
        | Already Restored
        |--------------------------------------------------------------------------
        */

        if (
            !$company->trashed()
        ) {

            return AppResponse::info(
                'Company is already restored.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Restore
        |--------------------------------------------------------------------------
        */

        $company->restore();


        /*
        |--------------------------------------------------------------------------
        | Keep Restored Company Inactive
        |--------------------------------------------------------------------------
        */

        $company->update([
            'status' =>
            false,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'company',

            action: 'restored',

            description: "Restored company {$company->company_name}",

            subject: $company,

            oldValues: [
                'record_state' =>
                'Archived',
            ],

            newValues: [
                'record_state' =>
                'Restored',

                'status' =>
                false,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        $this->notifySuperAdmins(
            title: 'Company Restored',

            message: "{$company->company_name} ({$company->company_code}) has been restored and remains inactive.",

            category: 'company',

            icon: 'bi-arrow-counterclockwise',

            targetType: 'company',

            targetId: $company->id,

            fallbackUrl: route(
                'super-admin.companies.index'
            ),

            level: 'info',
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::success(
            'Company restored successfully. Activate it when ready.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Company Audit Values
    |--------------------------------------------------------------------------
    */

    private function companyAuditValues(
        Company $company
    ): array {

        return [

            'company_code' =>
            $company->company_code,

            'company_name' =>
            $company->company_name,

            'owner_name' =>
            $company->owner_name,

            'gst_number' =>
            $company->gst_number,

            'pan_number' =>
            $company->pan_number,

            'email' =>
            $company->email,

            'phone' =>
            $company->phone,

            'website' =>
            $company->website,

            'address' =>
            $company->address,

            'city' =>
            $company->city,

            'state' =>
            $company->state,

            'country' =>
            $company->country,

            'pincode' =>
            $company->pincode,

            'status' =>
            (bool) $company->status,

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
    | Notify Super Administrators
    |--------------------------------------------------------------------------
    */

    private function notifySuperAdmins(
        string $title,
        string $message,
        string $category = 'system',
        string $icon = 'bi-bell',
        ?string $targetType = null,
        int|string|null $targetId = null,
        ?string $fallbackUrl = null,
        string $level = 'info',
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Active Super Administrators
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


        /*
        |--------------------------------------------------------------------------
        | Send Database Notification
        |--------------------------------------------------------------------------
        */

        foreach (
            $superAdmins as
            $admin
        ) {

            $admin->notify(
                new SuperAdminAlert(
                    title: $title,

                    message: $message,

                    category: $category,

                    icon: $icon,

                    targetType: $targetType,

                    targetId: $targetId,

                    fallbackUrl: $fallbackUrl,

                    level: $level,
                )
            );
        }
    }
}
