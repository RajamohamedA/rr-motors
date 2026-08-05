@extends('layouts.app')

@section('title','Purchase Details')

@section('content')

<div class="container-fluid">

    <!-- Header -->
    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center">

                <h4 class="mb-0">
                    <i class="fas fa-file-invoice"></i>
                    Purchase Details
                </h4>

                <div>

                    <a href="{{ route('purchases.index') }}"
                        class="btn btn-secondary btn-sm">

                        <i class="fas fa-arrow-left"></i>

                        Back

                    </a>

                    @can('purchase.view')
                    <a href="{{ route('purchases.print',$purchase->id) }}"
                        target="_blank"
                        class="btn btn-success btn-sm">

                        <i class="fas fa-print"></i>

                        Print

                    </a>
                    @endcan

                </div>

            </div>

        </div>

        <div class="card-body">

            <div class="row">

                <!-- Purchase Info -->

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>
                            <th width="45%">Purchase No</th>
                            <td>{{ $purchase->purchase_no }}</td>
                        </tr>

                        <tr>
                            <th>Date</th>
                            <td>{{ date('d-m-Y',strtotime($purchase->purchase_date)) }}</td>
                        </tr>

                        <tr>
                            <th>Invoice No</th>
                            <td>{{ $purchase->invoice_no }}</td>
                        </tr>

                        <tr>
                            <th>Store</th>
                            <td>{{ $purchase->store->store_name ?? '-' }}</td>
                        </tr>

                    </table>

                </div>

                <!-- Supplier -->

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>
                            <th width="45%">Supplier</th>
                            <td>{{ $purchase->supplier->supplier_name }}</td>
                        </tr>

                        <tr>
                            <th>Mobile</th>
                            <td>{{ $purchase->supplier->mobile }}</td>
                        </tr>

                        <tr>
                            <th>GST No</th>
                            <td>{{ $purchase->supplier->gst_no }}</td>
                        </tr>

                        <tr>
                            <th>Address</th>
                            <td>{{ $purchase->supplier->address }}</td>
                        </tr>

                    </table>

                </div>

                <!-- Company -->

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>
                            <th width="45%">Company</th>
                            <td>{{ $purchase->company->company_name ?? '' }}</td>
                        </tr>

                        <tr>
                            <th>Created By</th>
                            <td>{{ $purchase->created_by ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>

                                <span class="badge bg-success">

                                    Completed

                                </span>

                            </td>
                        </tr>

                        <tr>
                            <th>Remarks</th>
                            <td>{{ $purchase->remarks }}</td>
                        </tr>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <!-- Purchase Items -->

    <div class="card mt-3 shadow-sm">

        <div class="card-header">

            <strong>Purchase Items</strong>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-striped mb-0">

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Code</th>

                            <th>Product</th>

                            <th class="text-end">Qty</th>

                            <th class="text-end">Rate</th>

                            <th class="text-end">GST %</th>

                            <th class="text-end">GST Amt</th>

                            <th class="text-end">Total</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($purchase->items as $item)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $item->product->product_code }}</td>

                            <td>{{ $item->product->product_name }}</td>

                            <td class="text-end">{{ number_format($item->quantity,2) }}</td>

                            <td class="text-end">{{ number_format($item->purchase_price,2) }}</td>

                            <td class="text-end">{{ number_format($item->gst_percent,2) }}</td>

                            <td class="text-end">{{ number_format($item->gst_amount,2) }}</td>

                            <td class="text-end">{{ number_format($item->total_amount,2) }}</td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- Totals -->

    <div class="row mt-3">

        <div class="col-md-8"></div>

        <div class="col-md-4">

            <table class="table table-bordered">

                <tr>

                    <th>Sub Total</th>

                    <td class="text-end">

                        {{ number_format($purchase->sub_total,2) }}

                    </td>

                </tr>

                <tr>

                    <th>GST Total</th>

                    <td class="text-end">

                        {{ number_format($purchase->gst_total,2) }}

                    </td>

                </tr>

                <tr class="table-primary">

                    <th>Grand Total</th>

                    <th class="text-end">

                        {{ number_format($purchase->grand_total,2) }}

                    </th>

                </tr>

            </table>

        </div>

    </div>

</div>

@endsection