<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        {{ $godown->godown_name }} - Inventory PDF
    </title>


    <style>
        @page {
            size: A4 landscape;
            margin: 12px 14px;
        }


        body {
            margin: 0;

            color: #1f2937;

            font-family:
                DejaVu Sans,
                sans-serif;

            font-size: 8px;
        }


        .header-table,
        .godown-table,
        .filter-table,
        .summary-table,
        .inventory-table,
        .footer-table {
            width: 100%;

            border-collapse: collapse;
        }


        .header-table {
            margin-bottom: 8px;

            border-bottom: 2px solid #1f2937;
        }


        .header-table td {
            padding-bottom: 7px;

            vertical-align: top;
        }


        .company-name {
            font-size: 17px;
            font-weight: bold;
        }


        .company-meta {
            margin-top: 3px;

            color: #6b7280;

            font-size: 8px;
        }


        .branch-name {
            margin-top: 4px;

            font-size: 9px;
            font-weight: bold;
        }


        .report-title {
            font-size: 14px;
            font-weight: bold;

            text-align: right;
        }


        .generated {
            margin-top: 3px;

            color: #6b7280;

            font-size: 7px;

            text-align: right;
        }


        .godown-table {
            margin-bottom: 7px;
        }


        .godown-table td {
            width: 33.33%;

            padding: 6px;

            border: 1px solid #d1d5db;

            vertical-align: top;
        }


        .label {
            display: block;

            margin-bottom: 2px;

            color: #6b7280;

            font-size: 7px;
            font-weight: bold;

            text-transform: uppercase;
        }


        .value {
            font-size: 8px;
            font-weight: bold;
        }


        .filter-box {
            margin-bottom: 7px;

            padding: 5px;

            border: 1px solid #d1d5db;

            background: #f9fafb;
        }


        .filter-heading {
            margin-bottom: 4px;

            font-size: 7px;
            font-weight: bold;

            text-transform: uppercase;
        }


        .filter-table td {
            width: 20%;

            padding-right: 6px;

            vertical-align: top;
        }


        .filter-table .label {
            font-size: 6px;
        }


        .filter-table .value {
            font-size: 7px;
        }


        .summary-table {
            margin-bottom: 8px;
        }


        .summary-table td {
            width: 16.66%;

            padding: 5px;

            border: 1px solid #d1d5db;

            text-align: center;
        }


        .summary-table .summary-label {
            color: #6b7280;

            font-size: 6px;
            font-weight: bold;

            text-transform: uppercase;
        }


        .summary-table .summary-value {
            margin-top: 2px;

            font-size: 8px;
            font-weight: bold;
        }


        .inventory-table {
            table-layout: fixed;
        }


        .inventory-table thead {
            display: table-header-group;
        }


        .inventory-table th {
            padding: 4px 3px;

            border: 1px solid #9ca3af;

            background: #f3f4f6;

            color: #111827;

            font-size: 6px;
            font-weight: bold;

            text-align: left;

            text-transform: uppercase;

            vertical-align: middle;
        }


        .inventory-table td {
            padding: 4px 3px;

            border: 1px solid #d1d5db;

            font-size: 6.5px;

            vertical-align: top;
        }


        .inventory-table tr {
            page-break-inside: avoid;
        }


        .number {
            text-align: right !important;
        }


        .center {
            text-align: center !important;
        }


        .product-name {
            font-size: 7px;
            font-weight: bold;
        }


        .product-meta {
            margin-top: 1px;

            color: #6b7280;

            font-size: 5.5px;
        }


        .low-stock {
            color: #92400e;

            font-weight: bold;
        }


        .out-stock {
            color: #b91c1c;

            font-weight: bold;
        }


        .margin-positive {
            color: #166534;

            font-weight: bold;
        }


        .margin-negative {
            color: #b91c1c;

            font-weight: bold;
        }


        .no-data {
            padding: 25px;

            border: 1px solid #d1d5db;

            text-align: center;

            font-size: 9px;
        }


        .footer-table {
            margin-top: 8px;

            border-top: 1px solid #d1d5db;
        }


        .footer-table td {
            padding-top: 5px;

            color: #6b7280;

            font-size: 6px;
        }


        .footer-table td:last-child {
            text-align: right;
        }
    </style>

</head>


