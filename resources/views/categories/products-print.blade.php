<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        {{ $category->name }} - Product Report
    </title>


    <style>
        /*
        |--------------------------------------------------------------------------
        | Base
        |--------------------------------------------------------------------------
        */

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;

            background: #e9edf3;

            color: #1f2937;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12px;
        }


        .report-shell {
            width: 100%;

            max-width: 1500px;

            margin: 20px auto;

            padding: 0 18px;
        }


        .report-paper {
            overflow: hidden;

            border: 1px solid #cbd5e1;

            background: #ffffff;

            box-shadow:
                0 8px 30px rgba(15,
                    23,
                    42,
                    .12);
        }


        /*
        |--------------------------------------------------------------------------
        | Toolbar
        |--------------------------------------------------------------------------
        */

        .report-toolbar {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 12px;

            padding: 10px 14px;

            border-bottom:
                1px solid #dbe3ec;

            background:
                linear-gradient(180deg,
                    #ffffff,
                    #f8fafc);
        }


        .report-toolbar-left {
            display: flex;

            align-items: center;

            gap: 8px;
        }


        .report-toolbar-title {
            color: #334155;

            font-size: 13px;

            font-weight: 700;
        }


        .report-toolbar-actions {
            display: flex;

            align-items: center;

            gap: 7px;
        }


        .report-btn {
            min-height: 34px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 0 13px;

            border: 1px solid #cbd5e1;

            border-radius: 5px;

            background: #ffffff;

            color: #334155;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;
        }


        .report-btn-primary {
            border-color: #1d4ed8;

            background: #2563eb;

            color: #ffffff;
        }


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        .report-header {
            padding: 18px 20px 14px;

            border-bottom:
                2px solid #2563eb;
        }


        .report-header-grid {
            width: 100%;

            display: flex;

            justify-content: space-between;

            gap: 20px;
        }


        .report-company {
            min-width: 0;
        }


        .report-company-name {
            margin: 0;

            color: #172033;

            font-size: 23px;

            font-weight: 800;
        }


        .report-company-meta {
            margin-top: 4px;

            color: #64748b;

            font-size: 12px;
        }


        .report-title-box {
            text-align: right;
        }


        .report-title {
            margin: 0;

            color: #1d4ed8;

            font-size: 19px;

            font-weight: 800;
        }


        .report-subtitle {
            margin-top: 4px;

            color: #64748b;

            font-size: 12px;
        }


        /*
        |--------------------------------------------------------------------------
        | Category Context
        |--------------------------------------------------------------------------
        */

        .context-grid {
            display: grid;

            grid-template-columns:
                repeat(4,
                    minmax(0, 1fr));

            gap: 0;

            border-bottom:
                1px solid #dbe3ec;
        }


        .context-cell {
            min-height: 58px;

            padding: 10px 14px;

            border-right:
                1px solid #e2e8f0;

            background: #f8fafc;
        }


        .context-cell:last-child {
            border-right: 0;
        }


        .context-label {
            display: block;

            margin-bottom: 3px;

            color: #94a3b8;

            font-size: 11px;

            font-weight: 700;

            text-transform: uppercase;
        }


        .context-value {
            display: block;

            overflow: hidden;

            color: #334155;

            font-size: 13px;

            font-weight: 800;

            text-overflow: ellipsis;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        .filter-strip {
            display: flex;

            flex-wrap: wrap;

            gap: 8px;

            padding: 9px 14px;

            border-bottom:
                1px solid #e2e8f0;

            background: #ffffff;
        }


        .filter-chip {
            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 4px 8px;

            border: 1px solid #dbe3ec;

            border-radius: 4px;

            background: #f8fafc;

            color: #475569;

            font-size: 11px;
        }


        .filter-chip strong {
            color: #1e293b;
        }


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        .summary-grid {
            display: grid;

            grid-template-columns:
                repeat(3,
                    minmax(0, 1fr));

            gap: 8px;

            padding: 10px 14px;

            border-bottom:
                1px solid #e2e8f0;

            background: #ffffff;
        }


        .summary-card {
            padding: 9px 11px;

            border: 1px solid #dbe3ec;

            border-radius: 5px;

            background: #f8fafc;
        }


        .summary-label {
            color: #64748b;

            font-size: 11px;
        }


        .summary-value {
            display: block;

            margin-top: 2px;

            color: #172033;

            font-size: 17px;

            font-weight: 800;
        }


        /*
        |--------------------------------------------------------------------------
        | Table
        |--------------------------------------------------------------------------
        */

        .report-table-wrap {
            padding: 12px 14px 16px;
        }


        .report-table {
            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;
        }


        .report-table thead {
            display: table-header-group;
        }


        .report-table th {
            padding: 7px 6px;

            border: 1px solid #cbd5e1;

            background: #eaf1fb;

            color: #334155;

            font-size: 11px;

            font-weight: 800;

            text-align: left;

            white-space: nowrap;
        }


        .report-table td {
            padding: 7px 6px;

            border: 1px solid #e2e8f0;

            color: #334155;

            font-size: 11px;

            vertical-align: top;
        }


        .report-table tbody tr:nth-child(even) {
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

            font-weight: 800;
        }


        .product-sub {
            display: block;

            margin-top: 2px;

            color: #94a3b8;

            font-size: 10px;
        }


        .status-active {
            color: #047857;

            font-weight: 800;
        }


        .status-inactive {
            color: #64748b;

            font-weight: 800;
        }


        /*
        |--------------------------------------------------------------------------
        | Footer
        |--------------------------------------------------------------------------
        */

        .report-footer {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 12px;

            padding: 10px 14px;

            border-top:
                1px solid #dbe3ec;

            background: #f8fafc;

            color: #64748b;

            font-size: 10px;
        }


        /*
        |--------------------------------------------------------------------------
        | Print
        |--------------------------------------------------------------------------
        */

        @page {
            size: A4 landscape;

            margin: 10mm;
        }


        @media print {

            body {
                background: #ffffff;

                print-color-adjust: exact;

                -webkit-print-color-adjust: exact;
            }


            .report-shell {
                max-width: none;

                margin: 0;

                padding: 0;
            }


            .report-paper {
                border: 0;

                box-shadow: none;
            }


            .report-toolbar {
                display: none !important;
            }


            .report-table tr {
                page-break-inside: avoid;
            }


            .report-table thead {
                display: table-header-group;
            }


            .report-footer {
                margin-top: 8px;
            }
        }
    </style>

</head>


<body>

    <div class="report-shell">

        <div class="report-paper">


            {{-- =========================================================
            TOOLBAR
           ========================================================= --}}

            <div class="report-toolbar">

                <div class="report-toolbar-left">

                    <span class="report-toolbar-title">
                        Print Preview
                    </span>

                </div>


                <div class="report-toolbar-actions">

                    <button
                        type="button"
                        class="report-btn"
                        onclick="window.close()">
                        Close
                    </button>


                    <button
                        type="button"
                        class="report-btn report-btn-primary"
                        onclick="window.print()">
                        Print
                    </button>

                </div>

            </div>


            {{-- =========================================================
            HEADER
           ========================================================= --}}

            <header class="report-header">

                <div class="report-header-grid">

                    <div class="report-company">

                        <h1 class="report-company-name">

                            {{ session('active_company_name')
                            ?? 'Company'
                        }}

                        </h1>


                        <div class="report-company-meta">

                            {{ session('active_store_name')
                            ?? 'Branch'
                        }}

                            @if(
                            session(
                            'active_store_code'
                            )
                            )

                            ·
                            {{ session(
                                'active_store_code'
                            ) }}

                            @endif

                        </div>

                    </div>


                    <div class="report-title-box">

                        <h2 class="report-title">
                            Category Product Report
                        </h2>


                        <div class="report-subtitle">

                            Generated:
                            {{ now()->format(
                            'd M Y h:i A'
                        ) }}

                        </div>

                    </div>

                </div>

            </header>


            {{-- =========================================================
            CONTEXT
           ========================================================= --}}

            <section class="context-grid">

                <div class="context-cell">

                    <span class="context-label">
                        Category Code
                    </span>

                    <span class="context-value">

                        {{ $category->code
                        ?? '—'
                    }}

                    </span>

                </div>


                <div class="context-cell">

                    <span class="context-label">
                        Category
                    </span>

                    <span class="context-value">

                        {{ $category->name }}

                    </span>

                </div>


                <div class="context-cell">

                    <span class="context-label">
                        Branch
                    </span>

                    <span class="context-value">

                        {{ session(
                        'active_store_name'
                    ) ?? 'Active Branch' }}

                    </span>

                </div>


                <div class="context-cell">

                    <span class="context-label">
                        Category Status
                    </span>

                    <span class="context-value">

                        {{ $category->status
                        ? 'Active'
                        : 'Inactive'
                    }}

                    </span>

                </div>

            </section>


            {{-- =========================================================
            FILTERS
           ========================================================= --}}

            <section class="filter-strip">

                <span class="filter-chip">

                    Search:

                    <strong>

                        {{ !empty(
                        $filters['search']
                    )
                        ? $filters['search']
                        : 'All'
                    }}

                    </strong>

                </span>


                <span class="filter-chip">

                    Status:

                    <strong>

                        @if(
                        isset(
                        $filters['status']
                        )
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

                    </strong>

                </span>


                <span class="filter-chip">

                    Brand:

                    <strong>

                        {{ $selectedBrand?->brand_name
                        ?? 'All Brands'
                    }}

                    </strong>

                </span>

            </section>


            {{-- =========================================================
            SUMMARY
           ========================================================= --}}

            <section class="summary-grid">

                <div class="summary-card">

                    <span class="summary-label">
                        Products
                    </span>

                    <span class="summary-value">
                        {{ $summary['total'] ?? 0 }}
                    </span>

                </div>


                <div class="summary-card">

                    <span class="summary-label">
                        Active
                    </span>

                    <span class="summary-value">
                        {{ $summary['active'] ?? 0 }}
                    </span>

                </div>


                <div class="summary-card">

                    <span class="summary-label">
                        Inactive
                    </span>

                    <span class="summary-value">
                        {{ $summary['inactive'] ?? 0 }}
                    </span>

                </div>

            </section>


            {{-- =========================================================
            PRODUCT TABLE
           ========================================================= --}}

            <div class="report-table-wrap">

                <table class="report-table">

                    <thead>

                        <tr>

                            <th style="width: 4%">
                                #
                            </th>

                            <th style="width: 8%">
                                Code
                            </th>

                            <th style="width: 17%">
                                Product
                            </th>

                            <th style="width: 9%">
                                Brand
                            </th>

                            <th style="width: 9%">
                                Part No.
                            </th>

                            <th style="width: 7%">
                                Unit
                            </th>

                            <th style="width: 7%">
                                HSN
                            </th>

                            <th
                                class="text-right"
                                style="width: 8%">
                                Purchase
                            </th>

                            <th
                                class="text-right"
                                style="width: 8%">
                                Selling
                            </th>

                            <th
                                class="text-right"
                                style="width: 8%">
                                MRP
                            </th>

                            <th
                                class="text-right"
                                style="width: 6%">
                                GST
                            </th>

                            <th
                                class="text-center"
                                style="width: 7%">
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse(
                        $products as $index => $product
                        )

                        <tr>

                            <td>
                                {{ $index + 1 }}
                            </td>


                            <td>
                                {{ $product->product_code ?? '—' }}
                            </td>


                            <td>

                                <span class="product-name">

                                    {{ $product->product_name }}

                                </span>


                                @if(
                                $product->manufacturer
                                )

                                <span class="product-sub">

                                    {{ $product->manufacturer }}

                                </span>

                                @endif

                            </td>


                            <td>

                                {{ $product->brand?->brand_name
                                ?? '—'
                            }}

                            </td>


                            <td>

                                {{ $product->part_number
                                ?? '—'
                            }}

                            </td>


                            <td>

                                {{ $product->unit?->short_name
                                ?? $product->unit?->unit_name
                                ?? '—'
                            }}

                            </td>


                            <td>

                                {{ $product->hsn_code
                                ?? '—'
                            }}

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
                                    $product->gst_percent
                                    ?? 0
                                ),
                                2
                            ) }}%

                            </td>


                            <td class="text-center">

                                @if(
                                $product->status
                                )

                                <span class="status-active">
                                    Active
                                </span>

                                @else

                                <span class="status-inactive">
                                    Inactive
                                </span>

                                @endif

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="12"
                                class="text-center"
                                style="padding: 28px">

                                No products found for the selected filters.

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- =========================================================
            FOOTER
           ========================================================= --}}

            <footer class="report-footer">

                <span>

                    {{ session(
                    'active_company_name'
                ) ?? 'Company' }}

                    ·

                    {{ session(
                    'active_store_name'
                ) ?? 'Branch' }}

                </span>


                <span>

                    Category Product Report

                    ·

                    {{ $products->count() }} records

                </span>

            </footer>

        </div>

    </div>

</body>

</html>