@extends('layouts.app')

@section('title','Sales Invoice')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                <i class="fas fa-file-invoice-dollar"></i>
                Sales Invoice
            </h5>

            <a href="{{ route('sales.create') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus"></i>
                New Sales Invoice
            </a>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-light">

                        <tr>

                            <th width="60">#</th>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th width="180">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($sales as $sale)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $sale->invoice_no }}</td>

                            <td>{{ date('d-m-Y', strtotime($sale->invoice_date)) }}</td>

                            <td>{{ $sale->customer->customer_name ?? '' }}</td>

                            <td class="text-end">
                                {{ number_format($sale->grand_total,2) }}
                            </td>

                            <td class="text-end">
                                {{ number_format($sale->paid_amount,2) }}
                            </td>

                            <td class="text-end">
                                {{ number_format($sale->balance_amount,2) }}
                            </td>

                            <td>

                                <a href="{{ route('sales.show',$sale->id) }}"
                                   class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('sales.edit',$sale->id) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="{{ route('sales.print',$sale->id) }}"
                                   class="btn btn-secondary btn-sm"
                                   target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>

                                <form action="{{ route('sales.destroy',$sale->id) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Delete this invoice?')">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="text-center">

                                No Sales Invoice Found

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">

                {{ $sales->links() }}

            </div>

        </div>

    </div>

</div>

@endsection 