@extends('layouts.app')

@section('title','Sales Return Invoice')

@section('content')

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Sales Return Invoice</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
font-family:Arial,Helvetica,sans-serif;
font-size:13px;
padding:20px;
color:#000;
}

.invoice{
width:100%;
border:2px solid #000;
}

table{
width:100%;
border-collapse:collapse;
}

table td,
table th{
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

.company{
padding:15px;
}

.invoice-title{
background:#efefef;
font-size:22px;
font-weight:bold;
text-align:center;
padding:10px;
border-top:2px solid #000;
border-bottom:2px solid #000;
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
    body{
        padding:0;
        }

}

</style>

</head>

<body>

    <div class="d-flex justify-content-end mb-3 no-print">

        <button type="button"
                class="btn btn-primary"
                onclick="window.print()">

            <i class="fas fa-print"></i>

            Print Invoice

        </button>

        <a href="{{ route('sales-returns.index') }}"
        class="btn btn-secondary ms-2">

            <i class="fas fa-arrow-left"></i>

            Back

        </a>

    </div>

<div class="invoice">

<table>

<tr>

<td class="company no-border">

<h2>RR MOTORS</h2>

Electrical & Machinery Spare Parts

<br>

Thiruthuraipoondi

<br>

GSTIN : XXXXXXXXXXXXX

<br>

Phone : +91 XXXXXXXXXX

</td>

</tr>

</table>

<div class="invoice-title">

SALES RETURN INVOICE

</div>

<table>

<tr>

<td>

<b>Return No :</b>

{{ $return->return_no }}

</td>

<td>

<b>Return Date :</b>

{{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}

</td>

</tr>

<tr>

<td>

<b>Invoice No :</b>

{{ $return->sale->invoice_no }}

</td>

<td>

<b>Invoice Date :</b>

{{ \Carbon\Carbon::parse($return->sale->invoice_date)->format('d-m-Y') }}

</td>

</tr>

</table>

<table>

<tr>

<th colspan="2">

CUSTOMER DETAILS

</th>

</tr>

<tr>

<td width="25%">Customer</td>

<td>{{ $return->customer->customer_name }}</td>

</tr>

<tr>

<td>Address</td>

<td>{{ $return->customer->billing_address }}</td>

</tr>

<tr>

<td>Mobile</td>

<td>{{ $return->customer->mobile }}</td>

</tr>

<tr>

<td>GST No</td>

<td>{{ $return->customer->gst_number }}</td>

</tr>

</table>

<br>

@php

$subTotal=0;
$totalGST=0;

@endphp

<table>

<thead>

<tr>

<th>Sl</th>

<th>Code</th>

<th>Product</th>

<th>HSN</th>

<th>Qty</th>

<th>Unit</th>

<th>Rate</th>

<th>GST%</th>

<th>GST Amt</th>

<th>Total</th>

</tr>

</thead>

<tbody>

@foreach($return->items as $item)

@php

$basic=$item->quantity*$item->sale_price;

$subTotal+=$basic;

$totalGST+=$item->gst_amount;

@endphp

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ $item->product->product_code }}</td>

<td>{{ $item->product->product_name }}</td>

<td>{{ $item->product->hsn_code }}</td>

<td class="text-right">{{ number_format($item->quantity,2) }}</td>

<td>{{ $item->product->unit->short_name ?? '' }}</td>

<td class="text-right">{{ number_format($item->sale_price,2) }}</td>

<td class="text-right">{{ number_format($item->gst_percent,2) }}</td>

<td class="text-right">{{ number_format($item->gst_amount,2) }}</td>

<td class="text-right">{{ number_format($item->line_total,2) }}</td>

</tr>

@endforeach

</tbody>

</table>

<br>

<table>

<tr>

<td width="65%" rowspan="5" valign="top">

<b>Remarks</b>

<br><br>

{{ $return->remarks }}

</td>

<td width="20%">Sub Total</td>

<td class="text-right">

₹ {{ number_format($subTotal,2) }}

</td>

</tr>

<tr>

<td>GST Total</td>

<td class="text-right">

₹ {{ number_format($totalGST,2) }}

</td>

</tr>

<tr>

<td><b>Grand Total</b></td>

<td class="text-right">

<b>

₹ {{ number_format($return->total_amount,2) }}

</b>

</td>

</tr>

<tr>

<td colspan="2">

<b>Amount in Words</b>

<br>

{{ ucfirst(\NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($return->total_amount)) }}

Rupees Only

</td>

</tr>

</table>

<br><br>

<table>

<tr>

<td class="text-center">

____________________

<br>

Prepared By

</td>

<td class="text-center">

____________________

<br>

Checked By

</td>

<td class="text-center">

____________________

<br>

Authorized Signature

</td>

</tr>

</table>

</div>

<br>

<div class="text-center">

<small>

This is a computer generated Sales Return Invoice.

</small>

</div>

</body>

</html>

@endsection