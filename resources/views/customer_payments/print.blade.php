@php
$pdfMode = $pdfMode ?? false;
$paper = strtolower($paper ?? 'a4');
$companyName = data_get($payment, 'company.company_name')
?? data_get($payment, 'company.name')
?? config('app.name', 'RR Motors');
$storeName = data_get($payment, 'store.store_name')
?? data_get($payment, 'store.name')
?? 'Branch';
$storeAddress = data_get($payment, 'store.address')
?? data_get($payment, 'company.address');
$storePhone = data_get($payment, 'store.phone')
?? data_get($payment, 'company.phone');
$storeGst = data_get($payment, 'store.gst_number')
?? data_get($payment, 'company.gst_number');
$allocatedAmount = (float) $payment->allocations->sum('allocated_amount');
$advanceAmount = max(0, round((float) $payment->amount - $allocatedAmount, 2));
$cancelled = $payment->status === \App\Models\CustomerPayment::STATUS_CANCELLED;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receipt_no }}</title>
    <style>
        @page {
            size: A4 portrait;

            margin: {
                    {
                    $pdfMode ? '0': '18mm 10mm 10mm'
                }
            }

            ;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            color: #172033;

            background: {
                    {
                    $pdfMode ? '#ffffff': '#e7ecf3'
                }
            }

            ;
            font-family: DejaVu Sans,
            Arial,
            sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        html {
            padding: 0;
        }

        body {
            padding: {
                    {
                    $pdfMode ? '12mm 10mm 10mm': '26px 12px'
                }
            }

            ;

            text-align: {
                    {
                    $pdfMode ? 'left': 'center'
                }
            }

            ;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 210mm;
            max-width: 100%;
            margin: 0 auto 10px;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .08);
            text-align: left;
        }

        .toolbar strong {
            font-size: 12px;
        }

        .toolbar div {
            display: flex;
            gap: 6px;
        }

        .toolbar button,
        .toolbar a {
            display: inline-block;
            padding: 7px 11px;
            border: 0;
            border-radius: 5px;
            color: #fff;
            background: #1d4ed8;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar .close {
            color: #334155;
            border: 1px solid #cbd5e1;
            background: #fff;
        }

        .sheet {
            display: {
                    {
                    $pdfMode ? 'block': 'inline-block'
                }
            }

            ;
            position: relative;

            width: {
                    {
                    $pdfMode ? '100%': '210mm'
                }
            }

            ;

            min-height: {
                    {
                    $pdfMode ? '265mm': '297mm'
                }
            }

            ;
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;

            border: {
                    {
                    $pdfMode ? '0': '1px solid #cbd5e1'
                }
            }

            ;
            background: #fff;

            box-shadow: {
                    {
                    $pdfMode ? 'none': '0 10px 30px rgba(15,23,42,.15)'
                }
            }

            ;
            text-align: left;
            vertical-align: top;
        }

        .sheet,
        .toolbar {
            margin-left: auto;
            margin-right: auto;
        }

        .top-line {
            height: 5px;
            background: #1e3a5f;
        }

        .header {
            display: table;
            width: 100%;
            padding: 15px 17px 13px;
            color: #fff;
            background: #1e3a5f;
        }

        .header>div {
            display: table-cell;
            vertical-align: middle;
        }

        .company {
            width: 67%;
        }

        .company h1 {
            margin: 0 0 3px;
            font-size: 20px;
            letter-spacing: .2px;
        }

        .company p {
            margin: 1px 0;
            color: #dbeafe;
            font-size: 9px;
        }

        .document-title {
            text-align: right;
        }

        .document-title b {
            display: block;
            font-size: 17px;
            letter-spacing: 1px;
        }

        .document-title span {
            display: inline-block;
            margin-top: 4px;
            padding: 3px 7px;
            border: 1px solid #6f8cab;
            border-radius: 3px;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
        }

        .status-strip {
            display: table;
            width: 100%;
            padding: 8px 17px;
            border-bottom: 1px solid #dbe3ed;
            background: #f4f7fb;
        }

        .status-strip>div {
            display: table-cell;
            width: 33.333%;
        }

        .status-strip .center {
            text-align: center;
        }

        .status-strip .right {
            text-align: right;
        }

        .label {
            display: block;
            color: #64748b;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .value {
            display: block;
            margin-top: 1px;
            color: #172033;
            font-weight: 700;
        }

        .body {
            padding: 14px 17px 18px;
        }

        .party-payment {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            table-layout: fixed;
        }

        .party-payment>div {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .party-payment>div:first-child {
            padding-right: 6px;
        }

        .party-payment>div:last-child {
            padding-left: 6px;
        }

        .box {
            min-height: 116px;
            border: 1px solid #dbe3ed;
            border-radius: 5px;
            overflow: hidden;
        }

        .box-title {
            padding: 6px 9px;
            border-bottom: 1px solid #dbe3ed;
            color: #1e3a5f;
            background: #edf4fb;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .box-body {
            padding: 8px 9px;
        }

        .customer-name {
            margin-bottom: 4px;
            color: #0f2f50;
            font-size: 13px;
            font-weight: 700;
        }

        .muted {
            color: #64748b;
        }

        .detail-row {
            display: table;
            width: 100%;
            padding: 2px 0;
        }

        .detail-row span,
        .detail-row strong {
            display: table-cell;
        }

        .detail-row span {
            width: 42%;
            color: #64748b;
        }

        .detail-row strong {
            text-align: right;
        }

        .amount-banner {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #b7d9cc;
            border-radius: 5px;
            background: #eefaf5;
        }

        .amount-banner>div {
            display: table-cell;
            padding: 9px 12px;
            vertical-align: middle;
        }

        .amount-banner .words {
            width: 67%;
            color: #375b50;
        }

        .amount-banner .total {
            color: #0f766e;
            font-size: 18px;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .allocation-table {
            margin-bottom: 12px;
        }

        .allocation-table th {
            padding: 7px 7px;
            border: 1px solid #cfd9e5;
            color: #fff;
            background: #294d73;
            font-size: 9px;
            text-align: left;
            text-transform: uppercase;
        }

        .allocation-table td {
            padding: 7px;
            border: 1px solid #dbe3ed;
        }

        .allocation-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .allocation-table .number {
            width: 32px;
            text-align: center;
        }

        .allocation-table .money {
            text-align: right;
            white-space: nowrap;
        }

        .allocation-table tfoot td {
            color: #172033;
            background: #eef2f7;
            font-weight: 700;
        }

        .summary {
            width: 47%;
            margin-left: auto;
            margin-bottom: 12px;
            border: 1px solid #dbe3ed;
        }

        .summary td {
            padding: 6px 8px;
            border-bottom: 1px solid #e6ebf2;
        }

        .summary tr:last-child td {
            border-bottom: 0;
        }

        .summary td:last-child {
            text-align: right;
            font-weight: 700;
        }

        .summary .grand td {
            color: #fff;
            background: #1e3a5f;
            font-size: 12px;
        }

        .summary .advance td:last-child {
            color: #b45309;
        }

        .advance-note {
            margin-bottom: 12px;
            padding: 9px 10px;
            border: 1px solid #f1d28c;
            border-radius: 4px;
            color: #854d0e;
            background: #fffbeb;
        }

        .remarks {
            margin-bottom: 16px;
            padding: 8px 10px;
            border-left: 3px solid #94a3b8;
            background: #f8fafc;
        }

        .remarks p {
            margin: 3px 0 0;
            white-space: pre-line;
        }

        .footer {
            display: table;
            width: 100%;
            margin-top: 24px;
        }

        .footer>div {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }

        .footer .thanks {
            color: #64748b;
            font-size: 9px;
        }

        .signature {
            padding-top: 34px;
            text-align: right;
        }

        .signature strong {
            display: inline-block;
            min-width: 145px;
            padding-top: 5px;
            border-top: 1px solid #64748b;
            text-align: center;
        }

        .system-note {
            position: absolute;
            right: 17px;
            bottom: 9px;
            left: 17px;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            color: #94a3b8;
            font-size: 8px;
            text-align: center;
        }

        .cancelled-watermark {
            position: absolute;
            z-index: 5;
            top: 43%;
            left: 12%;
            width: 76%;
            color: rgba(185, 28, 28, .12);
            font-size: 62px;
            font-weight: 700;
            letter-spacing: 7px;
            text-align: center;
            transform: rotate(-24deg);
        }

        .cancelled-badge {
            display: inline-block;
            padding: 3px 7px;
            border: 1px solid #dc2626;
            border-radius: 3px;
            color: #b91c1c;
            background: #fee2e2;
            font-size: 9px;
            font-weight: 700;
        }

        @media screen and (min-width: 900px) {
            body {
                text-align: center !important;
            }

            .toolbar {
                width: 210mm !important;
                max-width: 210mm !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            .sheet {
                display: inline-block !important;
                width: 210mm !important;
                min-width: 210mm !important;
                max-width: 210mm !important;
                min-height: 297mm !important;
                margin-left: auto !important;
                margin-right: auto !important;
                text-align: left !important;
            }
        }

        @media screen and (max-width: 899px) {

            .toolbar,
            .sheet {
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
            }
        }

        @media print {

            html,
            body {
                background: #fff !important;
            }

            body {
                padding: {
                        {
                        $pdfMode ? '12mm 10mm 10mm': '0'
                    }
                }

                ;
                text-align: center;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                width: 100%;

                min-height: {
                        {
                        $pdfMode ? '265mm': '0'
                    }
                }

                ;
                margin-left: auto;
                margin-right: auto;
                border: 0;
                box-shadow: none;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>
    @unless($pdfMode)
    <div class="toolbar">
        <strong>Customer Receipt Preview</strong>
        <div>
            <a href="{{ route('customer-payments.index') }}" class="close">Back</a>
            <button type="button" onclick="window.print()">Print Receipt</button>
        </div>
    </div>
    @endunless

    <main class="sheet">
        @if($cancelled)<div class="cancelled-watermark">CANCELLED</div>@endif
        <div class="top-line"></div>
        <header class="header">
            <div class="company">
                <h1>{{ $companyName }}</h1>
                <p><strong>{{ $storeName }}</strong></p>
                @if($storeAddress)<p>{{ $storeAddress }}</p>@endif
                <p>
                    @if($storePhone)Phone: {{ $storePhone }}@endif
                    @if($storePhone && $storeGst)&nbsp; | &nbsp;@endif
                    @if($storeGst)GSTIN: {{ $storeGst }}@endif
                </p>
            </div>
            <div class="document-title">
                <b>PAYMENT RECEIPT</b>
                <span>{{ $payment->receipt_no }}</span>
            </div>
        </header>

        <section class="status-strip">
            <div><span class="label">Receipt Date</span><span class="value">{{ optional($payment->payment_date)->format('d-m-Y') }}</span></div>
            <div class="center"><span class="label">Payment Mode</span><span class="value">{{ $payment->payment_mode }}</span></div>
            <div class="right"><span class="label">Status</span><span class="value">@if($cancelled)<i class="cancelled-badge">CANCELLED</i>@else COMPLETED @endif</span></div>
        </section>

        <div class="body">
            <section class="party-payment">
                <div>
                    <div class="box">
                        <div class="box-title">Received From</div>
                        <div class="box-body">
                            <div class="customer-name">{{ data_get($payment, 'customer.customer_name', '—') }}</div>
                            <div class="muted">Customer Code: {{ data_get($payment, 'customer.customer_code', '—') }}</div>
                            @if(data_get($payment, 'customer.mobile'))<div class="muted">Mobile: {{ data_get($payment, 'customer.mobile') }}</div>@endif
                            @if(data_get($payment, 'customer.gst_number'))<div class="muted">GSTIN: {{ data_get($payment, 'customer.gst_number') }}</div>@endif
                            @if(data_get($payment, 'customer.billing_address'))<div class="muted">{{ data_get($payment, 'customer.billing_address') }}</div>@endif
                        </div>
                    </div>
                </div>
                <div>
                    <div class="box">
                        <div class="box-title">Payment Details</div>
                        <div class="box-body">
                            <div class="detail-row"><span>Deposit Account</span><strong>{{ data_get($payment, 'account.account_name', '—') }}</strong></div>
                            <div class="detail-row"><span>Account Code</span><strong>{{ data_get($payment, 'account.account_code', '—') }}</strong></div>
                            <div class="detail-row"><span>Reference No.</span><strong>{{ $payment->reference_no ?: '—' }}</strong></div>
                            <div class="detail-row"><span>Created By</span><strong>{{ data_get($payment, 'creator.name', '—') }}</strong></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="amount-banner">
                <div class="words"><span class="label">Amount Received</span>Payment received through {{ $payment->payment_mode }}.</div>
                <div class="total">₹ {{ number_format((float) $payment->amount, 2) }}</div>
            </section>

            @if($payment->allocations->isNotEmpty())
            <table class="allocation-table">
                <thead>
                    <tr>
                        <th class="number">#</th>
                        <th>Invoice Number</th>
                        <th>Invoice Date</th>
                        <th class="money">Invoice Total</th>
                        <th class="money">Allocated Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payment->allocations as $allocation)
                    <tr>
                        <td class="number">{{ $loop->iteration }}</td>
                        <td><strong>{{ data_get($allocation, 'sale.invoice_no', 'Sale #'.$allocation->sale_id) }}</strong></td>
                        <td>{{ optional(data_get($allocation, 'sale.invoice_date'))->format('d-m-Y') ?? '—' }}</td>
                        <td class="money">₹ {{ number_format((float) data_get($allocation, 'sale.grand_total', 0), 2) }}</td>
                        <td class="money"><strong>₹ {{ number_format((float) $allocation->allocated_amount, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="money">Total Invoice Allocation</td>
                        <td class="money">₹ {{ number_format($allocatedAmount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @else
            <div class="advance-note"><strong>Advance Receipt:</strong> No invoice has been allocated to this receipt.</div>
            @endif

            <table class="summary">
                <tr>
                    <td>Receipt Amount</td>
                    <td>₹ {{ number_format((float) $payment->amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Invoice Allocation</td>
                    <td>₹ {{ number_format($allocatedAmount, 2) }}</td>
                </tr>
                <tr class="advance">
                    <td>Customer Advance</td>
                    <td>₹ {{ number_format($advanceAmount, 2) }}</td>
                </tr>
                <tr class="grand">
                    <td>Amount Received</td>
                    <td>₹ {{ number_format((float) $payment->amount, 2) }}</td>
                </tr>
            </table>

            @if($payment->remarks)
            <div class="remarks"><span class="label">Remarks</span>
                <p>{{ $payment->remarks }}</p>
            </div>
            @endif

            @if($cancelled)
            <div class="advance-note"><strong>Cancellation:</strong> Cancelled by {{ data_get($payment, 'canceller.name', 'User') }}{{ $payment->cancelled_at ? ' on '.$payment->cancelled_at->format('d-m-Y h:i A') : '' }}. Reversing entries were created.</div>
            @endif

            <footer class="footer">
                <div class="thanks">Thank you. This receipt confirms the payment recorded in our accounts.</div>
                <div class="signature"><strong>Authorised Signatory</strong></div>
            </footer>
        </div>
        <div class="system-note">Computer-generated customer payment receipt · {{ $companyName }} · {{ $storeName }}</div>
    </main>

    @unless($pdfMode)
    <script>
        window.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'p') {
                event.preventDefault();
                window.print();
            }
        });
    </script>
    @endunless
</body>

</html>