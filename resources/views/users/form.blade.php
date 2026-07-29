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

            <div class="row">

                @if(auth()->user()->is_super_user)

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Company
                    </label>

                    <select name="company_id"
                        class="form-select"
                        required>

                        <option value="">
                            Select Company
                        </option>

                        @foreach($companies as $company)

                        <option value="{{ $company->id }}"
                            {{ old('company_id', $user->company_id ?? '') == $company->id ? 'selected' : '' }}>

                            {{ $company->company_name }}

                        </option>

                        @endforeach

                    </select>

                </div>

                @endif

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Name

                    </label>

                    <input type="text"
                        name="name"
                        class="form-control"
                        value="{{ old('name', $user->name ?? '') }}"
                        required>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Email

                    </label>

                    <input type="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email', $user->email ?? '') }}"
                        required>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Mobile

                    </label>

                    <input type="text"
                        name="mobile"
                        class="form-control"
                        value="{{ old('mobile', $user->mobile ?? '') }}">

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Password
                    </label>

                    <input type="password"
                        name="password"
                        class="form-control"
                        {{ isset($user) ? '' : 'required' }}>

                    @if(isset($user))
                    <small class="text-muted">
                        Leave blank to keep the current password.
                    </small>
                    @endif

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Confirm Password
                    </label>

                    <input type="password"
                        name="password_confirmation"
                        class="form-control"
                        {{ isset($user) ? '' : 'required' }}>

                </div>

            </div>

            <hr>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Roles

                    </label>

                    <select name="roles[]"
                        class="form-select"
                        multiple
                        size="6">

                        @foreach($roles as $role)

                        <option value="{{ $role->name }}"
                            {{ in_array($role->name, old('roles', isset($user) ? $user->roles->pluck('name')->toArray() : [])) ? 'selected' : '' }}>

                            {{ $role->name }}

                        </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Stores

                    </label>

                    <select name="stores[]"
                        class="form-select"
                        multiple
                        size="6">

                        @foreach($stores as $store)

                        <option value="{{ $store->id }}"
                            {{ in_array($store->id, old('stores', isset($user) ? $user->stores->pluck('id')->toArray() : [])) ? 'selected' : '' }}>

                            {{ $store->store_name }}

                        </option>

                        @endforeach

                    </select>

                </div>

            </div>

            <div class="row">

                <div class="col-md-4">

                    <label class="form-label">

                        Status

                    </label>

                    <select name="status"
                        class="form-select">

                        <option value="1"
                            {{ old('status', $user->status ?? 1) == 1 ? 'selected' : '' }}>

                            Active

                        </option>

                        <option value="0"
                            {{ old('status', $user->status ?? 1) == 0 ? 'selected' : '' }}>

                            Inactive

                        </option>

                    </select>

                </div>

            </div>

            <hr>

            <div class="text-end">

                <a href="{{ route('users.index') }}"
                    class="btn btn-secondary">

                    Back

                </a>

                <button class="btn btn-success">

                    <i class="bi bi-check-circle"></i>

                    Save User

                </button>

            </div>

        </form>

    </div>

</div>