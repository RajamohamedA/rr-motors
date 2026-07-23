@extends('layouts.app')

@section('title', 'Purchase Return')

@section('content')

<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center">

                <h4 class="mb-0">
                    <i class="fas fa-undo"></i>
                    Purchase Return List
                </h4>

                <a href="{{ route('purchase-returns.create') }}"
                    class="btn btn-light btn-sm">

                    <i class="fas fa-plus"></i>

                    New Return

                </a>

            </div>

        </div>

        <div class="card-body">

            <form method="GET">

                <div class="row mb-3">

                    <div class="col-md-4">

                        <input type="text"
                            name="search"
                            class="form-control"
                            placeholder="Return No / Supplier"
                            value="{{ request('search') }}">

                    </div>

                    <div class="col-md-2">

                        <button class="btn btn-primary">

                            <i class="fas fa-search"></i>

                            Search

                        </button>

                    </div>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-striped align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th width="5%">#</th>

                            <th>Return No</th>

                            <th>Purchase No</th>

                            <th>Supplier</th>

                            <th>Date</th>

                            <th class="text-end">Amount</th>

                            <th width="18%" class="text-center">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($returns as $return)

                        <tr>

                            <td>{{ $loop->iteration + ($returns->firstItem() - 1) }}</td>

                            <td>{{ $return->return_no }}</td>

                            <td>{{ $return->purchase->purchase_no ?? '-' }}</td>

                            <td>{{ $return->supplier->supplier_name ?? '-' }}</td>

                            <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</td>

                            <td class="text-end">
                                ₹ {{ number_format($return->total_amount,2) }}
                            </td>

                            <td class="text-center">

                                <a href="{{ route('purchase-returns.show',$return->id) }}"
                                    class="btn btn-info btn-sm">

                                    <i class="fas fa-eye"></i>

                                </a>

                                <a href="{{ route('purchase-returns.edit',$return->id) }}"
                                    class="btn btn-warning btn-sm">

                                    <i class="fas fa-edit"></i>

                                </a>

                                <a href="{{ route('purchase-returns.print',$return->id) }}"
                                    target="_blank"
                                    class="btn btn-primary btn-sm">

                                    <i class="fas fa-print"></i>

                                </a>

                                <form action="{{ route('purchase-returns.destroy',$return->id) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this Purchase Return?')">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="7" class="text-center text-muted">

                                No Purchase Returns Found.

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">

                {{ $returns->links() }}

            </div>

        </div>

    </div>

</div>

@endsection