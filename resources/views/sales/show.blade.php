@extends('layouts.app')

@section('title', 'Sales Invoice Details')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header bg-success text-white d-flex justify-content-between">

            <h5 class="mb-0">

                <i class="fas fa-file-invoice-dollar"></i>

                Sales Invoice Details

            </h5>

            <div>

                <a href="{{ route('sales.print', $sale->id) }}"
                   target="_blank"
                   class="btn btn-light btn-sm">

                    <i class="fas fa-print"></i>

                    Print

                </a>

                <a href="{{ route('sales.edit', $sale->id) }}"
                   class="btn btn-warning btn-sm">

                    <i class="fas fa-edit"></i>

                    Edit

                </a>

                <a href="{{ route('sales.index') }}"
                   class="btn btn-secondary btn-sm">

                    Back

                </a>

            </div>

        </div>

        <div class="card-body">

            <div class="row mb-4">

                <div class="col-md-3">

                    <strong>Invoice No</strong>

                    <p>{{ $sale->invoice_no }}</p>

                </div>

                <div class="col-md-3">

                    <strong>Invoice Date</strong>

                    <p>{{ date('d-m-Y', strtotime($sale->invoice_date)) }}</p>

                </div>

                <div class="col-md-3">

                    <strong>Company</strong>

                    <p>{{ $sale->company->company_name ?? '' }}</p>

                </div>

                <div class="col-md-3">

                    <strong>Store</strong>

                    <p>{{ $sale->store->store_name ?? '' }}</p>

                </div>

            </div>

            <div class="row mb-4">

                <div class="col-md-6">

                    <strong>Customer</strong>

                    <p>{{ $sale->customer->customer_name ?? '' }}</p>

                </div>

                <div class="col-md-6">

                    <strong>Remarks</strong>

                    <p>{{ $sale->remarks }}</p>

                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead class="table-light">

                        <tr>

                            <th>#</th>

                            <th>Product Code</th>

                            <th>Product Name</th>

                            <th class="text-end">Qty</th>

                            <th class="text-end">Price</th>

                            <th class="text-end">GST %</th>

                            <th class="text-end">Amount</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($sale->items as $item)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $item->product->product_code }}</td>

                            <td>{{ $item->product->product_name }}</td>

                            <td class="text-end">{{ $item->quantity }}</td>

                            <td class="text-end">{{ number_format($item->sale_price,2) }}</td>

                            <td class="text-end">{{ number_format($item->gst_percent,2) }}</td>

                            <td class="text-end">{{ number_format($item->line_total,2) }}</td>

                        </tr>

                        @endforeach

                    </tbody>

                    <tfoot>

                        <tr>

                            <th colspan="6" class="text-end">

                                Grand Total

                            </th>

                            <th class="text-end">

                                {{ number_format($sale->grand_total,2) }}

                            </th>

                        </tr>

                        <tr>

                            <th colspan="6" class="text-end">

                                Paid Amount

                            </th>

                            <th class="text-end">

                                {{ number_format($sale->paid_amount,2) }}

                            </th>

                        </tr>

                        <tr>

                            <th colspan="6" class="text-end">

                                Balance

                            </th>

                            <th class="text-end">

                                {{ number_format($sale->balance_amount,2) }}

                            </th>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection