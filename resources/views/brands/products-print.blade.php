<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        {{ $brand->brand_name }} - Products
    </title>


    <style>
        /* ==============================================================
           PAGE
           ============================================================== */

        * {
            box-sizing: border-box;
        }


        html,
        body {
            margin: 0;
            padding: 0;
        }


        body {
            background: #eef1f5;

            color: #111827;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12px;
        }


        .print-page {
            width: 100%;
            max-width: 1400px;

            margin: 20px auto;

            padding: 24px 28px;

            background: #ffffff;

            box-shadow:
                0 2px 14px rgba(0, 0, 0, .08);
        }


        /* ==============================================================
           SCREEN TOOLBAR
           ============================================================== */

        .screen-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 12px;

            max-width: 1400px;

            margin: 20px auto 0;

            padding: 11px 14px;

            border: 1px solid #dbe1e8;
            border-radius: 7px;

            background: #ffffff;
        }


        .screen-toolbar-left {
            color: #475569;

            font-size: 13px;
            font-weight: 700;
        }


        .screen-toolbar-actions {
            display: flex;
            align-items: center;

            gap: 7px;
        }


        .toolbar-btn {
            min-height: 36px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 0 14px;

            border: 1px solid #cbd5e1;
            border-radius: 6px;

            background: #ffffff;

            color: #334155;

            font-size: 12px;
            font-weight: 700;

            text-decoration: none;

            cursor: pointer;
        }


        .toolbar-btn:hover {
            background: #f8fafc;
        }


        .toolbar-btn.primary {
            border-color: #1d4ed8;

            background: #1d4ed8;

            color: #ffffff;
        }


        .toolbar-btn.primary:hover {
            background: #1e40af;
        }


        /* ==============================================================
           COMPANY HEADER
           ============================================================== */

        .company-header {
            display: table;

            width: 100%;

            padding-bottom: 14px;

            border-bottom: 2px solid #1e293b;
        }


        .company-logo,
        .company-information,
        .report-information {
            display: table-cell;

            vertical-align: middle;
        }


        .company-logo {
            width: 75px;
        }


        .company-logo img {
            display: block;

            max-width: 60px;
            max-height: 60px;
        }


        .logo-placeholder {
            width: 58px;
            height: 58px;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 1px solid #cbd5e1;
            border-radius: 6px;

            color: #64748b;

            font-size: 17px;
            font-weight: bold;
        }


        .company-information {
            padding-left: 4px;
        }


        .company-information h1 {
            margin: 0 0 4px;

            color: #0f172a;

            font-size: 21px;
            font-weight: 700;
        }


        .company-information p {
            margin: 2px 0;

            color: #475569;

            font-size: 10px;
            line-height: 1.4;
        }


        .report-information {
            width: 280px;

            text-align: right;
        }


        .report-information h2 {
            margin: 0 0 6px;

            color: #0f172a;

            font-size: 17px;
            font-weight: 700;

            text-transform: uppercase;
        }


        .report-information div {
            margin-top: 3px;

            color: #64748b;

            font-size: 10px;
        }


        /* ==============================================================
           REPORT TITLE
           ============================================================== */

        .report-title {
            padding: 14px 0 12px;

            text-align: center;
        }


        .report-title h3 {
            margin: 0;

            color: #111827;

            font-size: 16px;
            font-weight: 700;
        }


        .report-title p {
            margin: 4px 0 0;

            color: #64748b;

            font-size: 10px;
        }


        /* ==============================================================
           BRAND INFORMATION
           ============================================================== */

        .brand-information {
            width: 100%;

            margin-bottom: 12px;

            border-collapse: collapse;

            border: 1px solid #cbd5e1;
        }


        .brand-information td {
            width: 25%;

            padding: 7px 9px;

            border-right: 1px solid #e2e8f0;
        }


        .brand-information td:last-child {
            border-right: 0;
        }


        .brand-information span {
            display: block;

            margin-bottom: 2px;

            color: #64748b;

            font-size: 8px;
            font-weight: 700;

            text-transform: uppercase;
        }


        .brand-information strong {
            color: #1e293b;

            font-size: 10px;
        }


        /* ==============================================================
           FILTER INFORMATION
           ============================================================== */

        .filters {
            margin-bottom: 12px;

            padding: 7px 9px;

            border: 1px solid #e2e8f0;

            background: #f8fafc;

            color: #475569;

            font-size: 9px;
        }


        .filters strong {
            color: #1e293b;
        }


        .filter-item {
            display: inline-block;

            margin-right: 18px;
        }


        /* ==============================================================
           TABLE
           ============================================================== */

        .products-table {
            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;
        }


        .products-table thead {
            display: table-header-group;
        }


        .products-table tr {
            page-break-inside: avoid;
        }


        .products-table th {
            padding: 6px 5px;

            border: 1px solid #94a3b8;

            background: #e8edf3;

            color: #1e293b;

            font-size: 8px;
            font-weight: 700;

            text-align: left;

            text-transform: uppercase;
        }


        .products-table td {
            padding: 6px 5px;

            border: 1px solid #cbd5e1;

            color: #334155;

            font-size: 8px;

            line-height: 1.35;

            vertical-align: top;

            word-wrap: break-word;
        }


        .products-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }


        .products-table .serial {
            width: 3%;

            text-align: center;
        }


        .products-table .code {
            width: 10%;
        }


        .products-table .product {
            width: 19%;
        }


        .products-table .category {
            width: 12%;
        }


        .products-table .part {
            width: 10%;
        }


        .products-table .unit {
            width: 6%;
        }


        .products-table .money {
            width: 9%;

            text-align: right;
        }


        .products-table .gst {
            width: 6%;

            text-align: right;
        }


        .products-table .status {
            width: 7%;

            text-align: center;
        }


        .product-name {
            color: #111827;

            font-weight: 700;
        }


        .product-sub {
            display: block;

            margin-top: 2px;

            color: #64748b;

            font-size: 7px;
        }


        .status-active {
            color: #047857;

            font-weight: 700;
        }


        .status-inactive {
            color: #64748b;

            font-weight: 700;
        }


        /* ==============================================================
           EMPTY
           ============================================================== */

        .empty-row td {
            padding: 40px 10px;

            color: #64748b;

            text-align: center;
        }


        /* ==============================================================
           SUMMARY
           ============================================================== */

        .summary {
            display: table;

            width: 100%;

            margin-top: 12px;

            border: 1px solid #cbd5e1;
        }


        .summary-item {
            display: table-cell;

            width: 33.333%;

            padding: 8px 10px;

            border-right: 1px solid #e2e8f0;

            text-align: center;
        }


        .summary-item:last-child {
            border-right: 0;
        }


        .summary-item span {
            display: block;

            margin-bottom: 2px;

            color: #64748b;

            font-size: 8px;

            text-transform: uppercase;
        }


        .summary-item strong {
            color: #111827;

            font-size: 13px;
        }


        /* ==============================================================
           FOOTER
           ============================================================== */

        .report-footer {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;

            gap: 20px;

            margin-top: 28px;

            padding-top: 10px;

            border-top: 1px solid #cbd5e1;

            color: #64748b;

            font-size: 8px;
        }


        .report-footer-right {
            text-align: right;
        }


        /* ==============================================================
           PRINT
           ============================================================== */

        @page {
            size: A4 landscape;

            margin: 10mm;
        }


        @media print {

            html,
            body {
                background: #ffffff;
            }


            .no-print {
                display: none !important;
            }


            .print-page {
                width: 100%;
                max-width: none;

                margin: 0;
                padding: 0;

                box-shadow: none;
            }


            .products-table th {
                background: #e8edf3 !important;

                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }


            .products-table tbody tr:nth-child(even) {
                background: #f8fafc !important;

                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

        }
    </style>

</head>


<body>


    {{-- ================================================================
         SCREEN TOOLBAR
       ================================================================ --}}

    <div class="screen-toolbar no-print">

        <div class="screen-toolbar-left">

            Brand Product Report Preview

        </div>


        <div class="screen-toolbar-actions">

            <button
                type="button"
                class="toolbar-btn"
                onclick="window.close()">
                Close
            </button>


            <button
                type="button"
                class="toolbar-btn primary"
                onclick="window.print()">
                Print
            </button>

        </div>

    </div>


    <main class="print-page">


        {{-- ============================================================
             COMPANY HEADER
           ============================================================ --}}

        <header class="company-header">


            <div class="company-logo">

                @if(
                isset($company)
                &&
                $company->logo
                )

                <img
                    src="{{ asset('storage/' . $company->logo) }}"
                    alt="Logo">

                @else

                <div class="logo-placeholder">

                    {{
                            strtoupper(
                                substr(
                                    $company->company_name ?? 'RR',
                                    0,
                                    2
                                )
                            )
                        }}

                </div>

                @endif

            </div>


            <div class="company-information">

                <h1>
                    {{ $company->company_name ?? 'Company' }}
                </h1>


                @if(
                !empty($company->address)
                ||
                !empty($company->city)
                ||
                !empty($company->state)
                )

                <p>

                    {{ $company->address ?? '' }}

                    @if($company->city)
                    {{ $company->address ? ', ' : '' }}
                    {{ $company->city }}
                    @endif

                    @if($company->state)
                    , {{ $company->state }}
                    @endif

                    @if($company->pincode)
                    - {{ $company->pincode }}
                    @endif

                </p>

                @endif


                <p>

                    @if($company->phone ?? null)
                    Phone:
                    {{ $company->phone }}
                    @endif


                    @if($company->email ?? null)

                    @if($company->phone ?? null)
                    &nbsp; | &nbsp;
                    @endif

                    Email:
                    {{ $company->email }}

                    @endif

                </p>


                @if($company->gst_number ?? null)

                <p>
                    GSTIN:
                    <strong>
                        {{ $company->gst_number }}
                    </strong>
                </p>

                @endif

            </div>


            <div class="report-information">

                <h2>
                    Product Report
                </h2>


                <div>
                    Report:
                    <strong>
                        Brand Products
                    </strong>
                </div>


                @if(isset($store))

                <div>
                    Branch:
                    <strong>
                        {{ $store->store_name }}
                    </strong>
                </div>

                @endif


                <div>
                    Printed:
                    <strong>
                        {{ now()->format('d M Y, h:i A') }}
                    </strong>
                </div>

            </div>

        </header>


        {{-- ============================================================
             REPORT TITLE
           ============================================================ --}}

        <section class="report-title">

            <h3>
                {{ $brand->brand_name }} Products
            </h3>


            <p>
                Complete product list for brand
                {{ $brand->brand_code }}
            </p>

        </section>


        {{-- ============================================================
             BRAND INFORMATION
           ============================================================ --}}

        <table class="brand-information">

            <tr>

                <td>

                    <span>
                        Brand Name
                    </span>

                    <strong>
                        {{ $brand->brand_name }}
                    </strong>

                </td>


                <td>

                    <span>
                        Brand Code
                    </span>

                    <strong>
                        {{ $brand->brand_code }}
                    </strong>

                </td>


                <td>

                    <span>
                        Brand Status
                    </span>

                    <strong>
                        {{
                            $brand->status
                                ? 'Active'
                                : 'Inactive'
                        }}
                    </strong>

                </td>


                <td>

                    <span>
                        Total Products
                    </span>

                    <strong>
                        {{ $products->count() }}
                    </strong>

                </td>

            </tr>

        </table>


        {{-- ============================================================
             FILTERS
           ============================================================ --}}

        @php

        $search =
        $filters['search']
        ?? '';

        $status =
        $filters['status']
        ?? '';

        $categoryId =
        $filters['category_id']
        ?? '';

        @endphp


        @if(
        $search !== ''
        ||
        $status !== ''
        ||
        $categoryId !== ''
        )

        <div class="filters">

            <strong>
                Applied Filters:
            </strong>


            @if($search !== '')

            <span class="filter-item">

                Search:
                <strong>
                    {{ $search }}
                </strong>

            </span>

            @endif


            @if($status !== '')

            <span class="filter-item">

                Status:
                <strong>

                    {{
                                (string) $status === '1'
                                    ? 'Active'
                                    : 'Inactive'
                            }}

                </strong>

            </span>

            @endif


            @if(isset($selectedCategory))

            <span class="filter-item">

                Category:
                <strong>
                    {{ $selectedCategory->name }}
                </strong>

            </span>

            @endif

        </div>

        @endif


        {{-- ============================================================
             PRODUCTS
           ============================================================ --}}

        <table class="products-table">

            <thead>

                <tr>

                    <th class="serial">
                        #
                    </th>

                    <th class="code">
                        Code
                    </th>

                    <th class="product">
                        Product
                    </th>

                    <th class="category">
                        Category
                    </th>

                    <th class="part">
                        Part No.
                    </th>

                    <th class="unit">
                        Unit
                    </th>

                    <th class="money">
                        Purchase
                    </th>

                    <th class="money">
                        Selling
                    </th>

                    <th class="money">
                        MRP
                    </th>

                    <th class="gst">
                        GST
                    </th>

                    <th class="status">
                        Status
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($products as $index => $product)

                <tr>

                    <td class="serial">

                        {{ $index + 1 }}

                    </td>


                    <td>

                        {{ $product->product_code ?: '—' }}

                    </td>


                    <td>

                        <span class="product-name">

                            {{ $product->product_name }}

                        </span>


                        @if($product->manufacturer)

                        <span class="product-sub">

                            {{ $product->manufacturer }}

                        </span>

                        @endif

                    </td>


                    <td>

                        {{
                                $product->category?->name
                                ?? '—'
                            }}

                    </td>


                    <td>

                        {{ $product->part_number ?: '—' }}

                    </td>


                    <td>

                        {{
                                $product->unit?->short_name
                                ??
                                $product->unit?->unit_name
                                ??
                                '—'
                            }}

                    </td>


                    <td class="money">

                        ₹{{ number_format(
                                (float) $product->purchase_price,
                                2
                            ) }}

                    </td>


                    <td class="money">

                        <strong>

                            ₹{{ number_format(
                                    (float) $product->selling_price,
                                    2
                                ) }}

                        </strong>

                    </td>


                    <td class="money">

                        ₹{{ number_format(
                                (float) $product->mrp,
                                2
                            ) }}

                    </td>


                    <td class="gst">

                        {{
                                number_format(
                                    (float) $product->gst_percent,
                                    2
                                )
                            }}%

                    </td>


                    <td class="status">

                        <span
                            class="{{
                                    $product->status
                                        ? 'status-active'
                                        : 'status-inactive'
                                }}">

                            {{
                                    $product->status
                                        ? 'Active'
                                        : 'Inactive'
                                }}

                        </span>

                    </td>

                </tr>

                @empty

                <tr class="empty-row">

                    <td colspan="11">

                        No products found for this brand.

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>


        {{-- ============================================================
             SUMMARY
           ============================================================ --}}

        @php

        $activeCount =
        $products
        ->where(
        'status',
        true
        )
        ->count();


        $inactiveCount =
        $products
        ->where(
        'status',
        false
        )
        ->count();

        @endphp


        <section class="summary">

            <div class="summary-item">

                <span>
                    Total Products
                </span>

                <strong>
                    {{ $products->count() }}
                </strong>

            </div>


            <div class="summary-item">

                <span>
                    Active
                </span>

                <strong>
                    {{ $activeCount }}
                </strong>

            </div>


            <div class="summary-item">

                <span>
                    Inactive
                </span>

                <strong>
                    {{ $inactiveCount }}
                </strong>

            </div>

        </section>


        {{-- ============================================================
             FOOTER
           ============================================================ --}}

        <footer class="report-footer">

            <div>

                Generated by RR ERP

                @if(isset($store))

                <br>

                {{ $store->store_name }}

                @endif

            </div>


            <div class="report-footer-right">

                Brand:
                {{ $brand->brand_name }}

                <br>

                {{ now()->format('d-m-Y h:i A') }}

            </div>

        </footer>


    </main>


</body>

</html>