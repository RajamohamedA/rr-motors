<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        {{ $category->name }} - Products
    </title>

    <style>
        @page {
            margin: 16px 18px;
        }

        body {
            margin: 0;

            font-family:
                DejaVu Sans,
                sans-serif;

            color: #1f2937;

            font-size: 9px;
        }


        /* =========================================================
           HEADER
           ========================================================= */

        .header {
            width: 100%;

            margin-bottom: 12px;

            border-bottom: 2px solid #1d4ed8;

            padding-bottom: 8px;
        }


        .header-table {
            width: 100%;

            border-collapse: collapse;
        }


        .header-table td {
            vertical-align: top;
        }


        .title {
            margin: 0;

            color: #172033;

            font-size: 19px;
            font-weight: bold;
        }


        .subtitle {
            margin-top: 3px;

            color: #64748b;

            font-size: 9px;
        }


        .category-code {
            display: inline-block;

            margin-top: 5px;

            padding: 3px 6px;

            border: 1px solid #bfdbfe;

            background: #eff6ff;

            color: #1d4ed8;

            font-size: 8px;
            font-weight: bold;
        }


        .report-meta {
            text-align: right;

            color: #64748b;

            font-size: 8px;

            line-height: 1.6;
        }


        /* =========================================================
           CONTEXT
           ========================================================= */

        .context-table {
            width: 100%;

            margin-bottom: 10px;

            border-collapse: collapse;
        }


        .context-table td {
            width: 33.33%;

            padding: 6px 7px;

            border: 1px solid #dbe3ec;

            background: #f8fafc;

            vertical-align: top;
        }


        .context-label {
            display: block;

            margin-bottom: 2px;

            color: #94a3b8;

            font-size: 7px;
            font-weight: bold;

            text-transform: uppercase;
        }


        .context-value {
            color: #334155;

            font-size: 9px;
            font-weight: bold;
        }


        /* =========================================================
           FILTERS
           ========================================================= */

        .filters {
            margin-bottom: 9px;

            padding: 6px 7px;

            border: 1px solid #dbe3ec;

            background: #ffffff;
        }


        .filters strong {
            color: #334155;

            font-size: 8px;
        }


        .filter-item {
            display: inline-block;

            margin-right: 12px;

            color: #64748b;

            font-size: 8px;
        }


        /* =========================================================
           SUMMARY
           ========================================================= */

        .summary-table {
            width: 100%;

            margin-bottom: 10px;

            border-collapse: separate;

            border-spacing: 5px 0;
        }


        .summary-table td {
            padding: 6px 8px;

            border: 1px solid #dbe3ec;

            background: #f8fafc;
        }


        .summary-label {
            display: block;

            color: #64748b;

            font-size: 7px;
        }


        .summary-value {
            display: block;

            margin-top: 2px;

            color: #172033;

            font-size: 11px;
            font-weight: bold;
        }


        /* =========================================================
           PRODUCT TABLE
           ========================================================= */

        .product-table {
            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;
        }


        .product-table thead {
            display: table-header-group;
        }


        .product-table th {
            padding: 5px 4px;

            border: 1px solid #cbd5e1;

            background: #eaf1fb;

            color: #334155;

            font-size: 7px;
            font-weight: bold;

            text-align: left;
        }


        .product-table td {
            padding: 5px 4px;

            border: 1px solid #e2e8f0;

            color: #334155;

            font-size: 7px;

            vertical-align: top;

            word-wrap: break-word;
        }


        .product-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }


        .text-right {
            text-align: right !important;
        }


        .text-center {
            text-align: center !important;
        }


        .product-name {
            color: #172033;

            font-weight: bold;
        }


        .muted {
            color: #94a3b8;
        }


        .active {
            color: #047857;

            font-weight: bold;
        }


        .inactive {
            color: #64748b;

            font-weight: bold;
        }


        /* =========================================================
           COLUMN WIDTHS
           ========================================================= */

        .col-no {
            width: 3%;
        }

        .col-code {
            width: 7%;
        }

        .col-product {
            width: 14%;
        }

        .col-brand {
            width: 8%;
        }

        .col-part {
            width: 8%;
        }

        .col-unit {
            width: 5%;
        }

        .col-hsn {
            width: 6%;
        }

        .col-gst {
            width: 5%;
        }

        .col-price {
            width: 7%;
        }

        .col-stock {
            width: 6%;
        }

        .col-status {
            width: 6%;
        }


        /* =========================================================
           EMPTY
           ========================================================= */

        .empty {
            padding: 24px;

            border: 1px solid #dbe3ec;

            color: #64748b;

            text-align: center;

            font-size: 10px;
        }


        /* =========================================================
           FOOTER
           ========================================================= */

        .footer {
            margin-top: 10px;

            padding-top: 6px;

            border-top: 1px solid #dbe3ec;

            color: #94a3b8;

            font-size: 7px;

            text-align: right;
        }
    </style>
</head>

<body>

    {{-- =========================================================
        HEADER
       ========================================================= --}}

    <div class="header">

        <table class="header-table">

            <tr>

                <td>

                    <h1 class="title">
                        Category Products
                    </h1>


                    <div class="subtitle">

                        Product list for category

                        <strong>
                            {{ $category->name }}
                        </strong>

                    </div>


                    @if($category->code)

                    <span class="category-code">
                        {{ $category->code }}
                    </span>

                    @endif

                </td>


                <td class="report-meta">

                    <div>
                        Generated:
                        {{ now()->format('d M Y h:i A') }}
                    </div>

                    <div>
                        Category Status:
                        {{ $category->status ? 'Active' : 'Inactive' }}
                    </div>

                </td>

            </tr>

        </table>

    </div>


    {{-- =========================================================
        BUSINESS CONTEXT
       ========================================================= --}}

    <table class="context-table">

        <tr>

            <td>

                <span class="context-label">
                    Company
                </span>

                <span class="context-value">
                    {{ session('active_company_name') ?? 'Active Company' }}
                </span>

            </td>


            <td>

                <span class="context-label">
                    Branch
                </span>

                <span class="context-value">
                    {{ session('active_store_name') ?? 'Active Branch' }}
                </span>

            </td>


            <td>

                <span class="context-label">
                    Category
                </span>

                <span class="context-value">
                    {{ $category->name }}
                </span>

            </td>

        </tr>

    </table>


    {{-- =========================================================
        FILTERS
       ========================================================= --}}

    <div class="filters">

        <strong>
            Applied Filters:
        </strong>


        <span class="filter-item">

            Search:

            {{ !empty($filters['search'])
                ? $filters['search']
                : 'All'
            }}

        </span>


        <span class="filter-item">

            Status:

            @if(
            isset($filters['status'])
            &&
            $filters['status'] !== ''
            )

            {{ (int) $filters['status'] === 1
                    ? 'Active'
                    : 'Inactive'
                }}

            @else

            All

            @endif

        </span>


        <span class="filter-item">

            Brand:

            @if(
            isset($filters['brand_id'])
            &&
            $filters['brand_id'] !== ''
            )

            #{{ $filters['brand_id'] }}

            @else

            All Brands

            @endif

        </span>

    </div>


    {{-- =========================================================
        SUMMARY
       ========================================================= --}}

    @php

    $totalProducts =
    $products->count();


    $activeProducts =
    $products
    ->where(
    'status',
    true
    )
    ->count();


    $inactiveProducts =
    $products
    ->where(
    'status',
    false
    )
    ->count();


    $totalPurchaseValue =
    $products->sum(
    function ($product) {

    return
    ((float) $product->opening_stock)
    *
    ((float) $product->purchase_price);
    }
    );

    @endphp


    <table class="summary-table">

        <tr>

            <td>

                <span class="summary-label">
                    Products
                </span>

                <span class="summary-value">
                    {{ $totalProducts }}
                </span>

            </td>


            <td>

                <span class="summary-label">
                    Active
                </span>

                <span class="summary-value">
                    {{ $activeProducts }}
                </span>

            </td>


            <td>

                <span class="summary-label">
                    Inactive
                </span>

                <span class="summary-value">
                    {{ $inactiveProducts }}
                </span>

            </td>


            <td>

                <span class="summary-label">
                    Opening Stock Value
                </span>

                <span class="summary-value">
                    ₹{{ number_format(
                        $totalPurchaseValue,
                        2
                    ) }}
                </span>

            </td>

        </tr>

    </table>


    {{-- =========================================================
        PRODUCT TABLE
       ========================================================= --}}

    @if(
    $products->count() > 0
    )

    <table class="product-table">

        <thead>

            <tr>

                <th class="col-no text-center">
                    #
                </th>

                <th class="col-code">
                    Code
                </th>

                <th class="col-product">
                    Product
                </th>

                <th class="col-brand">
                    Brand
                </th>

                <th class="col-part">
                    Part No.
                </th>

                <th class="col-unit">
                    Unit
                </th>

                <th class="col-hsn">
                    HSN
                </th>

                <th class="col-gst text-right">
                    GST
                </th>

                <th class="col-price text-right">
                    Purchase
                </th>

                <th class="col-price text-right">
                    Selling
                </th>

                <th class="col-price text-right">
                    MRP
                </th>

                <th class="col-stock text-right">
                    Opening
                </th>

                <th class="col-stock text-right">
                    Min
                </th>

                <th class="col-status text-center">
                    Status
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach(
            $products as $index => $product
            )

            <tr>

                <td class="text-center">
                    {{ $index + 1 }}
                </td>


                <td>

                    {{ $product->product_code ?? '—' }}

                </td>


                <td>

                    <div class="product-name">

                        {{ $product->product_name }}

                    </div>


                    @if($product->manufacturer)

                    <div class="muted">
                        {{ $product->manufacturer }}
                    </div>

                    @endif

                </td>


                <td>

                    {{ $product->brand?->name ?? '—' }}

                </td>


                <td>

                    {{ $product->part_number ?? '—' }}

                </td>


                <td>

                    {{ $product->unit?->name ?? '—' }}

                </td>


                <td>

                    {{ $product->hsn_code ?? '—' }}

                </td>


                <td class="text-right">

                    {{ number_format(
                                (float) (
                                    $product->gst_percent
                                    ?? 0
                                ),
                                2
                            ) }}%

                </td>


                <td class="text-right">

                    ₹{{ number_format(
                                (float) (
                                    $product->purchase_price
                                    ?? 0
                                ),
                                2
                            ) }}

                </td>


                <td class="text-right">

                    ₹{{ number_format(
                                (float) (
                                    $product->selling_price
                                    ?? 0
                                ),
                                2
                            ) }}

                </td>


                <td class="text-right">

                    ₹{{ number_format(
                                (float) (
                                    $product->mrp
                                    ?? 0
                                ),
                                2
                            ) }}

                </td>


                <td class="text-right">

                    {{ number_format(
                                (float) (
                                    $product->opening_stock
                                    ?? 0
                                ),
                                2
                            ) }}

                </td>


                <td class="text-right">

                    {{ number_format(
                                (float) (
                                    $product->minimum_stock
                                    ?? 0
                                ),
                                2
                            ) }}

                </td>


                <td class="text-center">

                    @if($product->status)

                    <span class="active">
                        Active
                    </span>

                    @else

                    <span class="inactive">
                        Inactive
                    </span>

                    @endif

                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

    @else

    <div class="empty">

        No products found for the selected category and filters.

    </div>

    @endif


    {{-- =========================================================
        FOOTER
       ========================================================= --}}

    <div class="footer">

        RR ERP ·
        {{ session('active_company_name') ?? 'Company' }}
        ·
        {{ session('active_store_name') ?? 'Branch' }}

    </div>

</body>

</html>