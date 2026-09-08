@php
$isGstBill = ($salesReturn->billing_type ?? 'gst') === 'gst';
$customer = $salesReturn->customer;
$company = $salesReturn->company;
$store = $salesReturn->store;
$sale = $salesReturn->sale;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Sales Return {{ $salesReturn->return_no }}</title>

    <style id="page-size-style">
        @page {
            size: A4 portrait;
            margin: 10mm;
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
            color: #111827;
            background: #dfe5eb;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 7px;
            padding: 9px;
            border-bottom: 1px solid #aebac6;
            background: linear-gradient(180deg, #fff, #e5eaf0);
            box-shadow: 0 2px 8px rgb(20 35 55 / 14%);
        }

        .toolbar button,
        .toolbar a {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            justify-content: center;
            padding: 0 13px;
            border: 1px solid #a6b2bf;
            border-radius: 4px;
            color: #2b3a4b;
            background: linear-gradient(180deg, #fff, #e5eaf0);
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar button.primary {
            color: #fff;
            border-color: #07568f;
            background: linear-gradient(180deg, #2785c7, #0a5d9b);
        }

        .toolbar button.active {
            color: #fff;
            border-color: #07568f;
            background: #0a5d9b;
        }

        .paper {
            display: block;
            width: 210mm;
            max-width: calc(100vw - 28px);
            min-height: 297mm;
            margin: 14px auto 24px;
            padding: 10mm;
            background: #fff;
            border: 1px solid #8f9dab;
            box-shadow: 0 8px 28px rgb(20 35 55 / 20%);
        }

        .header td {
            width: 50%;
            vertical-align: top;
        }

        .company-name {
            margin: 0 0 3px;
            color: #0b5790;
            font-size: 20px;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .muted {
            color: #59687a;
        }

        .document-title {
            display: inline-block;
            min-width: 190px;
            padding: 7px 10px;
            border: 1px solid #174f79;
            color: #17324d;
            background: #eef5fa;
            font-size: 19px;
            font-weight: 800;
            text-align: right;
            text-transform: uppercase;
        }

        .document-status {
            margin-top: 5px;
            font-size: 10px;
            font-weight: 700;
            text-align: right;
            text-transform: uppercase;
        }

        .document-side {
            text-align: right;
        }

        .document-status span {
            display: inline-block;
            padding: 3px 7px;
            border: 1px solid #7f9db7;
        }

        .rule {
            margin: 8px 0;
            border-top: 2px solid #17679f;
        }

        .info-grid td {
            width: 33.333%;
            padding: 0 4px 7px 0;
            vertical-align: top;
        }

        .info-box {
            min-height: 88px;
            padding: 7px 8px;
            border: 1px solid #9eacba;
        }

        .info-box h3 {
            margin: -7px -8px 6px;
            padding: 5px 7px;
            border-bottom: 1px solid #9eacba;
            color: #fff;
            background: #286b9a;
            font-size: 10px;
            text-transform: uppercase;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 2px 0;
            padding-bottom: 1px;
            border-bottom: 1px dotted #d0d7df;
        }

        .info-row strong {
            overflow-wrap: anywhere;
            text-align: right;
        }

        .info-primary {
            margin-bottom: 4px;
            color: #17324d;
            font-size: 12px;
            font-weight: 800;
        }

        .items th {
            padding: 5px 4px;
            border: 1px solid #66798c;
            color: #fff;
            background: #285f88;
            font-size: 9px;
            text-align: left;
            white-space: nowrap;
        }

        .items td {
            padding: 5px 4px;
            border: 1px solid #aeb8c3;
            vertical-align: top;
        }

        .items tbody tr:nth-child(even) {
            background: #f1f5f8;
        }

        .number {
            text-align: right !important;
            white-space: nowrap;
        }

        .product-meta {
            display: block;
            margin-top: 1px;
            color: #667587;
            font-size: 9px;
        }

        .bottom-grid {
            margin-top: 8px;
        }

        .bottom-grid>tbody>tr>td {
            width: 50%;
            vertical-align: top;
        }

        .remarks {
            min-height: 72px;
            padding: 7px;
            border: 1px solid #aeb8c3;
            white-space: pre-wrap;
        }

        .totals {
            margin-left: auto;
            width: 96%;
        }

        .totals td {
            padding: 4px 6px;
            border-bottom: 1px dotted #aeb8c3;
        }

        .totals td:last-child {
            text-align: right;
        }

        .totals .grand td {
            border: 1px solid #164f79;
            color: #fff;
            background: #17679f;
            font-size: 13px;
            font-weight: 800;
        }

        .cancelled-note {
            margin-top: 8px;
            padding: 7px;
            border: 1px solid #b42335;
            color: #9f1f30;
            text-align: center;
            font-weight: 800;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 14px;
            color: #526174;
            text-align: center;
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
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                border: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button
            type="button"
            class="paper-button active"
            data-paper="a4">
            A4
        </button>

        <button
            type="button"
            class="paper-button"
            data-paper="a5">
            A5
        </button>

        <button
            id="print-button"
            type="button"
            class="primary"
            onclick="window.print()">
            Print Return
        </button>

        <a href="{{ route('sales-returns.show', $salesReturn) }}">
            Back
        </a>
    </div>

    <main
        id="print-paper"
        class="paper"
        data-paper="a4">
        <table class="header">
            <tr>
                <td>
                    <h1 class="company-name">
                        {{ $company->company_name ?? 'RR Motors' }}
                    </h1>
                    <div>{{ $store->store_name ?? '' }}</div>
                    <div class="muted">
                        {{ collect([
                            $store->address ?? $company->address ?? null,
                            $store->city ?? $company->city ?? null,
                            $store->state ?? $company->state ?? null,
                            $store->pincode ?? $company->pincode ?? null,
                        ])->filter()->join(', ') }}
                    </div>
                    <div class="muted">
                        Phone: {{ $store->phone ?? $company->phone ?? '-' }}
                    </div>
                    @if ($isGstBill)
                    <div>
                        <strong>GSTIN:</strong>
                        {{ $company->gst_number ?? '-' }}
                    </div>
                    @endif
                </td>
                <td class="document-side">
                    <div class="document-title">Sales Return</div>
                    <div class="document-status">
                        <span>
                            {{ strtoupper($salesReturn->status) }} ·
                            {{ $isGstBill ? 'GST' : 'NON-GST' }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="rule"></div>

        <table class="info-grid">
            <tr>
                <td>
                    <div class="info-box">
                        <h3>Return Details</h3>
                        <div class="info-row">
                            <span>Return No.</span>
                            <strong>{{ $salesReturn->return_no }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Return Date</span>
                            <strong>
                                {{ optional($salesReturn->return_date)->format('d-m-Y') }}
                            </strong>
                        </div>
                        <div class="info-row">
                            <span>Invoice No.</span>
                            <strong>{{ $sale->invoice_no ?? '-' }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Bill Type</span>
                            <strong>{{ $isGstBill ? 'GST' : 'Non-GST' }}</strong>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="info-box">
                        <h3>Customer</h3>
                        <div class="info-primary">
                            {{ $customer->customer_name ?? 'Walk-in Customer' }}
                        </div>
                        @if (filled($customer->mobile ?? $customer->phone ?? null))
                        <div>
                            Mobile: {{ $customer->mobile ?? $customer->phone }}
                        </div>
                        @endif
                        @if (filled($customer->customer_code ?? null))
                        <div>Code: {{ $customer->customer_code }}</div>
                        @endif
                        @if ($isGstBill && filled($customer->gst_number ?? null))
                        <div>GSTIN: {{ $customer->gst_number }}</div>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="info-box">
                        <h3>Refund & Return</h3>
                        <div class="info-row">
                            <span>Method</span>
                            <strong>
                                {{ str($salesReturn->refund_method)->replace('_', ' ')->title() }}
                            </strong>
                        </div>
                        <div class="info-row">
                            <span>Refund</span>
                            <strong>{{ str($salesReturn->refund_status)->title() }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Return Status</span>
                            <strong>{{ str($salesReturn->status)->title() }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Total Qty</span>
                            <strong>
                                {{ number_format((float) $salesReturn->items->sum('quantity'), 2) }}
                            </strong>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="number">Qty</th>
                    <th class="number">Free</th>
                    <th class="number">Rate</th>
                    <th class="number">Discount</th>
                    <th class="number">Taxable</th>
                    @if ($isGstBill)
                    <th class="number">GST</th>
                    @endif
                    <th class="number">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($salesReturn->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>
                            {{ $item->product->product_name ?? 'Product' }}
                        </strong>
                        <span class="product-meta">
                            {{ collect([
                                    $item->product->product_code ?? null,
                                    $item->product->model ?? null,
                                    $item->product->model_name ?? null,
                                    $item->product->part_number ?? null,
                                ])->filter()->unique()->join(' · ') }}
                        </span>
                    </td>
                    <td class="number">
                        {{ number_format((float) $item->quantity, 2) }}
                    </td>
                    <td class="number">
                        {{ number_format((float) $item->free_quantity, 2) }}
                    </td>
                    <td class="number">
                        {{ number_format((float) $item->sale_price, 2) }}
                    </td>
                    <td class="number">
                        {{ number_format((float) $item->discount_amount, 2) }}
                    </td>
                    <td class="number">
                        {{ number_format((float) $item->taxable_amount, 2) }}
                    </td>
                    @if ($isGstBill)
                    <td class="number">
                        {{ number_format((float) $item->gst_percent, 2) }}%
                        <br>
                        {{ number_format((float) $item->gst_amount, 2) }}
                    </td>
                    @endif
                    <td class="number">
                        <strong>
                            {{ number_format((float) $item->line_total, 2) }}
                        </strong>
                    </td>
                </tr>
                @empty
                <tr>
                    <td
                        colspan="{{ $isGstBill ? 9 : 8 }}"
                        style="padding: 18px; text-align: center;">
                        No returned products found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <table class="bottom-grid">
            <tr>
                <td style="padding-right: 8px;">
                    <div class="remarks">
                        <strong>Remarks</strong>
                        <div>
                            {{ $salesReturn->remarks ?: 'No remarks.' }}
                        </div>
                    </div>
                </td>
                <td>
                    <table class="totals">
                        <tr>
                            <td>Subtotal</td>
                            <td>
                                {{ number_format((float) $salesReturn->sub_total, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td>
                                - {{ number_format((float) $salesReturn->discount_total, 2) }}
                            </td>
                        </tr>
                        @if ($isGstBill)
                        <tr>
                            <td>GST Total</td>
                            <td>
                                {{ number_format((float) $salesReturn->gst_total, 2) }}
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td>Round Off</td>
                            <td>
                                {{ number_format((float) $salesReturn->round_off, 2) }}
                            </td>
                        </tr>
                        <tr class="grand">
                            <td>Return Total</td>
                            <td>
                                ₹ {{ number_format((float) $salesReturn->total_amount, 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        @if ($salesReturn->status === 'cancelled')
        <div class="cancelled-note">
            Cancelled — Stock movement reversed
        </div>
        @endif

        <div class="footer">
            Computer-generated sales return note · No signature required
        </div>
    </main>

    <script>
        const pageStyle = document.getElementById('page-size-style');
        const paper = document.getElementById('print-paper');
        const buttons = document.querySelectorAll('.paper-button');

        const selectPaper = (selected) => {
            const isA5 = selected === 'a5';

            pageStyle.textContent = `
                @page {
                    size: ${selected.toUpperCase()} portrait;
                    margin: ${isA5 ? '7mm' : '10mm'};
                }
            `;

            paper.style.display = 'block';
            paper.style.width = isA5 ? '148mm' : '210mm';
            paper.style.minHeight = isA5 ? '210mm' : '297mm';
            paper.style.padding = isA5 ? '7mm' : '10mm';
            paper.dataset.paper = selected;

            buttons.forEach((item) => {
                item.classList.toggle(
                    'active',
                    item.dataset.paper === selected,
                );
            });

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                selectPaper(button.dataset.paper);
            });
        });
    </script>
</body>

</html>