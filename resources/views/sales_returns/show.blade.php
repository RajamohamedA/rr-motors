@extends('layouts.app')

@section('title', 'Sales Return Details')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                <i class="fas fa-undo"></i>
                Sales Return Details
            </h4>

            <div>

                <a href="{{ route('sales-returns.index') }}"
                class="btn btn-secondary btn-sm">

                    <i class="fas fa-arrow-left"></i>
                    Back

                </a>

                <a href="{{ route('sales-returns.print', $return->id) }}"
                target="_blank"
                class="btn btn-primary btn-sm">

                    <i class="fas fa-print"></i>
                    Print

                </a>

            </div>

        </div>

        <div class="card-body">

            <div class="row mb-3">

                <div class="col-md-3">
                    <strong>Return No</strong><br>
                    {{ $return->return_no }}
                </div>

                <div class="col-md-3">
                    <strong>Date</strong><br>
                    {{ $return->return_date }}
                </div>

                <div class="col-md-3">
                    <strong>Invoice</strong><br>
                    {{ $return->sale->invoice_no }}
                </div>

                <div class="col-md-3">
                    <strong>Customer</strong><br>
                    {{ $return->customer->customer_name }}
                </div>

            </div>

            <table class="table table-bordered">

                <thead class="table-dark">

                <tr>
                    <th>#</th>
                    <th>Product Code</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>GST%</th>
                    <th>GST Amt</th>
                    <th>Total</th>
                </tr>

                </thead>

                <tbody>

                @foreach($return->items as $item)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $item->product->product_code }}</td>

                    <td>{{ $item->product->product_name }}</td>

                    <td>{{ number_format($item->quantity,2) }}</td>

                    <td>{{ number_format($item->sale_price,2) }}</td>

                    <td>{{ number_format($item->gst_percent,2) }}</td>

                    <td>{{ number_format($item->gst_amount,2) }}</td>

                    <td>{{ number_format($item->line_total,2) }}</td>

                </tr>

                @endforeach

                </tbody>

            </table>

            <div class="text-end">

                <h4>
                    Grand Total :
                    ₹ {{ number_format($return->total_amount,2) }}
                </h4>

            </div>

        </div>

    </div>

</div>

@endsection