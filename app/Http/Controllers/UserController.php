<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Store;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;



class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::with(['company', 'roles', 'stores']);

        if (!auth()->user()->is_super_user) {
            $users->where('company_id', auth()->user()->company_id);
        }

        if ($request->filled('search')) {
            $users->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $users->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = auth()->user()->is_super_user
            ? Company::where('status', 1)->get()
            : collect();

        $roles = auth()->user()->is_super_user
            ? Role::where('status', 1)->get()
            : Role::where('company_id', auth()->user()->company_id)
            ->where('status', 1)
            ->get();

        $stores = auth()->user()->is_super_user
            ? collect()
            : Store::where('company_id', auth()->user()->company_id)
            ->where('status', 1)
            ->orderBy('store_name')
            ->get();

        return view('users.create', compact(
            'companies',
            'roles',
            'stores'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isCreatingSuperAdmin = in_array(
            'Super Admin',
            $request->input('roles', [])
        );

        /*
        |--------------------------------------------------------------------------
        | Validate common fields
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],

            'mobile' => 'nullable|string|max:20',

            'password' => 'required|confirmed|min:6',

            'roles' => 'required|array|min:1',

            'roles.*' => 'exists:roles,name',

            'status' => 'required|boolean',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Super Admin validation
        |--------------------------------------------------------------------------
        */

        if ($isCreatingSuperAdmin) {

            // Only existing Super Admin can create another Super Admin
            if (!auth()->user()->is_super_user) {
                abort(403);
            }

            // Only one Super Admin
            if (User::where('is_super_user', true)->exists()) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'roles' => 'Only one Super Admin is allowed.',
                    ]);
            }

            // Super Admin does NOT need company/store
            $companyId = null;
            $storeIds = [];
        } else {

            /*
            |--------------------------------------------------------------------------
            | Normal User
            |--------------------------------------------------------------------------
            */

            if (auth()->user()->is_super_user) {

                $request->validate([
                    'company_id' => 'required|exists:companies,id',

                    'stores' => 'required|array|min:1',

                    'stores.*' => 'exists:stores,id',
                ]);

                $companyId = $request->company_id;
            } else {

                // Company Admin
                $companyId = auth()->user()->company_id;

                $request->validate([
                    'stores' => 'required|array|min:1',

                    'stores.*' => 'exists:stores,id',
                ]);
            }

            $storeIds = $request->stores;

            /*
            |--------------------------------------------------------------------------
            | Make sure selected stores belong to selected company
            |--------------------------------------------------------------------------
            */

            $validStoreCount = Store::whereIn('id', $storeIds)
                ->where('company_id', $companyId)
                ->where('status', 1)
                ->count();

            if ($validStoreCount != count($storeIds)) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'stores' => 'One or more selected stores do not belong to the selected company.',
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create User
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $request,
            $isCreatingSuperAdmin,
            $companyId,
            $storeIds
        ) {

            $user = User::create([
                'company_id' => $companyId,

                'name' => $request->name,

                'email' => $request->email,

                'mobile' => $request->mobile,

                'password' => Hash::make($request->password),

                'status' => $request->status,

                'is_super_user' => $isCreatingSuperAdmin,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Assign Role
            |--------------------------------------------------------------------------
            */

            $user->syncRoles($request->roles);

            /*
            |--------------------------------------------------------------------------
            | Assign Stores
            |--------------------------------------------------------------------------
            */

            if (!$isCreatingSuperAdmin) {

                $user->stores()->sync($storeIds);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Company Admin cannot view users from another company
        if (
            !auth()->user()->is_super_user &&
            $user->company_id != auth()->user()->company_id
        ) {
            abort(403);
        }

        $user->load([
            'company',
            'roles',
            'stores'
        ]);

        $permissions = $user->getAllPermissions()
            ->sortBy('name')
            ->groupBy(function ($permission) {
                return explode('.', $permission->name)[0];
            });

        return view('users.show', compact(
            'user',
            'permissions'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        /*
        |--------------------------------------------------------------------------
        | Company Admin cannot edit users from another company
        |--------------------------------------------------------------------------
        */

        if (
            !auth()->user()->is_super_user &&
            $user->company_id != auth()->user()->company_id
        ) {
            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | Companies
        |--------------------------------------------------------------------------
        */

        $companies = auth()->user()->is_super_user
            ? Company::where('status', 1)
            ->orderBy('company_name')
            ->get()
            : collect();


        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $roles = auth()->user()->is_super_user
            ? Role::where('status', 1)
            ->orderBy('name')
            ->get()
            : Role::where('company_id', auth()->user()->company_id)
            ->where('status', 1)
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Stores
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            /*
            | Super Admin:
            | Load stores belonging to the user's current company.
            |
            | If the user being edited is Super Admin,
            | company_id is NULL, so don't load stores.
            */

            $stores = $user->company_id
                ? Store::where('company_id', $user->company_id)
                ->where('status', 1)
                ->orderBy('store_name')
                ->get()
                : collect();
        } else {

            /*
            | Company Admin:
            | Only stores from their own company.
            */

            $stores = Store::where(
                'company_id',
                auth()->user()->company_id
            )
                ->where('status', 1)
                ->orderBy('store_name')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Load User Relationships
        |--------------------------------------------------------------------------
        */

        $user->load([
            'company',
            'roles',
            'stores',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Return Edit View
        |--------------------------------------------------------------------------
        */

        return view(
            'users.edit',
            compact(
                'user',
                'companies',
                'roles',
                'stores'
            )
        );
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        /*
        |--------------------------------------------------------------------------
        | Company Admin cannot edit users from another company
        |--------------------------------------------------------------------------
        */

        if (
            !auth()->user()->is_super_user &&
            $user->company_id != auth()->user()->company_id
        ) {
            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | Check selected role
        |--------------------------------------------------------------------------
        */

        $isSuperUser = in_array(
            'Super Admin',
            $request->input('roles', [])
        );


        /*
        |--------------------------------------------------------------------------
        | Common validation
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'mobile' => 'nullable|string|max:20',

            'status' => 'required|boolean',

            'roles' => 'required|array|min:1',

            'roles.*' => 'exists:roles,name',

            'password' => 'nullable|confirmed|min:6',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($isSuperUser) {

            /*
            | Only Super Admin can assign Super Admin role
            */

            if (!auth()->user()->is_super_user) {
                abort(403);
            }


            /*
            | Check if another Super Admin already exists
            */

            $exists = User::where('is_super_user', true)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($exists) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'roles' => 'Only one Super Admin is allowed.',
                    ]);
            }


            /*
            | Super Admin does NOT need Company
            | Super Admin does NOT need Store
            */

            $companyId = null;

            $storeIds = [];
        } else {

            /*
            |--------------------------------------------------------------------------
            | Normal User
            |--------------------------------------------------------------------------
            */

            if (auth()->user()->is_super_user) {

                /*
                | Super Admin editing a normal user
                */

                $request->validate([

                    'company_id' => 'required|exists:companies,id',

                    'stores' => 'required|array|min:1',

                    'stores.*' => 'exists:stores,id',
                ]);

                $companyId = $request->company_id;
            } else {

                /*
                | Company Admin editing a normal user
                */

                $companyId = auth()->user()->company_id;

                $request->validate([

                    'stores' => 'required|array|min:1',

                    'stores.*' => 'exists:stores,id',
                ]);
            }


            $storeIds = $request->stores;


            /*
            |--------------------------------------------------------------------------
            | Verify Stores belong to selected Company
            |--------------------------------------------------------------------------
            */

            $validStoreCount = Store::whereIn('id', $storeIds)
                ->where('company_id', $companyId)
                ->where('status', 1)
                ->count();


            if ($validStoreCount != count($storeIds)) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'stores' =>
                        'One or more selected stores do not belong to the selected company.',
                    ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Update User
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $request,
            $user,
            $isSuperUser,
            $companyId,
            $storeIds
        ) {

            $data = [

                'company_id' => $companyId,

                'name' => $request->name,

                'email' => $request->email,

                'mobile' => $request->mobile,

                'status' => $request->status,

                'is_super_user' => $isSuperUser,
            ];


            /*
            |--------------------------------------------------------------------------
            | Update Password
            |--------------------------------------------------------------------------
            */

            if ($request->filled('password')) {

                $data['password'] = Hash::make(
                    $request->password
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Update User
            |--------------------------------------------------------------------------
            */

            $user->update($data);


            /*
            |--------------------------------------------------------------------------
            | Sync Roles
            |--------------------------------------------------------------------------
            */

            $user->syncRoles(
                $request->roles
            );


            /*
            |--------------------------------------------------------------------------
            | Sync Stores
            |--------------------------------------------------------------------------
            */

            if ($isSuperUser) {

                // Super Admin has no store restrictions
                $user->stores()->sync([]);
            } else {

                $user->stores()->sync(
                    $storeIds
                );
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User updated successfully.'
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function deactivate(User $user)
    {
        if (
            !auth()->user()->is_super_user &&
            $user->company_id != auth()->user()->company_id
        ) {
            abort(403);
        }

        if ($user->id == auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->is_super_user) {
            return back()->with('error', 'Super Admin cannot be deactivated.');
        }

        $user->update([
            'status' => 0
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User deactivated successfully.');
    }

    public function activate(User $user)
    {
        if (
            !auth()->user()->is_super_user &&
            $user->company_id != auth()->user()->company_id
        ) {
            abort(403);
        }

        $user->update([
            'status' => 1
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User activated successfully.');
    }
}
