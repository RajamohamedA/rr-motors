<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Invoice {{ $sale->invoice_no }}</title>

    <style id="dynamic-page-size">
        @page {
            size: A5 portrait;
            margin: 7mm;
        }
    </style>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            color: #000;
            background: #dfe5eb;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.3;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 7px;
            padding: 10px;
            border-bottom: 1px solid #aebac6;
            background: linear-gradient(180deg, #f8fafc, #e5eaf0);
            box-shadow: 0 2px 8px rgb(20 35 55 / 14%);
        }

        .toolbar button {
            min-height: 34px;
            padding: 0 13px;
            border: 1px solid #07568f;
            border-radius: 4px;
            color: #fff;
            background: linear-gradient(180deg, #2785c7, #0a5d9b);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .toolbar button:hover {
            background: linear-gradient(180deg, #1978b9, #064d82);
        }

        .toolbar button.secondary {
            border-color: #a6b2bf;
            color: #2b3a4b;
            background: linear-gradient(180deg, #fff, #e5eaf0);
        }

        .paper {
            margin: 14px auto 24px;
            color: #000;
            background: #fff;
            box-shadow: 0 8px 28px rgb(20 35 55 / 20%);
        }

        .paper.a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm;
        }

        .paper.a5 {
            width: 148mm;
            min-height: 210mm;
            padding: 7mm;
        }

        .paper.thermal {
            width: 80mm;
            min-height: 80mm;
            padding: 3mm 2mm 5mm;
            font-size: 10px;
        }

        .company {
            text-align: center;
        }

        .company h1 {
            margin: 0 0 3px;
            font-size: 22px;
            font-weight: 900;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .company p {
            margin: 1px 0;
        }

        .bill-title {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 14px;
            color: #fff;
            background: #000;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 18px;
            margin: 9px 0;
            padding: 7px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .meta-row,
        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .meta-row strong {
            text-align: right;
        }

        .customer {
            margin: 7px 0;
            padding: 6px;
            border: 1px solid #777;
        }

        .customer strong,
        .customer span {
            display: block;
        }

        .customer .caption {
            margin-bottom: 2px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .items,
        .tax-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .items th,
        .items td {
            padding: 5px 4px;
            border: 1px solid #555;
            vertical-align: top;
        }

        .items th {
            font-size: 10px;
            text-transform: uppercase;
        }

        .items .serial {
            width: 7%;
            text-align: center;
        }

        .items .description {
            width: auto;
        }

        .items .qty {
            width: 11%;
        }

        .items .rate {
            width: 17%;
        }

        .items .amount {
            width: 19%;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .product-name {
            display: block;
            font-weight: 800;
        }

        .product-meta,
        .item-note {
            display: block;
            margin-top: 2px;
            font-size: 9px;
        }

        .summary {
            width: min(100%, 330px);
            margin: 9px 0 0 auto;
        }

        .total-row {
            padding: 3px 2px;
            border-bottom: 1px dotted #777;
        }

        .total-row.grand {
            margin-top: 3px;
            padding: 6px 3px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-size: 16px;
            font-weight: 900;
        }

        .total-row.balance {
            font-size: 13px;
            font-weight: 900;
        }

        .tax-title {
            margin: 10px 0 3px;
            font-size: 10px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .tax-table th,
        .tax-table td {
            padding: 3px;
            border: 1px solid #777;
            font-size: 9px;
            text-align: right;
        }

        .tax-table th:first-child,
        .tax-table td:first-child {
            text-align: left;
        }

        .footer {
            margin-top: 13px;
            padding-top: 7px;
            border-top: 1px dashed #000;
            text-align: center;
        }

        .footer strong,
        .footer span {
            display: block;
        }

        .footer strong {
            font-size: 13px;
        }

        .paper.thermal .company h1 {
            font-size: 16px;
        }

        .paper.thermal .company p {
            font-size: 9px;
        }

        .paper.thermal .bill-title {
            margin-top: 4px;
            padding: 2px 10px;
            font-size: 10px;
        }

        .paper.thermal .meta {
            gap: 2px 7px;
            margin: 5px 0;
            padding: 4px 0;
            font-size: 9px;
        }

        .paper.thermal .customer {
            padding: 4px 2px;
            border-right: 0;
            border-left: 0;
            font-size: 9px;
        }

        .paper.thermal .items th,
        .paper.thermal .items td {
            padding: 3px 1px;
            border-right: 0;
            border-left: 0;
            font-size: 9px;
        }

        .paper.thermal .items .serial {
            width: 6%;
        }

        .paper.thermal .items .qty {
            width: 11%;
        }

        .paper.thermal .items .rate {
            width: 17%;
        }

        .paper.thermal .items .amount {
            width: 20%;
        }

        .paper.thermal .product-meta,
        .paper.thermal .item-note {
            font-size: 8px;
        }

        .paper.thermal .summary {
            width: 100%;
            margin-top: 5px;
        }

        .paper.thermal .total-row {
            padding: 2px 1px;
            font-size: 9px;
        }

        .paper.thermal .total-row.grand {
            padding: 4px 2px;
            font-size: 14px;
        }

        .paper.thermal .total-row.balance {
            font-size: 11px;
        }

        .paper.thermal .footer {
            margin-top: 8px;
            font-size: 9px;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .paper {
                min-height: 0;
                margin: 0 auto;
                padding: 0;
                box-shadow: none;
            }

            .paper.a4 {
                width: 190mm;
            }

            .paper.a5 {
                width: 134mm;
            }

            .paper.thermal {
                width: 76mm;
            }

            .items tr,
            .tax-table tr,
            .total-row,
            .customer {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    @php
    $isGstBill = ($sale->billing_type ?? 'gst') === 'gst';
    $company = $sale->company;
    $store = $sale->store;
    $customer = $sale->customer;
    $items = $sale->items ?? collect();

    $totalQuantity = $items->sum(
    fn ($item) =>
    (float) $item->quantity
    + (float) $item->free_quantity
    );

    $taxSummary = $items
    ->groupBy(
    fn ($item) => number_format(
    (float) $item->gst_percent,
    2,
    '.',
    ''
    )
    )
    ->map(function ($group, $rate) {
    $taxable = $group->sum(function ($item) {
    $gross =
    (float) $item->quantity
    * (float) $item->sale_price;

    return max(
    0,
    $gross - (float) $item->discount
    );
    });

    $gst = $group->sum(
    fn ($item) => (float) $item->gst_amount
    );

    return [
    'rate' => (float) $rate,
    'taxable' => $taxable,
    'cgst' => $gst / 2,
    'sgst' => $gst / 2,
    'gst' => $gst,
    ];
    });
    @endphp

    <nav class="toolbar">
        <button
            type="button"
            onclick="printBill('thermal')">
            Quick Bill 80mm
        </button>

        <button
            type="button"
            onclick="printBill('a5')">
            Print A5
        </button>

        <button
            type="button"
            onclick="printBill('a4')">
            Print A4
        </button>

        <button
            type="button"
            class="secondary"
            onclick="window.close()">
            Close
        </button>
    </nav>

    <main
        id="invoice-paper"
        class="paper a5">
        <header class="company">
            <h1>{{ $company->company_name ?? 'RR Motors' }}</h1>

            @if($store?->store_name)
            <p><strong>{{ $store->store_name }}</strong></p>
            @endif

            @if($store?->address || $store?->city || $store?->pincode)
            <p>
                {{ $store->address }}
                {{ $store->city ? ', ' . $store->city : '' }}
                {{ $store->pincode ? ' - ' . $store->pincode : '' }}
            </p>
            @endif

            @if($store?->phone ?? $company?->phone)
            <p>Phone: {{ $store->phone ?? $company->phone }}</p>
            @endif

            @if($isGstBill && $company?->gst_number)
            <p><strong>GSTIN: {{ $company->gst_number }}</strong></p>
            @endif

            <div class="bill-title">
                {{ $isGstBill ? 'Tax Invoice' : 'Cash Bill' }}
            </div>
        </header>

        <section class="meta">
            <div class="meta-row">
                <span>Bill No</span>
                <strong>{{ $sale->invoice_no }}</strong>
            </div>

            <div class="meta-row">
                <span>Date</span>
                <strong>
                    {{ optional($sale->invoice_date)->format('d/m/Y') }}
                </strong>
            </div>

            <div class="meta-row">
                <span>Time</span>
                <strong>
                    {{ optional($sale->created_at)->format('h:i A') }}
                </strong>
            </div>

            <div class="meta-row">
                <span>Cashier</span>
                <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
            </div>
        </section>

        @if($customer)
        <section class="customer">
            <span class="caption">Customer</span>
            <strong>{{ $customer->customer_name ?? 'Customer' }}</strong>

            @if($customer->customer_code)
            <span>Code: {{ $customer->customer_code }}</span>
            @endif

            @if($customer->mobile ?? $customer->phone)
            <span>
                Phone: {{ $customer->mobile ?? $customer->phone }}
            </span>
            @endif

            @if($isGstBill && $customer->gst_number)
            <span>GSTIN: {{ $customer->gst_number }}</span>
            @endif
        </section>
        @endif

        <table class="items">
            <thead>
                <tr>
                    <th class="serial">#</th>
                    <th class="description">Description</th>
                    <th class="qty number">Qty</th>
                    <th class="rate number">Rate</th>
                    <th class="amount number">Amount</th>
                </tr>
            </thead>

            <tbody>
                @forelse($items as $index => $item)
                <tr>
                    <td class="serial">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        <span class="product-name">
                            {{ $item->product->product_name ?? 'Product' }}
                        </span>

                        @if($item->product?->product_code)
                        <span class="product-meta">
                            {{ $item->product->product_code }}
                        </span>
                        @endif

                        @if((float) $item->free_quantity > 0)
                        <span class="item-note">
                            Free:
                            {{ number_format((float) $item->free_quantity, 2) }}
                        </span>
                        @endif

                        @if((float) $item->discount > 0)
                        <span class="item-note">
                            Discount:
                            {{ $item->discount_type === 'percentage'
                                        ? number_format((float) $item->discount_value, 2) . '%'
                                        : 'Rs.' . number_format((float) $item->discount, 2) }}
                        </span>
                        @endif

                        @if($isGstBill && (float) $item->gst_percent > 0)
                        <span class="item-note">
                            GST:
                            {{ number_format((float) $item->gst_percent, 2) }}%
                        </span>
                        @endif
                    </td>

                    <td class="number">
                        {{ number_format((float) $item->quantity, 2) }}
                    </td>

                    <td class="number">
                        {{ number_format((float) $item->sale_price, 2) }}
                    </td>

                    <td class="number">
                        <strong>
                            {{ number_format((float) $item->line_total, 2) }}
                        </strong>
                    </td>
                </tr>
                @empty
                <tr>
                    <td
                        colspan="5"
                        style="text-align: center">
                        No items
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <section class="summary">
            <div class="total-row">
                <span>Items / Quantity</span>
                <strong>
                    {{ $items->count() }} /
                    {{ number_format($totalQuantity, 2) }}
                </strong>
            </div>

            <div class="total-row">
                <span>
                    {{ $isGstBill ? 'Taxable Amount' : 'Subtotal' }}
                </span>
                <strong>
                    {{ number_format((float) $sale->sub_total, 2) }}
                </strong>
            </div>

            @if($isGstBill)
            <div class="total-row">
                <span>GST</span>
                <strong>
                    {{ number_format((float) $sale->gst_total, 2) }}
                </strong>
            </div>
            @endif

            @if((float) $sale->final_discount > 0)
            <div class="total-row">
                <span>Discount</span>
                <strong>
                    -{{ number_format((float) $sale->final_discount, 2) }}
                </strong>
            </div>
            @endif

            @if((float) $sale->round_off !== 0.0)
            <div class="total-row">
                <span>Round Off</span>
                <strong>
                    {{ (float) $sale->round_off >= 0 ? '+' : '-' }}
                    {{ number_format(abs((float) $sale->round_off), 2) }}
                </strong>
            </div>
            @endif

            <div class="total-row grand">
                <span>Total</span>
                <strong>
                    Rs.{{ number_format((float) $sale->grand_total, 2) }}
                </strong>
            </div>

            <div class="total-row">
                <span>Paid</span>
                <strong>
                    Rs.{{ number_format((float) $sale->paid_amount, 2) }}
                </strong>
            </div>

            <div class="total-row balance">
                <span>Due Balance</span>
                <strong>
                    Rs.{{ number_format((float) $sale->balance_amount, 2) }}
                </strong>
            </div>
        </section>

        @if($isGstBill && (float) $sale->gst_total > 0)
        <div class="tax-title">GST Summary</div>

        <table class="tax-table">
            <thead>
                <tr>
                    <th>Tax %</th>
                    <th>Taxable</th>
                    <th>CGST</th>
                    <th>SGST</th>
                    <th>GST</th>
                </tr>
            </thead>

            <tbody>
                @foreach($taxSummary as $tax)
                <tr>
                    <td>{{ number_format($tax['rate'], 2) }}%</td>
                    <td>{{ number_format($tax['taxable'], 2) }}</td>
                    <td>{{ number_format($tax['cgst'], 2) }}</td>
                    <td>{{ number_format($tax['sgst'], 2) }}</td>
                    <td>{{ number_format($tax['gst'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($sale->remarks)
        <section class="customer">
            <span class="caption">Remarks</span>
            <span>{{ $sale->remarks }}</span>
        </section>
        @endif

        <footer class="footer">
            <strong>Thank You — Visit Again</strong>
            <span>This is a computer-generated invoice.</span>
        </footer>
    </main>

    <script>
        function printBill(size) {
            const paper = document.getElementById('invoice-paper');
            const pageStyle = document.getElementById('dynamic-page-size');

            paper.className = `paper ${size}`;

            if (size === 'thermal') {
                pageStyle.textContent = '@page { size: 80mm auto; margin: 0; }';
            } else if (size === 'a4') {
                pageStyle.textContent = '@page { size: A4 portrait; margin: 10mm; }';
            } else {
                pageStyle.textContent = '@page { size: A5 portrait; margin: 7mm; }';
            }

            window.setTimeout(function() {
                window.print();
            }, 80);
        }
    </script>
</body>

</html>