@extends('layouts.app')

@section('title','PURCHASE RETURN')

@section('content')

<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>
        Purchase Return Print
    </title>

    <link rel="stylesheet"
        href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">

    <link rel="stylesheet"
        href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

    <style>
        body {

            font-size: 13px;

            color: #000;

            background: #fff;

            margin: 20px;

        }

        .company-title {

            font-size: 28px;

            font-weight: bold;

            text-transform: uppercase;

        }

        .company-address {

            font-size: 13px;

        }

        .table {

            color: #000;

        }

        .table th {

            background: #f1f1f1;

        }

        .border-dark {

            border: 1px solid #000 !important;

        }

        .signature {

            margin-top: 70px;

            text-align: center;

            font-weight: bold;

        }

        @media print {

            .main-header,
            .main-sidebar,
            .main-footer,
            .navbar,
            .sidebar,
            footer,
            .no-print,
            .content-header {

                display: none !important;

            }

            .content-wrapper {

                margin-left: 0 !important;

            }

            body {

                padding: 0;

                margin: 0;

            }

            .container-fluid {

                width: 100% !important;

                padding: 0 !important;

            }

            .table th {

                background: #f1f1f1 !important;

                -webkit-print-color-adjust: exact;

                print-color-adjust: exact;

            }

        }
    </style>

</head>

