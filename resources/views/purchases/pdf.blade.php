<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Purchase {{ $purchase->purchase_no }}
    </title>


    <style>
        /* =========================================================
           PDF BASE
           ========================================================= */

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

            font-size: 10px;

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


        .company-title {
            margin: 0 0 4px;

            color: #111827;

            font-size: 20px;

            font-weight: bold;
        }


        .company-line {
            margin: 2px 0;

            color: #4b5563;

            font-size: 9px;
        }


        .document-area {
            text-align: right;
        }


        .document-title {
            margin: 0;

            color: #111827;

            font-size: 19px;

            font-weight: bold;
        }


        .document-number {
            margin-top: 4px;

            color: #1d4ed8;

            font-size: 12px;

            font-weight: bold;
        }


        .document-date {
            margin-top: 3px;

            color: #4b5563;

            font-size: 9px;
        }


        .header-border {
            width: 100%;

            height: 2px;

            margin-bottom: 10px;

            background: #111827;
        }


        /* =========================================================
           INFO LAYOUT
           ========================================================= */

        .info-layout {
            width: 100%;

            border-collapse: collapse;

            margin-bottom: 10px;
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
            padding: 6px 8px;

            border-bottom: 1px solid #cbd5e1;

            background: #e5e7eb;

            color: #1f2937;

            font-size: 9px;

            font-weight: bold;

            text-transform: uppercase;
        }


        .info-body {
            padding: 7px 8px;
        }


        .info-row {
            width: 100%;

            border-collapse: collapse;
        }


        .info-row td {
            padding: 2px 0;

            vertical-align: top;
        }


        .info-label {
            width: 42%;

            color: #6b7280;
        }


        .info-value {
            width: 58%;

            color: #111827;

            font-weight: bold;

            text-align: right;
        }


        /* =========================================================
           TAX BADGE
           ========================================================= */

        .tax-gst {
            padding: 2px 5px;

            background: #dcfce7;

            color: #166534;

            font-size: 8px;

            font-weight: bold;
        }


        .tax-non-gst {
            padding: 2px 5px;

            background: #e5e7eb;

            color: #4b5563;

            font-size: 8px;

            font-weight: bold;
        }


        /* =========================================================
           ITEMS TABLE
           ========================================================= */

        .items-table {
            width: 100%;

            border-collapse: collapse;

            margin-bottom: 10px;
        }


        .items-table th {
            padding: 5px 4px;

            border: 1px solid #9ca3af;

            background: #dbe1e8;

            color: #1f2937;

            font-size: 8px;

            font-weight: bold;

            text-align: left;

            text-transform: uppercase;
        }


        .items-table td {
            padding: 5px 4px;

            border: 1px solid #d1d5db;

            color: #374151;

            font-size: 8.5px;

            vertical-align: top;
        }


        .items-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }


        .items-table .center {
            text-align: center;
        }


        .items-table .number {
            text-align: right;

            white-space: nowrap;
        }


        .product-name {
            color: #111827;

            font-size: 8.5px;

            font-weight: bold;
        }


        .product-meta {
            margin-top: 2px;

            color: #6b7280;

            font-size: 7.5px;
        }


        .shop-location {
            color: #1d4ed8;

            font-weight: bold;
        }


        .godown-location {
            color: #6d28d9;

            font-weight: bold;
        }


        .free-qty {
            color: #7c3aed;

            font-weight: bold;
        }


        .stock-qty {
            color: #166534;

            font-weight: bold;
        }


        .discount-value {
            color: #b91c1c;
        }


        .tax-value {
            color: #166534;
        }


        .line-total {
            color: #111827;

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
            width: 57%;
        }


        .summary-table {
            width: 100%;

            border-collapse: collapse;

            border: 1px solid #cbd5e1;
        }


        .summary-table td {
            padding: 5px 7px;

            border-bottom: 1px solid #e5e7eb;

            font-size: 9px;
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


        .discount-row .summary-value {
            color: #b91c1c;
        }


        .grand-total td {
            padding-top: 7px;

            padding-bottom: 7px;

            border-top: 2px solid #1f2937;

            border-bottom: 2px solid #1f2937;

            background: #eaf2ff;

            color: #1d4ed8;

            font-size: 11px;

            font-weight: bold;
        }


        .balance-row td {
            background: #fffbeb;

            color: #92400e;

            font-weight: bold;
        }


        .paid-row td {
            color: #166534;
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

            font-size: 9px;

            font-weight: bold;
        }


        .remarks-text {
            color: #4b5563;

            font-size: 8.5px;

            line-height: 1.45;
        }


        /* =========================================================
           STOCK SUMMARY
           ========================================================= */

        .stock-summary {
            width: 100%;

            margin-top: 10px;

            border-collapse: collapse;
        }


        .stock-summary td {
            width: 33.33%;

            padding: 7px;

            border: 1px solid #d1d5db;

            text-align: center;
        }


        .stock-summary-label {
            display: block;

            color: #6b7280;

            font-size: 8px;
        }


        .stock-summary-value {
            display: block;

            margin-top: 2px;

            color: #111827;

            font-size: 11px;

            font-weight: bold;
        }


        /* =========================================================
           SIGNATURE
           ========================================================= */

        .signature-table {
            width: 100%;

            margin-top: 35px;

            border-collapse: collapse;
        }


        .signature-table td {
            width: 33.33%;

            padding: 0 20px;

            text-align: center;
        }


        .signature-line {
            padding-top: 6px;

            border-top: 1px solid #111827;

            color: #374151;

            font-size: 8px;

            font-weight: bold;
        }


        /* =========================================================
           FOOTER
           ========================================================= */

        .pdf-footer {
            margin-top: 12px;

            padding-top: 6px;

            border-top: 1px solid #e5e7eb;

            color: #9ca3af;

            font-size: 7.5px;

            text-align: center;
        }
    </style>

</head>


<body>

    <div class="pdf-page">

        {{-- =============================================================
         HEADER
         ============================================================= --}}

        <table class="header-table">

            <tr>

                <td width="67%">

                    <div class="company-title">
                        {{ $purchase->company?->company_name ?? 'Company' }}
                    </div>


                    @if($purchase->store?->store_name)

                    <div class="company-line">
                        Branch:
                        <strong>
                            {{ $purchase->store->store_name }}
                        </strong>
                    </div>

                    @endif


                    @if($purchase->company?->address)

                    <div class="company-line">
                        {{ $purchase->company->address }}
                    </div>

                    @endif


                    @if(
                    $purchase->company?->city
                    ||
                    $purchase->company?->state
                    ||
                    $purchase->company?->pincode
                    )

                    <div class="company-line">

                        {{ $purchase->company?->city }}

                        @if(
                        $purchase->company?->city
                        &&
                        $purchase->company?->state
                        )
                        ,
                        @endif

                        {{ $purchase->company?->state }}

                        @if($purchase->company?->pincode)

                        -
                        {{ $purchase->company->pincode }}

                        @endif

                    </div>

                    @endif


                    @if($purchase->company?->phone)

                    <div class="company-line">

                        Phone:
                        {{ $purchase->company->phone }}

                    </div>

                    @endif


                    @if($purchase->company?->gst_number)

                    <div class="company-line">

                        GSTIN:
                        <strong>
                            {{ $purchase->company->gst_number }}
                        </strong>

                    </div>

                    @endif

                </td>


                <td
                    width="33%"
                    class="document-area">

                    <div class="document-title">
                        PURCHASE
                    </div>


                    <div class="document-number">
                        {{ $purchase->purchase_no }}
                    </div>


                    <div class="document-date">

                        Date:

                        {{
                        $purchase->purchase_date
                            ? $purchase->purchase_date->format('d-m-Y')
                            : '-'
                    }}

                    </div>

                </td>

            </tr>

        </table>


        <div class="header-border"></div>


        {{-- =============================================================
         PURCHASE + SUPPLIER INFORMATION
         ============================================================= --}}

        <table class="info-layout">

            <tr>

                {{-- Purchase Information --}}

                <td width="49%">

                    <div class="info-box">

                        <div class="info-title">
                            Purchase Information
                        </div>


                        <div class="info-body">

                            <table class="info-row">

                                <tr>

                                    <td class="info-label">
                                        Purchase No.
                                    </td>

                                    <td class="info-value">
                                        {{ $purchase->purchase_no }}
                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Purchase Date
                                    </td>

                                    <td class="info-value">

                                        {{
                                        $purchase->purchase_date
                                            ? $purchase->purchase_date->format('d-m-Y')
                                            : '-'
                                    }}

                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Purchase Type
                                    </td>

                                    <td class="info-value">

                                        @if($purchase->tax_type === 'gst')

                                        <span class="tax-gst">
                                            GST Purchase
                                        </span>

                                        @else

                                        <span class="tax-non-gst">
                                            Without GST
                                        </span>

                                        @endif

                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Status
                                    </td>

                                    <td class="info-value">

                                        {{
                                        $purchase->status
                                            ? 'Active'
                                            : 'Inactive'
                                    }}

                                    </td>

                                </tr>

                            </table>

                        </div>

                    </div>

                </td>


                <td class="info-gap"></td>


                {{-- Supplier Information --}}

                <td width="49%">

                    <div class="info-box">

                        <div class="info-title">
                            Supplier Information
                        </div>


                        <div class="info-body">

                            <table class="info-row">

                                <tr>

                                    <td class="info-label">
                                        Supplier
                                    </td>

                                    <td class="info-value">
                                        {{ $purchase->supplier?->supplier_name ?? '-' }}
                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Invoice No.
                                    </td>

                                    <td class="info-value">
                                        {{ $purchase->invoice_no ?: '-' }}
                                    </td>

                                </tr>


                                <tr>

                                    <td class="info-label">
                                        Invoice Date
                                    </td>

                                    <td class="info-value">

                                        {{
                                        $purchase->invoice_date
                                            ? $purchase->invoice_date->format('d-m-Y')
                                            : '-'
                                    }}

                                    </td>

                                </tr>


                                @if($purchase->supplier?->gst_number)

                                <tr>

                                    <td class="info-label">
                                        Supplier GSTIN
                                    </td>

                                    <td class="info-value">
                                        {{ $purchase->supplier->gst_number }}
                                    </td>

                                </tr>

                                @endif

                            </table>

                        </div>

                    </div>

                </td>

            </tr>

        </table>


        {{-- =============================================================
         PURCHASE ITEMS
         ============================================================= --}}

        <table class="items-table">

            <thead>

                <tr>

                    <th width="3%">
                        #
                    </th>

                    <th width="20%">
                        Product
                    </th>

                    <th width="10%">
                        Location
                    </th>

                    <th width="6%">
                        Qty
                    </th>

                    <th width="6%">
                        Free
                    </th>

                    <th width="7%">
                        Stock Qty
                    </th>

                    <th width="8%">
                        Rate
                    </th>

                    <th width="8%">
                        Discount
                    </th>

                    <th width="9%">
                        Taxable
                    </th>


                    @if($purchase->tax_type === 'gst')

                    <th width="6%">
                        GST %
                    </th>

                    <th width="8%">
                        GST
                    </th>

                    @endif


                    <th width="9%">
                        Total
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($purchase->items as $index => $item)

                @php

                $quantity =
                (float) $item->quantity;

                $freeQuantity =
                (float) $item->free_quantity;

                $stockQuantity =
                $quantity + $freeQuantity;

                @endphp


                <tr>

                    {{-- Serial --}}

                    <td class="center">
                        {{ $index + 1 }}
                    </td>


                    {{-- Product --}}

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


                    {{-- Location --}}

                    <td>

                        @if($item->godown)

                        <span class="godown-location">

                            {{ $item->godown->godown_name }}

                        </span>

                        @else

                        <span class="shop-location">
                            Shop
                        </span>

                        @endif

                    </td>


                    {{-- Quantity --}}

                    <td class="number">

                        {{ number_format($quantity, 2) }}

                    </td>


                    {{-- Free Quantity --}}

                    <td class="number free-qty">

                        {{ number_format($freeQuantity, 2) }}

                    </td>


                    {{-- Stock Qty --}}

                    <td class="number stock-qty">

                        {{ number_format($stockQuantity, 2) }}

                    </td>


                    {{-- Purchase Rate --}}

                    <td class="number">

                        ₹{{ number_format((float) $item->purchase_price, 2) }}

                    </td>


                    {{-- Discount --}}

                    <td class="number discount-value">

                        @if((float) $item->discount > 0)

                        - ₹{{ number_format((float) $item->discount, 2) }}

                        @else

                        ₹0.00

                        @endif

                    </td>


                    {{-- Taxable --}}

                    <td class="number">

                        ₹{{ number_format((float) $item->taxable_amount, 2) }}

                    </td>


                    {{-- GST --}}

                    @if($purchase->tax_type === 'gst')

                    <td class="number">

                        {{ number_format((float) $item->gst_percent, 2) }}%

                    </td>


                    <td class="number tax-value">

                        ₹{{ number_format((float) $item->gst_amount, 2) }}

                    </td>

                    @endif


                    {{-- Line Total --}}

                    <td class="number line-total">

                        ₹{{ number_format((float) $item->line_total, 2) }}

                    </td>

                </tr>

                @empty

                <tr>

                    <td
                        colspan="{{ $purchase->tax_type === 'gst' ? 12 : 10 }}"
                        class="center">
                        No purchase items found.
                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>


        {{-- =============================================================
         STOCK SUMMARY
         ============================================================= --}}

        @php

        $totalPurchasedQty =
        $purchase->items->sum(
        fn ($item) =>
        (float) $item->quantity
        );

        $totalFreeQty =
        $purchase->items->sum(
        fn ($item) =>
        (float) $item->free_quantity
        );

        $totalStockQty =
        $totalPurchasedQty
        +
        $totalFreeQty;

        @endphp


        <table class="stock-summary">

            <tr>

                <td>

                    <span class="stock-summary-label">
                        Purchased Qty
                    </span>

                    <span class="stock-summary-value">
                        {{ number_format($totalPurchasedQty, 2) }}
                    </span>

                </td>


                <td>

                    <span class="stock-summary-label">
                        Free Qty
                    </span>

                    <span class="stock-summary-value">
                        {{ number_format($totalFreeQty, 2) }}
                    </span>

                </td>


                <td>

                    <span class="stock-summary-label">
                        Physical Stock Received
                    </span>

                    <span class="stock-summary-value">
                        {{ number_format($totalStockQty, 2) }}
                    </span>

                </td>

            </tr>

        </table>


        {{-- =============================================================
         FINANCIAL SUMMARY
         ============================================================= --}}

        <table class="summary-layout">

            <tr>

                <td class="summary-spacer"></td>


                <td width="43%">

                    <table class="summary-table">

                        {{-- Subtotal --}}

                        <tr>

                            <td class="summary-label">
                                Subtotal
                            </td>

                            <td class="summary-value">
                                ₹{{ number_format((float) $purchase->subtotal, 2) }}
                            </td>

                        </tr>


                        {{-- Discount --}}

                        <tr class="discount-row">

                            <td class="summary-label">
                                Discount
                            </td>

                            <td class="summary-value">

                                - ₹{{ number_format((float) $purchase->discount, 2) }}

                            </td>

                        </tr>


                        {{-- GST --}}

                        @if($purchase->tax_type === 'gst')

                        <tr>

                            <td class="summary-label">
                                GST
                            </td>

                            <td class="summary-value">
                                ₹{{ number_format((float) $purchase->tax_amount, 2) }}
                            </td>

                        </tr>

                        @endif


                        {{-- Other Charges --}}

                        <tr>

                            <td class="summary-label">
                                Other Charges
                            </td>

                            <td class="summary-value">
                                ₹{{ number_format((float) $purchase->other_charges, 2) }}
                            </td>

                        </tr>


                        {{-- Grand Total --}}

                        <tr class="grand-total">

                            <td>
                                Grand Total
                            </td>

                            <td class="summary-value">
                                ₹{{ number_format((float) $purchase->grand_total, 2) }}
                            </td>

                        </tr>


                        {{-- Paid --}}

                        <tr class="paid-row">

                            <td class="summary-label">
                                Paid Amount
                            </td>

                            <td class="summary-value">
                                ₹{{ number_format((float) $purchase->paid_amount, 2) }}
                            </td>

                        </tr>


                        {{-- Balance --}}

                        <tr class="balance-row">

                            <td>
                                Balance
                            </td>

                            <td class="summary-value">
                                ₹{{ number_format((float) $purchase->balance_amount, 2) }}
                            </td>

                        </tr>

                    </table>

                </td>

            </tr>

        </table>


        {{-- =============================================================
         REMARKS
         ============================================================= --}}

        @if($purchase->remarks)

        <div class="remarks-box">

            <div class="remarks-title">
                Remarks
            </div>


            <div class="remarks-text">
                {{ $purchase->remarks }}
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

        <div class="pdf-footer">

            Purchase {{ $purchase->purchase_no }}

            •

            {{ $purchase->company?->company_name ?? 'Company' }}

            •

            {{ $purchase->store?->store_name ?? 'Branch' }}

        </div>

    </div>

</body>

</html>