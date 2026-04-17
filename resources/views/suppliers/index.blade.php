@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Supplier List</h3>

        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Supplier
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">

        <div class="card-body">

            <form method="GET" action="{{ route('suppliers.index') }}" class="row mb-3">

                <div class="col-md-4">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search Supplier...">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary">
                        Search
                    </button>
                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Supplier</th>
                        <th>Company</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Status</th>
                        <th width="160">Action</th>
                    </tr>

                    </thead>

                    <tbody>

                    @forelse($suppliers as $supplier)

                        <tr>

                            <td>{{ $suppliers->firstItem() + $loop->index }}</td>

                            <td>{{ $supplier->supplier_code }}</td>

                            <td>{{ $supplier->supplier_name }}</td>

                            <td>{{ $supplier->company->company_name ?? '-' }}</td>

                            <td>{{ $supplier->phone }}</td>

                            <td>{{ $supplier->city }}</td>

                            <td>

                                @if($supplier->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif

                            </td>

                            <td>

                                <a href="{{ route('suppliers.edit',$supplier) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('suppliers.destroy',$supplier) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this supplier?')">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="text-center">
                                No Suppliers Found
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            {{ $suppliers->withQueryString()->links() }}

        </div>

    </div>

</div>

@endsection