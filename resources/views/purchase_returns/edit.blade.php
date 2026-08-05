@extends('layouts.app')

@section('title','Edit Purchase Return')

@section('content')

<div class="container-fluid">

    <form action="{{ route('purchase-returns.update',$purchaseReturn->id) }}" method="POST">

        @csrf
        @method('PUT')

        <input type="hidden"
            name="purchase_id"
            value="{{ $purchaseReturn->purchase_id }}">

        <input type="hidden"
            name="supplier_id"
            value="{{ $purchaseReturn->supplier_id }}">

        <div class="card shadow-sm">

            <div class="card-header bg-warning text-dark">

                <div class="d-flex justify-content-between align-items-center">

                    <h4 class="mb-0">

                        <i class="fas fa-edit"></i>

                        Edit Purchase Return

                    </h4>

                    <div>

                        @can('purchase-return.view')
                        <a href="{{ route('purchase-returns.show',$purchaseReturn->id) }}"
                            class="btn btn-primary btn-sm">

                            <i class="fas fa-eye"></i>

                            View

                        </a>
                        @endcan

                        <a href="{{ route('purchase-returns.index') }}"
                            class="btn btn-secondary btn-sm">

                            Back

                        </a>

                    </div>

                </div>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3">

                        <label>Return No</label>

                        <input type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->return_no }}"
                            readonly>

                    </div>

                    <div class="col-md-3">

                        <label>Purchase No</label>

                        <input type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->purchase->purchase_no }}"
                            readonly>

                    </div>

                    <div class="col-md-3">

                        <label>Return Date</label>

                        <input type="date"
                            name="return_date"
                            class="form-control"
                            value="{{ old('return_date',$purchaseReturn->return_date) }}">

                    </div>

                    <div class="col-md-3">

                        <label>Remarks</label>

                        <input type="text"
                            name="remarks"
                            class="form-control"
                            value="{{ old('remarks',$purchaseReturn->remarks) }}">

                    </div>

                </div>

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
                            class="form-control"
                            value="{{ $purchaseReturn->supplier->supplier_name }}"
                            readonly>

                    </div>

                    <div class="col-md-3">

                        <label>Mobile</label>

                        <input type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->supplier->mobile }}"
                            readonly>

                    </div>

                    <div class="col-md-5">

                        <label>Address</label>

                        <input type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->supplier->billing_address }}"
                            readonly>

                    </div>

                </div>

            </div>

        </div>



        <div class="card mt-3">

            <div class="card-header">

                Returned Items

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-bordered table-striped mb-0">

                        <thead class="table-dark">

                            <tr>

                                <th>Code</th>

                                <th>Product</th>

                                <th>Purchased</th>

                                <th>Returned</th>

                                <th>Balance</th>

                                <th width="120">Return Qty</th>

                                <th class="text-end">Rate</th>

                                <th class="text-end">GST%</th>

                                <th class="text-end">GST Amt</th>

                                <th class="text-end">Total</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($purchaseReturn->items as $item)

                            <tr>

                                <td>
                                    {{ $item->product->product_code }}

                                    <input type="hidden"
                                        name="product_id[]"
                                        value="{{ $item->product_id }}">
                                </td>

                                <td>
                                    {{ $item->product->product_name }}
                                </td>

                                <td class="text-center">
                                    {{ $item->purchaseItem->quantity }}
                                </td>

                                <td class="text-center">
                                    {{ $item->purchaseItem->returned_qty }}
                                </td>

                                <td class="text-center">
                                    <strong>{{ $item->purchaseItem->balance_qty }}</strong>
                                </td>

                                <td>

                                    <input
                                        type="number"
                                        class="form-control returnQty text-end"
                                        name="qty[]"
                                        value="{{ $item->quantity }}"
                                        min="0"
                                        max="{{ $item->purchaseItem->balance_qty + $item->quantity }}"
                                        data-price="{{ $item->purchase_price }}"
                                        data-gst="{{ $item->gst_percent }}">

                                </td>

                                <td class="text-end">

                                    {{ number_format($item->purchase_price,2) }}

                                    <input type="hidden"
                                        name="price[]"
                                        value="{{ $item->purchase_price }}">

                                </td>

                                <td class="text-end">

                                    {{ number_format($item->gst_percent,2) }}

                                    <input type="hidden"
                                        name="gst[]"
                                        value="{{ $item->gst_percent }}">

                                </td>

                                <td class="text-end">

                                    <span class="gstAmount">

                                        {{ number_format($item->gst_amount,2) }}

                                    </span>

                                    <input type="hidden"
                                        name="gst_amount[]"
                                        value="{{ $item->gst_amount }}">

                                </td>

                                <td class="text-end">

                                    <span class="returnAmount">

                                        {{ number_format($item->line_total,2) }}

                                    </span>

                                    <input type="hidden"
                                        name="amount[]"
                                        value="{{ $item->line_total }}">

                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="row mt-3">

            <div class="col-md-8"></div>

            <div class="col-md-4">

                <table class="table table-bordered">

                    <tr>

                        <th width="50%">

                            Sub Total

                        </th>

                        <td>

                            <input
                                type="text"
                                id="purchase_return_sub_total"
                                class="form-control text-end"
                                value="{{ number_format($purchaseReturn->sub_total,2,'.','') }}"
                                readonly>

                        </td>

                    </tr>

                    <tr>

                        <th>

                            GST Total

                        </th>

                        <td>

                            <input
                                type="text"
                                id="purchase_return_gst_total"
                                class="form-control text-end"
                                value="{{ number_format($purchaseReturn->gst_total,2,'.','') }}"
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
                                value="{{ number_format($purchaseReturn->total_amount,2,'.','') }}"
                                readonly>

                        </td>

                        <input type="hidden"
                            name="sub_total"
                            id="purchase_return_sub_total_hidden">

                        <input type="hidden"
                            name="gst_total"
                            id="purchase_return_gst_total_hidden">

                    </tr>

                </table>

            </div>

        </div>

        <div class="text-end mt-3 mb-4">

            <button
                type="submit"
                class="btn btn-success">

                <i class="fas fa-save"></i>

                Update Purchase Return

            </button>

            @can('purchase-return.view')
            <a href="{{ route('purchase-returns.show',$purchaseReturn->id) }}"
                class="btn btn-info">

                <i class="fas fa-eye"></i>

                View

            </a>
            @endcan

            <a href="{{ route('purchase-returns.index') }}"
                class="btn btn-secondary">

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection