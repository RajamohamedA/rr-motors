@extends('layouts.app')

@section('title','Sales Invoice List')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header bg-success text-white">

            <div class="d-flex justify-content-between align-items-center">

                <h4 class="mb-0">

                    <i class="fas fa-file-invoice-dollar"></i>

                    Sales Invoice List

                </h4>

                @can('sales.create')
                <a href="{{ route('sales.create') }}"
                    class="btn btn-light">

                    <i class="fas fa-plus"></i>

                    New Sales Invoice

                </a>
                @endcan

            </div>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-striped align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th width="5%">#</th>

                            <th width="15%">Invoice No</th>

                            <th width="12%">Date</th>

                            <th>Customer</th>

                            <th class="text-end" width="12%">

                                Grand Total

                            </th>

                            <th class="text-end" width="12%">

                                Paid

                            </th>

                            <th class="text-end" width="12%">

                                Balance

                            </th>

                            <th class="text-center" width="10%">

                                Status

                            </th>

                            <th class="text-center" width="20%">

                                Action

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($sales as $sale)

                        <tr>

                            <td>

                                {{ $loop->iteration + ($sales->currentPage()-1) * $sales->perPage() }}

                            </td>

                            <td>

                                <strong>

                                    {{ $sale->invoice_no }}

                                </strong>

                            </td>

                            <td>

                                {{ date('d-m-Y',strtotime($sale->invoice_date)) }}

                            </td>

                            <td>

                                {{ $sale->customer->customer_name ?? '-' }}

                            </td>

                            <td class="text-end fw-bold">

                                {{ number_format($sale->grand_total,2) }}

                            </td>

                            <td class="text-end text-success fw-bold">

                                {{ number_format($sale->paid_amount,2) }}

                            </td>

                            <td class="text-end text-danger fw-bold">

                                {{ number_format($sale->balance_amount,2) }}

                            </td>

                            <td class="text-center">

                                @if($sale->status == 'Completed')
                                <span class="badge bg-success">Completed</span>
                                @elseif($sale->status == 'Pending')
                                <span class="badge bg-warning">Pending</span>
                                @else
                                <span class="badge bg-danger">Cancelled</span>
                                @endif


                            </td>

                            <td class="text-center">

                                <div class="btn-group btn-group-sm">

                                    @can('sales.show')
                                    <a href="{{ route('sales.show',$sale->id) }}"
                                        class="btn btn-info"
                                        title="View">

                                        <i class="fas fa-eye"></i>

                                    </a>
                                    @endcan

                                    @can('sales.edit')
                                    <a href="{{ route('sales.edit',$sale->id) }}"
                                        class="btn btn-warning"
                                        title="Edit">

                                        <i class="fas fa-edit"></i>

                                    </a>
                                    @endcan

                                    @can('sales.print')
                                    <a href="{{ route('sales.print',$sale->id) }}"
                                        target="_blank"
                                        class="btn btn-secondary"
                                        title="Print">

                                        <i class="fas fa-print"></i>

                                    </a>
                                    @endcan

                                    @can('sales.delete')
                                    <form action="{{ route('sales.destroy',$sale->id) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this Sales Invoice?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="btn btn-danger btn-sm"
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

                            <td colspan="9" class="text-center text-muted py-4">

                                <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i>

                                <br>

                                No Sales Invoice Found.

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="d-flex justify-content-end mt-3">

                {{ $sales->links() }}

            </div>

        </div>

    </div>

</div>

@endsection