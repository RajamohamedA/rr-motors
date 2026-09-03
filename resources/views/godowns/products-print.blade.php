<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        {{ $godown->godown_name }} - Inventory Report
    </title>


    <style>
        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;

            background: #ffffff;

            color: #1f2937;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12px;
        }


        .report {
            width: 100%;

            padding: 20px;
        }


        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 20px;

            padding-bottom: 14px;

            border-bottom: 2px solid #1f2937;
        }


        .company h1 {
            margin: 0;

            font-size: 22px;
            font-weight: 700;
        }


        .company .code {
            margin-top: 4px;

            color: #6b7280;

            font-size: 11px;
        }


        .company .branch {
            margin-top: 6px;

            font-size: 12px;
            font-weight: 600;
        }


        .report-title {
            text-align: right;
        }


        .report-title h2 {
            margin: 0;

            font-size: 18px;
            font-weight: 700;
        }


        .report-title div {
            margin-top: 4px;

            color: #6b7280;

            font-size: 11px;
        }


        .godown-section {
            display: grid;

            grid-template-columns:
                repeat(3,
                    1fr);

            gap: 8px;

            margin-top: 14px;
        }


        .info-box {
            padding: 9px 10px;

            border: 1px solid #d1d5db;
        }


        .info-box span {
            display: block;

            margin-bottom: 3px;

            color: #6b7280;

            font-size: 10px;
            font-weight: 700;

            text-transform: uppercase;
        }


        .info-box strong {
            font-size: 12px;
        }


        .filters {
            margin-top: 12px;
            padding: 9px 10px;

            border: 1px solid #d1d5db;

            background: #f9fafb;
        }


        .filters-title {
            margin-bottom: 5px;

            font-size: 10px;
            font-weight: 700;

            text-transform: uppercase;
        }


        .filters-grid {
            display: grid;

            grid-template-columns:
                repeat(5,
                    1fr);

            gap: 7px;
        }


        .filter-item span {
            display: block;

            color: #6b7280;

            font-size: 9px;

            text-transform: uppercase;
        }


        .filter-item strong {
            display: block;

            margin-top: 2px;

            font-size: 10px;
        }


        .summary {
            display: grid;

            grid-template-columns:
                repeat(6,
                    1fr);

            gap: 7px;

            margin-top: 12px;
        }


        .summary-card {
            padding: 8px;

            border: 1px solid #d1d5db;

            text-align: center;
        }


        .summary-card span {
            display: block;

            color: #6b7280;

            font-size: 9px;
            font-weight: 700;

            text-transform: uppercase;
        }


        .summary-card strong {
            display: block;

            margin-top: 4px;

            font-size: 12px;
        }


        .table-wrap {
            margin-top: 14px;
        }


        table {
            width: 100%;

            border-collapse: collapse;

            table-layout: auto;
        }


        thead {
            display: table-header-group;
        }


        th {
            padding: 6px 5px;

            border: 1px solid #9ca3af;

            background: #f3f4f6;

            color: #111827;

            font-size: 9px;
            font-weight: 700;

            text-align: left;

            text-transform: uppercase;
        }


        td {
            padding: 6px 5px;

            border: 1px solid #d1d5db;

            font-size: 9px;

            vertical-align: top;
        }


        th.number,
        td.number {
            text-align: right;
        }


        th.center,
        td.center {
            text-align: center;
        }


        .product-name {
            font-weight: 700;
        }


        .product-meta {
            margin-top: 2px;

            color: #6b7280;

            font-size: 8px;
        }


        .low-stock {
            color: #92400e;

            font-weight: 700;
        }


        .out-stock {
            color: #b91c1c;

            font-weight: 700;
        }


        .margin-positive {
            color: #166534;

            font-weight: 700;
        }


        .margin-negative {
            color: #b91c1c;

            font-weight: 700;
        }


        .footer {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;

            margin-top: 14px;
            padding-top: 8px;

            border-top: 1px solid #d1d5db;

            color: #6b7280;

            font-size: 9px;
        }


        .no-data {
            padding: 30px;

            border: 1px solid #d1d5db;

            text-align: center;
        }


        .print-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;

            gap: 8px;

            margin-bottom: 12px;
        }


        .print-actions button {
            min-height: 34px;

            padding: 0 12px;

            border: 1px solid #9ca3af;
            border-radius: 5px;

            background: #ffffff;

            color: #1f2937;

            font-size: 11px;
            font-weight: 700;

            cursor: pointer;
        }


        @media print {

            @page {
                size: A4 landscape;

                margin: 10mm;
            }


            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }


            .report {
                padding: 0;
            }


            .print-actions {
                display: none;
            }


            tr {
                page-break-inside: avoid;
            }
        }
    </style>

</head>


<body>

    <div class="report">

        <div class="print-actions">

            <button
                type="button"
                onclick="window.print()">
                Print
            </button>


            <button
                type="button"
                onclick="window.close()">
                Close
            </button>

        </div>


        {{-- ================================================================
        HEADER
       ================================================================ --}}

        <div class="header">

            <div class="company">

                <h1>
                    {{ $company->company_name }}
                </h1>


                @if($company->company_code)

                <div class="code">
                    Company Code:
                    {{ $company->company_code }}
                </div>

                @endif


                <div class="branch">

                    Branch:
                    {{ $store->store_name }}

                    @if($store->store_code)

                    ({{ $store->store_code }})

                    @endif

                </div>

            </div>


            <div class="report-title">

                <h2>
                    Godown Inventory Report
                </h2>


                <div>
                    Generated:
                    {{ now()->format('d M Y h:i A') }}
                </div>

            </div>

        </div>


        {{-- ================================================================
        GODOWN DETAILS
       ================================================================ --}}

        <div class="godown-section">

            <div class="info-box">

                <span>
                    Godown
                </span>

                <strong>
                    {{ $godown->godown_name }}
                </strong>

            </div>


            <div class="info-box">

                <span>
                    Godown Code
                </span>

                <strong>
                    {{ $godown->godown_code ?: '—' }}
                </strong>

            </div>


            <div class="info-box">

                <span>
                    Location
                </span>

                <strong>
                    {{ $godown->address ?: '—' }}
                </strong>

            </div>

        </div>


        {{-- ================================================================
        FILTERS
       ================================================================ --}}

        <div class="filters">

            <div class="filters-title">
                Applied Filters
            </div>


            <div class="filters-grid">

                <div class="filter-item">

                    <span>
                        Search
                    </span>

                    <strong>
                        {{ $filters['search'] ?: 'All' }}
                    </strong>

                </div>


                <div class="filter-item">

                    <span>
                        Category
                    </span>

                    <strong>
                        {{ $selectedCategory?->name ?: 'All Categories' }}
                    </strong>

                </div>


                <div class="filter-item">

                    <span>
                        Brand
                    </span>

                    <strong>
                        {{ $selectedBrand?->brand_name ?: 'All Brands' }}
                    </strong>

                </div>


                <div class="filter-item">

                    <span>
                        Stock
                    </span>

                    <strong>
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
                    </strong>

                </div>


                <div class="filter-item">

                    <span>
                        Product Status
                    </span>

                    <strong>

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

                    </strong>

                </div>

            </div>

        </div>


        {{-- ================================================================
        SUMMARY
       ================================================================ --}}

        <div class="summary">

            <div class="summary-card">

                <span>
                    Products
                </span>

                <strong>
                    {{ number_format($summary['products_count'] ?? 0) }}
                </strong>

            </div>


            <div class="summary-card">

                <span>
                    Stock Qty
                </span>

                <strong>
                    {{ number_format($summary['stock_quantity'] ?? 0, 3) }}
                </strong>

            </div>


            <div class="summary-card">

                <span>
                    Est. Cost
                </span>

                <strong>
                    ₹{{ number_format($summary['estimated_cost'] ?? 0, 2) }}
                </strong>

            </div>


            <div class="summary-card">

                <span>
                    Selling Value
                </span>

                <strong>
                    ₹{{ number_format($summary['estimated_selling_value'] ?? 0, 2) }}
                </strong>

            </div>


            <div class="summary-card">

                <span>
                    Margin
                </span>

                <strong>
                    ₹{{ number_format($summary['potential_margin'] ?? 0, 2) }}
                </strong>

            </div>


            <div class="summary-card">

                <span>
                    Low Stock
                </span>

                <strong>
                    {{ number_format($summary['low_stock_count'] ?? 0) }}
                </strong>

            </div>

        </div>


        {{-- ================================================================
        PRODUCTS
       ================================================================ --}}

        <div class="table-wrap">

            @if($products->isNotEmpty())

            <table>

                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Product
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Brand
                        </th>

                        <th>
                            Part No.
                        </th>

                        <th class="center">
                            Unit
                        </th>

                        <th class="number">
                            Qty
                        </th>

                        <th class="number">
                            Min
                        </th>

                        <th class="number">
                            Unit Cost
                        </th>

                        <th class="number">
                            Est. Cost
                        </th>

                        <th class="number">
                            Sale Price
                        </th>

                        <th class="number">
                            Est. Selling
                        </th>

                        <th class="number">
                            Margin
                        </th>

                        <th class="center">
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

        </div>


        {{-- ================================================================
        FOOTER
       ================================================================ --}}

        <div class="footer">

            <div>
                RR ERP • Godown Inventory
            </div>


            <div>
                {{ $company->company_name }}
                /
                {{ $store->store_name }}
                /
                {{ $godown->godown_name }}
            </div>

        </div>

    </div>


    <script>
        /*
    |--------------------------------------------------------------------------
    | Optional Automatic Print
    |--------------------------------------------------------------------------
    |
    | Uncomment if you want the print dialog to open immediately.
    |
    */

        // window.addEventListener('load', () => window.print());
    </script>

</body>

</html>