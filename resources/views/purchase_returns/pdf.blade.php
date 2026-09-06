<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Purchase Return {{ $purchaseReturn->return_no }}
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
            padding: 0;

            color: #111827;

            font-family:
                DejaVu Sans,
                sans-serif;

            font-size: 9px;

            line-height: 1.35;
        }


        .pdf-page {
            width: 100%;
        }


        /* =========================================================
           HEADER
           ========================================================= */

        .header-table {
            width: 100%;

            border-collapse: collapse;

            margin-bottom: 10px;
        }


        .header-table td {
            vertical-align: top;
        }


        .company-name {
            margin: 0 0 4px;

            color: #111827;

            font-size: 19px;

            font-weight: bold;
        }


        .company-line {
            margin: 2px 0;

            color: #4b5563;

            font-size: 8px;
        }


        .document-area {
            text-align: right;
        }


        .document-title {
            margin: 0;

            color: #b91c1c;

            font-size: 18px;

            font-weight: bold;
        }


        .document-number {
            margin-top: 4px;

            color: #111827;

            font-size: 11px;

            font-weight: bold;
        }


        .document-date {
            margin-top: 3px;

            color: #64748b;

            font-size: 8px;
        }


        .header-rule {
            width: 100%;

            height: 2px;

            margin-bottom: 10px;

            background: #b91c1c;
        }


        /* =========================================================
           INFO BOXES
           ========================================================= */

        .info-layout {
            width: 100%;

            margin-bottom: 10px;

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
            padding: 5px 7px;

            border-bottom: 1px solid #cbd5e1;

            background: #f1f5f9;

            color: #1e293b;

            font-size: 8px;

            font-weight: bold;

            text-transform: uppercase;
        }


        .info-body {
            padding: 6px 7px;
        }


        .info-row {
            width: 100%;

            border-collapse: collapse;
        }


        .info-row td {
            padding: 2px 0;
        }


        .info-label {
            width: 42%;

            color: #64748b;
        }


        .info-value {
            width: 58%;

            color: #111827;

            font-weight: bold;

            text-align: right;
        }


        /* =========================================================
           TAX BADGES
           ========================================================= */

        .gst-badge {
            padding: 2px 5px;

            background: #dcfce7;

            color: #166534;

            font-size: 7px;

            font-weight: bold;
        }


        .non-gst-badge {
            padding: 2px 5px;

            background: #e5e7eb;

            color: #475569;

            font-size: 7px;

            font-weight: bold;
        }


        /* =========================================================
           ITEMS TABLE
           ========================================================= */

        .items-table {
            width: 100%;

            margin-bottom: 10px;

            border-collapse: collapse;
        }


        .items-table th {
            padding: 5px 4px;

            border: 1px solid #9ca3af;

            background: #e5e7eb;

            color: #1f2937;

            font-size: 7.5px;

            font-weight: bold;

            text-align: left;

            text-transform: uppercase;
        }


        .items-table td {
            padding: 5px 4px;

            border: 1px solid #d1d5db;

            color: #374151;

            font-size: 8px;

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

            font-size: 8px;

            font-weight: bold;
        }


        .product-meta {
            margin-top: 2px;

            color: #6b7280;

            font-size: 7px;
        }


        .location-shop {
            color: #1d4ed8;

            font-weight: bold;
        }


        .location-godown {
            color: #6d28d9;

            font-weight: bold;
        }


        .return-qty {
            color: #b45309;

            font-weight: bold;
        }


        .gst-value {
            color: #166534;

            font-weight: bold;
        }


        .line-total {
            color: #b91c1c;

            font-weight: bold;
        }


        /* =========================================================
           SUMMARY
           ========================================================= */

        .summary-layout {
            width: 100%;

            border-collapse: collapse;

            margin-top: 4px;
        }


        .summary-layout td {
            vertical-align: top;
        }


        .summary-spacer {
            width: 58%;
        }


        .summary-table {
            width: 100%;

            border-collapse: collapse;

            border: 1px solid #cbd5e1;
        }


        .summary-table td {
            padding: 5px 7px;

            border-bottom: 1px solid #e5e7eb;

            font-size: 8px;
        }


        .summary-label {
            color: #4b5563;
        }


        .summary-value {
            color: #111827;

            font-weight: bold;

            text-align: right;

            white-space: nowrap;
        }


        .grand-total td {
            padding-top: 7px;
            padding-bottom: 7px;

            border-top: 2px solid #b91c1c;

            background: #fff1f2;

            color: #b91c1c;

            font-size: 10px;

            font-weight: bold;
        }


        /* =========================================================
           STOCK NOTE
           ========================================================= */

        .stock-note {
            margin-top: 10px;

            padding: 6px 8px;

            border: 1px solid #fde68a;

            background: #fffbeb;

            color: #92400e;

            font-size: 7.5px;
        }


        /* =========================================================
           REMARKS
           ========================================================= */

        .remarks-box {
            margin-top: 10px;

            padding: 7px 8px;

            border: 1px solid #d1d5db;

            background: #f8fafc;
        }


        .remarks-title {
            margin-bottom: 3px;

            color: #111827;

            font-size: 8px;

            font-weight: bold;
        }


        .remarks-text {
            color: #4b5563;

            font-size: 7.5px;

            line-height: 1.45;
        }


        /* =========================================================
           SIGNATURE
           ========================================================= */

        .signature-table {
            width: 100%;

            margin-top: 32px;

            border-collapse: collapse;
        }


        .signature-table td {
            width: 33.33%;

            padding: 0 20px;

            text-align: center;
        }


        .signature-line {
            padding-top: 5px;

            border-top: 1px solid #111827;

            color: #374151;

            font-size: 7.5px;

            font-weight: bold;
        }


        /* =========================================================
           FOOTER
           ========================================================= */

        .footer {
            margin-top: 10px;

            padding-top: 6px;

            border-top: 1px solid #e5e7eb;

            color: #9ca3af;

            font-size: 7px;

            text-align: center;
        }
    </style>

</head>


<body>

    <div class="pdf-page">

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

                    <div class="company-name">
                        {{ $company?->company_name ?? 'Company' }}
                    </div>


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

                        -
                        {{ $company->pincode }}

                        @endif

                    </div>

                    @endif


                    @if($company?->phone)

                    <div class="company-line">

                        Phone:
                        {{ $company->phone }}

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
                    class="document-area">

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

                    <th width="28%">
                        Product
                    </th>

                    <th width="14%">
                        Location
                    </th>

                    <th width="10%">
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


                    <th width="14%">
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


                <td width="42%">

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

            Stock was reduced from the original Shop/Godown location
            for each returned purchase item.

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
         SIGNATURES
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

</body>

</html>