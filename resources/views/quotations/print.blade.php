@php
$pdfMode = $pdfMode ?? false;
$paper = strtolower($paper ?? 'a4');
$isGstBill = ($quotation->billing_type ?? 'gst') === 'gst';
$company = $quotation->company;
$store = $quotation->store;
$customer = $quotation->customer;
$mechanic = $quotation->mechanic;
$title = $quotation->documentTitle();
$quantityTotal = $quotation->items->sum(
fn ($item) => (float) $item->quantity
);
$freeQuantityTotal = $quotation->items->sum(
fn ($item) => (float) $item->free_quantity
);
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>{{ $title }} {{ $quotation->quotation_no }}</title>

    <style id="page-size-style">
        @page {
            size: {
                    {
                    strtoupper($paper)
                }
            }

            portrait;
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
            color: #17212b;

            background: {
                    {
                    $pdfMode ? '#ffffff': '#dce3e9'
                }
            }

            ;
            font-family: DejaVu Sans,
            Arial,
            Helvetica,
            sans-serif;
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
            gap: 6px;
            padding: 8px;
            border-bottom: 1px solid #a9b5c1;
            background: linear-gradient(#ffffff, #e3e8ed);
            box-shadow: 0 2px 8px rgb(20 35 55 / 16%);
        }

        .toolbar a,
        .toolbar button {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            justify-content: center;
            padding: 0 12px;
            border: 1px solid #9fadb9;
            border-radius: 3px;
            color: #243547;
            background: linear-gradient(#ffffff, #e4e9ee);
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar .primary,
        .toolbar .active {
            border-color: #07568f;
            color: #ffffff;
            background: linear-gradient(#2582c2, #0a5d9b);
        }

        .paper {
            display: block;

            width: {
                    {
                    $paper ==='a5' ? '148mm': '210mm'
                }
            }

            ;

            min-height: {
                    {
                    $paper ==='a5' ? '210mm': '297mm'
                }
            }

            ;

            max-width: {
                    {
                    $pdfMode ? '100%': 'none'
                }
            }

            ;

            margin: {
                    {
                    $pdfMode ? '0': '12px auto 22px'
                }
            }

            ;

            padding: {
                    {
                    $pdfMode ? '0': '8mm'
                }
            }

            ;

            border: {
                    {
                    $pdfMode ? '0': '1px solid #8f9dab'
                }
            }

            ;
            background: #ffffff;

            box-shadow: {
                    {
                    $pdfMode ? 'none': '0 8px 28px rgb(20 35 55 / 20%)'
                }
            }

            ;
        }

        body.screen-mode .paper {
            padding-top: 10mm;
        }

        body.pdf-mode .paper {
            width: auto !important;
            max-width: none !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 4mm 6mm 3mm 4mm !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        body.pdf-mode .detail-table td {
            padding-bottom: 5px;
        }

        body.pdf-mode .items th,
        body.pdf-mode .items td {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .top-table td {
            width: 50%;
            vertical-align: top;
        }

        .company-name {
            margin: 0 0 3px;
            color: #0b5790;
            font-size: 19px;
            line-height: 1.12;
            text-transform: uppercase;
        }

        .company-lines {
            color: #4e5f70;
            font-size: 10px;
        }

        .document-side {
            text-align: right;
        }

        .document-title {
            display: inline-block;
            min-width: 190px;
            padding: 7px 10px;
            border: 1px solid #174f79;
            color: #17324d;
            background: #eef5fa;
            font-size: 18px;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
        }

        .status {
            display: inline-block;
            margin-top: 5px;
            padding: 2px 7px;
            border: 1px solid #7f96aa;
            color: #31485e;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .rule {
            margin: 8px 0;
            border-top: 2px solid #17679f;
        }

        .detail-table td {
            width: 50%;
            padding: 0 4px 7px 0;
            vertical-align: top;
        }

        .detail-table td:last-child {
            padding-right: 0;
        }

        .box {
            min-height: 92px;
            border: 1px solid #9eacba;
        }

        .box-title {
            padding: 4px 7px;
            border-bottom: 1px solid #9eacba;
            color: #ffffff;
            background: #286b9a;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .box-body {
            padding: 6px 7px;
        }

        .primary-text {
            margin-bottom: 3px;
            color: #17324d;
            font-size: 12px;
            font-weight: 800;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 2px 0;
            border-bottom: 1px dotted #d2d9df;
        }

        .meta-row strong {
            text-align: right;
        }

        .items {
            table-layout: fixed;
        }

        .items th {
            padding: 5px 3px;
            border: 1px solid #64788a;
            color: #ffffff;
            background: #285f88;
            font-size: 9px;
            text-align: left;
            white-space: nowrap;
        }

        .items td {
            padding: 5px 3px;
            border: 1px solid #aeb8c3;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        .items tbody tr:nth-child(even) {
            background: #f1f5f8;
        }

        .number {
            text-align: right !important;
            white-space: nowrap;
        }

        .center {
            text-align: center !important;
        }

        .product-name {
            color: #182f43;
            font-weight: 700;
        }

        .product-meta {
            color: #687786;
            font-size: 9px;
        }

        .summary-table {
            margin-top: 7px;
        }

        .summary-table>tbody>tr>td {
            width: 50%;
            vertical-align: top;
        }

        .summary-left {
            padding-right: 8px;
        }

        .note-box {
            margin-bottom: 6px;
            padding: 6px 7px;
            border: 1px solid #b2bec9;
            background: #f7f9fb;
        }

        .note-box strong {
            display: block;
            margin-bottom: 2px;
            color: #2b587d;
            font-size: 9px;
            text-transform: uppercase;
        }

        .totals {
            margin-left: auto;
        }

        .totals td {
            padding: 4px 6px;
            border-bottom: 1px solid #c6d0d9;
        }

        .totals td:last-child {
            width: 42%;
            text-align: right;
            white-space: nowrap;
        }

        .totals .grand td {
            border: 1px solid #174f79;
            color: #ffffff;
            background: #245f8b;
            font-size: 13px;
            font-weight: 800;
        }

        .footer {
            margin-top: 12px;
            padding-top: 7px;
            border-top: 1px solid #9eacba;
        }

        .footer td {
            width: 50%;
            vertical-align: bottom;
        }

        .signature {
            padding-top: 30px;
            text-align: right;
        }

        .muted {
            color: #667585;
        }

        body.screen-mode.a5 .paper {
            width: 148mm;
            min-height: 210mm;
            margin-right: auto;
            margin-left: auto;
        }

        body.screen-mode.a4 .paper {
            width: 210mm;
            min-height: 297mm;
            margin-right: auto;
            margin-left: auto;
        }

        body.a5 .document-title {
            min-width: 145px;
            font-size: 15px;
        }

        body.a5 .company-name {
            font-size: 16px;
        }

        body.a5 .paper {
            font-size: 9px;
        }

        body.a5 .items th,
        body.a5 .product-meta {
            font-size: 8px;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none !important;
            }

            body.screen-mode .paper {
                width: auto !important;
                min-height: 0 !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
            }
        }

        @media screen and (max-width: 680px) {
            .paper {
                width: calc(100vw - 16px) !important;
                min-height: auto !important;
                max-width: calc(100vw - 16px) !important;
                margin-top: 8px;
                margin-right: auto !important;
                margin-left: auto !important;
                padding: 5mm;
            }

            .document-title {
                min-width: 135px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body class="{{ $paper }} {{ $pdfMode ? 'pdf-mode' : 'screen-mode' }}">
    @unless ($pdfMode)
    <div class="toolbar">
        <a href="{{ route('quotations.show', $quotation) }}">
            Back
        </a>

        <button
            type="button"
            class="paper-button {{ $paper === 'a4' ? 'active' : '' }}"
            data-paper="a4">
            A4
        </button>

        <button
            type="button"
            class="paper-button {{ $paper === 'a5' ? 'active' : '' }}"
            data-paper="a5">
            A5
        </button>

        <button
            type="button"
            class="primary"
            onclick="window.print()">
            Print
        </button>

        <a
            id="pdf-link"
            href="{{ route('quotations.pdf', ['quotation' => $quotation, 'paper' => $paper]) }}">
            Download PDF
        </a>
    </div>
    @endunless

    <main class="paper">
        <table class="top-table">
            <tr>
                <td>
                    <h1 class="company-name">
                        {{ $company->company_name ?? 'Company' }}
                    </h1>

                    <div class="company-lines">
                        {{ $store->store_name ?? '' }}
                        @if ($store?->address)
                        <br>{{ $store->address }}
                        @elseif ($company?->address)
                        <br>{{ $company->address }}
                        @endif

                        @if ($store?->city || $store?->pincode)
                        <br>
                        {{ collect([$store->city, $store->state, $store->pincode])->filter()->join(', ') }}
                        @endif

                        @if ($company?->gst_number)
                        <br>GSTIN: {{ $company->gst_number }}
                        @endif

                        @if ($store?->phone || $company?->phone)
                        <br>Phone: {{ $store->phone ?? $company->phone }}
                        @endif
                    </div>
                </td>

                <td class="document-side">
                    <div class="document-title">{{ $title }}</div>
                    <br>
                    <span class="status">{{ $quotation->status }}</span>
                </td>
            </tr>
        </table>

        <div class="rule"></div>

        <table class="detail-table">
            <tr>
                <td>
                    <div class="box">
                        <div class="box-title">Customer Details</div>
                        <div class="box-body">
                            <div class="primary-text">
                                {{ $customer->customer_name ?? 'Walk-in Customer' }}
                            </div>

                            @if ($customer?->billing_address)
                            <div>{{ $customer->billing_address }}</div>
                            @endif

                            @if ($customer?->mobile)
                            <div>Mobile: {{ $customer->mobile }}</div>
                            @endif

                            @if ($customer?->gst_number)
                            <div>GSTIN: {{ $customer->gst_number }}</div>
                            @endif
                        </div>
                    </div>
                </td>

                <td>
                    <div class="box">
                        <div class="box-title">Document Details</div>
                        <div class="box-body">
                            <div class="meta-row">
                                <span>Number</span>
                                <strong>{{ $quotation->quotation_no }}</strong>
                            </div>
                            <div class="meta-row">
                                <span>Date</span>
                                <strong>{{ $quotation->quotation_date?->format('d-m-Y') }}</strong>
                            </div>
                            <div class="meta-row">
                                <span>Valid Until</span>
                                <strong>
                                    {{ $quotation->valid_until?->format('d-m-Y') ?? '—' }}
                                </strong>
                            </div>
                            <div class="meta-row">
                                <span>Billing</span>
                                <strong>{{ $isGstBill ? 'GST' : 'Without GST' }}</strong>
                            </div>
                            @if ($mechanic)
                            <div class="meta-row">
                                <span>Mechanic</span>
                                <strong>{{ $mechanic->mechanic_name }}</strong>
                            </div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 4%" class="center">#</th>
                    <th style="width: {{ $isGstBill ? '29%' : '36%' }}">Product</th>
                    <th style="width: 8%" class="number">Qty</th>
                    <th style="width: 8%" class="number">Free</th>
                    <th style="width: 12%" class="number">Rate</th>
                    <th style="width: 11%" class="number">Discount</th>
                    @if ($isGstBill)
                    <th style="width: 9%" class="number">GST</th>
                    @endif
                    <th style="width: 14%" class="number">Amount</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($quotation->items as $item)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>
                        <div class="product-name">
                            {{ $item->product->product_name ?? 'Product' }}
                        </div>
                        <div class="product-meta">
                            {{ collect([
                                    $item->product->product_code ?? null,
                                    $item->product->part_number ?? null,
                                    $item->product->unit->unit_name ?? null,
                                ])->filter()->join(' · ') }}
                        </div>
                    </td>
                    <td class="number">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="number">{{ number_format((float) $item->free_quantity, 2) }}</td>
                    <td class="number">{{ number_format((float) $item->sale_price, 2) }}</td>
                    <td class="number">
                        {{ number_format((float) $item->discount_amount, 2) }}
                        @if ($item->discount_type === 'percentage' && (float) $item->discount_value > 0)
                        <div class="product-meta">
                            {{ number_format((float) $item->discount_value, 2) }}%
                        </div>
                        @endif
                    </td>
                    @if ($isGstBill)
                    <td class="number">
                        {{ number_format((float) $item->gst_amount, 2) }}
                        <div class="product-meta">
                            {{ number_format((float) $item->gst_percent, 2) }}%
                        </div>
                    </td>
                    @endif
                    <td class="number">{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td class="summary-left">
                    <div class="note-box">
                        <strong>Quantity Summary</strong>
                        {{ number_format($quantityTotal, 2) }} billed
                        @if ($freeQuantityTotal > 0)
                        · {{ number_format($freeQuantityTotal, 2) }} free
                        @endif
                        · {{ $quotation->items->count() }} item(s)
                    </div>

                    @if ($quotation->terms_conditions)
                    <div class="note-box">
                        <strong>Terms &amp; Conditions</strong>
                        {!! nl2br(e($quotation->terms_conditions)) !!}
                    </div>
                    @endif

                    @if ($quotation->remarks)
                    <div class="note-box">
                        <strong>Remarks</strong>
                        {!! nl2br(e($quotation->remarks)) !!}
                    </div>
                    @endif
                </td>

                <td>
                    <table class="totals">
                        <tr>
                            <td>Sub Total</td>
                            <td>{{ number_format((float) $quotation->sub_total, 2) }}</td>
                        </tr>
                        @if ($isGstBill)
                        <tr>
                            <td>GST Total</td>
                            <td>{{ number_format((float) $quotation->gst_total, 2) }}</td>
                        </tr>
                        @endif
                        @if ((float) $quotation->final_discount > 0)
                        <tr>
                            <td>Final Discount</td>
                            <td>-{{ number_format((float) $quotation->final_discount, 2) }}</td>
                        </tr>
                        @endif
                        @if ((float) $quotation->round_off !== 0.0)
                        <tr>
                            <td>Round Off</td>
                            <td>{{ number_format((float) $quotation->round_off, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="grand">
                            <td>Grand Total</td>
                            <td>₹ {{ number_format((float) $quotation->grand_total, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="footer">
            <tr>
                <td class="muted">
                    Generated by {{ $quotation->creator->name ?? 'System' }}
                    <br>
                    This {{ strtolower($title) }} is not a payment receipt.
                </td>
                <td class="signature">
                    <strong>For {{ $company->company_name ?? 'Company' }}</strong>
                    <br>Authorised Signatory
                </td>
            </tr>
        </table>
    </main>

    @unless ($pdfMode)
    <script>
        (() => {
            const style = document.getElementById("page-size-style");
            const pdfLink = document.getElementById("pdf-link");
            const buttons = document.querySelectorAll(".paper-button");

            const selectPaper = (paper) => {
                document.body.classList.remove("a4", "a5");
                document.body.classList.add(paper);
                style.textContent = `@page { size: ${paper.toUpperCase()} portrait; margin: 7mm; }`;

                buttons.forEach((button) => {
                    button.classList.toggle(
                        "active",
                        button.dataset.paper === paper,
                    );
                });

                const url = new URL(pdfLink.href);
                url.searchParams.set("paper", paper);
                pdfLink.href = url.toString();
            };

            buttons.forEach((button) => {
                button.addEventListener("click", () => {
                    selectPaper(button.dataset.paper);
                });
            });
        })();
    </script>
    @endunless
</body>

</html>