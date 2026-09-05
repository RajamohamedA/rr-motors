<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Purchase {{ $purchase->purchase_no }}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;

            padding: 20px;

            background: #ffffff;

            color: #111827;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12px;
        }

        .print-page {
            max-width: 1050px;

            margin: 0 auto;
        }

        .print-header {
            display: flex;

            align-items: flex-start;

            justify-content: space-between;

            gap: 20px;

            padding-bottom: 14px;

            border-bottom: 2px solid #111827;
        }

        .company-block h1 {
            margin: 0;

            font-size: 22px;

            font-weight: 800;
        }

        .company-block p {
            margin: 4px 0 0;

            color: #4b5563;

            font-size: 12px;

            line-height: 1.45;
        }

        .document-title {
            text-align: right;
        }

        .document-title h2 {
            margin: 0;

            font-size: 22px;

            font-weight: 800;
        }

        .document-title span {
            display: block;

            margin-top: 4px;

            color: #4b5563;

            font-size: 12px;
        }

        .info-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 12px;

            margin-top: 14px;
        }

        .info-box {
            border: 1px solid #d1d5db;

            border-radius: 6px;

            overflow: hidden;
        }

        .info-box-title {
            padding: 7px 9px;

            border-bottom: 1px solid #d1d5db;

            background: #f3f4f6;

            font-size: 12px;

            font-weight: 700;

            text-transform: uppercase;
        }

        .info-box-body {
            padding: 9px;
        }

        .info-row {
            display: flex;

            justify-content: space-between;

            gap: 12px;

            padding: 3px 0;
        }

        .info-row span {
            color: #6b7280;
        }

        .info-row strong {
            text-align: right;

            font-weight: 700;
        }

        .tax-badge {
            display: inline-block;

            padding: 3px 7px;

            border-radius: 999px;

            background: #f3f4f6;

            font-size: 11px;

            font-weight: 700;
        }

        .tax-badge.gst {
            background: #dcfce7;

            color: #166534;
        }

        .tax-badge.non-gst {
            background: #e5e7eb;

            color: #4b5563;
        }

        .items-table {
            width: 100%;

            margin-top: 14px;

            border-collapse: collapse;
        }

        .items-table th {
            padding: 7px 6px;

            border: 1px solid #9ca3af;

            background: #e5e7eb;

            font-size: 10px;

            font-weight: 700;

            text-transform: uppercase;
        }

        .items-table td {
            padding: 7px 6px;

            border: 1px solid #d1d5db;

            vertical-align: top;
        }

        .items-table .number {
            text-align: right;

            white-space: nowrap;
        }

        .items-table .center {
            text-align: center;
        }

        .product-name {
            font-weight: 700;
        }

        .product-meta {
            margin-top: 2px;

            color: #6b7280;

            font-size: 10px;
        }

        .location {
            font-weight: 700;
        }

        .location.shop {
            color: #1d4ed8;
        }

        .location.godown {
            color: #6d28d9;
        }

        .summary-section {
            display: flex;

            justify-content: flex-end;

            margin-top: 14px;
        }

        .summary-table {
            width: 360px;

            border-collapse: collapse;
        }

        .summary-table td {
            padding: 6px 8px;

            border-bottom: 1px solid #e5e7eb;
        }

        .summary-table td:first-child {
            color: #4b5563;
        }

        .summary-table td:last-child {
            text-align: right;

            font-weight: 700;
        }

        .summary-table .grand-total td {
            border-top: 2px solid #111827;

            border-bottom: 2px solid #111827;

            background: #f3f4f6;

            font-size: 14px;

            font-weight: 800;
        }

        .summary-table .balance td {
            font-size: 13px;

            font-weight: 800;
        }

        .remarks {
            margin-top: 14px;

            padding: 10px;

            border: 1px solid #d1d5db;

            border-radius: 6px;
        }

        .remarks strong {
            display: block;

            margin-bottom: 4px;
        }

        .remarks p {
            margin: 0;

            color: #4b5563;

            line-height: 1.5;
        }

        .footer {
            display: flex;

            justify-content: space-between;

            gap: 20px;

            margin-top: 42px;
        }

        .signature {
            min-width: 180px;

            padding-top: 8px;

            border-top: 1px solid #111827;

            text-align: center;

            font-size: 11px;

            font-weight: 700;
        }

        .print-actions {
            display: flex;

            justify-content: flex-end;

            gap: 8px;

            max-width: 1050px;

            margin: 0 auto 12px;
        }

        .print-actions button {
            min-height: 36px;

            padding: 0 14px;

            border: 1px solid #9ca3af;

            border-radius: 5px;

            background: #ffffff;

            color: #111827;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;
        }

        .print-actions .primary {
            border-color: #2563eb;

            background: #2563eb;

            color: #ffffff;
        }

        @media print {
            body {
                padding: 0;
            }

            .print-actions {
                display: none;
            }

            .print-page {
                max-width: none;
            }

            @page {
                size: A4 landscape;

                margin: 10mm;
            }
        }
    </style>
</head>

<body>

    <div class="print-actions">

        <button
            type="button"
            onclick="window.close()">
            Close
        </button>

        <button
            type="button"
            class="primary"
            onclick="window.print()">
            Print
        </button>

    </div>


    <div class="print-page">

        {{-- =========================================================
             HEADER
             ========================================================= --}}

        <div class="print-header">

            <div class="company-block">

                <h1>
                    {{ $purchase->company?->company_name ?? 'Company' }}
                </h1>

                <p>
                    {{ $purchase->store?->store_name ?? 'Branch' }}
                </p>

                @if($purchase->company?->address)
                <p>
                    {{ $purchase->company->address }}
                </p>
                @endif

                @if($purchase->company?->city || $purchase->company?->state)
                <p>
                    {{ $purchase->company?->city }}

                    @if($purchase->company?->city && $purchase->company?->state)
                    ,
                    @endif

                    {{ $purchase->company?->state }}
                </p>
                @endif

                @if($purchase->company?->gst_number)
                <p>
                    GSTIN:
                    <strong>
                        {{ $purchase->company->gst_number }}
                    </strong>
                </p>
                @endif

            </div>


            <div class="document-title">

                <h2>
                    PURCHASE
                </h2>

                <span>
                    {{ $purchase->purchase_no }}
                </span>

                <span>
                    {{ optional($purchase->purchase_date)->format('d-m-Y') }}
                </span>

            </div>

        </div>


        {{-- =========================================================
             PURCHASE / SUPPLIER INFO
             ========================================================= --}}

        <div class="info-grid">

            <div class="info-box">

                <div class="info-box-title">
                    Purchase Information
                </div>

                <div class="info-box-body">

                    <div class="info-row">

                        <span>
                            Purchase No.
                        </span>

                        <strong>
                            {{ $purchase->purchase_no }}
                        </strong>

                    </div>


                    <div class="info-row">

                        <span>
                            Purchase Date
                        </span>

                        <strong>
                            {{ optional($purchase->purchase_date)->format('d-m-Y') }}
                        </strong>

                    </div>


                    <div class="info-row">

                        <span>
                            Purchase Type
                        </span>

                        <strong>

                            @if($purchase->tax_type === 'gst')

                            <span class="tax-badge gst">
                                GST Purchase
                            </span>

                            @else

                            <span class="tax-badge non-gst">
                                Without GST
                            </span>

                            @endif

                        </strong>

                    </div>

                </div>

            </div>


            <div class="info-box">

                <div class="info-box-title">
                    Supplier
                </div>


                <div class="info-box-body">

                    <div class="info-row">

                        <span>
                            Supplier
                        </span>

                        <strong>
                            {{ $purchase->supplier?->supplier_name ?? '-' }}
                        </strong>

                    </div>


                    <div class="info-row">

                        <span>
                            Supplier Invoice
                        </span>

                        <strong>
                            {{ $purchase->invoice_no ?: '-' }}
                        </strong>

                    </div>


                    <div class="info-row">

                        <span>
                            Invoice Date
                        </span>

                        <strong>

                            @if($purchase->invoice_date)

                            {{ optional($purchase->invoice_date)->format('d-m-Y') }}

                            @else

                            -

                            @endif

                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             ITEMS
             ========================================================= --}}

        <table class="items-table">

            <thead>

                <tr>

                    <th>
                        #
                    </th>

                    <th>
                        Product
                    </th>

                    <th>
                        Location
                    </th>

                    <th>
                        Qty
                    </th>

                    <th>
                        Free
                    </th>

                    <th>
                        Stock Qty
                    </th>

                    <th>
                        Rate
                    </th>

                    <th>
                        Discount
                    </th>

                    <th>
                        Taxable
                    </th>

                    @if($purchase->tax_type === 'gst')

                    <th>
                        GST %
                    </th>

                    <th>
                        GST Amt
                    </th>

                    @endif

                    <th>
                        Total
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($purchase->items as $index => $item)

                <tr>

                    <td class="center">
                        {{ $index + 1 }}
                    </td>


                    <td>

                        <div class="product-name">
                            {{ $item->product?->product_name ?? '-' }}
                        </div>


                        <div class="product-meta">

                            {{ $item->product?->product_code }}

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

                        <span class="location godown">
                            {{ $item->godown->godown_name }}
                        </span>

                        @else

                        <span class="location shop">
                            Shop
                        </span>

                        @endif

                    </td>


                    <td class="number">
                        {{ number_format((float) $item->quantity, 2) }}
                    </td>


                    <td class="number">
                        {{ number_format((float) $item->free_quantity, 2) }}
                    </td>


                    <td class="number">
                        {{
                                number_format(
                                    (float) $item->quantity
                                    +
                                    (float) $item->free_quantity,
                                    2
                                )
                            }}
                    </td>


                    <td class="number">
                        ₹{{ number_format((float) $item->purchase_price, 2) }}
                    </td>


                    <td class="number">
                        ₹{{ number_format((float) $item->discount, 2) }}
                    </td>


                    <td class="number">
                        ₹{{ number_format((float) $item->taxable_amount, 2) }}
                    </td>


                    @if($purchase->tax_type === 'gst')

                    <td class="number">
                        {{ number_format((float) $item->gst_percent, 2) }}%
                    </td>


                    <td class="number">
                        ₹{{ number_format((float) $item->gst_amount, 2) }}
                    </td>

                    @endif


                    <td class="number">
                        ₹{{ number_format((float) $item->line_total, 2) }}
                    </td>

                </tr>

                @empty

                <tr>

                    <td
                        colspan="{{ $purchase->tax_type === 'gst' ? 12 : 10 }}"
                        class="center">
                        No purchase items.
                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>


        {{-- =========================================================
             SUMMARY
             ========================================================= --}}

        <div class="summary-section">

            <table class="summary-table">

                <tr>

                    <td>
                        Subtotal
                    </td>

                    <td>
                        ₹{{ number_format((float) $purchase->subtotal, 2) }}
                    </td>

                </tr>


                <tr>

                    <td>
                        Discount
                    </td>

                    <td>
                        - ₹{{ number_format((float) $purchase->discount, 2) }}
                    </td>

                </tr>


                @if($purchase->tax_type === 'gst')

                <tr>

                    <td>
                        GST
                    </td>

                    <td>
                        ₹{{ number_format((float) $purchase->tax_amount, 2) }}
                    </td>

                </tr>

                @endif


                <tr>

                    <td>
                        Other Charges
                    </td>

                    <td>
                        ₹{{ number_format((float) $purchase->other_charges, 2) }}
                    </td>

                </tr>


                <tr class="grand-total">

                    <td>
                        Grand Total
                    </td>

                    <td>
                        ₹{{ number_format((float) $purchase->grand_total, 2) }}
                    </td>

                </tr>


                <tr>

                    <td>
                        Paid Amount
                    </td>

                    <td>
                        ₹{{ number_format((float) $purchase->paid_amount, 2) }}
                    </td>

                </tr>


                <tr class="balance">

                    <td>
                        Balance
                    </td>

                    <td>
                        ₹{{ number_format((float) $purchase->balance_amount, 2) }}
                    </td>

                </tr>

            </table>

        </div>


        {{-- =========================================================
             REMARKS
             ========================================================= --}}

        @if($purchase->remarks)

        <div class="remarks">

            <strong>
                Remarks
            </strong>

            <p>
                {{ $purchase->remarks }}
            </p>

        </div>

        @endif


        {{-- =========================================================
             SIGNATURE
             ========================================================= --}}

        <div class="footer">

            <div class="signature">
                Prepared By
            </div>


            <div class="signature">
                Checked By
            </div>


            <div class="signature">
                Authorized Signature
            </div>

        </div>

    </div>

</body>

</html>