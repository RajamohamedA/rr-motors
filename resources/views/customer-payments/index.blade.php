@extends('layouts.app')

@section('title','Customer Payments')

@section('content')

<div class="card shadow-sm">

    <div class="card-header bg-primary text-white d-flex justify-content-between">

        <h5 class="mb-0">

            <i class="fas fa-money-bill-wave"></i>

            Customer Payments

        </h5>

        <a href="{{ route('customer-payments.create') }}"
            class="btn btn-light">

            <i class="fas fa-plus"></i>

            Receive Payment

        </a>

    </div>

    <div class="card-body">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">

            <i class="fas fa-check-circle"></i>

            {{ session('success') }}

            <button class="btn-close"
                data-bs-dismiss="alert"></button>

        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-times-circle"></i>

            {{ session('error') }}

            <button class="btn-close"
                data-bs-dismiss="alert"></button>

        </div>
        @endif

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">

                    <tr>

                        <th>#</th>

                        <th>Receipt No</th>

                        <th>Date</th>

                        <th>Invoice No</th>

                        <th>Customer</th>

                        <th>Payment Mode</th>

                        <th class="text-end">Amount</th>

                        <th width="170">Action</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($payments as $payment)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $payment->receipt_no }}</td>

                        <td>{{ $payment->payment_date->format('d-m-Y') }}</td>

                        <td>{{ $payment->sale->invoice_no }}</td>

                        <td>{{ $payment->customer->customer_name }}</td>

                        <td>

                            <span class="badge bg-info">

                                {{ $payment->payment_mode }}

                            </span>

                        </td>

                        <td class="text-end">

                            {{ number_format($payment->amount,2) }}

                        </td>

                        <td>

                            <a href="{{ route('customer-payments.show',$payment->id) }}"
                                class="btn btn-info btn-sm">

                                <i class="fas fa-eye"></i>

                            </a>

                            <a href="{{ route('customer-payments.edit',$payment->id) }}"
                                class="btn btn-warning btn-sm">

                                <i class="fas fa-edit"></i>

                            </a>

                            <a href="{{ route('customer-payments.print',$payment->id) }}"
                                target="_blank"
                                class="btn btn-success btn-sm">

                                <i class="fas fa-print"></i>

                            </a>

                            <form action="{{ route('customer-payments.destroy',$payment->id) }}"
                                method="POST"
                                class="d-inline">

                                @csrf

                                @method('DELETE')

                                <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete Payment?')">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="8"
                            class="text-center">

                            No Payments Found

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">

            {{ $payments->links() }}

        </div>

    </div>

</div>

@endsection