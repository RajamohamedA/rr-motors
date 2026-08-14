@extends('layouts.app')

@section('title', 'Purchase Print')

@section('content')

<div class="container-fluid">

    <!-- Print / Close Buttons -->
    <div class="d-flex justify-content-end gap-2 mb-3 no-print">

        <button
            type="button"
            class="btn btn-primary"
            onclick="window.print()">

            <i class="fas fa-print"></i>
            Print

        </button>

        <button
            type="button"
            class="btn btn-secondary"
            onclick="window.close()">

            <i class="fas fa-times"></i>
            Close

        </button>

    </div>


    <!-- Purchase Document -->
    <div class="card">

        <!-- Header -->
        <div class="card-header text-center">

            <h3 class="mb-1">
                PURCHASE
            </h3>

            <h5 class="mb-0">
                {{ $purchase->purchase_no }}
            </h5>

        </div>


        <div class="card-body">

            <!-- Company / Purchase Information -->
            <div class="row mb-3">

                <!-- Company -->
                <div class="col-md-4">

                    <h6 class="fw-bold">
                        Company
                    </h6>

                    <div>
                        {{ $purchase->company->company_name ?? '-' }}
                    </div>

                </div>


                <!-- Store -->
                <div class="col-md-4">

                    <h6 class="fw-bold">
                        Store
                    </h6>

                    <div>
                        {{ $purchase->store->store_name ?? '-' }}
                    </div>

                </div>


                <!-- Purchase Date -->
                <div class="col-md-4">

                    <h6 class="fw-bold">
                        Purchase Date
                    </h6>

                    <div>

                        {{ $purchase->purchase_date
                            ? date(
                                'd-m-Y',
                                strtotime($purchase->purchase_date)
                            )
                            : '-'
                        }}

                    </div>

                </div>

            </div>


            <hr>


            <!-- Supplier / Invoice -->
            <div class="row mb-4">

                <!-- Supplier -->
                <div class="col-md-4">

                    <h6 class="fw-bold">
                        Supplier
                    </h6>

                    <div>
                        {{ $purchase->supplier->supplier_name ?? '-' }}
                    </div>

                    @if($purchase->supplier)

                    @if($purchase->supplier->mobile)

                    <div>
                        Mobile:
                        {{ $purchase->supplier->mobile }}
                    </div>

                    @endif

                    @if($purchase->supplier->gst_no)

                    <div>
                        GST No:
                        {{ $purchase->supplier->gst_no }}
                    </div>

                    @endif

                    @endif

                </div>


                <!-- Invoice No -->
                <div class="col-md-4">

                    <h6 class="fw-bold">
                        Invoice No
                    </h6>

                    <div>
                        {{ $purchase->invoice_no ?: '-' }}
                    </div>

                </div>


                <!-- Invoice Date -->
                <div class="col-md-4">

                    <h6 class="fw-bold">
                        Invoice Date
                    </h6>

                    <div>

                        {{ $purchase->invoice_date
                            ? date(
                                'd-m-Y',
                                strtotime($purchase->invoice_date)
                            )
                            : '-'
                        }}

                    </div>

                </div>

            </div>


            <!-- Purchase Items -->
            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th width="40">
                                #
                            </th>

                            <th>
                                Code
                            </th>

                            <th>
                                Product
                            </th>

                            <th>
                                Godown
                            </th>

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

                        @forelse($purchase->items as $item)

                        <tr>

                            <!-- # -->
                            <td>
                                {{ $loop->iteration }}
                            </td>


                            <!-- Code -->
                            <td>
                                {{ $item->product->product_code ?? '-' }}
                            </td>


                            <!-- Product -->
                            <td>

                                {{ $item->product->product_name ?? '-' }}

                            </td>


                            <!-- Godown -->
                            <td>

                                @if($item->godown)

                                {{ $item->godown->godown_name }}

                                @else

                                Shop / No Godown

                                @endif

                            </td>


                            <!-- Qty -->
                            <td class="text-end">

                                {{ number_format(
                                        $item->quantity,
                                        2
                                    ) }}

                            </td>


                            <!-- Rate -->
                            <td class="text-end">

                                {{ number_format(
                                        $item->purchase_price,
                                        2
                                    ) }}

                            </td>


                            <!-- GST % -->
                            <td class="text-end">

                                {{ number_format(
                                        $item->gst_percent,
                                        2
                                    ) }}

                            </td>


                            <!-- GST Amount -->
                            <td class="text-end">

                                {{ number_format(
                                        $item->gst_amount,
                                        2
                                    ) }}

                            </td>


                            <!-- Total -->
                            <td class="text-end">

                                {{ number_format(
                                        $item->total_amount,
                                        2
                                    ) }}

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="9"
                                class="text-center">

                                No purchase items found.

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            <!-- Totals -->
            <div class="row mt-3">

                <div class="col-md-7">

                    @if($purchase->remarks)

                    <div>

                        <strong>
                            Remarks:
                        </strong>

                        {{ $purchase->remarks }}

                    </div>

                    @endif

                </div>


                <div class="col-md-5">

                    <table class="table table-bordered">

                        <tr>

                            <th>
                                Sub Total
                            </th>

                            <td class="text-end">

                                {{ number_format(
                                    $purchase->sub_total ?? 0,
                                    2
                                ) }}

                            </td>

                        </tr>


                        <tr>

                            <th>
                                GST Total
                            </th>

                            <td class="text-end">

                                {{ number_format(
                                    $purchase->gst_total ?? 0,
                                    2
                                ) }}

                            </td>

                        </tr>


                        <tr>

                            <th>
                                Grand Total
                            </th>

                            <th class="text-end">

                                {{ number_format(
                                    $purchase->grand_total ?? 0,
                                    2
                                ) }}

                            </th>

                        </tr>

                    </table>

                </div>

            </div>


            <!-- Signature -->
            <div class="row mt-5">

                <div class="col-6">

                    <div class="text-center">

                        <br>
                        <br>

                        __________________________

                        <br>

                        Supplier Signature

                    </div>

                </div>


                <div class="col-6">

                    <div class="text-center">

                        <br>
                        <br>

                        __________________________

                        <br>

                        Authorized Signature

                    </div>

                </div>

            </div>


            <!-- Footer -->
            <div class="text-center mt-4">

                <small class="text-muted">

                    Purchase No:
                    {{ $purchase->purchase_no }}

                </small>

            </div>

        </div>

    </div>

</div>


<style>
    body {
        background: #fff !important;
    }


    @media print {

        @page {
            size: A4;
            margin: 10mm;
        }


        .no-print {
            display: none !important;
        }


        .navbar,
        .sidebar,
        .main-sidebar,
        .app-sidebar,
        footer {
            display: none !important;
        }


        .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }


        .card {
            border: none !important;
            box-shadow: none !important;
        }


        .card-header {
            background: transparent !important;
            color: #000 !important;
            border-bottom: 2px solid #000 !important;
        }


        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }


        .table th,
        .table td {
            border: 1px solid #000 !important;
            padding: 5px !important;
        }


        .table thead th {
            background: #eee !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }


        tr {
            page-break-inside: avoid;
        }

    }
</style>

@endsection