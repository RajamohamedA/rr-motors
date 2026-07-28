<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Current Stock Report</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h2,
        h3,
        h4,
        p {
            margin: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
        }

        table th {
            background: #e9ecef;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            font-weight: bold;
        }

        @media print {

            @page {
                size: A4 landscape;
                margin: 10mm;
            }

        }
    </style>

</head>

<body onload="window.print()">

    <div class="text-center">

        <h2>{{ config('app.name') }}</h2>

        <h3>CURRENT STOCK REPORT</h3>

        <p>
            Printed :
            {{ now()->format('d-m-Y h:i A') }}
        </p>

    </div>

    <table>

        <thead>

            <tr>

                <th>#</th>

                <th>Product Code</th>

                <th>Product Name</th>

                <th>Category</th>

                <th>Brand</th>

                <th>Company</th>

                <th>Store</th>

                <th>Unit</th>

                <th class="text-right">Purchase</th>

                <th class="text-right">Selling</th>

                <th class="text-right">Stock</th>

                <th class="text-right">Value</th>

            </tr>

        </thead>

        <tbody>

            @php
            $sl=1;
            @endphp

            @foreach($stocks as $stock)

            @php

            $purchase = $stock->product->purchase_price ?? 0;

            $value = $purchase * $stock->current_stock;

            @endphp

            <tr>

                <td>{{ $sl++ }}</td>

                <td>{{ $stock->product->product_code }}</td>

                <td>{{ $stock->product->product_name }}</td>

                <td>{{ optional($stock->product->category)->category_name }}</td>

                <td>{{ optional($stock->product->brand)->brand_name }}</td>

                <td>{{ optional($stock->company)->company_name }}</td>

                <td>{{ optional($stock->store)->store_name }}</td>

                <td>{{ optional($stock->product->unit)->short_name }}</td>

                <td class="text-right">

                    {{ number_format($purchase,2) }}

                </td>

                <td class="text-right">

                    {{ number_format($stock->product->selling_price ?? 0,2) }}

                </td>

                <td class="text-right">

                    {{ number_format($stock->current_stock,2) }}

                </td>

                <td class="text-right">

                    {{ number_format($value,2) }}

                </td>

            </tr>

            @endforeach

        </tbody>

        <tfoot>

            <tr>

                <th colspan="10" class="text-right">

                    GRAND TOTAL

                </th>

                <th class="text-right">

                    {{ number_format($summary['qty'],2) }}

                </th>

                <th class="text-right">

                    {{ number_format($summary['value'],2) }}

                </th>

            </tr>

        </tfoot>

    </table>

    <div class="footer">

        <table style="border:none">

            <tr style="border:none">

                <td style="border:none">

                    Total Products :
                    {{ $summary['products'] }}

                </td>

                <td style="border:none">

                    Low Stock :
                    {{ $summary['low_stock'] }}

                </td>

                <td style="border:none">

                    Out Of Stock :
                    {{ $summary['out_stock'] }}

                </td>

            </tr>

        </table>

    </div>

</body>

</html>