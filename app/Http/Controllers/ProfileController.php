<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show / Edit Profile
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request
    ): Response {

        $user =
            $request->user();

        $user->load([
            'company:id,company_name,company_code',

            'stores:id,company_id,store_code,store_name,status',

            'roles:id,name',
        ]);


        return Inertia::render(
            'Profile/Show',
            [
                'userData' =>
                $this->profileData(
                    $user
                ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    |
    | Your /profile route can point here.
    | We return the same Profile/Show page because that page can contain
    | both profile information and edit forms.
    |
    */

    public function edit(
        Request $request
    ): Response {

        return $this->show(
            $request
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Profile
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request
    ): RedirectResponse {

        $user =
            $request->user();


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
                    )->ignore(
                        $user->id
                    ),
                ],

                'mobile' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Capture Old Values
        |--------------------------------------------------------------------------
        */

        $oldValues =
            $this->profileAuditValues(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        if ($user->email !== $validated['email']) {
            $user->forceFill(['email_verified_at' => null]);
        }

        $user->update([
            'name' =>
            $validated['name'],

            'email' =>
            $validated['email'],

            'mobile' =>
            $validated['mobile']
                ?? null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Reload User
        |--------------------------------------------------------------------------
        */

        $user->refresh();


        /*
        |--------------------------------------------------------------------------
        | New Values
        |--------------------------------------------------------------------------
        */

        $newValues =
            $this->profileAuditValues(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Changed Values Only
        |--------------------------------------------------------------------------
        */

        [
            $changedOld,
            $changedNew,
        ] = $this->changedAuditValues(
            $oldValues,
            $newValues
        );


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        if (
            !empty($changedNew)
        ) {

            ActivityLogger::log(
                module: 'profile',

                action: 'updated',

                description: "Updated profile for {$user->name}",

                subject: $user,

                oldValues: $changedOld,

                newValues: $changedNew
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            'Profile updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    */

    public function updatePassword(
        Request $request
    ): RedirectResponse {

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'current_password' => [
                    'required',
                    'current_password',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Update Password
        |--------------------------------------------------------------------------
        */

        $user->update([
            'password' =>
            Hash::make(
                $validated['password']
            ),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        |
        | Never store the password or password hash.
        |
        */

        ActivityLogger::log(
            module: 'profile',

            action: 'password-changed',

            description: "Changed password for {$user->name}",

            subject: $user,

            oldValues: [
                'password' =>
                '********',
            ],

            newValues: [
                'password' =>
                'Changed',
            ]
        );


        return back()->with(
            'success',
            'Password updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Account
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request
    ): RedirectResponse {

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Super Admin Protection
        |--------------------------------------------------------------------------
        |
        | A Super Administrator should not accidentally remove the platform
        | administrator account from the normal profile screen.
        |
        */

        if (
            (bool) $user->is_super_user
        ) {

            return back()->with(
                'error',
                'Super Administrator accounts cannot be deleted from the profile page.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Password
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'password' => [
                'required',
                'current_password',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $user->load([
            'company:id,company_name,company_code',

            'stores:id,company_id,store_code,store_name',

            'roles:id,name',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Capture Audit Values
        |--------------------------------------------------------------------------
        */

        $oldValues = [

            ...$this->profileAuditValues(
                $user
            ),

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

            'roles' =>
            $user->roles
                ->pluck('name')
                ->sort()
                ->values()
                ->implode(', '),
        ];


        $userName =
            $user->name;

        $userEmail =
            $user->email;


        /*
        |--------------------------------------------------------------------------
        | Activity Log Before Delete
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            module: 'profile',

            action: 'deleted',

            description: "Deleted account {$userName} ({$userEmail})",

            subject: $user,

            oldValues: $oldValues,

            newValues: [
                'record_state' =>
                'Deleted',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Remove Relationships
        |--------------------------------------------------------------------------
        */

        $user->stores()
            ->detach();

        $user->syncRoles([]);


        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Auth::guard('web')
            ->logout();


        /*
        |--------------------------------------------------------------------------
        | Delete User
        |--------------------------------------------------------------------------
        */

        $user->delete();


        /*
        |--------------------------------------------------------------------------
        | Invalidate Session
        |--------------------------------------------------------------------------
        */

        $request->session()
            ->invalidate();

        $request->session()
            ->regenerateToken();


        return redirect('/')
            ->with(
                'success',
                'Your account has been deleted.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Page Data
    |--------------------------------------------------------------------------
    */

    private function profileData(
        $user
    ): array {

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

            'is_super_user' =>
            (bool) $user->is_super_user,

            'last_login_at' =>
            $user->last_login_at,


            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | Branches
            |--------------------------------------------------------------------------
            */

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

                        'status' =>
                        (bool) $store->status,
                    ]
                )
                ->values(),


            /*
            |--------------------------------------------------------------------------
            | Roles
            |--------------------------------------------------------------------------
            */

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
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Audit Values
    |--------------------------------------------------------------------------
    */

    private function profileAuditValues(
        $user
    ): array {

        return [
            'name' =>
            $user->name,

            'email' =>
            $user->email,

            'mobile' =>
            $user->mobile,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Detect Changes
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
}
