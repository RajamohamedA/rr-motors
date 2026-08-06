<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Sales Invoice</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
            background: #fff;
            padding: 20px;
        }

        .invoice {
            width: 100%;
            border: 2px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
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

        .fw-bold {
            font-weight: bold;
        }

        h2 {
            margin-bottom: 5px;
        }

        .company-header {
            padding: 15px;
        }

        .company-header p {
            margin: 2px;
        }

        .invoice-title {
            background: #efefef;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        @media print {

            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }

        }
    </style>

</head>

<body>

    <div class="no-print text-right">

        <button onclick="window.print()">

            Print Sales Invoice

        </button>

    </div>

    <br>

    <div class="invoice">

        <!-- Company -->

        <table>

            <tr>

                <td class="company-header no-border">

                    <h2>{{ $sale->company->company_name ?? 'RR MOTORS' }}</h2>

                    <p>{{ $sale->company->address ?? '' }}</p>

                    <p>GSTIN : {{ $sale->company->gst_number ?? '' }}</p>

                    <p>Phone : {{ $sale->company->mobile ?? '' }}</p>

                    <p>Email : {{ $sale->company->email ?? '' }}</p>

                </td>

            </tr>

        </table>

        <div class="invoice-title">

            SALES INVOICE

        </div>

        <table>

            <tr>

                <td width="50%">

                    <b>Invoice No :</b>

                    {{ $sale->invoice_no }}

                </td>

                <td>

                    <b>Invoice Date :</b>

                    {{ \Carbon\Carbon::parse($sale->invoice_date)->format('d-m-Y') }}

                </td>

            </tr>

        </table>

        <table>

            <tr>

                <th colspan="2" style="background:#efefef;">

                    CUSTOMER DETAILS

                </th>

            </tr>

            <tr>

                <td width="25%">

                    Customer Name

                </td>

                <td>

                    {{ $sale->customer->customer_name }}

                </td>

            </tr>

            <tr>

                <td>

                    Address

                </td>

                <td>

                    {{ $sale->customer->billing_address }}

                </td>

            </tr>

            <tr>

                <td>

                    Mobile

                </td>

                <td>

                    {{ $sale->customer->mobile }}

                </td>

            </tr>

            <tr>

                <td>

                    GST No

                </td>

                <td>

                    {{ $sale->customer->gst_number }}

                </td>

            </tr>

        </table>

        <br>

        <!-- PRODUCT DETAILS STARTS HERE -->

        <table>

            <thead>

                <tr style="background:#efefef;">

                    <th width="5%">#</th>

                    <th width="12%">Code</th>

                    <th>Product Name</th>

                    <th width="10%">HSN</th>

                    <th width="8%">Qty</th>

                    <th width="8%">Unit</th>

                    <th width="10%">Rate</th>

                    <th width="8%">GST%</th>

                    <th width="10%">GST Amt</th>

                    <th width="12%">Total</th>

                </tr>

            </thead>

            <tbody>

                @php

                $sl = 1;
                $subTotal = 0;
                $totalGST = 0;
                $discount = 0;

                @endphp

                @foreach($sale->items as $item)

                @php

                $lineAmount = $item->quantity * $item->sale_price;

                $subTotal += $lineAmount;

                $totalGST += $item->gst_amount;

                @endphp

                <tr>

                    <td class="text-center">

                        {{ $sl++ }}

                    </td>

                    <td>

                        {{ $item->product->product_code }}

                    </td>

                    <td>

                        {{ $item->product->product_name }}

                    </td>

                    <td class="text-center">

                        {{ $item->product->hsn_code }}

                    </td>

                    <td class="text-right">

                        {{ number_format($item->quantity,2) }}

                    </td>

                    <td class="text-center">

                        {{ $item->product->unit->short_name ?? '' }}

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

                    <td class="text-right fw-bold">

                        {{ number_format($item->line_total,2) }}

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

        <br>

        <!-- TOTAL SECTION STARTS HERE -->

        <table>

            <tr>

                <td width="65%" rowspan="7" style="vertical-align:top;">

                    <b>Remarks</b>

                    <br><br>

                    {{ $sale->remarks }}

                </td>

                <td width="20%">

                    Sub Total

                </td>

                <td class="text-right">

                    ₹ {{ number_format($subTotal,2) }}

                </td>

            </tr>

            <tr>

                <td>

                    Discount

                </td>

                <td class="text-right">

                    ₹ {{ number_format($discount,2) }}

                </td>

            </tr>

            <tr>

                <td>

                    GST Total

                </td>

                <td class="text-right">

                    ₹ {{ number_format($totalGST,2) }}

                </td>

            </tr>

            <tr>

                <td class="fw-bold">

                    Grand Total

                </td>

                <td class="text-right fw-bold">

                    ₹ {{ number_format($sale->grand_total,2) }}

                </td>

            </tr>

            <tr>

                <td>

                    Paid Amount

                </td>

                <td class="text-right">

                    ₹ {{ number_format($sale->paid_amount,2) }}

                </td>

            </tr>

            <tr>

                <td class="fw-bold">

                    Balance Amount

                </td>

                <td class="text-right fw-bold">

                    ₹ {{ number_format($sale->balance_amount,2) }}

                </td>

            </tr>

            <tr>

                <td colspan="2">

                    <b>Amount in Words</b>

                    <br>

                    {{ ucwords(\NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($sale->grand_total)) }}

                    Rupees Only

                </td>

            </tr>

        </table>

        <br><br>

        <table>

            <tr>

                <td class="text-center" width="33%">

                    ______________________

                    <br>

                    Prepared By

                </td>

                <td class="text-center" width="33%">

                    ______________________

                    <br>

                    Customer Signature

                </td>

                <td class="text-center" width="34%">

                    ______________________

                    <br>

                    Authorized Signature

                </td>

            </tr>

        </table>

    </div>

    <br>

    <div class="text-center">

        <small>

            This is a computer generated Sales Invoice.

        </small>

    </div>

    <script>
        window.onload = function() {

            window.print();

        };

    </script>

</body>

</html>