@extends('layouts.app')

@section('title', 'Sales Return')

@section('content')

<div class="container-fluid">

<form action="{{ route('sales-returns.store') }}" method="POST">

@csrf

<input type="hidden" name="sale_id" id="sale_id">
<input type="hidden" name="customer_id" id="customer_id">
<input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">

<div class="card shadow-sm">

    <div class="card-header bg-primary text-white">

        <div class="d-flex justify-content-between">

            <h4 class="mb-0">
                <i class="fas fa-undo"></i>
                Sales Return
            </h4>

            <a href="{{ route('sales-returns.index') }}"
               class="btn btn-light btn-sm">

                <i class="fas fa-list"></i>

                Return List

            </a>

        </div>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-5">

                <label class="form-label">

                    Search Invoice

                </label>

                <input
                    type="text"
                    id="invoice_search"
                    class="form-control"
                    placeholder="Invoice No / Customer">

                <div id="invoiceResult"
                     class="list-group mt-1"
                     style="display:none;"></div>

            </div>

            <div class="col-md-3">

                <label class="form-label">

                    Return Date

                </label>

                <input
                    type="date"
                    name="return_date"
                    class="form-control"
                    value="{{ date('Y-m-d') }}">

            </div>

            <div class="col-md-4">

                <label class="form-label">

                    Remarks

                </label>

                <input
                    type="text"
                    name="remarks"
                    class="form-control">

            </div>

        </div>

        <input
            type="hidden"
            id="searchUrl"
            value="{{ route('sales-return.search') }}">

        <input
            type="hidden"
            id="invoiceUrl"
            value="{{ url('/sales-return/get-invoice') }}">

    </div>

</div>


<div class="card mt-3">

    <div class="card-header bg-secondary text-white">

        Customer Details

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-4">

                <label>Customer</label>

                <input
                    type="text"
                    id="customer_name"
                    class="form-control"
                    readonly>

            </div>

            <div class="col-md-3">

                <label>Mobile</label>

                <input
                    type="text"
                    id="customer_mobile"
                    class="form-control"
                    readonly>

            </div>

            <div class="col-md-5">

                <label>Address</label>

                <input
                    type="text"
                    id="customer_address"
                    class="form-control"
                    readonly>

            </div>

        </div>

    </div>

</div>


<div class="card mt-3">

    <div class="card-header bg-secondary text-white">

        Return Items

    </div>

    <div class="card-body p-0">

        <table class="table table-bordered table-striped mb-0">

            <thead class="table-dark">

            <tr>

                <th width="8%">Code</th>

                <th>Product</th>

                <th width="18%">Sold / Returned / Balance</th>

                <th width="12%">Return Qty</th>

                <th width="10%">Price</th>

                <th width="15%">GST</th>

                <th width="12%">Amount</th>

            </tr>

            </thead>

            <tbody id="returnItems">

            <tr>

                <td colspan="7" class="text-center text-muted">

                    Search Sales Invoice...

                </td>

            </tr>

            </tbody>

        </table>

    </div>

</div>


<div class="row mt-3">

    <div class="col-md-8"></div>

    <div class="col-md-4">

        <table class="table table-bordered">

            <tr>

                <th width="50%">

                    GST Total

                </th>

                <td>

                    <input
                        type="text"
                        id="gst_total"
                        name="gst_total"
                        class="form-control text-end"
                        value="0.00"
                        readonly>

                </td>

            </tr>

            <tr>

                <th>

                    Grand Total

                </th>

                <td>

                    <input
                        type="text"
                        id="grand_total"
                        name="grand_total"
                        class="form-control text-end fw-bold"
                        value="0.00"
                        readonly>

                </td>

            </tr>

        </table>

    </div>

</div>


<div class="text-end mb-4">

    <button
        type="submit"
        class="btn btn-success">

        <i class="fas fa-save"></i>

        Save Sales Return

    </button>

    <a href="{{ route('sales.index') }}"
       class="btn btn-secondary">

        Cancel

    </a>

</div>

</form>

</div>

@endsection