@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4>
            <i class="bi bi-people"></i>
            User Management
        </h4>

        <a href="{{ route('users.create') }}"
            class="btn btn-primary">

            <i class="bi bi-plus-circle"></i>

            Add User

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
                            placeholder="Search Name / Email"
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

                        <th width="60">#</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Company</th>

                        <th>Role</th>

                        <th>Stores</th>

                        <th>Status</th>

                        <th width="170">Action</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($users as $key => $user)

                    <tr>

                        <td>

                            {{ $users->firstItem() + $key }}

                        </td>

                        <td>

                            {{ $user->name }}

                            @if($user->is_super_user)

                            <span class="badge bg-danger">

                                Super Admin

                            </span>

                            @endif

                        </td>

                        <td>

                            {{ $user->email }}

                        </td>

                        <td>

                            {{ $user->company->company_name ?? '-' }}

                        </td>

                        <td>

                            @forelse($user->roles as $role)

                            <span class="badge bg-primary">

                                {{ $role->name }}

                            </span>

                            @empty

                            -

                            @endforelse

                        </td>

                        <td>

                            @forelse($user->stores as $store)

                            <span class="badge bg-secondary">

                                {{ $store->store_name }}

                            </span>

                            @empty

                            -

                            @endforelse

                        </td>

                        <td>

                            @if($user->status)

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

                            <a href="{{ route('users.show',$user->id) }}"
                                class="btn btn-info btn-sm">

                                <i class="bi bi-eye"></i>

                            </a>

                            <a href="{{ route('users.edit',$user->id) }}"
                                class="btn btn-warning btn-sm">

                                <i class="bi bi-pencil"></i>

                            </a>

                            @if($user->status)

                            <form action="{{ route('users.deactivate', $user) }}"
                                method="POST"
                                class="d-inline">

                                @csrf

                                <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Deactivate this user?')">

                                    <i class="bi bi-person-x"></i>

                                </button>

                            </form>

                            @else

                            <form action="{{ route('users.activate', $user) }}"
                                method="POST"
                                class="d-inline">

                                @csrf

                                <button class="btn btn-success btn-sm">

                                    <i class="bi bi-person-check"></i>

                                </button>

                            </form>

                            @endif

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="8"
                            class="text-center">

                            No Users Found

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($users->hasPages())

        <div class="card-footer">

            {{ $users->links() }}

        </div>

        @endif

    </div>

</div>

@endsection