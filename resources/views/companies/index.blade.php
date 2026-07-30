@extends('layouts.app')

@section('title', 'Companies')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Companies</h3>

        @can('company.create')
        <a href="{{ route('companies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Company
        </a>
        @endcan
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}

        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow-sm">

        <div class="card-body">

            <form method="GET" action="{{ route('companies.index') }}" class="row mb-3">

                <div class="col-md-4">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search Company">
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
                            <th>Company</th>
                            <th>GST</th>
                            <th>Phone</th>
                            <th>Logo</th>
                            <th>Status</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($companies as $company)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $company->company_name }}</td>

                            <td>{{ $company->gst_number }}</td>

                            <td>{{ $company->phone }}</td>
                            <td>
                                @if($company->logo)
                                <img src="{{ asset('storage/'.$company->logo) }}"
                                    width="50"
                                    height="50"
                                    class="rounded border">
                                @else
                                <span class="text-muted">No Logo</span>
                                @endif
                            </td>

                            <td>
                                @if($company->status)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>

                            <td>
                                @can('company.edit')
                                <a href="{{ route('companies.edit', $company->id) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('company.delete')
                                <form action="{{ route('companies.destroy', $company->id) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this company?')">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </form>
                                @endcan

                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="6" class="text-center">
                                No companies found.
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{ $companies->links() }}

        </div>

    </div>

</div>

@endsection