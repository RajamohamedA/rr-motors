<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Purchase Return {{ $purchaseReturn->return_no }}
    </title>


    <style>
        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            padding: 20px;

            background: #eef2f7;

            color: #111827;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12px;

            line-height: 1.4;
        }


        .print-toolbar {
            max-width: 1180px;

            margin:
                0 auto 12px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 10px;

            padding: 10px 12px;

            border: 1px solid #d1d5db;
            border-radius: 8px;

            background: #ffffff;
        }


        .print-toolbar strong {
            color: #111827;

            font-size: 14px;
        }


        .print-toolbar-actions {
            display: flex;

            gap: 8px;
        }


        .print-btn {
            min-height: 34px;

            padding: 0 12px;

            border: 1px solid #cbd5e1;
            border-radius: 5px;

            background: #ffffff;

            color: #334155;

            font-size: 12px;
            font-weight: 700;

            cursor: pointer;
        }


        .print-btn.primary {
            border-color: #2563eb;

            background: #2563eb;

            color: #ffffff;
        }


        .document {
            max-width: 1180px;

            margin: 0 auto;

            padding: 22px;

            border: 1px solid #d1d5db;

            background: #ffffff;

            box-shadow:
                0 6px 20px rgba(15, 23, 42, 0.08);
        }


        /* =========================================================
           HEADER
           ========================================================= */

        .header-table {
            width: 100%;

            border-collapse: collapse;
        }


        .header-table td {
            vertical-align: top;
        }


        .company-name {
            margin: 0;

            color: #111827;

            font-size: 22px;

            font-weight: 800;
        }


        .company-line {
            margin-top: 3px;

            color: #4b5563;

            font-size: 11px;
        }


        .document-heading {
            text-align: right;
        }


        .document-title {
            margin: 0;

            color: #b91c1c;

            font-size: 22px;

            font-weight: 900;
        }


        .document-number {
            margin-top: 4px;

            color: #111827;

            font-size: 14px;

            font-weight: 800;
        }


        .document-date {
            margin-top: 3px;

            color: #64748b;

            font-size: 11px;
        }


        .header-rule {
            margin:
                12px 0 14px;

            height: 2px;

            background: #b91c1c;
        }


        /* =========================================================
           INFO BOXES
           ========================================================= */

        .info-layout {
            width: 100%;

            margin-bottom: 14px;

            border-collapse: collapse;
        }


        .info-layout>tbody>tr>td {
            vertical-align: top;
        }


        .info-gap {
            width: 10px;
        }


        .info-box {
            border: 1px solid #cbd5e1;
        }


        .info-title {
            padding: 7px 9px;

            border-bottom: 1px solid #cbd5e1;

            background: #f1f5f9;

            color: #1e293b;

            font-size: 11px;

            font-weight: 800;

            text-transform: uppercase;
        }


        .info-body {
            padding: 8px 9px;
        }


        .info-row {
            width: 100%;

            border-collapse: collapse;
        }


        .info-row td {
            padding: 3px 0;
        }


        .info-label {
            width: 42%;

            color: #64748b;
        }


        .info-value {
            width: 58%;

            color: #111827;

            font-weight: 700;

            text-align: right;
        }


        .gst-badge,
        .non-gst-badge {
            display: inline-block;

            padding: 3px 6px;

            border-radius: 4px;

            font-size: 10px;

            font-weight: 800;
        }


        .gst-badge {
            background: #dcfce7;

            color: #166534;
        }


        .non-gst-badge {
            background: #e5e7eb;

            color: #475569;
        }


        /* =========================================================
           ITEMS
           ========================================================= */

        .items-table {
            width: 100%;

            margin-bottom: 14px;

            border-collapse: collapse;
        }


        .items-table th {
            padding: 7px 6px;

            border: 1px solid #9ca3af;

            background: #e5e7eb;

            color: #1f2937;

            font-size: 10px;

            font-weight: 800;

            text-align: left;

            text-transform: uppercase;
        }


        .items-table td {
            padding: 7px 6px;

            border: 1px solid #d1d5db;

            color: #374151;

            font-size: 10px;

            vertical-align: middle;
        }


        .items-table tbody tr:nth-child(even) td {
            background: #fafafa;
        }


        .center {
            text-align: center;
        }


        .number {
            text-align: right;

            white-space: nowrap;
        }


        .product-name {
            color: #111827;

            font-weight: 800;
        }


        .product-meta {
            margin-top: 2px;

            color: #6b7280;

            font-size: 9px;
        }


        .location-shop {
            color: #1d4ed8;

            font-weight: 700;
        }


        .location-godown {
            color: #6d28d9;

            font-weight: 700;
        }


        .return-qty {
            color: #b45309;

            font-weight: 800;
        }


        .gst-value {
            color: #166534;

            font-weight: 700;
        }


        .line-total {
            color: #b91c1c;

            font-weight: 800;
        }


        /* =========================================================
           SUMMARY
           ========================================================= */

        .summary-layout {
            width: 100%;

            border-collapse: collapse;
        }


        .summary-layout td {
            vertical-align: top;
        }


        .summary-spacer {
            width: 57%;
        }


        .summary-table {
            width: 100%;

            border-collapse: collapse;

            border: 1px solid #cbd5e1;
        }


        .summary-table td {
            padding: 7px 9px;

            border-bottom: 1px solid #e5e7eb;

            font-size: 11px;
        }


        .summary-label {
            color: #4b5563;
        }


        .summary-value {
            color: #111827;

            font-weight: 800;

            text-align: right;
        }


        .grand-total td {
            border-top: 2px solid #b91c1c;

            background: #fff1f2;

            color: #b91c1c;

            font-size: 13px;

            font-weight: 900;
        }


        /* =========================================================
           REMARKS
           ========================================================= */

        .remarks-box {
            margin-top: 14px;

            padding: 9px 10px;

            border: 1px solid #d1d5db;

            background: #f8fafc;
        }


        .remarks-title {
            margin-bottom: 4px;

            color: #111827;

            font-size: 11px;

            font-weight: 800;
        }


        .remarks-text {
            color: #4b5563;

            font-size: 10px;

            line-height: 1.5;
        }


        /* =========================================================
           NOTE
           ========================================================= */

        .stock-note {
            margin-top: 12px;

            padding: 8px 10px;

            border: 1px solid #fde68a;

            background: #fffbeb;

            color: #92400e;

            font-size: 10px;
        }


        /* =========================================================
           SIGNATURE
           ========================================================= */

        .signature-table {
            width: 100%;

            margin-top: 42px;

            border-collapse: collapse;
        }


        .signature-table td {
            width: 33.33%;

            padding: 0 24px;

            text-align: center;
        }


        .signature-line {
            padding-top: 6px;

            border-top: 1px solid #111827;

            color: #374151;

            font-size: 10px;

            font-weight: 700;
        }


        /* =========================================================
           FOOTER
           ========================================================= */

        .footer {
            margin-top: 18px;

            padding-top: 8px;

            border-top: 1px solid #e5e7eb;

            color: #9ca3af;

            font-size: 9px;

            text-align: center;
        }


        /* =========================================================
           PRINT
           ========================================================= */

        @media print {

            @page {
                size: A4 landscape;

                margin: 10mm;
            }


            body {
                padding: 0;

                background: #ffffff;
            }


            .print-toolbar {
                display: none !important;
            }


            .document {
                max-width: none;

                padding: 0;

                border: 0;

                box-shadow: none;
            }
        }
    </style>

