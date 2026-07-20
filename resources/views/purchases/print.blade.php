<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Purchase Invoice</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family: Arial, Helvetica, sans-serif;

            font-size:13px;

            color:#000;

            background:#fff;

            padding:20px;

        }

        .invoice{

            width:100%;

            border:2px solid #000;

        }

        table{

            width:100%;

            border-collapse:collapse;

        }

        td,
        th{

            border:1px solid #000;

            padding:6px;

        }

        .no-border{

            border:none!important;

        }

        .text-center{

            text-align:center;

        }

        .text-right{

            text-align:right;

        }

        .fw-bold{

            font-weight:bold;

        }

        h2{

            margin-bottom:5px;

        }

        .company-header{

            padding:15px;

        }

        .company-header p{

            margin:2px;

        }

        .invoice-title{

            background:#efefef;

            font-size:20px;

            font-weight:bold;

            text-align:center;

            padding:10px;

            border-top:2px solid #000;

            border-bottom:2px solid #000;

        }

        @page{

            size:A4;

            margin:10mm;

        }

        @media print{

            .no-print{

                display:none;

            }

            body{

                padding:0;

            }

        }

    </style>

</head>

<body>

<div class="no-print text-right mb-3">

    <button onclick="window.print()">

        Print Invoice

    </button>

</div>

<div class="invoice">

    <!-- Company -->

    <table>

        <tr>

            <td class="company-header no-border">

                <h2>RR MOTORS</h2>

                <p>Electrical & Machinery Spare Parts</p>

                <p>Thiruthuraipoondi, Tamil Nadu</p>

                <p>GSTIN : XXXXXXXXXXXXXX</p>

                <p>Phone : +91-XXXXXXXXXX</p>

                <p>Email : info@rrmotors.com</p>

            </td>

        </tr>

    </table>

    <div class="invoice-title">

        PURCHASE INVOICE

    </div>

    <table>

        <tr>

            <td width="50%">

                <b>Purchase No :</b>

                {{ $purchase->purchase_no }}

            </td>

            <td>

                <b>Purchase Date :</b>

                {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') }}

            </td>

        </tr>

        <tr>

            <td>

                <b>Invoice No :</b>

                {{ $purchase->invoice_no }}

            </td>

            <td>

                <b>Invoice Date :</b>

                {{ $purchase->invoice_date }}

            </td>

        </tr>

    </table>

    <table>

        <tr>

            <th colspan="2"
                style="background:#f2f2f2;">

                SUPPLIER DETAILS

            </th>

        </tr>

        <tr>

            <td width="25%">
                Supplier Name
            </td>

            <td>
                {{ $purchase->supplier->supplier_name }}
            </td>

        </tr>

        <tr>

            <td>
                Address
            </td>

            <td>
                {{ $purchase->supplier->address }}
            </td>

        </tr>

        <tr>

            <td>
                Mobile
            </td>

            <td>
                {{ $purchase->supplier->mobile }}
            </td>

        </tr>

        <tr>

            <td>
                GST No
            </td>

            <td>
                {{ $purchase->supplier->gst_number }}
            </td>

        </tr>

    </table>

    <br>

    <!-- product details -->
    <table>

        <thead>

        <tr style="background:#efefef;">

            <th width="5%">#</th>

            <th width="12%">Code</th>

            <th>Product</th>

            <th width="10%">HSN</th>

            <th width="8%">Qty</th>

            <th width="8%">Unit</th>

            <th width="10%">Price</th>

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

        @endphp

        @foreach($purchase->items as $item)

        @php

        $subTotal += ($item->quantity * $item->purchase_price);

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

            <td>
                {{ $item->product->hsn_code }}
            </td>

            <td class="text-right">
                {{ number_format($item->quantity,2) }}
            </td>

            <td>
                {{ $item->product->unit->short_name ?? '' }}
            </td>

            <td class="text-right">
                {{ number_format($item->purchase_price,2) }}
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

    </table>

    <!-- invoice summery -->
    @php

    $discount = 0;

    @endphp

    <br>

    <table>

        <tr>

            <td width="65%" rowspan="5" style="vertical-align:top;">

                <b>Remarks</b>

                <br><br>

                {{ $purchase->remarks }}

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

                ₹ {{ number_format($purchase->grand_total,2) }}

            </td>

        </tr>

        <tr>

            <td colspan="2">

                <b>Amount in Words</b>

                <br>

                {{ \NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($purchase->grand_total) }}
                Rupees Only

            </td>

        </tr>

    </table>

    <!-- signature -->
    <br><br>

    <table>

        <tr>

            <td class="text-center">

                ______________________

                <br>

                Prepared By

            </td>

            <td class="text-center">

                ______________________

                <br>

                Checked By

            </td>

            <td class="text-center">

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

        This is a computer generated Purchase Invoice.

    </small>

</div>

</div>

</body>

</html>