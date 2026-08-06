@extends('layouts.app')

@section('title','Sales Report')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>
            <i class="fas fa-chart-line text-success"></i>
            Sales Report
        </h3>

        <div>
            @can('sales-report.print')
            <a href="{{ route('reports.sales.print', request()->all()) }}"
                target="_blank"
                class="btn btn-primary">
                <i class="fas fa-print"></i> Print
            </a>
            @endcan

            @can('sales-report.pdf')
            <a href="{{ route('reports.sales.pdf', request()->all()) }}"
                target="_blank"
                class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            @endcan

            @can('sales-report.excel')
            <a href="{{ route('reports.sales.csv', request()->query()) }}"
                class="btn btn-success">

                <i class="fas fa-file-csv"></i>

                Export CSV

            </a>
            @endcan
        </div>

    </div>

    <form method="GET" action="{{ route('reports.sales') }}">

        <div class="card mb-3">

            <div class="card-header bg-primary text-white">
                Filters
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-2">
                        <label>From Date</label>
                        <input type="date"
                            name="from_date"
                            value="{{ request('from_date') }}"
                            class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label>To Date</label>
                        <input type="date"
                            name="to_date"
                            value="{{ request('to_date') }}"
                            class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label>Invoice No</label>
                        <input type="text"
                            name="invoice_no"
                            value="{{ request('invoice_no') }}"
                            class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label>Customer</label>

                        <select name="customer_id" class="form-select">

                            <option value="">All</option>

                            @foreach($customers as $customer)

                            <option value="{{ $customer->id }}"
                                {{ request('customer_id')==$customer->id?'selected':'' }}>

                                {{ $customer->customer_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-2">
                        <label>Company</label>

                        <select name="company_id" class="form-select">

                            <option value="">All</option>

                            @foreach($companies as $company)

                            <option value="{{ $company->id }}"
                                {{ request('company_id')==$company->id?'selected':'' }}>

                                {{ $company->company_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-2">
                        <label>Store</label>

                        <select name="store_id" class="form-select">

                            <option value="">All</option>

                            @foreach($stores as $store)

                            <option value="{{ $store->id }}"
                                {{ request('store_id')==$store->id?'selected':'' }}>

                                {{ $store->store_name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="mt-3 text-end">

                    <button class="btn btn-success">

                        <i class="fas fa-search"></i>

                        Search

                    </button>

                    <a href="{{ route('reports.sales') }}"
                        class="btn btn-secondary">

                        Reset

                    </a>

                </div>

            </div>

        </div>

    </form>

    <div class="row mb-3">

        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Sales</h6>
                    <h4>{{ number_format($summary['grand'],2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Sub Total</h6>
                    <h4>{{ number_format($summary['subtotal'],2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card bg-warning">
                <div class="card-body">
                    <h6>GST</h6>
                    <h4>{{ number_format($summary['gst'],2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Discount</h6>
                    <h4>{{ number_format($summary['discount'],2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h6>Paid</h6>
                    <h4>{{ number_format($summary['paid'],2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Balance</h6>
                    <h4>{{ number_format($summary['balance'],2) }}</h4>
                </div>
            </div>
        </div>

    </div>

    <div class="card">

        <div class="card-header bg-secondary text-white">
            Sales List
        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">

                    <tr>

                        <th>Date</th>

                        <th>Invoice</th>

                        <th>Customer</th>

                        <th>Company</th>

                        <th>Store</th>

                        <th class="text-end">Grand Total</th>

                        <th class="text-end">Paid</th>

                        <th class="text-end">Balance</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($sales as $sale)

                    <tr>

                        <td>{{ date('d-m-Y',strtotime($sale->invoice_date)) }}</td>

                        <td>{{ $sale->invoice_no }}</td>

                        <td>{{ $sale->customer->customer_name }}</td>

                        <td>{{ $sale->company->company_name }}</td>

                        <td>{{ $sale->store->store_name }}</td>

                        <td class="text-end">{{ number_format($sale->grand_total,2) }}</td>

                        <td class="text-end">{{ number_format($sale->paid_amount,2) }}</td>

                        <td class="text-end">{{ number_format($sale->balance_amount,2) }}</td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="8" class="text-center">

                            No Records Found

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer">

            {{ $sales->links() }}

        </div>

    </div>

</div>

@endsection