</head>


<body>


    {{-- =============================================================
    PRINT TOOLBAR
   ============================================================= --}}

    <div class="print-toolbar">

        <strong>
            Purchase Return {{ $purchaseReturn->return_no }}
        </strong>


        <div class="print-toolbar-actions">

            <button
                type="button"
                class="print-btn"
                onclick="window.close()">
                Close
            </button>


            <button
                type="button"
                class="print-btn primary"
                onclick="window.print()">
                Print
            </button>

        </div>

    </div>


    <div class="document">


        @php

        $purchase =
        $purchaseReturn->purchase;

        $company =
        $purchase?->company;

        $store =
        $purchase?->store;

        $items =
        $purchaseReturn->items;

        $totalQty =
        $items->sum(
        fn ($item) =>
        (float) $item->quantity
        );

        @endphp


        {{-- =============================================================
        HEADER
       ============================================================= --}}

        <table class="header-table">

            <tr>

                <td width="65%">

                    <h1 class="company-name">
                        {{ $company?->company_name ?? 'Company' }}
                    </h1>


                    @if($store?->store_name)

                    <div class="company-line">

                        Branch:

                        <strong>
                            {{ $store->store_name }}
                        </strong>

                    </div>

                    @endif


                    @if($company?->address)

                    <div class="company-line">
                        {{ $company->address }}
                    </div>

                    @endif


                    @if(
                    $company?->city
                    ||
                    $company?->state
                    ||
                    $company?->pincode
                    )

                    <div class="company-line">

                        {{ $company?->city }}

                        @if(
                        $company?->city
                        &&
                        $company?->state
                        )
                        ,
                        @endif

                        {{ $company?->state }}

                        @if($company?->pincode)
                        - {{ $company->pincode }}
                        @endif

                    </div>

                    @endif


                    @if($company?->phone)

                    <div class="company-line">
                        Phone: {{ $company->phone }}
                    </div>

                    @endif


                    @if($company?->gst_number)

                    <div class="company-line">

                        GSTIN:

                        <strong>
                            {{ $company->gst_number }}
                        </strong>

                    </div>

                    @endif

                </td>


                <td
                    width="35%"
                    class="document-heading">

                    <div class="document-title">
                        PURCHASE RETURN
                    </div>


                    <div class="document-number">
                        {{ $purchaseReturn->return_no }}
                    </div>


                    <div class="document-date">

                        Return Date:

                        {{
                        $purchaseReturn->return_date
                            ? \Carbon\Carbon::parse(
                                $purchaseReturn->return_date
                            )->format('d-m-Y')
                            : '-'
                    }}

                    </div>

                </td>

            </tr>

        </table>


        <div class="header-rule"></div>


        {{-- =============================================================
        INFORMATION
       ============================================================= --}}

        <table class="info-layout">

            <tr>

                <td width="49%">

                    <div class="info-box">

                        <div class="info-title">
                            Return Information
                        </div>


                        <div class="info-body">

                            <table class="info-row">

                                <tr>

                                    <td class="info-label">
                                        Return No.
                                    </td>

                                    <td class="info-value">
                                        {{ $purchaseReturn->return_no }}
                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Return Date
                                    </td>

                                    <td class="info-value">

                                        {{
                                        $purchaseReturn->return_date
                                            ? \Carbon\Carbon::parse(
                                                $purchaseReturn->return_date
                                            )->format('d-m-Y')
                                            : '-'
                                    }}

                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Purchase Type
                                    </td>

                                    <td class="info-value">

                                        @if($purchase?->tax_type === 'gst')

                                        <span class="gst-badge">
                                            GST Purchase
                                        </span>

                                        @else

                                        <span class="non-gst-badge">
                                            Without GST
                                        </span>

                                        @endif

                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Returned Qty
                                    </td>

                                    <td class="info-value">
                                        {{ number_format($totalQty, 2) }}
                                    </td>

                                </tr>

                            </table>

                        </div>

                    </div>

                </td>


                <td class="info-gap"></td>


                <td width="49%">

                    <div class="info-box">

                        <div class="info-title">
                            Original Purchase
                        </div>


                        <div class="info-body">

                            <table class="info-row">

                                <tr>

                                    <td class="info-label">
                                        Purchase No.
                                    </td>

                                    <td class="info-value">
                                        {{ $purchase?->purchase_no ?? '-' }}
                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Purchase Date
                                    </td>

                                    <td class="info-value">

                                        {{
                                        $purchase?->purchase_date
                                            ? \Carbon\Carbon::parse(
                                                $purchase->purchase_date
                                            )->format('d-m-Y')
                                            : '-'
                                    }}

                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Supplier
                                    </td>

                                    <td class="info-value">
                                        {{ $purchaseReturn->supplier?->supplier_name ?? '-' }}
                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Supplier Invoice
                                    </td>

                                    <td class="info-value">
                                        {{ $purchase?->invoice_no ?: '-' }}
                                    </td>

                                </tr>

                            </table>

                        </div>

                    </div>

                </td>

            </tr>

        </table>


        {{-- =============================================================
        ITEMS
       ============================================================= --}}

        <table class="items-table">

            <thead>

                <tr>

                    <th width="4%">
                        #
                    </th>

                    <th width="27%">
                        Product
                    </th>

                    <th width="13%">
                        Location
                    </th>

                    <th width="9%">
                        Return Qty
                    </th>

                    <th width="11%">
                        Rate
                    </th>


                    @if($purchase?->tax_type === 'gst')

                    <th width="8%">
                        GST %
                    </th>

                    <th width="11%">
                        GST
                    </th>

                    @endif


                    <th width="13%">
                        Total
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($items as $index => $item)

                <tr>

                    <td class="center">
                        {{ $index + 1 }}
                    </td>


                    <td>

                        <div class="product-name">
                            {{ $item->product?->product_name ?? '-' }}
                        </div>


                        <div class="product-meta">

                            {{ $item->product?->product_code ?? '' }}


                            @if($item->product?->part_number)

                            • Part:
                            {{ $item->product->part_number }}

                            @endif


                            @if($item->product?->unit)

                            •

                            {{
                                    $item->product->unit->short_name
                                    ??
                                    $item->product->unit->unit_name
                                }}

                            @endif

                        </div>

                    </td>


                    <td>

                        @if($item->godown)

                        <span class="location-godown">
                            {{ $item->godown->godown_name }}
                        </span>

                        @else

                        <span class="location-shop">
                            Shop
                        </span>

                        @endif

                    </td>


                    <td class="number return-qty">
                        {{ number_format((float) $item->quantity, 2) }}
                    </td>


                    <td class="number">
                        ₹{{ number_format((float) $item->purchase_price, 2) }}
                    </td>


                    @if($purchase?->tax_type === 'gst')

                    <td class="number">

                        {{ number_format((float) $item->gst_percent, 2) }}%

                    </td>


                    <td class="number gst-value">

                        ₹{{ number_format((float) $item->gst_amount, 2) }}

                    </td>

                    @endif


                    <td class="number line-total">

                        ₹{{ number_format((float) $item->line_total, 2) }}

                    </td>

                </tr>

                @empty

                <tr>

                    <td
                        colspan="{{ $purchase?->tax_type === 'gst' ? 8 : 6 }}"
                        class="center">
                        No returned items found.
                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>


        {{-- =============================================================
        SUMMARY
       ============================================================= --}}

        <table class="summary-layout">

            <tr>

                <td class="summary-spacer"></td>


                <td width="43%">

                    <table class="summary-table">

                        <tr>

                            <td class="summary-label">
                                Returned Quantity
                            </td>

                            <td class="summary-value">
                                {{ number_format($totalQty, 2) }}
                            </td>

                        </tr>


                        <tr>

                            <td class="summary-label">
                                Subtotal
                            </td>

                            <td class="summary-value">
                                ₹{{ number_format((float) $purchaseReturn->sub_total, 2) }}
                            </td>

                        </tr>


                        @if($purchase?->tax_type === 'gst')

                        <tr>

                            <td class="summary-label">
                                GST
                            </td>

                            <td class="summary-value">
                                ₹{{ number_format((float) $purchaseReturn->gst_total, 2) }}
                            </td>

                        </tr>

                        @endif


                        <tr class="grand-total">

                            <td>
                                Return Total
                            </td>

                            <td class="summary-value">
                                ₹{{ number_format((float) $purchaseReturn->total_amount, 2) }}
                            </td>

                        </tr>

                    </table>

                </td>

            </tr>

        </table>


        {{-- =============================================================
        STOCK NOTE
       ============================================================= --}}

        <div class="stock-note">

            Stock for this purchase return was reduced from the original
            Shop/Godown location of each purchased item.

        </div>


        {{-- =============================================================
        REMARKS
       ============================================================= --}}

        @if($purchaseReturn->remarks)

        <div class="remarks-box">

            <div class="remarks-title">
                Remarks
            </div>


            <div class="remarks-text">
                {{ $purchaseReturn->remarks }}
            </div>

        </div>

        @endif


        {{-- =============================================================
        SIGNATURE
       ============================================================= --}}

        <table class="signature-table">

            <tr>

                <td>

                    <div class="signature-line">
                        Prepared By
                    </div>

                </td>


                <td>

                    <div class="signature-line">
                        Checked By
                    </div>

                </td>


                <td>

                    <div class="signature-line">
                        Authorized Signature
                    </div>

                </td>

            </tr>

        </table>


        {{-- =============================================================
        FOOTER
       ============================================================= --}}

        <div class="footer">

            Purchase Return
            {{ $purchaseReturn->return_no }}

            •

            {{ $company?->company_name ?? 'Company' }}

            •

            {{ $store?->store_name ?? 'Branch' }}

        </div>

    </div>


    <script>
        /*
    |--------------------------------------------------------------------------
    | Keyboard Print
    |--------------------------------------------------------------------------
    */

        document.addEventListener(
            'keydown',
            function(event) {

                if (
                    event.ctrlKey &&
                    event.key.toLowerCase() === 'p'
                ) {

                    event.preventDefault();

                    window.print();
                }
            }
        );
    </script>


</body>

</html>