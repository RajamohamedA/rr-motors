<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        {{ $brand->brand_name }} - Products PDF
    </title>


    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }


        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;

            color: #111827;

            font-family:
                DejaVu Sans,
                sans-serif;

            font-size: 9px;
        }


        .page {
            width: 100%;
        }


        /* =========================================================
           HEADER
           ========================================================= */

        .header-table {
            width: 100%;

            margin-bottom: 10px;

            border-collapse: collapse;

            border-bottom: 2px solid #1e293b;
        }


        .header-table td {
            padding-bottom: 10px;

            vertical-align: middle;
        }


        .logo-cell {
            width: 70px;
        }


        .logo {
            max-width: 55px;
            max-height: 55px;
        }


        .logo-placeholder {
            width: 52px;
            height: 52px;

            border: 1px solid #cbd5e1;

            color: #475569;

            font-size: 16px;
            font-weight: bold;

            text-align: center;
            line-height: 52px;
        }


        .company-cell h1 {
            margin: 0 0 3px;

            color: #0f172a;

            font-size: 17px;
        }


        .company-cell p {
            margin: 1px 0;

            color: #475569;

            font-size: 8px;
            line-height: 1.35;
        }


        .report-cell {
            width: 220px;

            text-align: right;
        }


        .report-cell h2 {
            margin: 0 0 4px;

            color: #0f172a;

            font-size: 14px;

            text-transform: uppercase;
        }


        .report-cell div {
            margin-top: 2px;

            color: #64748b;

            font-size: 8px;
        }


        /* =========================================================
           TITLE
           ========================================================= */

        .report-title {
            margin-bottom: 9px;

            text-align: center;
        }


        .report-title h3 {
            margin: 0;

            color: #111827;

            font-size: 13px;
        }


        .report-title p {
            margin: 3px 0 0;

            color: #64748b;

            font-size: 8px;
        }


        /* =========================================================
           BRAND INFO
           ========================================================= */

        .brand-info {
            width: 100%;

            margin-bottom: 8px;

            border-collapse: collapse;
        }


        .brand-info td {
            width: 25%;

            padding: 6px 7px;

            border: 1px solid #cbd5e1;

            background: #f8fafc;
        }


        .brand-info span {
            display: block;

            margin-bottom: 2px;

            color: #64748b;

            font-size: 7px;
            font-weight: bold;

            text-transform: uppercase;
        }


        .brand-info strong {
            color: #1e293b;

            font-size: 9px;
        }


        /* =========================================================
           FILTERS
           ========================================================= */

        .filters {
            margin-bottom: 8px;

            padding: 5px 7px;

            border: 1px solid #e2e8f0;

            background: #f8fafc;

            color: #475569;

            font-size: 7px;
        }


        .filters strong {
            color: #1e293b;
        }


        .filter-item {
            margin-right: 14px;
        }


        /* =========================================================
           PRODUCTS
           ========================================================= */

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
            padding: 5px 4px;

            border: 1px solid #94a3b8;

            background: #e8edf3;

            color: #1e293b;

            font-size: 7px;
            font-weight: bold;

            text-align: left;

            text-transform: uppercase;
        }


        .products-table td {
            padding: 5px 4px;

            border: 1px solid #cbd5e1;

            color: #334155;

            font-size: 7px;

            vertical-align: top;
        }


        .serial {
            width: 3%;

            text-align: center;
        }


        .code {
            width: 10%;
        }


        .product {
            width: 19%;
        }


        .category {
            width: 12%;
        }


        .part {
            width: 10%;
        }


        .unit {
            width: 6%;
        }


        .money {
            width: 9%;

            text-align: right;
        }


        .gst {
            width: 6%;

            text-align: right;
        }


        .status {
            width: 7%;

            text-align: center;
        }


        .product-name {
            color: #111827;

            font-weight: bold;
        }


        .product-sub {
            display: block;

            margin-top: 1px;

            color: #64748b;

            font-size: 6px;
        }


        .status-active {
            color: #047857;

            font-weight: bold;
        }


        .status-inactive {
            color: #64748b;

            font-weight: bold;
        }


        /* =========================================================
           SUMMARY
           ========================================================= */

        .summary {
            width: 100%;

            margin-top: 8px;

            border-collapse: collapse;
        }


        .summary td {
            width: 33.333%;

            padding: 6px 8px;

            border: 1px solid #cbd5e1;

            text-align: center;
        }


        .summary span {
            display: block;

            color: #64748b;

            font-size: 7px;

            text-transform: uppercase;
        }


        .summary strong {
            color: #111827;

            font-size: 11px;
        }


        /* =========================================================
           FOOTER
           ========================================================= */

        .footer {
            width: 100%;

            margin-top: 14px;

            border-top: 1px solid #cbd5e1;

            color: #64748b;

            font-size: 7px;
        }


        .footer td {
            padding-top: 6px;
        }


        .footer-right {
            text-align: right;
        }
    </style>

