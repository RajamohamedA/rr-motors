@extends('layouts.app')

@section('title','Stock Ledger')

@section('content')

<div class="card shadow-sm">

    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-warehouse"></i>
            Stock Ledger
        </h5>
    </div>

    <div class="card-body">

        <form method="GET" action="{{ route('stock-ledgers.index') }}">

            <div class="row">

                <div class="col-md-2 mb-3">
                    <label>Company</label>
                    <select name="company_id" class="form-select">
                        <option value="">All</option>

                        @foreach($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ request('company_id')==$company->id ? 'selected':'' }}>
                            {{ $company->company_name }}
                        </option>
                        @endforeach

                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label>Store</label>

                    <select name="store_id" class="form-select">

                        <option value="">All</option>

                        @foreach($stores as $store)
                        <option value="{{ $store->id }}"
                            {{ request('store_id')==$store->id ? 'selected':'' }}>
                            {{ $store->store_name }}
                        </option>
                        @endforeach

                    </select>

                </div>

                <div class="col-md-3 mb-3">

                    <label>Product</label>

                    <select name="product_id" class="form-select">

                        <option value="">All</option>

                        @foreach($products as $product)
                        <option value="{{ $product->id }}"
                            {{ request('product_id')==$product->id ? 'selected':'' }}>
                            {{ $product->product_name }}
                        </option>
                        @endforeach

                    </select>

                </div>

                <div class="col-md-2 mb-3">

                    <label>From</label>

                    <input type="date"
                        name="from_date"
                        value="{{ request('from_date') }}"
                        class="form-control">

                </div>

                <div class="col-md-2 mb-3">

                    <label>To</label>

                    <input type="date"
                        name="to_date"
                        value="{{ request('to_date') }}"
                        class="form-control">

                </div>

                <div class="col-md-1 d-flex align-items-end">

                    <button class="btn btn-primary w-100">

                        <i class="fas fa-search"></i>

                    </button>

                </div>

            </div>

        </form>

        <div class="table-responsive mt-3">

            <table class="table table-bordered table-striped">

                <thead class="table-dark">

                    <tr>

                        <th>Date</th>
                        <th>Reference</th>
                        <th>Transaction</th>
                        <th>Product</th>
                        <th class="text-end">Qty In</th>
                        <th class="text-end">Qty Out</th>
                        <th class="text-end">Balance</th>
                        <th>Remarks</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($ledgers as $ledger)

                    <tr>

                        <td>{{ $ledger->transaction_date }}</td>

                        <td>{{ $ledger->reference_no }}</td>

                        <td>{{ $ledger->transaction_type }}</td>

                        <td>
                            @can('stock-ledger.show')
                            <a href="{{ route('stock-ledgers.show',$ledger->product_id) }}">

                                {{ $ledger->product->product_name }}

                            </a>
                            @else
                                {{ $ledger->product->product_name }}
                            @endif
                        </td>

                        <td class="text-end text-success">

                            {{ number_format($ledger->qty_in,2) }}

                        </td>

                        <td class="text-end text-danger">

                            {{ number_format($ledger->qty_out,2) }}

                        </td>

                        <td class="text-end fw-bold">

                            {{ number_format($ledger->balance_qty,2) }}

                        </td>

                        <td>{{ $ledger->remarks }}</td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="8" class="text-center">

                            No Stock Ledger Found

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">

            {{ $ledgers->withQueryString()->links() }}

        </div>

    </div>

</div>

@endsection