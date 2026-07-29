
@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3">

        <h4>

            <i class="bi bi-person-circle"></i>

            User Details

        </h4>

        <a href="{{ route('users.index') }}"
            class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Back

        </a>

    </div>

    <div class="row">

        <!-- User Information -->

        <div class="col-md-6">

            <div class="card mb-3">

                <div class="card-header bg-primary text-white">

                    Basic Information

                </div>

                <div class="card-body">

                    <table class="table table-borderless">

                        <tr>

                            <th width="180">Name</th>

                            <td>{{ $user->name }}</td>

                        </tr>

                        <tr>

                            <th>Email</th>

                            <td>{{ $user->email }}</td>

                        </tr>

                        <tr>

                            <th>Mobile</th>

                            <td>{{ $user->mobile ?: '-' }}</td>

                        </tr>

                        <tr>

                            <th>Company</th>

                            <td>{{ $user->company->company_name ?? '-' }}</td>

                        </tr>

                        <tr>

                            <th>Status</th>

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

                        </tr>

                        <tr>

                            <th>Super User</th>

                            <td>

                                @if($user->is_super_user)

                                <span class="badge bg-danger">

                                    Yes

                                </span>

                                @else

                                No

                                @endif

                            </td>

                        </tr>

                        <tr>

                            <th>Created</th>

                            <td>

                                {{ $user->created_at->format('d-m-Y h:i A') }}

                            </td>

                        </tr>

                        <tr>

                            <th>Last Login</th>

                            <td>

                                {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d-m-Y h:i A') : '-' }}

                            </td>

                        </tr>

                    </table>

                </div>

            </div>

        </div>

        <!-- Roles & Stores -->

        <div class="col-md-6">

            <div class="card mb-3">

                <div class="card-header bg-success text-white">

                    Roles

                </div>

                <div class="card-body">

                    @forelse($user->roles as $role)

                    <span class="badge bg-primary">

                        {{ $role->name }}

                    </span>

                    @empty

                    No Role Assigned

                    @endforelse

                </div>

            </div>

            <div class="card">

                <div class="card-header bg-info text-white">

                    Store Access

                </div>

                <div class="card-body">

                    @forelse($user->stores as $store)

                    <span class="badge bg-secondary">

                        {{ $store->store_name }}

                    </span>

                    @empty

                    No Store Assigned

                    @endforelse

                </div>

            </div>

        </div>

    </div>

    <!-- Permissions -->

    <div class="card mt-3">

        <div class="card-header bg-dark text-white">

            Effective Permissions

        </div>

        <div class="card-body">

            @forelse($permissions as $module => $items)

            <div class="mb-4">

                <h6 class="border-bottom pb-2">

                    {{ ucfirst($module) }}

                </h6>

                <div class="row">

                    @foreach($items as $permission)

                    <div class="col-md-3 mb-2">

                        <span class="badge bg-success">

                            {{ $permission->name }}

                        </span>

                    </div>

                    @endforeach

                </div>

            </div>

            @empty

            <p class="text-muted">

                No permissions assigned.

            </p>

            @endforelse

        </div>

    </div>

</div>

@endsection