<div class="card">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            <i class="bi bi-shield-lock"></i>

            {{ isset($role) ? 'Edit Role' : 'Create Role' }}
        </h5>

    </div>

    <div class="card-body">

        <form action="{{ $route }}" method="POST">

            @csrf

            @if($method == 'PUT')
            @method('PUT')
            @endif

            <div class="row">

                <!-- Role Name -->

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Role Name <span class="text-danger">*</span>

                    </label>

                    <input type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $role->name ?? '') }}"
                        required>

                    @error('name')

                    <div class="invalid-feedback">

                        {{ $message }}

                    </div>

                    @enderror

                </div>

                <!-- Status -->

                <div class="col-md-3 mb-3">

                    <label class="form-label">

                        Status

                    </label>

                    <select name="status"
                        class="form-select">

                        <option value="1"
                            {{ old('status', $role->status ?? 1) == 1 ? 'selected' : '' }}>

                            Active

                        </option>

                        <option value="0"
                            {{ old('status', $role->status ?? 1) == 0 ? 'selected' : '' }}>

                            Inactive

                        </option>

                    </select>

                </div>

            </div>

            <!-- Description -->

            <div class="mb-3">

                <label class="form-label">

                    Description

                </label>

                <textarea
                    name="description"
                    rows="3"
                    class="form-control">{{ old('description', $role->description ?? '') }}</textarea>

            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">

                    Permissions

                </h5>

                <button type="button"
                    class="btn btn-sm btn-outline-primary"
                    id="checkAll">

                    Select All

                </button>

            </div>

            @foreach($permissions as $module => $items)

            <div class="card mb-3">

                <div class="card-header bg-light">

                    <strong>

                        {{ ucfirst($module) }}

                    </strong>

                </div>

                <div class="card-body">

                    <div class="row">

                        @foreach($items as $permission)

                        <div class="col-md-3 mb-2">

                            <div class="form-check">

                                <input
                                    class="form-check-input permission-checkbox"
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->name }}"

                                    @if(isset($role))

                                    {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}

                                    @endif>

                                <label class="form-check-label">

                                    {{ ucfirst(last(explode('.', $permission->name))) }}

                                </label>

                            </div>

                        </div>

                        @endforeach

                    </div>

                </div>

            </div>

            @endforeach

            <div class="text-end">

                <a href="{{ route('roles.index') }}"
                    class="btn btn-secondary">

                    Back

                </a>

                <button class="btn btn-success">

                    <i class="bi bi-check-circle"></i>

                    Save Role

                </button>

            </div>

        </form>

    </div>

</div>

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {

        document
            .getElementById('checkAll')
            .addEventListener('click', function() {

                let checkboxes =
                    document.querySelectorAll('.permission-checkbox');

                let checked = [...checkboxes].every(c => c.checked);

                checkboxes.forEach(c => c.checked = !checked);

                this.innerText =
                    checked ? 'Select All' : 'Unselect All';

            });

    });
</script>

@endpush