<body>

    {{-- ======================================================================
    HEADER
   ====================================================================== --}}

    <table class="header-table">

        <tr>

            <td>

                <div class="company-name">
                    {{ $company->company_name }}
                </div>


                @if($company->company_code)

                <div class="company-meta">
                    Company Code:
                    {{ $company->company_code }}
                </div>

                @endif


                <div class="branch-name">

                    Branch:
                    {{ $store->store_name }}

                    @if($store->store_code)

                    ({{ $store->store_code }})

                    @endif

                </div>

            </td>


            <td>

                <div class="report-title">
                    Godown Inventory Report
                </div>


                <div class="generated">
                    Generated:
                    {{ now()->format('d M Y h:i A') }}
                </div>

            </td>

        </tr>

    </table>


    {{-- ======================================================================
    GODOWN DETAILS
   ====================================================================== --}}

    <table class="godown-table">

        <tr>

            <td>

                <span class="label">
                    Godown
                </span>


                <span class="value">
                    {{ $godown->godown_name }}
                </span>

            </td>


            <td>

                <span class="label">
                    Godown Code
                </span>


                <span class="value">
                    {{ $godown->godown_code ?: '—' }}
                </span>

            </td>


            <td>

                <span class="label">
                    Location
                </span>


                <span class="value">
                    {{ $godown->address ?: '—' }}
                </span>

            </td>

        </tr>

    </table>


    {{-- ======================================================================
    FILTERS
   ====================================================================== --}}

    <div class="filter-box">

        <div class="filter-heading">
            Applied Filters
        </div>


        <table class="filter-table">

            <tr>

                <td>

                    <span class="label">
                        Search
                    </span>

                    <span class="value">
                        {{ $filters['search'] ?: 'All' }}
                    </span>

                </td>


                <td>

                    <span class="label">
                        Category
                    </span>

                    <span class="value">
                        {{ $selectedCategory?->name ?: 'All Categories' }}
                    </span>

                </td>


                <td>

                    <span class="label">
                        Brand
                    </span>

                    <span class="value">
                        {{ $selectedBrand?->brand_name ?: 'All Brands' }}
                    </span>

                </td>


                <td>

                    <span class="label">
                        Stock
                    </span>


                    <span class="value">

                        @switch($filters['stock_status'])

                        @case('in_stock')
                        In Stock
                        @break

                        @case('low_stock')
                        Low Stock
                        @break

                        @case('out_of_stock')
                        Out of Stock
                        @break

                        @default
                        All Stock

                        @endswitch

                    </span>

                </td>


                <td>

                    <span class="label">
                        Product Status
                    </span>


                    <span class="value">

                        @if(
                        $filters['status'] === '1'
                        ||
                        $filters['status'] === 1
                        )

                        Active

                        @elseif(
                        $filters['status'] === '0'
                        ||
                        $filters['status'] === 0
                        )

                        Inactive

                        @else

                        All

                        @endif

                    </span>

                </td>

            </tr>

        </table>

    </div>


    {{-- ======================================================================
    SUMMARY
   ====================================================================== --}}

    <table class="summary-table">

        <tr>

            <td>

                <div class="summary-label">
                    Products
                </div>

                <div class="summary-value">
                    {{ number_format($summary['products_count'] ?? 0) }}
                </div>

            </td>


            <td>

                <div class="summary-label">
                    Stock Qty
                </div>

                <div class="summary-value">
                    {{ number_format($summary['stock_quantity'] ?? 0, 3) }}
                </div>

            </td>


            <td>

                <div class="summary-label">
                    Estimated Cost
                </div>

                <div class="summary-value">
                    ₹{{ number_format($summary['estimated_cost'] ?? 0, 2) }}
                </div>

            </td>


            <td>

                <div class="summary-label">
                    Selling Value
                </div>

                <div class="summary-value">
                    ₹{{ number_format($summary['estimated_selling_value'] ?? 0, 2) }}
                </div>

            </td>


            <td>

                <div class="summary-label">
                    Potential Margin
                </div>

                <div class="summary-value">
                    ₹{{ number_format($summary['potential_margin'] ?? 0, 2) }}
                </div>

            </td>


            <td>

                <div class="summary-label">
                    Low Stock
                </div>

                <div class="summary-value">
                    {{ number_format($summary['low_stock_count'] ?? 0) }}
                </div>

            </td>

        </tr>

    </table>


    {{-- ======================================================================
    INVENTORY
   ====================================================================== --}}

    @if($products->isNotEmpty())

    <table class="inventory-table">

        <thead>

            <tr>

                <th style="width: 2%;">
                    #
                </th>

                <th style="width: 15%;">
                    Product
                </th>

                <th style="width: 8%;">
                    Category
                </th>

                <th style="width: 7%;">
                    Brand
                </th>

                <th style="width: 7%;">
                    Part No.
                </th>

                <th
                    class="center"
                    style="width: 4%;">
                    Unit
                </th>

                <th
                    class="number"
                    style="width: 5%;">
                    Qty
                </th>

                <th
                    class="number"
                    style="width: 5%;">
                    Min
                </th>

                <th
                    class="number"
                    style="width: 7%;">
                    Unit Cost
                </th>

                <th
                    class="number"
                    style="width: 8%;">
                    Est. Cost
                </th>

                <th
                    class="number"
                    style="width: 7%;">
                    Sale Price
                </th>

                <th
                    class="number"
                    style="width: 8%;">
                    Est. Selling
                </th>

                <th
                    class="number"
                    style="width: 8%;">
                    Margin
                </th>

                <th
                    class="center"
                    style="width: 5%;">
                    Stock
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach($products as $index => $product)

            @php

            $quantity =
            (float) (
            $product->balance_qty
            ?? 0
            );


            $unitCost =
            (float) (
            $product->unit_cost
            ?? 0
            );


            if ($unitCost <= 0) {

                $unitCost=(float) (
                $product->purchase_price
                ?? 0
                );
                }


                $sellingPrice =
                (float) (
                $product->selling_price
                ?? 0
                );


                $estimatedCost =
                $quantity
                *
                $unitCost;


                $estimatedSelling =
                $quantity
                *
                $sellingPrice;


                $margin =
                $estimatedSelling
                -
                $estimatedCost;


                $minimumStock =
                (float) (
                $product->minimum_stock
                ?? 0
                );


                $isOut =
                $quantity <= 0;


                    $isLow=!$isOut
                    &&
                    $minimumStock> 0
                    &&
                    $quantity <= $minimumStock;

                        @endphp


                        <tr>

                        <td>
                            {{ $index + 1 }}
                        </td>


                        <td>

                            <div class="product-name">
                                {{ $product->product_name }}
                            </div>


                            <div class="product-meta">

                                {{ $product->product_code ?: 'No Code' }}

                                @if($product->barcode)

                                • {{ $product->barcode }}

                                @endif

                            </div>

                        </td>


                        <td>
                            {{ $product->category?->name ?: '—' }}
                        </td>


                        <td>
                            {{ $product->brand?->brand_name ?: '—' }}
                        </td>


                        <td>
                            {{ $product->part_number ?: '—' }}
                        </td>


                        <td class="center">

                            {{
                        $product->unit?->short_name
                        ?: $product->unit?->unit_name
                        ?: '—'
                    }}

                        </td>


                        <td
                            class="number
                    {{ $isOut ? 'out-stock' : ($isLow ? 'low-stock' : '') }}">

                            {{ number_format($quantity, 3) }}

                        </td>


                        <td class="number">
                            {{ number_format($minimumStock, 3) }}
                        </td>


                        <td class="number">
                            ₹{{ number_format($unitCost, 2) }}
                        </td>


                        <td class="number">
                            ₹{{ number_format($estimatedCost, 2) }}
                        </td>


                        <td class="number">
                            ₹{{ number_format($sellingPrice, 2) }}
                        </td>


                        <td class="number">
                            ₹{{ number_format($estimatedSelling, 2) }}
                        </td>


                        <td
                            class="number
                    {{ $margin >= 0 ? 'margin-positive' : 'margin-negative' }}">

                            ₹{{ number_format($margin, 2) }}

                        </td>


                        <td class="center">

                            @if($isOut)

                            <span class="out-stock">
                                Out
                            </span>

                            @elseif($isLow)

                            <span class="low-stock">
                                Low
                            </span>

                            @else

                            In Stock

                            @endif

                        </td>

                        </tr>

                        @endforeach

        </tbody>

    </table>

    @else

    <div class="no-data">
        No products found for the selected filters.
    </div>

    @endif


    {{-- ======================================================================
    FOOTER
   ====================================================================== --}}

    <table class="footer-table">

        <tr>

            <td>
                RR Motors ERP • Godown Inventory
            </td>


            <td>

                {{ $company->company_name }}
                /
                {{ $store->store_name }}
                /
                {{ $godown->godown_name }}

            </td>

        </tr>

    </table>

</body>

</html>