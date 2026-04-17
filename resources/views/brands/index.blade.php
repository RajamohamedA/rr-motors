@extends('layouts.app')

@section('title', 'Brands')

@section('content')

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Brand List</h3>

        <a href="{{ route('brands.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Brand
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">

        <div class="card-body">

            <form method="GET" action="{{ route('brands.index') }}" class="row mb-3">

                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="form-control"
                           placeholder="Search Brand...">
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

                            <th width="60">#</th>
                            <th>Brand Code</th>
                            <th>Brand Name</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th width="150">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($brands as $brand)

                        <tr>

                            <td>{{ $brands->firstItem() + $loop->index }}</td>

                            <td>{{ $brand->brand_code }}</td>

                            <td>{{ $brand->brand_name }}</td>

                            <td>{{ $brand->company->company_name ?? '-' }}</td>

                            <td>
                                @if($brand->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>

                            <td>

                                <a href="{{ route('brands.edit',$brand) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('brands.destroy',$brand) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Delete this brand?')">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center">
                                No brands found.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            {{ $brands->withQueryString()->links() }}

        </div>

    </div>

</div>

@endsection