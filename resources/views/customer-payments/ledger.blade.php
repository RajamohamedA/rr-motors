@extends('layouts.app')

@section('title','Customer Ledger')

@section('content')

<div class="card shadow-sm">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">

            <i class="fas fa-book"></i>

            Customer Ledger

        </h5>

    </div>

    <div class="card-body">

        <form method="GET" action="{{ route('customer-ledger') }}">

            <div class="row">

                <div class="col-md-5">

                    <label class="form-label">Customer</label>

                    <select name="customer_id" class="form-select" required>

                        <option value="">Select Customer</option>

                        @foreach($customers as $customer)

                        <option value="{{ $customer->id }}"
                            {{ request('customer_id') == $customer->id ? 'selected' : '' }}>

                            {{ $customer->customer_name }}

                        </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-2 d-flex align-items-end">

                    <button class="btn btn-primary w-100">

                        <i class="fas fa-search"></i>

                        View

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

@if($ledger->count())

<div class="card shadow-sm mt-3">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">

            Ledger Details

        </h5>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-striped">

                <thead class="table-dark">

                    <tr>

                        <th>Date</th>

                        <th>Reference</th>

                        <th>Transaction</th>

                        <th class="text-end">Debit</th>

                        <th class="text-end">Credit</th>

                        <th class="text-end">Balance</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($ledger as $row)

                    <tr>

                        <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>

                        <td>{{ $row['reference'] }}</td>

                        <td>{{ $row['type'] }}</td>

                        <td class="text-end text-danger">

                            {{ number_format($row['debit'],2) }}

                        </td>

                        <td class="text-end text-success">

                            {{ number_format($row['credit'],2) }}

                        </td>

                        <td class="text-end fw-bold">

                            {{ number_format($row['balance'],2) }}

                        </td>

                    </tr>

                    @endforeach

                </tbody>

                <tfoot>

                    <tr>

                        <th colspan="5" class="text-end">

                            Outstanding

                        </th>

                        <th class="text-end text-danger">

                            {{ number_format($ledger->last()['balance'],2) }}

                        </th>

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

</div>

@endif

@endsection