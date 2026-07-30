@extends('layouts.app')

@section('title','Units')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">

    {{ session('success') }}

    <button class="btn-close" data-bs-dismiss="alert"></button>

</div>
@endif

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3 class="fw-bold">
            Units
        </h3>

        @can('unit.create')
        <a href="{{ route('units.create') }}"
            class="btn btn-primary">

            <i class="fas fa-plus"></i>

            Add Unit

        </a>
        @endcan

    </div>

    @if(session('success'))

    <div class="alert alert-success">

        {{ session('success') }}

    </div>

    @endif

    <div class="card shadow-sm">

        <div class="card-body">

            <form method="GET" class="row mb-3">

                <div class="col-md-4">

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search Unit">

                </div>

                <div class="col-md-2">

                    <button class="btn btn-primary">

                        Search

                    </button>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Unit Name</th>

                            <th>Short Name</th>

                            <th>Status</th>

                            <th width="180">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($units as $unit)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $unit->unit_name }}</td>

                            <td>{{ $unit->short_name }}</td>

                            <td>

                                @if($unit->status)

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

                                @can('unit.edit')
                                <a href="{{ route('units.edit',$unit) }}"
                                    class="btn btn-warning btn-sm">

                                    <i class="fas fa-edit"></i>

                                </a>
                                @endcan

                                @can('unit.delete')

                                <form
                                    action="{{ route('units.destroy',$unit) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        onclick="return confirm('Delete this unit?')"
                                        class="btn btn-danger btn-sm">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>
                                @endcan

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="5"
                                class="text-center">

                                No Records Found

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{ $units->links() }}

        </div>

    </div>

</div>

@endsection