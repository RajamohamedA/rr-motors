@extends('layouts.app')

@section('title', 'Create Purchase')

@section('content')

<div class="container-fluid mt-4">

    <form action="{{ route('purchases.store') }}" method="POST">

        @csrf

        <!-- Purchase Header -->
        <div class="card shadow-sm mb-3">

            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart"></i>
                    Create Purchase
                </h5>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Purchase No</label>
                        <input type="text"
                               name="purchase_no"
                               class="form-control"
                               value="{{ $purchaseNo }}"
                               readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Purchase Date</label>
                        <input type="date"
                               name="purchase_date"
                               class="form-control"
                               value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Company</label>

                        <select name="company_id"
                                id="company_id"
                                class="form-select">

                            <option value="">Select Company</option>

                            @foreach($companies as $company)

                                <option value="{{ $company->id }}">
                                    {{ $company->company_name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label">Store</label>

                        <select name="store_id"
                                id="store_id"
                                class="form-select">

                            <option value="">Select Store</option>

                            @foreach($stores as $store)

                                <option value="{{ $store->id }}">
                                    {{ $store->store_name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">Supplier</label>

                        <select name="supplier_id"
                                class="form-select">

                            <option value="">Select Supplier</option>

                            @foreach($suppliers as $supplier)

                                <option value="{{ $supplier->id }}">
                                    {{ $supplier->supplier_name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">Invoice No</label>

                        <input type="text"
                               name="invoice_no"
                               class="form-control">

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">Invoice Date</label>

                        <input type="date"
                               name="invoice_date"
                               class="form-control">

                    </div>

                </div>

            </div>

        </div>

        <!-- Product Search -->

        <div class="card shadow-sm mb-3">

            <div class="card-body">

                <label class="form-label">
                    Search Product
                </label>

                <input type="text"
                       id="product_search"
                       class="form-control form-control-lg"
                       placeholder="Search by Product Code / Barcode / Product Name">

                <div id="searchResult"
                    class="list-group mt-2"
                    style="display:none;max-height:300px;overflow-y:auto;">
                </div>

            </div>

        </div>

        <!-- Purchase Items -->

        <div class="card shadow-sm">

            <div class="card-header">
                Purchase Items
            </div>

            <div class="card-body">

                <table class="table table-bordered"
                       id="purchaseTable">

                    <thead>

                    <tr>

                        <th>Code</th>

                        <th>Product</th>

                        <th width="100">Qty</th>

                        <th width="120">Price</th>

                        <th width="90">GST%</th>

                        <th width="140">Amount</th>

                        <th width="70">Action</th>

                    </tr>

                    </thead>

                    <tbody>

                    </tbody>

                    <tfoot>

                    <tr>

                        <td colspan="5"
                            class="text-end">

                            Grand Total

                        </td>

                        <td>

                            <input type="text"
                                   id="grand_total"
                                   name="grand_total"
                                   class="form-control"
                                   readonly
                                   value="0.00">

                        </td>

                        <td></td>

                    </tr>

                    </tfoot>

                </table>

            </div>

        </div>

        <!-- Remarks -->

        <div class="card shadow-sm mt-3">

            <div class="card-body">

                <label class="form-label">

                    Remarks

                </label>

                <textarea
                    name="remarks"
                    rows="3"
                    class="form-control"></textarea>

            </div>

        </div>

        <!-- get url -->
        <input type="hidden" id="productSearchUrl"
       value="{{ route('products.search') }}">

        <!-- Save Button -->

        <div class="text-end mt-3">

            <button class="btn btn-success btn-lg">

                <i class="fas fa-save"></i>

                Save Purchase

            </button>

        </div>

    </form>

</div>


@endsection