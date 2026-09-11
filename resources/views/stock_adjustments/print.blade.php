<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        {{ $adjustment->adjustment_no }} - Stock Adjustment
    </title>

    <style id="page-size-style">
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
    </style>

    <style>
        :root {
            --ink: #172b38;
            --muted: #586d7a;
            --border: #738895;
            --soft-border: #c7d1d7;
            --panel: #edf2f4;
            --accent: #08776f;
            --accent-dark: #075c57;
            --danger: #a82c3e;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
        }

        body {
            color: var(--ink);
            background: #cbd3d8;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        button,
        select {
            font: inherit;
        }

        .print-toolbar {
            position: sticky;
            z-index: 100;
            top: 0;
            display: flex;
            min-height: 54px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 16px;
            border-bottom: 1px solid #788b97;
            color: #ffffff;
            background: linear-gradient(180deg, #344e5d, #253d4b);
            box-shadow: 0 3px 10px rgb(20 35 45 / 25%);
        }

        .toolbar-title,
        .toolbar-actions,
        .size-control {
            display: flex;
            align-items: center;
        }

        .toolbar-title {
            min-width: 0;
            gap: 9px;
        }

        .toolbar-title-icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border: 1px solid #55a9a2;
            border-radius: 4px;
            background: #08776f;
            font-size: 17px;
            font-weight: 800;
        }

        .toolbar-title strong,
        .toolbar-title span {
            display: block;
        }

        .toolbar-title strong {
            font-size: 12px;
        }

        .toolbar-title span {
            overflow: hidden;
            color: #c4d1d8;
            font-size: 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .toolbar-actions {
            gap: 7px;
        }

        .size-control {
            gap: 6px;
            padding: 4px 7px;
            border: 1px solid #6e8491;
            border-radius: 4px;
            background: rgb(255 255 255 / 8%);
        }

        .size-control label {
            color: #d5dfe4;
            font-size: 10px;
            font-weight: 700;
        }

        .size-control select {
            height: 29px;
            padding: 0 25px 0 8px;
            border: 1px solid #91a4af;
            border-radius: 3px;
            outline: none;
            color: #263f4d;
            background: #ffffff;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .toolbar-button {
            display: inline-flex;
            min-height: 35px;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0 12px;
            border: 1px solid;
            border-radius: 4px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 750;
            cursor: pointer;
        }

        .toolbar-button.print {
            border-color: #07625b;
            background: linear-gradient(#1d998f, #08746c);
        }

        .toolbar-button.pdf {
            border-color: #9d2638;
            background: linear-gradient(#d34d5e, #ad2c3e);
        }

        .toolbar-button.close {
            border-color: #80929d;
            background: linear-gradient(#687f8d, #4e6573);
        }

        .toolbar-button:hover {
            filter: brightness(1.08);
        }

        .preview-stage {
            display: flex;
            width: 100%;
            min-height: calc(100vh - 54px);
            align-items: flex-start;
            justify-content: center;
            padding: 18px;
        }

        .paper {
            width: 210mm;
            min-height: 297mm;
            padding: 9mm;
            overflow: hidden;
            border: 1px solid #a9b4bb;
            color: var(--ink);
            background: #ffffff;
            box-shadow: 0 8px 26px rgb(22 38 49 / 25%);
            transition: width 150ms ease, min-height 150ms ease;
        }

        .paper.paper-a5 {
            width: 148mm;
            min-height: 210mm;
            padding: 7mm;
            font-size: 9.5px;
        }

        .document-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 58mm;
            gap: 8mm;
            align-items: start;
            padding-bottom: 4mm;
            border-bottom: 2px solid #263f4d;
        }

        .company-block h1 {
            margin: 0;
            color: #14394a;
            font-size: 20px;
            font-weight: 850;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .company-block .branch-name {
            margin-top: 2px;
            color: var(--accent-dark);
            font-size: 11px;
            font-weight: 800;
        }

        .company-block address {
            max-width: 105mm;
            margin-top: 4px;
            color: var(--muted);
            font-size: 9.5px;
            font-style: normal;
            line-height: 1.4;
        }

        .company-contact {
            display: flex;
            flex-wrap: wrap;
            gap: 2px 10px;
            margin-top: 4px;
            color: #435b69;
            font-size: 9px;
        }

        .company-contact strong {
            color: #243e4c;
        }

        .document-title {
            border: 1px solid #546d7b;
            background: #f4f7f8;
        }

        .document-title>strong {
            display: block;
            padding: 6px 7px;
            color: #ffffff;
            background: #304b5a;
            font-size: 12px;
            letter-spacing: 0.6px;
            text-align: center;
            text-transform: uppercase;
        }

        .document-title dl {
            display: grid;
            grid-template-columns: 24mm minmax(0, 1fr);
            margin: 0;
        }

        .document-title dt,
        .document-title dd {
            min-width: 0;
            margin: 0;
            padding: 4px 6px;
            border-bottom: 1px solid #c7d1d7;
            font-size: 9.5px;
        }

        .document-title dt {
            color: #60727e;
            background: #e8edef;
            font-weight: 700;
        }

        .document-title dd {
            overflow: hidden;
            color: #203a49;
            background: #ffffff;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .document-title dt:last-of-type,
        .document-title dd:last-of-type {
            border-bottom: 0;
        }

        .document-overview {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin: 4mm 0;
            border: 1px solid var(--border);
        }

        .overview-item {
            min-width: 0;
            padding: 5px 7px;
            border-right: 1px solid var(--soft-border);
            background: #f7f9fa;
        }

        .overview-item:last-child {
            border-right: 0;
        }

        .overview-item span,
        .overview-item strong {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .overview-item span {
            color: var(--muted);
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .overview-item strong {
            margin-top: 1px;
            color: #203c4b;
            font-size: 10.5px;
        }

        .overview-item strong.increase {
            color: #087047;
        }

        .overview-item strong.decrease,
        .overview-item strong.cancelled {
            color: var(--danger);
        }

        .section-heading {
            display: flex;
            min-height: 27px;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 4px 7px;
            border: 1px solid #657b88;
            border-bottom: 0;
            color: #ffffff;
            background: #405968;
        }

        .section-heading strong {
            font-size: 10px;
            letter-spacing: 0.2px;
        }

        .section-heading span {
            color: #d6e0e5;
            font-size: 8.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            padding: 4px 5px;
            border: 1px solid #9cabb4;
            vertical-align: middle;
        }

        th {
            color: #263f4d;
            background: #e1e8eb;
            font-size: 8.5px;
            font-weight: 800;
            text-align: left;
        }

        td {
            color: #263d4a;
            font-size: 9px;
        }

        tbody tr:nth-child(even) td {
            background: #f6f8f9;
        }

        th.serial,
        td.serial {
            width: 7mm;
            text-align: center;
        }

        th.product {
            width: 47mm;
        }

        th.location {
            width: 27mm;
        }

        th.number,
        td.number {
            text-align: right;
        }

        td.product strong,
        td.product small {
            display: block;
        }

        td.product strong {
            color: #173848;
            font-size: 9.5px;
        }

        td.product small {
            margin-top: 1px;
            color: #687a85;
            font-size: 7.8px;
        }

        td.product small b {
            margin: 0 3px;
            color: #8c9aa3;
        }

        .location-label {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            padding: 2px 5px;
            border: 1px solid #9eb0bb;
            border-radius: 7px;
            color: #3d5665;
            background: #eef3f5;
            font-size: 7.8px;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .adjusted-in {
            color: #087047;
            font-weight: 800;
        }

        .adjusted-out {
            color: var(--danger);
            font-weight: 800;
        }

        .stock-after,
        .line-total {
            color: #15566d;
            font-weight: 800;
        }

        tfoot td {
            color: #203b49;
            background: #e4eaed;
            font-weight: 800;
        }

        .notes-audit-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(0, 0.7fr);
            gap: 4mm;
            margin-top: 4mm;
        }

        .box {
            border: 1px solid var(--border);
        }

        .box-title {
            padding: 4px 6px;
            border-bottom: 1px solid var(--border);
            color: #ffffff;
            background: #405968;
            font-size: 9px;
            font-weight: 800;
        }

        .box-body {
            min-height: 22mm;
            padding: 6px;
            color: #425b69;
            background: #fafbfb;
            font-size: 8.5px;
            white-space: pre-wrap;
        }

        .audit-list {
            margin: 0;
        }

        .audit-list div {
            display: grid;
            grid-template-columns: 23mm minmax(0, 1fr);
            border-bottom: 1px solid #d4dde2;
        }

        .audit-list div:last-child {
            border-bottom: 0;
        }

        .audit-list dt,
        .audit-list dd {
            margin: 0;
            padding: 3px 5px;
            font-size: 8px;
        }

        .audit-list dt {
            color: #647783;
            background: #edf2f4;
            font-weight: 700;
        }

        .audit-list dd {
            overflow: hidden;
            color: #2f4958;
            font-weight: 750;
            text-align: right;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cancel-box {
            margin-top: 4mm;
            border: 1px solid #c97a87;
            color: #842838;
            background: #fff0f2;
        }

        .cancel-box strong {
            display: block;
            padding: 4px 6px;
            border-bottom: 1px solid #d698a2;
            font-size: 9px;
        }

        .cancel-box p {
            margin: 0;
            padding: 5px 6px;
            color: #8d4d5a;
            font-size: 8.5px;
        }

        .signature-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14mm;
            margin-top: 14mm;
        }

        .signature {
            padding-top: 3px;
            border-top: 1px solid #516875;
            color: #4b606d;
            font-size: 8px;
            font-weight: 700;
            text-align: center;
        }

        .document-footer {
            margin-top: 6mm;
            padding-top: 3px;
            border-top: 1px solid #aebbc3;
            color: #75848d;
            font-size: 7.5px;
            text-align: center;
        }

        .paper.paper-a5 .document-header {
            grid-template-columns: minmax(0, 1fr) 45mm;
            gap: 4mm;
        }

        .paper.paper-a5 .company-block h1 {
            font-size: 15px;
        }

        .paper.paper-a5 .company-block address {
            max-width: 70mm;
            font-size: 8px;
        }

        .paper.paper-a5 .company-contact {
            display: block;
            font-size: 7.5px;
        }

        .paper.paper-a5 .document-title>strong {
            font-size: 9px;
        }

        .paper.paper-a5 .document-title dl {
            grid-template-columns: 18mm minmax(0, 1fr);
        }

        .paper.paper-a5 .document-title dt,
        .paper.paper-a5 .document-title dd {
            padding: 3px 4px;
            font-size: 7.5px;
        }

        .paper.paper-a5 .document-overview {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .paper.paper-a5 .overview-item:nth-child(2) {
            border-right: 0;
        }

        .paper.paper-a5 .overview-item:nth-child(-n + 2) {
            border-bottom: 1px solid var(--soft-border);
        }

        .paper.paper-a5 th,
        .paper.paper-a5 td {
            padding: 3px;
            font-size: 7.2px;
        }

        .paper.paper-a5 th.product {
            width: 30mm;
        }

        .paper.paper-a5 th.location {
            width: 18mm;
        }

        .paper.paper-a5 td.product strong {
            font-size: 7.5px;
        }

        .paper.paper-a5 td.product small {
            font-size: 6.3px;
        }

        .paper.paper-a5 .notes-audit-grid {
            grid-template-columns: 1fr;
        }

        .paper.paper-a5 .signature-row {
            gap: 6mm;
            margin-top: 10mm;
        }

        @media (max-width: 760px) {
            .print-toolbar {
                position: static;
                align-items: stretch;
                flex-direction: column;
            }

            .toolbar-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .size-control {
                grid-column: 1 / -1;
                justify-content: space-between;
            }

            .preview-stage {
                justify-content: flex-start;
                overflow-x: auto;
                padding: 10px;
            }

            .paper {
                flex: 0 0 auto;
            }
        }

        @media print {

            html,
            body {
                width: auto;
                min-height: 0;
                background: #ffffff;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .preview-stage {
                display: block;
                width: auto;
                min-height: 0;
                padding: 0;
            }

            .paper,
            .paper.paper-a5 {
                width: auto;
                min-height: 0;
                padding: 0;
                overflow: visible;
                border: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

@php
$companyName = $adjustment->company?->company_name
?? $adjustment->company?->name
?? 'Company';

$storeName = $adjustment->store?->store_name
?? $adjustment->store?->name
?? 'Branch';

$companyAddress = $adjustment->company?->address
?? $adjustment->company?->billing_address
?? null;

$companyPhone = $adjustment->company?->phone
?? $adjustment->company?->mobile
?? null;

$companyGst = $adjustment->company?->gst_number
?? $adjustment->company?->gstin
?? null;

$items = $adjustment->items ?? collect();
$totalQuantity = (float) $items->sum('qty');
$totalCost = (float) $items->sum('total_cost');
$isIncrease = $adjustment->type === 'increase';
$isCompleted = $adjustment->status === 'Completed';
@endphp

<body>
    <div class="print-toolbar no-print">
        <div class="toolbar-title">
            <div class="toolbar-title-icon">SA</div>

            <div>
                <strong>Stock Adjustment Print Preview</strong>
                <span>{{ $adjustment->adjustment_no }}</span>
            </div>
        </div>

        <div class="toolbar-actions">
            <div class="size-control">
                <label for="paper-size">Paper Size</label>

                <select
                    id="paper-size"
                    onchange="changePaperSize(this.value)">
                    <option value="a4" selected>A4</option>
                    <option value="a5">A5</option>
                </select>
            </div>

            <button
                type="button"
                class="toolbar-button print"
                onclick="window.print()">
                <span aria-hidden="true">▣</span>
                Print
            </button>

            <button
                type="button"
                class="toolbar-button pdf"
                onclick="downloadPdf()">
                <span aria-hidden="true">↓</span>
                PDF
            </button>

            <button
                type="button"
                class="toolbar-button close"
                onclick="closePreview()">
                <span aria-hidden="true">×</span>
                Close
            </button>
        </div>
    </div>

    <main class="preview-stage">
        <article
            id="print-paper"
            class="paper paper-a4">
            <header class="document-header">
                <div class="company-block">
                    <h1>{{ $companyName }}</h1>
                    <div class="branch-name">{{ $storeName }}</div>

                    @if ($companyAddress)
                    <address>{{ $companyAddress }}</address>
                    @endif

                    <div class="company-contact">
                        @if ($companyPhone)
                        <span>
                            <strong>Phone:</strong> {{ $companyPhone }}
                        </span>
                        @endif

                        @if ($companyGst)
                        <span>
                            <strong>GSTIN:</strong> {{ $companyGst }}
                        </span>
                        @endif
                    </div>
                </div>

                <div class="document-title">
                    <strong>Stock Adjustment</strong>

                    <dl>
                        <dt>Number</dt>
                        <dd>{{ $adjustment->adjustment_no }}</dd>

                        <dt>Date</dt>
                        <dd>
                            {{ optional($adjustment->adjustment_date)->format('d-m-Y') }}
                        </dd>

                        <dt>Status</dt>
                        <dd>{{ $adjustment->status }}</dd>
                    </dl>
                </div>
            </header>

            <section class="document-overview">
                <div class="overview-item">
                    <span>Adjustment Type</span>
                    <strong class="{{ $isIncrease ? 'increase' : 'decrease' }}">
                        {{ $isIncrease ? 'Stock Increase' : 'Stock Decrease' }}
                    </strong>
                </div>

                <div class="overview-item">
                    <span>Total Items</span>
                    <strong>{{ number_format($items->count()) }}</strong>
                </div>

                <div class="overview-item">
                    <span>Total Quantity</span>
                    <strong>{{ number_format($totalQuantity, 2) }}</strong>
                </div>

                <div class="overview-item">
                    <span>Stock Value</span>
                    <strong>₹{{ number_format($totalCost, 2) }}</strong>
                </div>
            </section>

            <section>
                <div class="section-heading">
                    <strong>Stock Movement Details</strong>
                    <span>Before and after balances recorded at entry time</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th class="serial">#</th>
                            <th class="product">Product</th>
                            <th class="location">Location</th>
                            <th class="number">Before</th>
                            <th class="number">Adjusted</th>
                            <th class="number">After</th>
                            <th class="number">Unit Cost</th>
                            <th class="number">Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($items as $index => $item)
                        @php
                        $product = $item->product;
                        $productName = $product->product_name
                        ?? 'Unknown Product';
                        $productCode = $product->product_code
                        ?? '—';
                        $partNumber = $product->part_number
                        ?? '—';
                        $location = $item->godown_id === null
                        ? 'Shop'
                        : ($item->godown?->godown_name ?? 'Godown');
                        @endphp

                        <tr>
                            <td class="serial">{{ $index + 1 }}</td>

                            <td class="product">
                                <strong>{{ $productName }}</strong>
                                <small>
                                    {{ $productCode }}
                                    <b>•</b>
                                    Part: {{ $partNumber }}
                                </small>
                            </td>

                            <td>
                                <span class="location-label">
                                    {{ $location }}
                                </span>
                            </td>

                            <td class="number">
                                {{ number_format((float) $item->stock_before, 2) }}
                            </td>

                            <td class="number {{ $isIncrease ? 'adjusted-in' : 'adjusted-out' }}">
                                {{ $isIncrease ? '+' : '−' }}{{ number_format((float) $item->qty, 2) }}
                            </td>

                            <td class="number stock-after">
                                {{ number_format((float) $item->stock_after, 2) }}
                            </td>

                            <td class="number">
                                ₹{{ number_format((float) $item->unit_cost, 2) }}
                            </td>

                            <td class="number line-total">
                                ₹{{ number_format((float) $item->total_cost, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="padding: 15px; text-align: center;">
                                No adjustment items found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    @if ($items->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                Document Total
                            </td>
                            <td class="number">
                                {{ number_format($totalQuantity, 2) }}
                            </td>
                            <td colspan="2"></td>
                            <td class="number">
                                ₹{{ number_format($totalCost, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </section>

            <section class="notes-audit-grid">
                <div class="box">
                    <div class="box-title">Reason / Remarks</div>
                    <div class="box-body">{{ $adjustment->remarks ?: 'No remarks entered.' }}</div>
                </div>

                <div class="box">
                    <div class="box-title">Audit Information</div>

                    <dl class="audit-list">
                        <div>
                            <dt>Created By</dt>
                            <dd>{{ $adjustment->creator?->name ?? 'System' }}</dd>
                        </div>

                        <div>
                            <dt>Created At</dt>
                            <dd>
                                {{ optional($adjustment->created_at)->format('d-m-Y h:i A') }}
                            </dd>
                        </div>

                        <div>
                            <dt>Last Updated</dt>
                            <dd>
                                {{ optional($adjustment->updated_at)->format('d-m-Y h:i A') }}
                            </dd>
                        </div>

                        @if (!$isCompleted)
                        <div>
                            <dt>Cancelled By</dt>
                            <dd>
                                {{ $adjustment->cancelledBy?->name ?? 'System' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Cancelled At</dt>
                            <dd>
                                {{ optional($adjustment->cancelled_at)->format('d-m-Y h:i A') }}
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </section>

            @if (!$isCompleted)
            <section class="cancel-box">
                <strong>Cancelled Adjustment</strong>
                <p>
                    {{ $adjustment->cancel_reason ?: 'No cancellation reason recorded.' }}
                </p>
            </section>
            @endif

            <section class="signature-row">
                <div class="signature">Prepared By</div>
                <div class="signature">Verified By</div>
                <div class="signature">Authorised Signature</div>
            </section>

            <footer class="document-footer">
                Computer-generated stock adjustment document.
                Stock movements are recorded in the inventory ledger.
            </footer>
        </article>
    </main>

    <script>
        function changePaperSize(size) {
            const paper = document.getElementById('print-paper');
            const pageStyle = document.getElementById('page-size-style');
            const normalizedSize = size === 'a5' ? 'a5' : 'a4';

            paper.classList.remove('paper-a4', 'paper-a5');
            paper.classList.add(`paper-${normalizedSize}`);

            pageStyle.textContent = normalizedSize === 'a5' ?
                '@page { size: A5 portrait; margin: 7mm; }' :
                '@page { size: A4 portrait; margin: 8mm; }';
        }

        function closePreview() {
            const detailsUrl = @json(route('stock-adjustments.show', $adjustment));

            if (window.opener && !window.opener.closed) {
                window.close();

                window.setTimeout(() => {
                    window.location.href = detailsUrl;
                }, 150);

                return;
            }

            if (window.history.length > 1) {
                window.history.back();
                return;
            }

            window.location.href = detailsUrl;
        }

        function downloadPdf() {
            const paperSize = document.getElementById('paper-size').value;
            const baseUrl = @json(route('stock-adjustments.pdf', $adjustment));

            window.location.href = `${baseUrl}?paper=${paperSize}`;
        }
    </script>
</body>

</html>