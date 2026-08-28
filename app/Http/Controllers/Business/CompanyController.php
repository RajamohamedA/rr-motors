<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\ActivityLogger;
use App\Support\AppResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        | Company Admin Only
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->hasRole(
                'Company Admin'
            ),
            403,
            'You do not have permission to manage the company profile.'
        );


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        |
        | Never accept company_id from the browser.
        |
        */

        $company =
            Company::query()
            ->findOrFail(
                $user->company_id
            );


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (bool) $company->status,
            403,
            'Your company is inactive.'
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
        | Company Admin Only
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->hasRole(
                'Company Admin'
            ),
            403,
            'You do not have permission to edit the company profile.'
        );


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()
            ->findOrFail(
                $user->company_id
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
                'Your company is inactive. Please contact the Super Administrator.'
            );
        }


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

        $company->update(
            $validated
        );


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

                description: "Company Admin updated company profile {$company->company_name}",

                subject: $company,

                oldValues: $changedOld,

                newValues: $changedNew
            );
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
}
