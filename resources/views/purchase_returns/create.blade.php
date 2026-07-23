@extends('layouts.app')

@section('title','Purchase Return')

@section('content')

<div class="container-fluid">

    <form action="{{ route('purchase-returns.store') }}" method="POST">

        @csrf

        <input type="hidden" name="purchase_id" id="purchase_id">
        <input type="hidden" name="supplier_id" id="supplier_id">

        <div class="card shadow-sm">

            <div class="card-header bg-danger text-white">

                <div class="d-flex justify-content-between align-items-center">

                    <h4 class="mb-0">

                        <i class="fas fa-undo"></i>

                        Purchase Return

                    </h4>

                    <a href="{{ route('purchase-returns.index') }}"
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

                            Search Purchase

                        </label>

                        <input type="text"
                            id="purchase_search"
                            class="form-control"
                            placeholder="Purchase No / Supplier">

                        <div id="purchaseResult"
                            class="list-group mt-1"
                            style="display:none"></div>

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">

                            Return Date

                        </label>

                        <input type="date"
                            name="return_date"
                            class="form-control"
                            value="{{ date('Y-m-d') }}">

                    </div>

                    <div class="col-md-4">

                        <label class="form-label">

                            Remarks

                        </label>

                        <input type="text"
                            name="remarks"
                            class="form-control">

                    </div>

                </div>

                <input type="hidden"
                    id="searchUrl"
                    value="{{ route('purchase-return.search') }}">

                <input type="hidden"
                    id="purchaseUrl"
                    value="{{ url('purchase-return/get-invoice') }}">

            </div>

        </div>

        <div class="card mt-3">

            <div class="card-header">

                Supplier Details

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">

                        <label>Supplier</label>

                        <input type="text"
                            id="supplier_name"
                            class="form-control"
                            readonly>

                    </div>

                    <div class="col-md-3">

                        <label>Mobile</label>

                        <input type="text"
                            id="supplier_mobile"
                            class="form-control"
                            readonly>

                    </div>

                    <div class="col-md-5">

                        <label>Address</label>

                        <input type="text"
                            id="supplier_address"
                            class="form-control"
                            readonly>

                    </div>

                </div>

            </div>

        </div>

        <div class="card mt-3">

            <div class="card-header">

                Return Items

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-bordered table-striped mb-0">

                        <thead class="table-dark">

                            <tr>

                                <th width="8%">Code</th>

                                <th>Product</th>

                                <th width="8%">Purchased</th>

                                <th width="8%">Returned</th>

                                <th width="8%">Balance</th>

                                <th width="10%">Return Qty</th>

                                <th width="10%" class="text-end">Rate</th>

                                <th width="8%" class="text-end">GST %</th>

                                <th width="10%" class="text-end">GST Amt</th>

                                <th width="12%" class="text-end">Total</th>

                            </tr>

                        </thead>

                        <tbody id="returnItems">

                            <tr>

                                <td colspan="10"
                                    class="text-center text-muted">

                                    Search Purchase Invoice...

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="row mt-3">

            <div class="col-md-8">

            </div>

            <div class="col-md-4">

                <table class="table table-bordered">

                    <tr>

                        <th width="50%">

                            Sub Total

                        </th>

                        <td>

                            <input type="text"
                                id="purchase_return_sub_total"
                                class="form-control text-end"
                                value="0.00"
                                readonly>

                        </td>

                    </tr>

                    <tr>

                        <th>

                            GST Total

                        </th>

                        <td>

                            <input type="text"
                                id="purchase_return_gst_total"
                                class="form-control text-end"
                                value="0.00"
                                readonly>

                        </td>

                    </tr>

                    <tr class="table-primary">

                        <th>

                            Grand Total

                        </th>

                        <td>

                            <input
                                type="text"
                                id="purchase_return_grand_total"
                                name="grand_total"
                                class="form-control text-end fw-bold"
                                value="0.00"
                                readonly>

                        </td>

                    </tr>

                </table>

            </div>

        </div>

        <div class="text-end mt-3 mb-4">

            <button type="submit"
                class="btn btn-success">

                <i class="fas fa-save"></i>

                Save Purchase Return

            </button>

            <a href="{{ route('purchases.index') }}"
                class="btn btn-secondary">

                <i class="fas fa-times"></i>

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection