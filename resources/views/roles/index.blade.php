```blade
@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4>
            <i class="bi bi-shield-lock"></i>
            Role Management
        </h4>

        <a href="{{ route('roles.create') }}"
            class="btn btn-primary">

            <i class="bi bi-plus-circle"></i>
            Add Role

        </a>

    </div>

    @if(session('success'))

    <div class="alert alert-success alert-dismissible fade show">

        {{ session('success') }}

        <button class="btn-close"
            data-bs-dismiss="alert"></button>

    </div>

    @endif


    <div class="card">

        <div class="card-header">

            <form method="GET">

                <div class="row">

                    <div class="col-md-4">

                        <input type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search Role..."
                            value="{{ request('search') }}">

                    </div>

                    <div class="col-md-2">

                        <button class="btn btn-primary">

                            <i class="bi bi-search"></i>
                            Search

                        </button>

                    </div>

                </div>

            </form>

        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark">

                    <tr>

                        <th width="70">#</th>

                        <th>Role</th>

                        <th>Description</th>

                        <th>Permissions</th>

                        <th>Status</th>

                        <th width="180">Action</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($roles as $key => $role)

                    <tr>

                        <td>
                            {{ $roles->firstItem() + $key }}
                        </td>

                        <td>

                            <strong>

                                {{ $role->name }}

                            </strong>

                        </td>

                        <td>

                            {{ $role->description }}

                        </td>

                        <td>

                            <span class="badge bg-info">

                                {{ $role->permissions->count() }}

                            </span>

                        </td>

                        <td>

                            @if($role->status)

                            <span class="badge bg-success">

                                Active

                            </span>

                            @else

                            <span class="badge bg-danger">

                                Inactive

                            </span>

                            @endif

                        </td>

                        <td>

                            <a href="{{ route('roles.edit',$role->id) }}"
                                class="btn btn-warning btn-sm">

                                <i class="bi bi-pencil-square"></i>

                            </a>

                            <form action="{{ route('roles.destroy',$role->id) }}"
                                method="POST"
                                class="d-inline">

                                @csrf

                                @method('DELETE')

                                <button
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this role?')">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="6"
                            class="text-center text-muted">

                            No Roles Found

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($roles->hasPages())

        <div class="card-footer">

            {{ $roles->links() }}

        </div>

        @endif

    </div>

</div>

@endsection