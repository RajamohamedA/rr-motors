@extends('layouts.app')

@section('title', 'Purchase List')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <!-- Card Header -->
        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center">

                <h4 class="mb-0">
                    <i class="fas fa-shopping-cart"></i>
                    Purchase List
                </h4>

                @can('purchase.create')
                <a href="{{ route('purchases.create') }}"
                    class="btn btn-light">

                    <i class="fas fa-plus"></i>

                    New Purchase

                </a>
                @endcan

            </div>

        </div>

        <!-- Card Body -->
        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-striped align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th width="5%">#</th>

                            <th width="15%">Purchase No</th>

                            <th width="12%">Date</th>

                            <th>Supplier</th>

                            <th width="15%" class="text-end">

                                Grand Total

                            </th>

                            <th width="10%" class="text-center">

                                Status

                            </th>

                            <th width="20%" class="text-center">

                                Action

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($purchases as $purchase)

                        <tr>

                            <td>

                                {{ $loop->iteration + ($purchases->currentPage()-1) * $purchases->perPage() }}

                            </td>

                            <td>

                                <strong>

                                    {{ $purchase->purchase_no }}

                                </strong>

                            </td>

                            <td>

                                {{ date('d-m-Y',strtotime($purchase->purchase_date)) }}

                            </td>

                            <td>

                                {{ $purchase->supplier->supplier_name ?? '-' }}

                            </td>

                            <td class="text-end fw-bold">

                                {{ number_format($purchase->grand_total,2) }}

                            </td>

                            <td class="text-center">

                                @if($purchase->status)

                                <span class="badge bg-success">

                                    Active

                                </span>

                                @else

                                <span class="badge bg-danger">

                                    Inactive

                                </span>

                                @endif

                            </td>

                            <td class="text-center">

                                <div class="btn-group btn-group-sm">

                                    <!-- View -->

                                    @can('purchase.view')
                                    <a href="{{ route('purchases.show',$purchase->id) }}"
                                        class="btn btn-info"
                                        title="View">

                                        <i class="fas fa-eye"></i>

                                    </a>
                                    @endcan

                                    <!-- Edit -->
                                    @can('purchase.edit')

                                    <a href="{{ route('purchases.edit',$purchase->id) }}"
                                        class="btn btn-warning"
                                        title="Edit">

                                        <i class="fas fa-edit"></i>

                                    </a>
                                    @endcan

                                    <!-- Print -->
                                    @can('purchase.view')

                                    <a href="{{ route('purchases.print',$purchase->id) }}"
                                        target="_blank"
                                        class="btn btn-secondary"
                                        title="Print">

                                        <i class="fas fa-print"></i>

                                    </a>
                                    @endcan

                                    <!-- Delete -->
                                    @can('purchase.delete')

                                    <form action="{{ route('purchases.destroy',$purchase->id) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this purchase?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm"
                                            title="Delete">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>
                                    @endcan

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="7" class="text-center text-muted py-4">

                                <i class="fas fa-box-open fa-2x mb-2"></i>

                                <br>

                                No Purchase Records Found.

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <!-- Pagination -->

            <div class="d-flex justify-content-end mt-3">

                {{ $purchases->links() }}

            </div>

        </div>

    </div>

</div>

@endsection