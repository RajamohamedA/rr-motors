@extends('layouts.app')

@section('title','Sales Return Invoice')

@section('content')




<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Quotation Print</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            padding: 20px;
            color: #000;
        }

        .invoice {
            width: 100%;
            border: 2px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td,
        table th {
            border: 1px solid #000;
            padding: 6px;
        }

        .no-border {
            border: none !important;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .company {
            padding: 15px;
        }

        .invoice-title {
            background: #efefef;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
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
            }

        }
    </style>

</head>

<body>

    <div class="no-print text-right">

        <button onclick="window.print()">Print</button>

    </div>

    <table class="no-border">

        <tr class="no-border">

            <td class="no-border" width="20%">

                {{-- Company Logo --}}
                {{-- <img src="{{ asset('logo.png') }}" width="120"> --}}

            </td>

            <td class="no-border text-center">

                <div class="company">

                    {{ $quotation->company->company_name }}

                </div>

                <div>

                    {{ $quotation->company->address ?? '' }}

                </div>

                <div>

                    Phone :
                    {{ $quotation->company->mobile ?? '' }}

                </div>

                <div>

                    GST :
                    {{ $quotation->company->gst_no ?? '' }}

                </div>

            </td>

        </tr>

    </table>

    <hr>

    <div class="title">

        QUOTATION

    </div>

    <table>

        <tr>

            <td width="25%">

                <strong>Quotation No</strong>

            </td>

            <td width="25%">

                {{ $quotation->quotation_no }}

            </td>

            <td width="25%">

                <strong>Date</strong>

            </td>

            <td>

                {{ date('d-m-Y',strtotime($quotation->quotation_date)) }}

            </td>

        </tr>

        <tr>

            <td>

                <strong>Valid Until</strong>

            </td>

            <td>

                {{ $quotation->valid_until }}

            </td>

            <td>

                <strong>Status</strong>

            </td>

            <td>

                {{ $quotation->status }}

            </td>

        </tr>

    </table>

    <br>

    <table>

        <tr>

            <td>

                <strong>Customer</strong><br>

                {{ $quotation->customer->customer_name }}<br>

                {{ $quotation->customer->billing_address ?? '' }}<br>

                Mobile :
                {{ $quotation->customer->mobile ?? '' }}<br>

                GST :
                {{ $quotation->customer->gst_no ?? '' }}

            </td>

        </tr>

    </table>

    <br>

    <table>

        <thead>

            <tr>

                <th width="5%">#</th>

                <th width="12%">Code</th>

                <th>Product</th>

                <th width="8%">Unit</th>

                <th width="8%">Qty</th>

                <th width="12%">Price</th>

                <th width="8%">GST%</th>

                <th width="12%">GST Amt</th>

                <th width="15%">Amount</th>

            </tr>

        </thead>

        <tbody>

            @foreach($quotation->items as $item)

            <tr>

                <td class="text-center">

                    {{ $loop->iteration }}

                </td>

                <td>

                    {{ $item->product->product_code }}

                </td>

                <td>

                    {{ $item->product->product_name }}

                </td>

                <td class="text-center">

                    {{ $item->product->unit->short_name ?? '' }}

                </td>

                <td class="text-right">

                    {{ number_format($item->quantity,2) }}

                </td>

                <td class="text-right">

                    {{ number_format($item->sale_price,2) }}

                </td>

                <td class="text-right">

                    {{ number_format($item->gst_percent,2) }}

                </td>

                <td class="text-right">

                    {{ number_format($item->gst_amount,2) }}

                </td>

                <td class="text-right">

                    {{ number_format($item->line_total,2) }}

                </td>

            </tr>

            @endforeach

        </tbody>

        <tfoot>

            <tr>

                <th colspan="8" class="text-right">

                    Sub Total

                </th>

                <th class="text-right">

                    {{ number_format($quotation->sub_total,2) }}

                </th>

            </tr>

            <tr>

                <th colspan="8" class="text-right">

                    GST Total

                </th>

                <th class="text-right">

                    {{ number_format($quotation->gst_total,2) }}

                </th>

            </tr>

            <tr>

                <th colspan="8" class="text-right">

                    Grand Total

                </th>

                <th class="text-right">

                    {{ number_format($quotation->grand_total,2) }}

                </th>

            </tr>

        </tfoot>

    </table>

    @if($quotation->remarks)

    <div class="mt20">

        <strong>Remarks</strong>

        <br>

        {{ $quotation->remarks }}

    </div>

    @endif

    <div class="mt20">

        <strong>Terms & Conditions</strong>

        <ol>

            <li>Quotation is valid up to the validity date mentioned above.</li>

            <li>Prices are subject to change without prior notice.</li>

            <li>Goods once sold cannot be returned.</li>

            <li>Delivery subject to stock availability.</li>

        </ol>

    </div>

    <table class="signature no-border">

        <tr class="no-border">

            <td class="no-border text-center">

                ______________________

                <br>

                Customer Signature

            </td>

            <td class="no-border text-center">

                ______________________

                <br>

                Authorized Signature

            </td>

        </tr>

    </table>

    <script>
        window.onload = function() {

            window.print();

        }
    </script>

</body>

</html>

@endsection