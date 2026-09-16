@php
$isPdf = (bool) ($pdfMode ?? false);
$company = $payment->company;
$store = $payment->store;
$supplier = $payment->supplier;
$purchase = $payment->purchase;
$account = $payment->account;
$cancelled = $payment->status === \App\Models\SupplierPayment::STATUS_CANCELLED;
$money = static fn ($value) => 'Rs. ' . number_format((float) $value, 2);
$date = static fn ($value) => $value
? \Illuminate\Support\Carbon::parse($value)->format('d M Y')
: '—';
$branchAddress = collect([
$store->address ?? null,
$store->city ?? null,
$store->state ?? null,
$store->pincode ?? null,
])->filter()->implode(', ');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Payment {{ $payment->payment_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        html,
        body {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        body {
            padding: {
                    {
                    $isPdf ? '0': '18px 0 30px'
                }
            }

            ;
            color: #172033;

            background-color: {
                    {
                    $isPdf ? '#fff': '#dce2eb'
                }
            }

            ;
            font-family: DejaVu Sans,
            Arial,
            sans-serif;
            font-size: 11px;
            line-height: 1.42;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 18px;
            padding: 10px;
            border: 1px solid #aab5c3;
            background: #eef2f7;
        }

        .toolbar button,
        .toolbar a {
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            padding: 0 15px;
            border: 1px solid #1d4ed8;
            border-radius: 5px;
            color: #fff;
            background: #2563eb;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar a {
            border-color: #64748b;
            background: #64748b;
        }

        .sheet {
            position: relative;
            width: 190mm;
            min-height: 270mm;
            margin: auto;
            overflow: hidden;
            border: 1px solid #64748b;
            background-color: #fff;

            box-shadow: {
                    {
                    $isPdf ? 'none': '0 10px 30px rgba(15,23,42,.18)'
                }
            }

            ;
        }

        .watermark {
            position: absolute;
            top: 44%;
            left: 13%;
            z-index: 0;
            color: rgba(185, 28, 28, .09);
            font-size: 64px;
            font-weight: bold;
            letter-spacing: 9px;
            transform: rotate(-25deg);
        }

        .document {
            position: relative;
            z-index: 1;
        }

        .header {
            display: table;
            width: 100%;
            padding: 16px 18px;
            border-bottom: 4px solid #2563eb;
            color: #fff;
            background-color: #172554 !important;
        }

        .company,
        .voucher-id {
            display: table-cell;
            vertical-align: middle;
        }

        .company {
            width: 66%;
        }

        .voucher-id {
            text-align: right;
        }

        .company h1 {
            margin: 0;
            font-size: 20px;
        }

        .company p {
            margin: 3px 0 0;
            color: #dbeafe;
            font-size: 9.5px;
        }

        .voucher-id small {
            display: block;
            color: #bfdbfe;
            font-size: 9px;
            text-transform: uppercase;
        }

        .voucher-id strong {
            display: block;
            margin-top: 3px;
            font-size: 15px;
        }

        .title {
            padding: 10px 18px;
            border-bottom: 1px solid #cbd5e1;
            text-align: center;
            background-color: #eff6ff !important;
        }

        .title strong {
            color: #172554;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .title span {
            display: block;
            margin-top: 2px;
            color: #475569;
            font-size: 9px;
        }

        .grid {
            display: table;
            width: 100%;
            border-bottom: 1px solid #94a3b8;
            table-layout: fixed;
        }

        .cell {
            display: table-cell;
            width: 50%;
            min-height: 115px;
            padding: 13px 15px;
            vertical-align: top;
        }

        .cell+.cell {
            border-left: 1px solid #cbd5e1;
        }

        .label {
            margin-bottom: 7px;
            color: #475569;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .primary {
            color: #172033;
            font-size: 13px;
            font-weight: bold;
        }

        .secondary {
            margin-top: 3px;
            color: #475569;
            font-size: 10px;
        }

        .line {
            display: table;
            width: 100%;
            min-height: 22px;
        }

        .line span,
        .line b {
            display: table-cell;
            padding: 2px 0;
        }

        .line span {
            width: 43%;
            color: #64748b;
        }

        .line b {
            text-align: right;
        }

        .amount {
            display: table;
            width: 100%;
            padding: 18px;
            border-bottom: 1px solid #93c5fd;
            background-color: #eff6ff !important;
        }

        .amount-main,
        .mode {
            display: table-cell;
            vertical-align: middle;
        }

        .amount-main small {
            display: block;
            color: #1e40af;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .amount-main strong {
            display: block;
            margin-top: 2px;
            color: #172554;
            font-size: 29px;
        }

        .amount-main span {
            display: block;
            margin-top: 4px;
            color: #475569;
            font-size: 9.5px;
        }

        .mode {
            width: 30%;
            text-align: right;
        }

        .mode b {
            display: inline-block;
            padding: 7px 12px;
            border: 1px solid #60a5fa;
            border-radius: 5px;
            color: #1e40af;
            background: #fff;
        }

        .remarks {
            min-height: 78px;
            padding: 13px 18px;
            border-bottom: 1px solid #cbd5e1;
        }

        .remarks p {
            margin: 5px 0 0;
            padding: 8px;
            border: 1px solid #e2e8f0;
            color: #475569;
            background: #f8fafc;
        }

        .audit {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .audit>div {
            display: table-cell;
            width: 33.333%;
            min-height: 68px;
            padding: 12px 15px;
            border-right: 1px solid #dbe3ed;
            vertical-align: top;
        }

        .audit>div:last-child {
            border-right: 0;
        }

        .audit small {
            display: block;
            color: #64748b;
            font-size: 9px;
        }

        .audit strong {
            display: block;
            margin-top: 4px;
        }

        .signature {
            display: block;
            width: 120px;
            margin-top: 24px;
            border-bottom: 1px solid #475569;
        }

        .completed {
            color: #166534;
        }

        .cancelled {
            color: #b91c1c;
        }

        .footer {
            position: absolute;
            right: 18px;
            bottom: 12px;
            left: 18px;
            padding-top: 7px;
            border-top: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 8.5px;
            text-align: center;
        }

        @media print {

            html,
            body {
                width: 210mm;
                min-height: 297mm;
                background-color: #fff !important;
            }

            body {
                display: block;
                padding: 0 !important;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                width: 190mm;
                min-height: 270mm;
                margin: 0 auto !important;
                border: 0;
                box-shadow: none;
            }

            .header {
                color: #fff !important;
                background-color: #172554 !important;
            }

            .title,
            .amount {
                background-color: #eff6ff !important;
            }
        }
    </style>
</head>

<body>
    @unless($isPdf)
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print Voucher</button>
        <a href="{{ route('supplier-payments.show', $payment) }}">Back</a>
    </div>
    @endunless

    <main class="sheet">
        @if($cancelled)<div class="watermark">CANCELLED</div>@endif
        <div class="document">
            <header class="header">
                <div class="company">
                    <h1>{{ $company->company_name ?? 'Company' }}</h1>
                    <p>{{ $store->store_name ?? 'Branch' }}{{ $branchAddress ? ' · ' . $branchAddress : '' }}</p>
                </div>
                <div class="voucher-id"><small>Voucher Number</small><strong>{{ $payment->payment_no }}</strong></div>
            </header>

            <section class="title"><strong>SUPPLIER PAYMENT VOUCHER</strong><span>Branch accounting document</span></section>

            <section class="grid">
                <div class="cell">
                    <div class="label">Paid To</div>
                    <div class="primary">{{ $supplier->supplier_name ?? '—' }}</div>
                    <div class="secondary">{{ $supplier->supplier_code ?? '—' }}</div>
                    @if($supplier?->phone)<div class="secondary">Phone: {{ $supplier->phone }}</div>@endif
                    @if($supplier?->gst_number)<div class="secondary">GST: {{ $supplier->gst_number }}</div>@endif
                </div>
                <div class="cell">
                    <div class="label">Payment Information</div>
                    <div class="line"><span>Payment date</span><b>{{ $date($payment->payment_date) }}</b></div>
                    <div class="line"><span>Payment mode</span><b>{{ $payment->payment_mode }}</b></div>
                    <div class="line"><span>Reference</span><b>{{ $payment->reference_no ?: '—' }}</b></div>
                    <div class="line"><span>Status</span><b class="{{ $cancelled ? 'cancelled' : 'completed' }}">{{ $payment->status }}</b></div>
                </div>
            </section>

            <section class="grid">
                <div class="cell">
                    <div class="label">Paid From Account</div>
                    <div class="primary">{{ $account->account_name ?? 'Account unavailable' }}</div>
                    <div class="secondary">{{ $account->account_code ?? '—' }}</div>
                    @if($account?->bank_name)<div class="secondary">{{ $account->bank_name }}</div>@endif
                    @if($account?->upi_id)<div class="secondary">UPI: {{ $account->upi_id }}</div>@endif
                </div>
                <div class="cell">
                    <div class="label">Purchase Allocation</div>
                    @if($purchase)
                    <div class="primary">{{ $purchase->purchase_no }}</div>
                    <div class="secondary">Purchase date: {{ $date($purchase->purchase_date) }}</div>
                    <div class="secondary">Purchase total: {{ $money($purchase->grand_total) }}</div>
                    @else
                    <div class="primary">Supplier Advance</div>
                    <div class="secondary">Not allocated to a specific purchase</div>
                    @endif
                </div>
            </section>

            <section class="amount">
                <div class="amount-main">
                    <small>Amount Paid</small>
                    <strong>{{ $money($payment->amount) }}</strong>
                    <span>Indian Rupees {{ number_format((float) $payment->amount, 2) }} only</span>
                </div>
                <div class="mode"><b>{{ $payment->payment_mode }}</b></div>
            </section>

            <section class="remarks">
                <div class="label">Remarks</div>
                <p>{{ $payment->remarks ?: 'No remarks were entered.' }}</p>
            </section>
            <section class="audit">
                <div><small>Created By</small><strong>{{ $payment->creator->name ?? 'System' }}</strong></div>
                <div><small>Document Status</small><strong class="{{ $cancelled ? 'cancelled' : 'completed' }}">{{ $payment->status }}</strong></div>
                <div><small>Authorised Signature</small><span class="signature"></span></div>
            </section>
        </div>
        <footer class="footer">Computer-generated supplier payment voucher · {{ $payment->payment_no }}</footer>
    </main>

    @unless($isPdf)
    <script>
        window.addEventListener('load', function() {
            window.setTimeout(function() {
                window.print();
            }, 350);
        });
    </script>
    @endunless
</body>

</html>