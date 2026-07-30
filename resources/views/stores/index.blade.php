@extends('layouts.app')

@section('title', 'Stores')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Stores</h3>

        @can('store.create')
        <a href="{{ route('stores.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Store
        </a>
        @endcan
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow-sm">

        <div class="card-body">

            <form method="GET" action="{{ route('stores.index') }}" class="row mb-3">

                <div class="col-md-4">
                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search Store Name / Code">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Company</th>
                            <th>Store</th>
                            <th>Contact</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Status</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($stores as $store)

                        <tr>

                            <td>{{ $stores->firstItem() + $loop->index }}</td>

                            <td>
                                <span class="badge bg-secondary">
                                    {{ $store->store_code }}
                                </span>
                            </td>

                            <td>{{ $store->company->company_name ?? '-' }}</td>

                            <td>{{ $store->store_name }}</td>

                            <td>{{ $store->contact_person }}</td>

                            <td>{{ $store->phone }}</td>

                            <td>{{ $store->city }}</td>

                            <td>
                                @if($store->status)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>

                            <td>
                                @can('store.edit')
                                <a href="{{ route('stores.edit',$store) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                @can('store.delete')
                                <form action="{{ route('stores.destroy',$store) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this store?')">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </form>
                                @endcan

                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="9" class="text-center">
                                No Stores Found
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{ $stores->withQueryString()->links() }}

        </div>

    </div>

</div>

@endsection