</head>


<body>

    <div class="page">


        {{-- ============================================================
         HEADER
       ============================================================ --}}

        <table class="header-table">

            <tr>

                <td class="logo-cell">

                    @php
                    $logoPath = null;

                    if (
                    isset($company)
                    &&
                    $company->logo
                    ) {
                    $candidate =
                    storage_path(
                    'app/public/' . $company->logo
                    );

                    if (
                    file_exists(
                    $candidate
                    )
                    ) {
                    $logoPath =
                    $candidate;
                    }
                    }
                    @endphp


                    @if ($logoPath)

                    <img
                        src="{{ $logoPath }}"
                        class="logo"
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

                </td>


                <td class="company-cell">

                    <h1>
                        {{ $company->company_name ?? 'Company' }}
                    </h1>


                    @if (
                    !empty($company->address)
                    ||
                    !empty($company->city)
                    ||
                    !empty($company->state)
                    )

                    <p>

                        {{ $company->address ?? '' }}

                        @if ($company->city)
                        {{ $company->address ? ', ' : '' }}
                        {{ $company->city }}
                        @endif

                        @if ($company->state)
                        , {{ $company->state }}
                        @endif

                        @if ($company->pincode)
                        - {{ $company->pincode }}
                        @endif

                    </p>

                    @endif


                    @if ($company->phone ?? null)

                    <p>
                        Phone:
                        {{ $company->phone }}
                    </p>

                    @endif


                    @if ($company->gst_number ?? null)

                    <p>
                        GSTIN:
                        <strong>
                            {{ $company->gst_number }}
                        </strong>
                    </p>

                    @endif

                </td>


                <td class="report-cell">

                    <h2>
                        Product Report
                    </h2>


                    <div>
                        Brand Products
                    </div>


                    @if (isset($store))

                    <div>
                        Branch:
                        <strong>
                            {{ $store->store_name }}
                        </strong>
                    </div>

                    @endif


                    <div>
                        Generated:
                        <strong>
                            {{ now()->format('d M Y, h:i A') }}
                        </strong>
                    </div>

                </td>

            </tr>

        </table>


        {{-- ============================================================
         TITLE
       ============================================================ --}}

        <div class="report-title">

            <h3>
                {{ $brand->brand_name }} Products
            </h3>

            <p>
                Brand Code:
                {{ $brand->brand_code }}
            </p>

        </div>


        {{-- ============================================================
         BRAND INFO
       ============================================================ --}}

        <table class="brand-info">

            <tr>

                <td>

                    <span>
                        Brand
                    </span>

                    <strong>
                        {{ $brand->brand_name }}
                    </strong>

                </td>


                <td>

                    <span>
                        Code
                    </span>

                    <strong>
                        {{ $brand->brand_code }}
                    </strong>

                </td>


                <td>

                    <span>
                        Status
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
                        Products
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


        @if (
        $search !== ''
        ||
        $status !== ''
        ||
        $categoryId !== ''
        )

        <div class="filters">

            <strong>
                Filters:
            </strong>

            @if ($search !== '')

            <span class="filter-item">
                Search:
                <strong>
                    {{ $search }}
                </strong>
            </span>

            @endif


            @if ($status !== '')

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


            @if (isset($selectedCategory))

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


                        @if ($product->manufacturer)

                        <span class="product-sub">
                            {{ $product->manufacturer }}
                        </span>

                        @endif

                    </td>


                    <td>
                        {{ $product->category?->name ?? '—' }}
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
                        {{ number_format((float) $product->purchase_price, 2) }}
                    </td>


                    <td class="money">
                        <strong>
                            {{ number_format((float) $product->selling_price, 2) }}
                        </strong>
                    </td>


                    <td class="money">
                        {{ number_format((float) $product->mrp, 2) }}
                    </td>


                    <td class="gst">
                        {{ number_format((float) $product->gst_percent, 2) }}%
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

                <tr>

                    <td
                        colspan="11"
                        style="padding: 28px; text-align: center; color: #64748b;">
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


        <table class="summary">

            <tr>

                <td>

                    <span>
                        Total Products
                    </span>

                    <strong>
                        {{ $products->count() }}
                    </strong>

                </td>


                <td>

                    <span>
                        Active
                    </span>

                    <strong>
                        {{ $activeCount }}
                    </strong>

                </td>


                <td>

                    <span>
                        Inactive
                    </span>

                    <strong>
                        {{ $inactiveCount }}
                    </strong>

                </td>

            </tr>

        </table>


        {{-- ============================================================
         FOOTER
       ============================================================ --}}

        <table class="footer">

            <tr>

                <td>
                    Generated by RR ERP
                </td>


                <td class="footer-right">

                    {{ $brand->brand_name }}

                    @if (isset($store))
                    · {{ $store->store_name }}
                    @endif

                </td>

            </tr>

        </table>


    </div>

</body>

</html>