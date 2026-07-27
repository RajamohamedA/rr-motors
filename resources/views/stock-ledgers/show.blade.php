@extends('layouts.app')

@section('title','Product Stock Card')

@section('content')

<div class="card shadow-sm">

    <div class="card-header bg-success text-white">

        <h5>

            <i class="fas fa-box"></i>

            Product Stock Card

        </h5>

    </div>

    <div class="card-body">

        <div class="row mb-4">

            <div class="col-md-4">
                <strong>Product Code</strong><br>
                {{ $product->product_code }}
            </div>

            <div class="col-md-4">
                <strong>Product Name</strong><br>
                {{ $product->product_name }}
            </div>

            <div class="col-md-4">
                <strong>Current Stock</strong><br>

                <span class="badge bg-primary">

                    {{ number_format($product->current_stock,2) }}

                </span>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-striped">

                <thead class="table-dark">

                    <tr>

                        <th>Date</th>

                        <th>Reference</th>

                        <th>Transaction</th>

                        <th>Store</th>

                        <th class="text-end">Qty In</th>

                        <th class="text-end">Qty Out</th>

                        <th class="text-end">Balance</th>

                        <th>Remarks</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($ledgers as $ledger)

                    <tr>

                        <td>{{ $ledger->transaction_date }}</td>

                        <td>{{ $ledger->reference_no }}</td>

                        <td>{{ $ledger->transaction_type }}</td>

                        <td>{{ $ledger->store->store_name }}</td>

                        <td class="text-success text-end">

                            {{ number_format($ledger->qty_in,2) }}

                        </td>

                        <td class="text-danger text-end">

                            {{ number_format($ledger->qty_out,2) }}

                        </td>

                        <td class="fw-bold text-end">

                            {{ number_format($ledger->balance_qty,2) }}

                        </td>

                        <td>{{ $ledger->remarks }}</td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection