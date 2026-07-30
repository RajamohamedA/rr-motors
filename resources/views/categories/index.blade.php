@extends('layouts.app')

@section('title','Categories')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3>Categories</h3>

        @can('category.create')
        <a href="{{ route('categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Category
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

            <form class="row mb-3">

                <div class="col-md-4">

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search Category">

                </div>

                <div class="col-md-2">

                    <button class="btn btn-primary">

                        <i class="fas fa-search"></i>

                        Search

                    </button>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Code</th>

                            <th>Name</th>

                            <th>Status</th>

                            <th width="140">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($categories as $category)

                        <tr>

                            <td>{{ $categories->firstItem()+$loop->index }}</td>

                            <td>{{ $category->code }}</td>

                            <td>{{ $category->name }}</td>

                            <td>

                                @if($category->status)

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

                                @can('category.edit')
                                <a
                                    href="{{ route('categories.edit',$category) }}"
                                    class="btn btn-warning btn-sm">

                                    <i class="fas fa-edit"></i>

                                </a>
                                @endcan

                                @can('category.delete')
                                <form
                                    action="{{ route('categories.destroy',$category) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        onclick="return confirm('Delete Category?')"
                                        class="btn btn-danger btn-sm">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>
                                @endcan

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="5" class="text-center">

                                No Categories Found

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{ $categories->withQueryString()->links() }}

        </div>

    </div>

</div>

@endsection