<body>

    <div class="container-fluid">

        <!-- =========================================================
         PRINT BUTTONS
    ========================================================== -->

        <div class="text-end mb-3 no-print">

            <button
                class="btn btn-success"
                onclick="window.print()">

                <i class="fas fa-print"></i>

                Print

            </button>

            <button
                class="btn btn-secondary"
                onclick="window.close()">

                Close

            </button>

        </div>


        <!-- =========================================================
         COMPANY HEADER
    ========================================================== -->

        @php

        /*
        |--------------------------------------------------------------------------
        | Use Purchase Company
        |--------------------------------------------------------------------------
        */

        $company = $purchaseReturn->purchase->company ?? null;

        @endphp


        <div class="text-center mb-4">

            <div class="company-title">

                {{ $company->company_name ?? 'RR MOTORS' }}

            </div>

            <div class="company-address">

                {{ $company->address ?? '' }}

            </div>

            <div>

                Phone :

                {{ $company->phone ?? '' }}

                &nbsp;&nbsp;

                GST :

                {{ $company->gst_number ?? '' }}

            </div>

        </div>


        <hr>


        <!-- =========================================================
         TITLE
    ========================================================== -->

        <h3 class="text-center">

            PURCHASE RETURN

        </h3>


        <!-- =========================================================
         RETURN INFORMATION
    ========================================================== -->

        <table class="table table-bordered">

            <tr>

                <th width="20%">

                    Return No

                </th>

                <td width="30%">

                    {{ $purchaseReturn->return_no }}

                </td>

                <th width="20%">

                    Return Date

                </th>

                <td>

                    {{ date(
                    'd-m-Y',
                    strtotime($purchaseReturn->return_date)
                ) }}

                </td>

            </tr>

            <tr>

                <th>

                    Purchase No

                </th>

                <td>

                    {{ $purchaseReturn->purchase->purchase_no ?? '-' }}

                </td>

                <th>

                    Purchase Date

                </th>

                <td>

                    {{ $purchaseReturn->purchase &&
                   $purchaseReturn->purchase->purchase_date
                    ? date(
                        'd-m-Y',
                        strtotime(
                            $purchaseReturn->purchase->purchase_date
                        )
                    )
                    : '-'
                }}

                </td>

            </tr>

            <tr>

                <th>

                    Store

                </th>

                <td>

                    {{ $purchaseReturn->purchase->store->store_name ?? '-' }}

                </td>

                <th>

                    Company

                </th>

                <td>

                    {{ $company->company_name ?? '-' }}

                </td>

            </tr>

        </table>


        <!-- =========================================================
         SUPPLIER DETAILS
    ========================================================== -->

        <h5 class="mt-4">

            Supplier Details

        </h5>


        <table class="table table-bordered">

            <tr>

                <th width="20%">

                    Supplier

                </th>

                <td>

                    {{ $purchaseReturn->supplier->supplier_name ?? '-' }}

                </td>

            </tr>

            <tr>

                <th>

                    Mobile

                </th>

                <td>

                    {{ $purchaseReturn->supplier->mobile ?? '-' }}

                </td>

            </tr>

            <tr>

                <th>

                    Address

                </th>

                <td>

                    {{ $purchaseReturn->supplier->billing_address
                    ?? $purchaseReturn->supplier->address
                    ?? '-'
                }}

                </td>

            </tr>

        </table>


        <!-- =========================================================
         RETURN ITEMS
    ========================================================== -->

        <table class="table table-bordered">

            <thead>

                <tr>

                    <th width="4%">

                        #

                    </th>

                    <th>

                        Product

                    </th>

                    <th>

                        Code

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

                        Line Total

                    </th>

                </tr>

            </thead>


            <tbody>

                @php

                $sl = 1;

                $subTotal = 0;

                $gstTotal = 0;

                $grandTotal = 0;

                @endphp


                @forelse($purchaseReturn->items as $item)

                @php

                /*
                |--------------------------------------------------------------------------
                | Find Original Purchase Item
                |--------------------------------------------------------------------------
                */

                $purchaseItem = $purchaseReturn
                ->purchase
                ->items
                ->firstWhere(
                'product_id',
                $item->product_id
                );


                /*
                |--------------------------------------------------------------------------
                | Get Godown
                |--------------------------------------------------------------------------
                */

                $godownName = 'Shop / No Godown';

                if ($purchaseItem && $purchaseItem->godown) {

                $godownName =
                $purchaseItem->godown->godown_name;

                }


                /*
                |--------------------------------------------------------------------------
                | Totals
                |--------------------------------------------------------------------------
                */

                $sub =

                $item->quantity
                *
                $item->purchase_price;

                $subTotal += $sub;

                $gstTotal +=
                (float) $item->gst_amount;

                $grandTotal +=
                (float) $item->line_total;

                @endphp


                <tr>

                    <!-- Serial -->

                    <td class="text-center">

                        {{ $sl++ }}

                    </td>


                    <!-- Product -->

                    <td>

                        {{ $item->product->product_name ?? '-' }}

                    </td>


                    <!-- Code -->

                    <td>

                        {{ $item->product->product_code ?? '-' }}

                    </td>


                    <!-- =================================================
                         GODOWN
                    ================================================== -->

                    <td>

                        {{ $godownName }}

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

                        {{ number_format(
                            $item->purchase_price,
                            2
                        ) }}

                    </td>


                    <!-- GST -->

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


                    <!-- Line Total -->

                    <td class="text-end">

                        {{ number_format(
                            $item->line_total,
                            2
                        ) }}

                    </td>

                </tr>

                @empty

                <tr>

                    <td
                        colspan="9"
                        class="text-center">

                        No return items found.

                    </td>

                </tr>

                @endforelse

            </tbody>


            <!-- =========================================================
             TOTALS
        ========================================================== -->

            <tfoot>

                <tr>

                    <th
                        colspan="8"
                        class="text-end">

                        Sub Total

                    </th>

                    <th class="text-end">

                        {{ number_format(
                        $subTotal,
                        2
                    ) }}

                    </th>

                </tr>


                <tr>

                    <th
                        colspan="8"
                        class="text-end">

                        GST Total

                    </th>

                    <th class="text-end">

                        {{ number_format(
                        $gstTotal,
                        2
                    ) }}

                    </th>

                </tr>


                <tr>

                    <th
                        colspan="8"
                        class="text-end">

                        Grand Total

                    </th>

                    <th class="text-end">

                        {{ number_format(
                        $grandTotal,
                        2
                    ) }}

                    </th>

                </tr>

            </tfoot>

        </table>


        <!-- =========================================================
         REMARKS
    ========================================================== -->

        <div class="row mt-4">

            <div class="col-md-12">

                <strong>

                    Remarks :

                </strong>

                {{ $purchaseReturn->remarks ?? '-' }}

            </div>

        </div>


        <!-- =========================================================
         SIGNATURES
    ========================================================== -->

        <div class="row mt-5">

            <div class="col-4 text-center">

                _______________________

                <br>

                Prepared By

            </div>


            <div class="col-4 text-center">

                _______________________

                <br>

                Checked By

            </div>


            <div class="col-4 text-center">

                _______________________

                <br>

                Authorized Signatory

            </div>

        </div>

    </div>


    <script>
        window.onload = function() {

            window.print();

        };
    </script>


</body>

</html>

@endsection