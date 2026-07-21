@extends('layouts.app')

@section('title','Customer Details')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white d-flex justify-content-between">

            <h5 class="mb-0">
                <i class="fas fa-user"></i>
                Customer Details
            </h5>

            <a href="{{ route('customers.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>
                    <th width="250">Customer Code</th>
                    <td>{{ $customer->customer_code }}</td>
                </tr>

                <tr>
                    <th>Customer Name</th>
                    <td>{{ $customer->customer_name }}</td>
                </tr>

                <tr>
                    <th>Contact Person</th>
                    <td>{{ $customer->contact_person }}</td>
                </tr>

                <tr>
                    <th>Mobile</th>
                    <td>{{ $customer->mobile }}</td>
                </tr>

                <tr>
                    <th>Email</th>
                    <td>{{ $customer->email }}</td>
                </tr>

                <tr>
                    <th>Billing Address</th>
                    <td>{{ $customer->billing_address }}</td>
                </tr>

                <tr>
                    <th>City</th>
                    <td>{{ $customer->city }}</td>
                </tr>

                <tr>
                    <th>GST Number</th>
                    <td>{{ $customer->gst_number }}</td>
                </tr>

                <tr>
                    <th>Credit Limit</th>
                    <td>{{ number_format($customer->credit_limit,2) }}</td>
                </tr>

                <tr>
                    <th>Opening Balance</th>
                    <td>
                        {{ $customer->balance_type }}
                        {{ number_format($customer->opening_balance,2) }}
                    </td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        @if($customer->status)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                 </tr>

            </table>

        </div>

    </div>

</div>

@endsection