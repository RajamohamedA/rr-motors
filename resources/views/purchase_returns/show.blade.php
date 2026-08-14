@extends('layouts.app')

@section('title','Purchase Return Details')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <!-- Header -->
        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center">

                <h4 class="mb-0">
                    <i class="fas fa-undo"></i>
                    Purchase Return Details
                </h4>

                <div>

                    <a href="{{ route('purchase-returns.index') }}"
                        class="btn btn-secondary btn-sm">

                        <i class="fas fa-arrow-left"></i>
                        Back

                    </a>

                    @can('purchase-return.view')
                    <a href="{{ route('purchase-returns.print',$return->id) }}"
                        target="_blank"
                        class="btn btn-light btn-sm">

                        <i class="fas fa-print"></i>
                        Print

                    </a>
                    @endcan

                    @can('purchase-return.edit')
                    <a href="{{ route('purchase-returns.edit',$return->id) }}"
                        class="btn btn-warning btn-sm">

                        <i class="fas fa-edit"></i>
                        Edit

                    </a>
                    @endcan

                </div>

            </div>

        </div>

        <div class="card-body">

            <!-- =========================================================
                 RETURN / PURCHASE INFORMATION
            ========================================================== -->

            <div class="row">

                <div class="col-md-3 mb-3">

                    <label class="fw-bold">
                        Return No
                    </label>

                    <input type="text"
                        class="form-control"
                        value="{{ $return->return_no }}"
                        readonly>

                </div>

                <div class="col-md-3 mb-3">

                    <label class="fw-bold">
                        Return Date
                    </label>

                    <input type="text"
                        class="form-control"
                        value="{{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}"
                        readonly>

                </div>

                <div class="col-md-3 mb-3">

                    <label class="fw-bold">
                        Purchase No
                    </label>

                    <input type="text"
                        class="form-control"
                        value="{{ $return->purchase->purchase_no ?? '-' }}"
                        readonly>

                </div>

                <div class="col-md-3 mb-3">

                    <label class="fw-bold">
                        Purchase Date
                    </label>

                    <input type="text"
                        class="form-control"
                        value="{{ $return->purchase && $return->purchase->purchase_date
                            ? \Carbon\Carbon::parse($return->purchase->purchase_date)->format('d-m-Y')
                            : '-' }}"
                        readonly>

                </div>

            </div>

            <hr>

            <!-- =========================================================
                 COMPANY / STORE
            ========================================================== -->

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label class="fw-bold">
                        Company
                    </label>

                    <input type="text"
                        class="form-control"
                        value="{{ $return->purchase->company->company_name ?? '-' }}"
                        readonly>

                </div>

                <div class="col-md-4 mb-3">

                    <label class="fw-bold">
                        Store
                    </label>

                    <input type="text"
                        class="form-control"
                        value="{{ $return->purchase->store->store_name ?? '-' }}"
                        readonly>

                </div>

                <div class="col-md-4 mb-3">

                    <label class="fw-bold">
                        Supplier
                    </label>

                    <input type="text"
                        class="form-control"
                        value="{{ $return->supplier->supplier_name ?? '-' }}"
                        readonly>

                </div>

            </div>

            <hr>

            <!-- =========================================================
                 SUPPLIER DETAILS
            ========================================================== -->

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label class="fw-bold">
                        Supplier
                    </label>

                    <input type="text"
                        class="form-control"
                        value="{{ $return->supplier->supplier_name ?? '-' }}"
                        readonly>

                </div>

                <div class="col-md-3 mb-3">

                    <label class="fw-bold">
                        Mobile
                    </label>

                    <input type="text"
                        class="form-control"
                        value="{{ $return->supplier->mobile ?? '-' }}"
                        readonly>

                </div>

                <div class="col-md-5 mb-3">

                    <label class="fw-bold">
                        Address
                    </label>

                    <input type="text"
                        class="form-control"
                        value="{{ $return->supplier->address ?? '-' }}"
                        readonly>

                </div>

            </div>

            <hr>

            <!-- =========================================================
                 RETURN ITEMS
            ========================================================== -->

            <h5 class="mb-3">

                <i class="fas fa-box-open"></i>

                Returned Items

            </h5>

            <div class="table-responsive">

                <table class="table table-bordered table-striped align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Code</th>

                            <th>Product</th>

                            <th>HSN</th>

                            <th>Unit</th>

                            <th>Godown</th>

                            <th class="text-end">
                                Qty
                            </th>

                            <th class="text-end">
                                Rate
                            </th>

                            <th class="text-end">
                                GST %
                            </th>

                            <th class="text-end">
                                GST Amt
                            </th>

                            <th class="text-end">
                                Total
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @php

                        $subTotal = 0;
                        $gstTotal = 0;

                        @endphp

                        @forelse($return->items as $item)

                        @php

                        $basicAmount =
                        $item->quantity *
                        $item->purchase_price;

                        $subTotal += $basicAmount;

                        $gstTotal +=
                        (float) $item->gst_amount;

                        @endphp

                        <tr>

                            <!-- # -->

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <!-- Product Code -->

                            <td>
                                {{ $item->product->product_code ?? '-' }}
                            </td>

                            <!-- Product -->

                            <td>

                                <strong>
                                    {{ $item->product->product_name ?? '-' }}
                                </strong>

                            </td>

                            <!-- HSN -->

                            <td>
                                {{ $item->product->hsn_code ?? '-' }}
                            </td>

                            <!-- Unit -->

                            <td>
                                {{ $item->product->unit->short_name ?? '-' }}
                            </td>

                            <!-- =================================================
                                     GODOWN
                                ================================================== -->

                            <td>

                                @php
                                $purchaseItem = $return->purchase
                                ->items
                                ->firstWhere('product_id', $item->product_id);
                                @endphp

                                @if($purchaseItem && $purchaseItem->godown)
                                <span class="badge bg-info text-dark">
                                    {{ $purchaseItem->godown->godown_name }}
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    Shop / No Godown
                                </span>
                                @endif

                            </td>

                            <!-- Quantity -->

                            <td class="text-end">

                                {{ number_format(
                                        $item->quantity,
                                        2
                                    ) }}

                            </td>

                            <!-- Rate -->

                            <td class="text-end">

                                ₹ {{ number_format(
                                        $item->purchase_price,
                                        2
                                    ) }}

                            </td>

                            <!-- GST -->

                            <td class="text-end">

                                {{ number_format(
                                        $item->gst_percent,
                                        2
                                    ) }}%

                            </td>

                            <!-- GST Amount -->

                            <td class="text-end">

                                ₹ {{ number_format(
                                        $item->gst_amount,
                                        2
                                    ) }}

                            </td>

                            <!-- Total -->

                            <td class="text-end fw-bold">

                                ₹ {{ number_format(
                                        $item->line_total,
                                        2
                                    ) }}

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="11"
                                class="text-center text-muted">

                                No return items found.

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <!-- =========================================================
                 REMARKS + TOTALS
            ========================================================== -->

            <div class="row mt-4">

                <!-- Remarks -->

                <div class="col-md-8">

                    <label class="fw-bold">
                        Remarks
                    </label>

                    <textarea
                        class="form-control"
                        rows="5"
                        readonly>{{ $return->remarks ?? '' }}</textarea>

                </div>

                <!-- Totals -->

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>

                            <th>
                                Sub Total
                            </th>

                            <td class="text-end">

                                ₹ {{ number_format(
                                    $subTotal,
                                    2
                                ) }}

                            </td>

                        </tr>

                        <tr>

                            <th>
                                GST Total
                            </th>

                            <td class="text-end">

                                ₹ {{ number_format(
                                    $gstTotal,
                                    2
                                ) }}

                            </td>

                        </tr>

                        <tr class="table-primary">

                            <th>
                                Grand Total
                            </th>

                            <th class="text-end">

                                ₹ {{ number_format(
                                    $return->total_amount,
                                    2
                                ) }}

                            </th>

                        </tr>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection