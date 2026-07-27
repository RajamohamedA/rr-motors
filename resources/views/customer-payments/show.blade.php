@extends('layouts.app')

@section('title','Customer Payment Details')

@section('content')

<div class="card shadow-sm">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            Customer Payment Details
        </h5>

    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <tr>
                <th>Receipt No</th>
                <td>{{ $payment->receipt_no }}</td>
            </tr>

            <tr>
                <th>Date</th>
                <td>{{ $payment->payment_date->format('d-m-Y') }}</td>
            </tr>

            <tr>
                <th>Customer</th>
                <td>{{ $payment->customer->customer_name }}</td>
            </tr>

            <tr>
                <th>Invoice</th>
                <td>{{ $payment->sale->invoice_no }}</td>
            </tr>

            <tr>
                <th>Amount</th>
                <td>{{ number_format($payment->amount,2) }}</td>
            </tr>

            <tr>
                <th>Payment Mode</th>
                <td>{{ $payment->payment_mode }}</td>
            </tr>

            <tr>
                <th>Reference No</th>
                <td>{{ $payment->reference_no }}</td>
            </tr>

            <tr>
                <th>Remarks</th>
                <td>{{ $payment->remarks }}</td>
            </tr>

        </table>

        <a href="{{ route('customer-payments.index') }}"
            class="btn btn-secondary">

            Back

        </a>

    </div>

</div>

@endsection