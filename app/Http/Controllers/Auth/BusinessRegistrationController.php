<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SuperAdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Throwable;

class BusinessRegistrationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration Page
    |--------------------------------------------------------------------------
    */

    public function create(): Response
    {
        return Inertia::render(
            'Auth/RegisterBusiness'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Register Business
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Validate Registration
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                /*
                |--------------------------------------------------------------------------
                | Company Administrator
                |--------------------------------------------------------------------------
                */

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
                    'required',
                    'string',
                    'max:30',
                ],

                'password' => [
                    'required',
                    'confirmed',

                    Password::min(8)
                        ->letters()
                        ->numbers(),
                ],


                /*
                |--------------------------------------------------------------------------
                | Company
                |--------------------------------------------------------------------------
                */

                'company_name' => [
                    'required',
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

                'company_phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'company_email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],


                /*
                |--------------------------------------------------------------------------
                | Main Branch
                |--------------------------------------------------------------------------
                */

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

                'store_phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'store_email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'address' => [
                    'required',
                    'string',
                    'max:1000',
                ],

                'city' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'state' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'country' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'pincode' => [
                    'required',
                    'string',
                    'max:20',
                ],


                /*
                |--------------------------------------------------------------------------
                | Agreement
                |--------------------------------------------------------------------------
                */

                'terms' => [
                    'accepted',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Administrator
        |--------------------------------------------------------------------------
        */

        $validated['name'] =
            trim(
                $validated['name']
            );


        $validated['email'] =
            strtolower(
                trim(
                    $validated['email']
                )
            );


        $validated['mobile'] =
            trim(
                $validated['mobile']
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize Company
        |--------------------------------------------------------------------------
        */

        $validated['company_name'] =
            trim(
                $validated['company_name']
            );


        if (
            !empty($validated['company_email'])
        ) {

            $validated['company_email'] =
                strtolower(
                    trim(
                        $validated['company_email']
                    )
                );
        }


        if (
            !empty($validated['company_phone'])
        ) {

            $validated['company_phone'] =
                trim(
                    $validated['company_phone']
                );
        }


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


        /*
        |--------------------------------------------------------------------------
        | Normalize Branch
        |--------------------------------------------------------------------------
        */

        $validated['store_name'] =
            trim(
                $validated['store_name']
            );


        $validated['address'] =
            trim(
                $validated['address']
            );


        $validated['city'] =
            trim(
                $validated['city']
            );


        $validated['state'] =
            trim(
                $validated['state']
            );


        $validated['country'] =
            trim(
                $validated['country']
            );


        $validated['pincode'] =
            trim(
                $validated['pincode']
            );


        if (
            !empty($validated['contact_person'])
        ) {

            $validated['contact_person'] =
                trim(
                    $validated['contact_person']
                );
        }


        if (
            !empty($validated['store_phone'])
        ) {

            $validated['store_phone'] =
                trim(
                    $validated['store_phone']
                );
        }


        if (
            !empty($validated['store_email'])
        ) {

            $validated['store_email'] =
                strtolower(
                    trim(
                        $validated['store_email']
                    )
                );
        }


        try {

            /*
            |--------------------------------------------------------------------------
            | Create Complete Business Registration
            |--------------------------------------------------------------------------
            */

            $registration =
                DB::transaction(
                    function () use (
                        $validated
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Company Admin Role
                        |--------------------------------------------------------------------------
                        */

                        $companyAdminRole =
                            Role::query()
                            ->where(
                                'name',
                                'Company Admin'
                            )
                            ->where(
                                'guard_name',
                                'web'
                            )
                            ->first();


                        if (
                            !$companyAdminRole
                        ) {

                            throw new \RuntimeException(
                                'Company Admin role is not configured. Please run RoleSeeder.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Generate Company Code
                        |--------------------------------------------------------------------------
                        */

                        $companyCode =
                            Company::generateCompanyCode(
                                $validated['company_name']
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Create Company
                        |--------------------------------------------------------------------------
                        |
                        | New public registrations remain inactive until
                        | subscription/payment is approved by Super Admin.
                        |
                        */

                        $company =
                            Company::create([

                                'company_code' =>
                                $companyCode,

                                'company_name' =>
                                $validated['company_name'],

                                'owner_name' =>
                                $validated['name'],

                                'gst_number' =>
                                $validated['gst_number']
                                    ?? null,

                                'pan_number' =>
                                $validated['pan_number']
                                    ?? null,

                                'email' =>
                                $validated['company_email']
                                    ?? $validated['email'],

                                'phone' =>
                                $validated['company_phone']
                                    ?? $validated['mobile'],

                                'address' =>
                                $validated['address'],

                                'city' =>
                                $validated['city'],

                                'state' =>
                                $validated['state'],

                                'country' =>
                                $validated['country'],

                                'pincode' =>
                                $validated['pincode'],

                                /*
                                |--------------------------------------------------------------------------
                                | Pending Activation
                                |--------------------------------------------------------------------------
                                */

                                'status' =>
                                false,
                            ]);


                        /*
                        |--------------------------------------------------------------------------
                        | First Branch Number
                        |--------------------------------------------------------------------------
                        */

                        $branchNumber =
                            1;


                        /*
                        |--------------------------------------------------------------------------
                        | Generate Main Branch Code
                        |--------------------------------------------------------------------------
                        */

                        $storeCode =
                            strtoupper(
                                $company->company_code
                            )
                            . '-B'
                            . str_pad(
                                $branchNumber,
                                3,
                                '0',
                                STR_PAD_LEFT
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Branch Contact Person
                        |--------------------------------------------------------------------------
                        */

                        $contactPerson =
                            !empty($validated['contact_person'])
                            ? $validated['contact_person']
                            : $validated['name'];


                        /*
                        |--------------------------------------------------------------------------
                        | Branch Phone
                        |--------------------------------------------------------------------------
                        */

                        $storePhone =
                            !empty($validated['store_phone'])
                            ? $validated['store_phone']
                            : (
                                !empty($validated['company_phone'])
                                ? $validated['company_phone']
                                : $validated['mobile']
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Branch Email
                        |--------------------------------------------------------------------------
                        */

                        $storeEmail =
                            !empty($validated['store_email'])
                            ? $validated['store_email']
                            : (
                                !empty($validated['company_email'])
                                ? $validated['company_email']
                                : $validated['email']
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Create Main Branch
                        |--------------------------------------------------------------------------
                        */

                        $store =
                            Store::create([

                                'company_id' =>
                                $company->id,

                                'branch_number' =>
                                $branchNumber,

                                'store_code' =>
                                $storeCode,

                                'store_name' =>
                                $validated['store_name'],

                                'contact_person' =>
                                $contactPerson,

                                'phone' =>
                                $storePhone,

                                'email' =>
                                $storeEmail,

                                'address' =>
                                $validated['address'],

                                'city' =>
                                $validated['city'],

                                'state' =>
                                $validated['state'],

                                'country' =>
                                $validated['country'],

                                'pincode' =>
                                $validated['pincode'],

                                /*
                                |--------------------------------------------------------------------------
                                | Pending Activation
                                |--------------------------------------------------------------------------
                                */

                                'status' =>
                                false,
                            ]);


                        /*
                        |--------------------------------------------------------------------------
                        | Create Company Admin User
                        |--------------------------------------------------------------------------
                        */

                        $user =
                            User::create([

                                'company_id' =>
                                $company->id,

                                /*
                                |--------------------------------------------------------------------------
                                | Default Main Branch
                                |--------------------------------------------------------------------------
                                */

                                'store_id' =>
                                $store->id,

                                'name' =>
                                $validated['name'],

                                'email' =>
                                $validated['email'],

                                'mobile' =>
                                $validated['mobile'],

                                'password' =>
                                Hash::make(
                                    $validated['password']
                                ),

                                /*
                                |--------------------------------------------------------------------------
                                | Pending Activation
                                |--------------------------------------------------------------------------
                                */

                                'status' =>
                                false,

                                /*
                                |--------------------------------------------------------------------------
                                | Not Platform Super Admin
                                |--------------------------------------------------------------------------
                                */

                                'is_super_user' =>
                                false,
                            ]);


                        /*
                        |--------------------------------------------------------------------------
                        | Assign Main Branch
                        |--------------------------------------------------------------------------
                        */

                        $user
                            ->stores()
                            ->sync([
                                $store->id,
                            ]);


                        /*
                        |--------------------------------------------------------------------------
                        | Assign Company Admin Role
                        |--------------------------------------------------------------------------
                        */

                        $user->syncRoles([
                            $companyAdminRole,
                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | Return Registration Data
                        |--------------------------------------------------------------------------
                        */

                        return [

                            'company' =>
                            $company,

                            'store' =>
                            $store,

                            'user' =>
                            $user,
                        ];
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | Registration Activity Log
            |--------------------------------------------------------------------------
            |
            | Run only after the registration transaction succeeds.
            |
            */

            ActivityLogger::log(

                module: 'company',

                action: 'registered',

                description: "New business registration received: "
                    . "{$registration['company']->company_name} "
                    . "({$registration['company']->company_code})",

                subject: $registration['company'],

                newValues: [

                    'company_id' =>
                    $registration['company']->id,

                    'company_code' =>
                    $registration['company']->company_code,

                    'company_name' =>
                    $registration['company']->company_name,

                    'administrator_name' =>
                    $registration['user']->name,

                    'administrator_email' =>
                    $registration['user']->email,

                    'administrator_mobile' =>
                    $registration['user']->mobile,

                    'branch_id' =>
                    $registration['store']->id,

                    'branch_code' =>
                    $registration['store']->store_code,

                    'branch_name' =>
                    $registration['store']->store_name,

                    'registration_status' =>
                    'Pending Activation',
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Notify Super Administrators
            |--------------------------------------------------------------------------
            |
            | Clicking this notification opens the registered company.
            |
            */

            SuperAdminNotificationService::send(

                title: 'New Business Registration',

                message: "{$registration['company']->company_name} "
                    . "has registered for RR ERP and is waiting for "
                    . "subscription/payment verification and activation.",

                category: 'company',

                icon: 'bi-building-add',

                targetType: 'company',

                targetId: $registration['company']->id,

                fallbackUrl: route(
                    'super-admin.companies.show',
                    $registration['company']->id
                ),

                level: 'info',
            );


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT — Do Not Login
            |--------------------------------------------------------------------------
            |
            | Registration is waiting for Super Admin approval.
            |
            | Therefore we intentionally do NOT:
            |
            | Auth::login()
            | active_company_id
            | active_store_id
            |
            */


            /*
            |--------------------------------------------------------------------------
            | Redirect To Login
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'app_message',
                    [

                        'id' =>
                        (string) Str::uuid(),

                        'type' =>
                        'success',

                        'message' =>
                        'Your RR ERP business registration has been submitted successfully. Please complete your subscription/payment and wait for Super Admin activation before signing in.',
                    ]
                );
        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Log Real Technical Error
            |--------------------------------------------------------------------------
            */

            report(
                $exception
            );


            /*
            |--------------------------------------------------------------------------
            | Return To Registration Page
            |--------------------------------------------------------------------------
            */

            return back()
                ->withInput(
                    $request->except([
                        'password',
                        'password_confirmation',
                    ])
                )
                ->with(
                    'app_message',
                    [

                        'id' =>
                        (string) Str::uuid(),

                        'type' =>
                        'error',

                        'message' =>
                        'We could not complete your business registration. Please review your details and try again.',
                    ]
                );
        }
    }
}
