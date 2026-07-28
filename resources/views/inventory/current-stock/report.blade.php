<div style="text-align:center;margin-bottom:15px;">

    <h2 style="margin:0;">
        {{ config('app.name') }}
    </h2>

    <h3 style="margin:5px 0;">
        CURRENT STOCK REPORT
    </h3>

    <p style="margin:0;">
        Generated :
        {{ now()->format('d-m-Y h:i A') }}
    </p>

</div>

<table width="100%" cellpadding="5" cellspacing="0" border="1" style="border-collapse:collapse;font-size:12px;">

    <thead style="background:#efefef;">

        <tr>

            <th>#</th>

            <th>Code</th>

            <th>Product</th>

            <th>Category</th>

            <th>Brand</th>

            <th>Company</th>

            <th>Store</th>

            <th>Unit</th>

            <th style="text-align:right;">Purchase</th>

            <th style="text-align:right;">Selling</th>

            <th style="text-align:right;">Current Stock</th>

            <th style="text-align:right;">Stock Value</th>

        </tr>

    </thead>

    <tbody>

        @php
        $sl = 1;
        $grandQty = 0;
        $grandValue = 0;
        @endphp

        @forelse($stocks as $stock)

        @php

        $purchase = $stock->product->purchase_price ?? 0;

        $selling = $stock->product->selling_price ?? 0;

        $value = $purchase * $stock->current_stock;

        $grandQty += $stock->current_stock;

        $grandValue += $value;

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

            <td style="text-align:right;">
                {{ number_format($purchase,2) }}
            </td>

            <td style="text-align:right;">
                {{ number_format($selling,2) }}
            </td>

            <td style="text-align:right;">
                {{ number_format($stock->current_stock,2) }}
            </td>

            <td style="text-align:right;">
                {{ number_format($value,2) }}
            </td>

        </tr>

        @empty

        <tr>

            <td colspan="12" style="text-align:center;">
                No stock records found.
            </td>

        </tr>

        @endforelse

    </tbody>

    <tfoot>

        <tr style="font-weight:bold;background:#efefef;">

            <td colspan="10" style="text-align:right;">

                GRAND TOTAL

            </td>

            <td style="text-align:right;">

                {{ number_format($grandQty,2) }}

            </td>

            <td style="text-align:right;">

                {{ number_format($grandValue,2) }}

            </td>

        </tr>

    </tfoot>

</table>

<br>

<table width="100%">

    <tr>

        <td>

            Total Products :
            {{ $summary['products'] }}

        </td>

        <td>

            Low Stock :
            {{ $summary['low_stock'] }}

        </td>

        <td>

            Out Of Stock :
            {{ $summary['out_stock'] }}

        </td>

    </tr>

</table>