@php
$paper = strtolower($paper ?? request('paper', 'a4'));
$paper = in_array($paper, ['a4', 'a5'], true) ? $paper : 'a4';
$pdfMode = (bool) ($pdfMode ?? false);
$isA5 = $paper === 'a5';
$isGstBill = ($salesReturn->billing_type ?? 'gst') === 'gst';
$customer = $salesReturn->customer;
$company = $salesReturn->company;
$store = $salesReturn->store;
$sale = $salesReturn->sale;
$refundAccount = $salesReturn->account;
$mechanicImpact = $mechanicImpact ?? null;
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
            size: {
                    {
                    strtoupper($paper)
                }
            }

            portrait;

            margin: {
                    {
                    $pdfMode ? ($isA5 ? '10mm' : '12mm'): ($isA5 ? '7mm' : '10mm')
                }
            }

            ;
        }
    </style>

    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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

        .preview-stage {
            min-height: calc(100vh - 53px);
            padding: 24px;
            overflow: auto;
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
            padding: 8px 18px;
            border-bottom: 1px solid #aebac6;
            background: linear-gradient(180deg, #fff, #e5eaf0);
            box-shadow: 0 2px 8px rgb(20 35 55 / 14%);
        }

        .toolbar-inner {
            display: flex;
            width: 210mm;
            max-width: 100%;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 0 auto;
        }

        .toolbar-title {
            color: #18324a;
            font-size: 12px;
            font-weight: 800;
        }

        .toolbar-title small {
            display: block;
            color: #657587;
            font-size: 10px;
            font-weight: 600;
        }

        .toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 7px;
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

        .toolbar a.pdf-button {
            color: #fff;
            border-color: #9a3412;
            background: linear-gradient(180deg, #ea580c, #c2410c);
        }

        .toolbar button.active {
            color: #fff;
            border-color: #07568f;
            background: #0a5d9b;
        }

        .paper {
            display: block;

            width: {
                    {
                    $isA5 ? '148mm': '210mm'
                }
            }

            ;
            max-width: calc(100vw - 28px);

            min-height: {
                    {
                    $isA5 ? '210mm': '297mm'
                }
            }

            ;
            margin: 0 auto;

            padding: {
                    {
                    $isA5 ? '7mm': '10mm'
                }
            }

            ;
            background: #fff;
            border: 1px solid #8f9dab;
            border-top: 4px solid #17679f;
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

        .mechanic-impact {
            margin: 0 0 8px;
            border: 1px solid #d2a24d;
            background: #fffaf0;
        }

        .mechanic-impact-title {
            padding: 5px 7px;
            color: #704507;
            background: #f8dfad;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .mechanic-impact table {
            table-layout: fixed;
        }

        .mechanic-impact td {
            width: 25%;
            padding: 5px 7px;
            border-top: 1px solid #ead3a8;
            border-right: 1px solid #ead3a8;
            vertical-align: top;
        }

        .mechanic-impact td:last-child {
            border-right: 0;
        }

        .mechanic-impact span,
        .mechanic-impact strong {
            display: block;
        }

        .mechanic-impact span {
            margin-bottom: 2px;
            color: #7d6845;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .mechanic-impact strong {
            color: #3f2d10;
            font-size: 9px;
        }

        .mechanic-impact .deduction strong {
            color: #a3382e;
        }

        .mechanic-impact .advance strong {
            color: #9a5a08;
        }

        .items th {
            padding: 6px 5px;
            border: 1px solid #66798c;
            color: #fff;
            background: #285f88;
            font-size: 9px;
            text-align: left;
            white-space: nowrap;
        }

        .items td {
            padding: 6px 5px;
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

            .preview-stage {
                min-height: 0;
                padding: 0;
                overflow: visible;
            }

            .paper {
                width: auto !important;
                min-height: 0;
                margin: 0;
                padding: 0 !important;
                border: 0;
                box-shadow: none;
            }

        }

        @if ($pdfMode) body {
            background: #fff;
            font-family: DejaVu Sans, sans-serif;
        }

        .toolbar {
            display: none !important;
        }

        .preview-stage {
            min-height: 0;
            padding: 0;
            overflow: visible;
        }

        .paper {
            width: auto !important;
            max-width: none;
            min-height: 0;
            margin: 0 auto !important;

            padding: {
                    {
                    $isA5 ? '7mm': '9mm'
                }
            }

            !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        @endif @media (max-width: 720px) {
            .preview-stage {
                padding: 12px;
            }

            .toolbar-inner {
                align-items: flex-start;
                flex-direction: column;
            }

            .toolbar-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</head>

<body>
    @unless ($pdfMode)
    <div class="toolbar">
        <div class="toolbar-inner">
            <div class="toolbar-title">
                Sales Return Preview
                <small>
                    {{ $salesReturn->return_no }} ·
                    <span id="paper-label">{{ strtoupper($paper) }}</span> portrait
                </small>
            </div>

            <div class="toolbar-actions">
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
                    id="print-button"
                    type="button"
                    class="primary"
                    onclick="window.print()">
                    Print
                </button>

                <a
                    id="pdf-button"
                    class="pdf-button"
                    href="{{ route('sales-returns.pdf', [
                        'salesReturn' => $salesReturn,
                        'paper' => $paper,
                    ]) }}">
                    PDF
                </a>

                <a href="{{ route('sales-returns.show', $salesReturn) }}">
                    Back
                </a>
            </div>
        </div>
    </div>
    @endunless

    <div class="preview-stage">
        <main
            id="print-paper"
            class="paper"
            @if ($pdfMode)
            style="width: auto !important;
                max-width: none !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: {{ $isA5 ? '7mm' : '9mm' }} !important;
                border: 0 !important;
                box-shadow: none !important;"
            @endif
            data-paper="{{ $paper }}">
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
                                <span>Account</span>
                                <strong>
                                    {{ $refundAccount->account_name ?? ($salesReturn->isCustomerCredit() ? 'Customer Credit' : '-') }}
                                </strong>
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

            @if ($mechanicImpact)
            <section class="mechanic-impact">
                <div class="mechanic-impact-title">
                    Mechanic Commission Impact
                </div>
                <table>
                    <tr>
                        <td>
                            <span>Mechanic</span>
                            <strong>
                                {{ $mechanicImpact['mechanic']['mechanic_name'] ?? '-' }}
                                · {{ $mechanicImpact['mechanic']['mechanic_code'] ?? '-' }}
                            </strong>
                        </td>
                        <td>
                            <span>Original Commission</span>
                            <strong>₹ {{ number_format((float) $mechanicImpact['original_commission'], 2) }}</strong>
                        </td>
                        <td class="deduction">
                            <span>Net Return Deduction</span>
                            <strong>₹ {{ number_format((float) $mechanicImpact['this_return_deduction'], 2) }}</strong>
                            @if ((float) ($mechanicImpact['this_return_reversed'] ?? 0) > 0)
                            <span style="margin-top: 2px; color: #16794a;">
                                Reversed: ₹ {{ number_format((float) $mechanicImpact['this_return_reversed'], 2) }}
                            </span>
                            @endif
                        </td>
                        <td>
                            <span>Total Return Deduction</span>
                            <strong>₹ {{ number_format((float) $mechanicImpact['total_return_deduction'], 2) }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span>Effective Commission</span>
                            <strong>₹ {{ number_format((float) $mechanicImpact['effective_commission'], 2) }}</strong>
                        </td>
                        <td>
                            <span>Already Paid</span>
                            <strong>₹ {{ number_format((float) $mechanicImpact['paid_commission'], 2) }}</strong>
                        </td>
                        <td>
                            <span>Current Payable</span>
                            <strong>₹ {{ number_format((float) $mechanicImpact['payable_balance'], 2) }}</strong>
                        </td>
                        <td class="advance">
                            <span>Mechanic Advance</span>
                            <strong>₹ {{ number_format((float) $mechanicImpact['advance_amount'], 2) }}</strong>
                        </td>
                    </tr>
                </table>
            </section>
            @endif

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
    </div>

    @unless ($pdfMode)
    <script>
        const pageStyle = document.getElementById('page-size-style');
        const paper = document.getElementById('print-paper');
        const buttons = document.querySelectorAll('.paper-button');
        const paperLabel = document.getElementById('paper-label');
        const pdfButton = document.getElementById('pdf-button');
        const pdfUrl = @json(route('sales-returns.pdf', $salesReturn));
        const initialPaper = @json($paper ? : 'a4');

        const selectPaper = (selected, scrollToPaper = true) => {
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
            paper.style.maxWidth = 'none';
            paper.dataset.paper = selected;
            paperLabel.textContent = selected.toUpperCase();
            pdfButton.href = `${pdfUrl}?paper=${selected}`;

            buttons.forEach((item) => {
                item.classList.toggle(
                    'active',
                    item.dataset.paper === selected,
                );
            });

            if (scrollToPaper) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                selectPaper(button.dataset.paper);
            });
        });

        /*
         * Apply A4 immediately on first load. Previously the correct width and
         * padding were applied only after manually clicking the A4 button.
         */
        selectPaper(initialPaper === 'a5' ? 'a5' : 'a4', false);
    </script>
    @endunless
</body>

</html>