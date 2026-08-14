@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-1">
                <i class="bi bi-building"></i>
                Godowns
            </h4>

            <small class="text-muted">
                Manage godowns and warehouse locations
            </small>
        </div>

        @can('godown.create')
        <a href="{{ route('godowns.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Add Godown
        </a>
        @endcan

    </div>


    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">

        <i class="bi bi-check-circle me-1"></i>

        {{ session('success') }}

        <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>
    @endif


    {{-- Error Message --}}
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">

        <i class="bi bi-exclamation-triangle me-1"></i>

        {{ session('error') }}

        <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>
    @endif


    {{-- Godown Card --}}
    <div class="card shadow-sm">

        <div class="card-header">

            <div class="row align-items-center">

                <div class="col-md-6">

                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-1"></i>
                        Godown List
                    </h5>

                </div>


                <div class="col-md-6">

                    <form method="GET"
                        action="{{ route('godowns.index') }}">

                        <div class="input-group">

                            <input type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search godown..."
                                value="{{ request('search') }}">

                            <button type="submit"
                                class="btn btn-outline-primary">

                                <i class="bi bi-search"></i>

                                Search

                            </button>


                            @if(request('search'))

                            <a href="{{ route('godowns.index') }}"
                                class="btn btn-outline-secondary">

                                <i class="bi bi-x-lg"></i>

                            </a>

                            @endif

                        </div>

                    </form>

                </div>

            </div>

        </div>


        {{-- Table --}}
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-bordered align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th width="60">#</th>

                            @if(auth()->user()->is_super_user)
                            <th>Company</th>
                            @endif

                            <th>Store</th>

                            <th>Code</th>

                            <th>Godown Name</th>

                            <th>Address</th>

                            <th width="100">
                                Status
                            </th>

                            <th width="130" class="text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($godowns as $godown)

                        <tr>

                            {{-- Serial Number --}}
                            <td>
                                {{ $godowns->firstItem() + $loop->index }}
                            </td>


                            {{-- Company --}}
                            @if(auth()->user()->is_super_user)

                            <td>
                                {{ $godown->company->company_name ?? '-' }}
                            </td>

                            @endif


                            {{-- Store --}}
                            <td>
                                {{ $godown->store->store_name ?? '-' }}
                            </td>


                            {{-- Code --}}
                            <td>

                                @if($godown->godown_code)

                                <span class="badge bg-secondary">
                                    {{ $godown->godown_code }}
                                </span>

                                @else

                                <span class="text-muted">
                                    -
                                </span>

                                @endif

                            </td>


                            {{-- Godown Name --}}
                            <td>

                                <strong>
                                    {{ $godown->godown_name }}
                                </strong>

                            </td>


                            {{-- Address --}}
                            <td>

                                @if($godown->address)

                                {{ $godown->address }}

                                @else

                                <span class="text-muted">
                                    -
                                </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td>

                                @if($godown->status)

                                <span class="badge bg-success">
                                    Active
                                </span>

                                @else

                                <span class="badge bg-danger">
                                    Inactive
                                </span>

                                @endif

                            </td>


                            {{-- Actions --}}
                            <td class="text-center">

                                <div class="btn-group"
                                    role="group">

                                    {{-- View --}}
                                    @can('godown.view')

                                    <a href="{{ route('godowns.show', $godown->id) }}"
                                        class="btn btn-sm btn-outline-info"
                                        title="View">

                                        <i class="bi bi-eye"></i>

                                    </a>

                                    @endcan


                                    {{-- Edit --}}
                                    @can('godown.edit')

                                    <a href="{{ route('godowns.edit', $godown->id) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Edit">

                                        <i class="bi bi-pencil"></i>

                                    </a>

                                    @endcan


                                    {{-- Delete --}}
                                    @can('godown.delete')

                                    <form action="{{ route('godowns.destroy', $godown->id) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this godown?');">

                                        @csrf

                                        @method('DELETE')

                                        <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete">

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </form>

                                    @endcan

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="{{ auth()->user()->is_super_user ? 8 : 7 }}"
                                class="text-center py-5">

                                <div class="text-muted">

                                    <i class="bi bi-building fs-1 d-block mb-3"></i>

                                    <h6>
                                        No Godowns Found
                                    </h6>

                                    <p class="mb-3">
                                        No godowns are available.
                                    </p>


                                    @can('godown.create')

                                    <a href="{{ route('godowns.create') }}"
                                        class="btn btn-primary btn-sm">

                                        <i class="bi bi-plus-circle me-1"></i>

                                        Add Godown

                                    </a>

                                    @endcan

                                </div>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Pagination --}}
        @if($godowns->hasPages())

        <div class="card-footer">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div class="text-muted small">

                    Showing
                    <strong>{{ $godowns->firstItem() }}</strong>
                    to
                    <strong>{{ $godowns->lastItem() }}</strong>
                    of
                    <strong>{{ $godowns->total() }}</strong>
                    godowns

                </div>

                <div>
                    {{ $godowns->links() }}
                </div>

            </div>

        </div>

        @endif

    </div>

</div>

@endsection