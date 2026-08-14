<div class="card">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            <i class="bi bi-person-plus"></i>
            {{ isset($user) ? 'Edit User' : 'Create User' }}
        </h5>

    </div>

    <div class="card-body">

        <form action="{{ $route }}" method="POST">

            @csrf

            @if($method == 'PUT')
            @method('PUT')
            @endif


            {{-- ========================================================= --}}
            {{-- BASIC INFORMATION --}}
            {{-- ========================================================= --}}

            <div class="row">

                {{-- Company --}}
                @if(auth()->user()->is_super_user)

                <div
                    class="col-md-6 mb-3"
                    id="company-section">

                    <label class="form-label">
                        Company
                    </label>

                    <select
                        name="company_id"
                        id="company_id"
                        class="form-select">

                        <option value="">
                            Select Company
                        </option>

                        @foreach($companies as $company)

                        <option
                            value="{{ $company->id }}"
                            {{ old(
                                        'company_id',
                                        $user->company_id ?? ''
                                    ) == $company->id ? 'selected' : '' }}>
                            {{ $company->company_name }}
                        </option>

                        @endforeach

                    </select>

                    @error('company_id')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

                @endif


                {{-- Name --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ old('name', $user->name ?? '') }}"
                        required>

                    @error('name')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- Email --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email', $user->email ?? '') }}"
                        required>

                    @error('email')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- Mobile --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Mobile
                    </label>

                    <input
                        type="text"
                        name="mobile"
                        class="form-control"
                        value="{{ old('mobile', $user->mobile ?? '') }}">

                    @error('mobile')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- Password --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        {{ isset($user) ? '' : 'required' }}>

                    @if(isset($user))

                    <small class="text-muted">
                        Leave blank to keep the current password.
                    </small>

                    @endif

                    @error('password')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- Confirm Password --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        {{ isset($user) ? '' : 'required' }}>

                </div>

            </div>


            <hr>


            {{-- ========================================================= --}}
            {{-- ROLE + STORE --}}
            {{-- ========================================================= --}}

            <div class="row">

                {{-- Roles --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Roles
                    </label>

                    <select
                        name="roles[]"
                        id="role"
                        class="form-select"
                        multiple
                        size="6">

                        @foreach($roles as $role)

                        <option
                            value="{{ $role->name }}"
                            {{ in_array(
                                    $role->name,
                                    old(
                                        'roles',
                                        isset($user)
                                            ? $user->roles
                                                ->pluck('name')
                                                ->toArray()
                                            : []
                                    )
                                ) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>

                        @endforeach

                    </select>

                    <small class="text-muted">
                        Hold Ctrl to select multiple roles.
                    </small>

                    @error('roles')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- Stores --}}
                <div
                    class="col-md-6 mb-3"
                    id="store-section">

                    <label class="form-label">
                        Stores
                    </label>

                    <select
                        name="stores[]"
                        id="stores"
                        class="form-select"
                        multiple
                        size="6">

                        @foreach($stores as $store)

                        <option
                            value="{{ $store->id }}"
                            {{ in_array(
                                    $store->id,
                                    old(
                                        'stores',
                                        isset($user)
                                            ? $user->stores
                                                ->pluck('id')
                                                ->toArray()
                                            : []
                                    )
                                ) ? 'selected' : '' }}>
                            {{ $store->store_name }}
                        </option>

                        @endforeach

                    </select>

                    <small class="text-muted">
                        Hold Ctrl to select multiple stores.
                    </small>

                    @error('stores')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- STATUS --}}
            {{-- ========================================================= --}}

            <div class="row">

                <div class="col-md-4">

                    <label class="form-label">
                        Status
                    </label>

                    <select
                        name="status"
                        class="form-select">

                        <option
                            value="1"
                            {{ old(
                                'status',
                                $user->status ?? 1
                            ) == 1 ? 'selected' : '' }}>
                            Active
                        </option>

                        <option
                            value="0"
                            {{ old(
                                'status',
                                $user->status ?? 1
                            ) == 0 ? 'selected' : '' }}>
                            Inactive
                        </option>

                    </select>

                    @error('status')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

            </div>


            <hr>


            {{-- ========================================================= --}}
            {{-- BUTTONS --}}
            {{-- ========================================================= --}}

            <div class="text-end">

                <a
                    href="{{ route('users.index') }}"
                    class="btn btn-secondary">
                    Back
                </a>

                <button
                    type="submit"
                    class="btn btn-success">
                    <i class="bi bi-check-circle"></i>
                    Save User
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ========================================================= --}}
{{-- SUPER ADMIN SHOW / HIDE --}}
{{-- ========================================================= --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const roleSelect = document.getElementById('role');

        const companySection =
            document.getElementById('company-section');

        const storeSection =
            document.getElementById('store-section');

        const companySelect =
            document.getElementById('company_id');

        const storeSelect =
            document.getElementById('stores');


        function handleRoleChange() {

            /*
            |--------------------------------------------------------------------------
            | Get selected roles
            |--------------------------------------------------------------------------
            */

            const selectedRoles =
                Array.from(roleSelect.selectedOptions)
                .map(option => option.value);


            /*
            |--------------------------------------------------------------------------
            | Check Super Admin
            |--------------------------------------------------------------------------
            */

            const isSuperAdmin =
                selectedRoles.includes('Super Admin');


            if (isSuperAdmin) {

                /*
                |--------------------------------------------------------------------------
                | Hide Company
                |--------------------------------------------------------------------------
                */

                if (companySection) {
                    companySection.style.display = 'none';
                }


                /*
                |--------------------------------------------------------------------------
                | Hide Stores
                |--------------------------------------------------------------------------
                */

                if (storeSection) {
                    storeSection.style.display = 'none';
                }


                /*
                |--------------------------------------------------------------------------
                | Clear Company
                |--------------------------------------------------------------------------
                */

                if (companySelect) {
                    companySelect.value = '';
                }


                /*
                |--------------------------------------------------------------------------
                | Clear Stores
                |--------------------------------------------------------------------------
                */

                if (storeSelect) {

                    Array.from(
                        storeSelect.options
                    ).forEach(function(option) {

                        option.selected = false;

                    });

                }

            } else {

                /*
                |--------------------------------------------------------------------------
                | Show Company
                |--------------------------------------------------------------------------
                */

                if (companySection) {
                    companySection.style.display = '';
                }


                /*
                |--------------------------------------------------------------------------
                | Show Stores
                |--------------------------------------------------------------------------
                */

                if (storeSection) {
                    storeSection.style.display = '';
                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Run when page loads
        |--------------------------------------------------------------------------
        */

        handleRoleChange();


        /*
        |--------------------------------------------------------------------------
        | Run when role changes
        |--------------------------------------------------------------------------
        */

        roleSelect.addEventListener(
            'change',
            handleRoleChange
        );

    });
</script>