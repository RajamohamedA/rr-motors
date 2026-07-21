@extends('layouts.app')

@section('title', 'Sales Return')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            <h4 class="mb-0">
                <i class="fas fa-undo"></i>
                New Sales Return
            </h4>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <label class="form-label">
                        Search Invoice
                    </label>

                    <input type="text"
                           id="invoice_search"
                           class="form-control"
                           placeholder="Invoice No / Customer">

                    <div id="invoiceResult"
                         class="list-group mt-1"
                         style="display:none;"></div>

                    <input type="hidden"
                        id="searchUrl"
                        value="{{ route('sales-return.search') }}">

                    <input type="hidden"
                        id="invoiceUrl"
                        value="{{ url('/sales-return/get-invoice') }}">

                </div>


                <div class="col-md-3">

                    <label class="form-label">
                        Return Date
                    </label>

                    <input type="date"
                           class="form-control"
                           value="{{ date('Y-m-d') }}">

                </div>

            </div>

            <hr>

            <div id="invoiceDetails"></div>

        </div>

    </div>

</div>

<form action="{{ route('sales-returns.store') }}" method="POST">

    @csrf

    <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">

    <input type="hidden" name="sale_id" id="sale_id">

    <input type="hidden" name="customer_id" id="customer_id">

    ...
    
</form>

@endsection