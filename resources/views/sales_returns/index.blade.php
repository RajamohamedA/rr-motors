@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header">

            <div class="d-flex justify-content-between">

                <h4>Sales Return</h4>

                <a href="{{ route('sales-returns.create') }}"
                   class="btn btn-primary">

                    <i class="fas fa-plus"></i>

                    New Return

                </a>

            </div>

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <thead>

                <tr>

                    <th>Return No</th>

                    <th>Date</th>

                    <th>Invoice</th>

                    <th>Customer</th>

                    <th>Total</th>

                    <th>Action</th>

                </tr>

                </thead>

                <tbody>

                @foreach($returns as $return)

                    <tr>

                        <td>{{ $return->return_no }}</td>

                        <td>{{ $return->return_date }}</td>

                        <td>{{ $return->sale->invoice_no }}</td>

                        <td>{{ $return->customer->customer_name }}</td>

                        <td>{{ number_format($return->total_amount,2) }}</td>

                        <td>

                            <a href="{{ route('sales-returns.show',$return->id) }}"
                               class="btn btn-info btn-sm">

                                View

                            </a>

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection