<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\ActivityLogger;
use App\Support\AppResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show Company Profile
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request
    ): Response {

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | Access
        |--------------------------------------------------------------------------
        |
        | Super Admin:
        |   Use the active company selected in business context.
        |
        | Company Admin:
        |   Use the company assigned to the authenticated user.
        |
        */

        $company =
            $this->resolveCompany(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (bool) $company->status,
            403,
            $user->is_super_user
                ? 'The selected company is inactive.'
                : 'Your company is inactive.'
        );


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Business/Company/Show',
            [

                'company' => [

                    'id' =>
                    $company->id,

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

                    'logo' =>
                    $company->logo,

                    /*
                    |--------------------------------------------------------------------------
                    | Read Only Platform State
                    |--------------------------------------------------------------------------
                    */

                    'status' =>
                    (bool) $company->status,

                    'created_at' =>
                    $company->created_at
                        ?->toISOString(),

                    'updated_at' =>
                    $company->updated_at
                        ?->toISOString(),
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Company Profile
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request
    ): RedirectResponse {

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | Access / Company
        |--------------------------------------------------------------------------
        */

        $company =
            $this->resolveCompany(
                $request
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
                $user->is_super_user
                    ? 'The selected company is inactive.'
                    : 'Your company is inactive. Please contact the Super Administrator.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

                'company_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'owner_name' => [
                    'required',
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
        | Normalize
        |--------------------------------------------------------------------------
        */

        unset($validated['logo']);

        $validated['company_name'] =
            trim(
                $validated['company_name']
            );


        $validated['owner_name'] =
            trim(
                $validated['owner_name']
            );


        if (
            !empty($validated['gst_number'])
        ) {

            $validated['gst_number'] =
                strtoupper(
                    trim(
                        $validated['gst_number']
                    )
                );
        }


        if (
            !empty($validated['pan_number'])
        ) {

            $validated['pan_number'] =
                strtoupper(
                    trim(
                        $validated['pan_number']
                    )
                );
        }


        if (
            !empty($validated['email'])
        ) {

            $validated['email'] =
                strtolower(
                    trim(
                        $validated['email']
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Capture Old Values
        |--------------------------------------------------------------------------
        */

        $oldValues = [
            'logo' => $company->logo,

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
        ];


        /*
        |--------------------------------------------------------------------------
        | Update Allowed Fields Only
        |--------------------------------------------------------------------------
        |
        | Company Admin cannot update:
        |
        | company_code
        | status
        | subscription information
        | deleted_at
        |
        */

        $oldLogo = $company->logo;
        $newLogo = null;
        if ($request->hasFile('logo')) {
            $newLogo = $request->file('logo')->store('companies', 'public');
            abort_unless($newLogo, 500, 'Unable to store the company logo.');
            $validated['logo'] = $newLogo;
        }

        try {
            $company->update($validated);
        } catch (\Throwable $exception) {
            if ($newLogo) {
                Storage::disk('public')->delete($newLogo);
            }
            throw $exception;
        }

        // Delete the previous file only after the new path has been saved.
        if ($newLogo && $oldLogo && str_starts_with($oldLogo, 'companies/')) {
            Storage::disk('public')->delete($oldLogo);
        }


        /*
        |--------------------------------------------------------------------------
        | Refresh
        |--------------------------------------------------------------------------
        */

        $company->refresh();


        /*
        |--------------------------------------------------------------------------
        | New Values
        |--------------------------------------------------------------------------
        */

        $newValues = [
            'logo' => $company->logo,

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
        ];


        /*
        |--------------------------------------------------------------------------
        | Detect Changes
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        if (
            !empty($changedNew)
        ) {

            ActivityLogger::log(
                module: 'company',

                action: 'updated',

                description: $user->is_super_user
                    ? "Super Admin updated company profile {$company->company_name}"
                    : "Company Admin updated company profile {$company->company_name}",

                subject: $company,

                oldValues: $changedOld,

                newValues: $changedNew
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Refresh Active Company Session
        |--------------------------------------------------------------------------
        */

        if (
            (int) session(
                'active_company_id'
            ) ===
            (int) $company->id
        ) {

            session([
                'active_company_name' =>
                $company->company_name,

                'active_company_code' =>
                $company->company_code,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return AppResponse::success(
            'Company profile updated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Accessible Company
    |--------------------------------------------------------------------------
    |
    | Super Admin:
    |   Company comes from active business context.
    |
    | Company Admin:
    |   Company comes from authenticated user's company_id.
    |
    | Normal User:
    |   Access denied.
    |
    */

    private function resolveCompany(
        Request $request
    ): Company {

        $user =
            $request->user();


        abort_unless(
            $user,
            401
        );


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


            abort_if(
                !$companyId,
                403,
                'No active company is selected.'
            );


            return Company::query()
                ->findOrFail(
                    $companyId
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Admin
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->hasRole(
                'Company Admin'
            ),
            403,
            'You do not have permission to manage the company profile.'
        );


        $companyId =
            (int) $user->company_id;


        abort_if(
            !$companyId,
            403,
            'No company is assigned to your account.'
        );


        return Company::query()
            ->findOrFail(
                $companyId
            );
    }
}
