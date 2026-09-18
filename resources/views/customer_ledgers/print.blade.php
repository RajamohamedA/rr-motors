@php
$pdfMode = $pdfMode ?? false;
$companyName = data_get($company, 'company_name') ?? data_get($company, 'name') ?? config('app.name');
$storeName = data_get($store, 'store_name') ?? data_get($store, 'name') ?? 'Branch';
$address = data_get($store, 'address') ?? data_get($company, 'address');
$phone = data_get($store, 'phone') ?? data_get($company, 'phone');
$gst = data_get($store, 'gst_number') ?? data_get($company, 'gst_number');
$balanceText = function ($value) {
$value = (float) $value;
return '₹ ' . number_format(abs($value), 2) . ' ' . ($value < -0.009 ? 'Cr' : 'Dr' );
    };
    @endphp
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Customer Ledger - {{ $customer->customer_name }}</title>
        <style>
            @page {
                size: A4 portrait;

                margin: {
                        {
                        $pdfMode ? '0': '14mm 10mm'
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
                        $pdfMode ? '#fff': '#e8edf4'
                    }
                }

                ;
                font-family: DejaVu Sans,
                Arial,
                sans-serif;
                font-size: 9px;
                line-height: 1.4;
            }

            body {
                padding: {
                        {
                        $pdfMode ? '11mm 9mm 10mm': '24px 12px'
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
                border-radius: 6px;
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

            .toolbar a,
            .toolbar button {
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

            .toolbar .back {
                color: #334155;
                border: 1px solid #cbd5e1;
                background: #fff;
            }

            .paper {
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
                        $pdfMode ? '276mm': '297mm'
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
                color: #172033;
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

            .header {
                display: table;
                width: 100%;
                padding: 13px 16px;
                color: #fff;
                background: #1e3a5f;
            }

            .header>div {
                display: table-cell;
                vertical-align: middle;
            }

            .company {
                width: 68%;
            }

            .company h1 {
                margin: 0 0 2px;
                font-size: 18px;
            }

            .company p {
                margin: 1px 0;
                color: #dbeafe;
                font-size: 8px;
            }

            .title {
                text-align: right;
            }

            .title strong {
                display: block;
                font-size: 16px;
                letter-spacing: .8px;
            }

            .title span {
                display: inline-block;
                margin-top: 3px;
                padding: 3px 7px;
                border: 1px solid #7591ad;
                border-radius: 3px;
                font-size: 8px;
            }

            .customer-band {
                display: table;
                width: 100%;
                padding: 9px 16px;
                border-bottom: 1px solid #d8e1eb;
                background: #f3f7fb;
            }

            .customer-band>div {
                display: table-cell;
                vertical-align: middle;
            }

            .customer-details {
                width: 55%;
            }

            .customer-details strong {
                display: block;
                color: #15385c;
                font-size: 12px;
            }

            .customer-details span {
                color: #64748b;
            }

            .period {
                text-align: right;
            }

            .period small {
                display: block;
                color: #64748b;
                font-size: 7px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .period strong {
                font-size: 10px;
            }

            .body {
                padding: 12px 16px 20px;
            }

            .summary {
                display: table;
                width: 100%;
                margin-bottom: 11px;
                table-layout: fixed;
            }

            .summary>div {
                display: table-cell;
                padding-right: 6px;
            }

            .summary>div:last-child {
                padding-right: 0;
            }

            .summary-box {
                padding: 8px 9px;
                border: 1px solid #d9e2ec;
                border-top: 3px solid;
                border-radius: 4px;
                background: #fff;
            }

            .summary-box small {
                display: block;
                color: #64748b;
                font-size: 7px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .summary-box strong {
                display: block;
                margin-top: 2px;
                font-size: 11px;
            }

            .opening {
                border-top-color: #475569;
            }

            .debit {
                border-top-color: #dc2626;
            }

            .credit {
                border-top-color: #16a34a;
            }

            .closing {
                border-top-color: #2563eb;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            .ledger {
                table-layout: fixed;
            }

            .ledger th {
                padding: 6px 5px;
                border: 1px solid #b9c8d8;
                color: #fff;
                background: #294d73;
                font-size: 7px;
                text-align: left;
                text-transform: uppercase;
            }

            .ledger td {
                padding: 6px 5px;
                border: 1px solid #d9e2ec;
                vertical-align: top;
                overflow-wrap: break-word;
            }

            .ledger tbody tr:nth-child(even) {
                background: #f8fafc;
            }

            .ledger .opening-row {
                background: #fff8e8 !important;
            }

            .ledger .reversal-row {
                background: #fff3f3 !important;
            }

            .ledger .serial {
                width: 5%;
                text-align: center;
            }

            .ledger .date {
                width: 11%;
                white-space: nowrap;
            }

            .ledger .type {
                width: 15%;
            }

            .ledger .reference {
                width: 14%;
            }

            .ledger .remarks {
                width: 23%;
            }

            .ledger .money {
                width: 11%;
                text-align: right;
                white-space: nowrap;
            }

            .type-badge {
                display: inline-block;
                padding: 2px 4px;
                border-radius: 3px;
                color: #334155;
                background: #e2e8f0;
                font-size: 7px;
                font-weight: 700;
            }

            .reversal-mark {
                display: block;
                margin-top: 2px;
                color: #b91c1c;
                font-size: 6px;
                font-weight: 700;
            }

            .debit-value {
                color: #b91c1c;
                font-weight: 700;
            }

            .credit-value {
                color: #15803d;
                font-weight: 700;
            }

            .balance-dr {
                color: #a16207;
                font-weight: 700;
            }

            .balance-cr {
                color: #15803d;
                font-weight: 700;
            }

            .empty {
                padding: 24px !important;
                color: #64748b;
                text-align: center;
            }

            .totals {
                width: 52%;
                margin: 11px 0 0 auto;
                border: 1px solid #d8e1eb;
            }

            .totals td {
                padding: 6px 8px;
                border-bottom: 1px solid #e5eaf0;
            }

            .totals td:last-child {
                text-align: right;
                font-weight: 700;
            }

            .totals tr:last-child td {
                border-bottom: 0;
                color: #fff;
                background: #1e3a5f;
                font-size: 10px;
            }

            .note {
                margin-top: 13px;
                padding: 7px 9px;
                border-left: 3px solid #94a3b8;
                color: #64748b;
                background: #f8fafc;
                font-size: 7px;
            }

            .footer {
                display: table;
                width: 100%;
                margin-top: 26px;
            }

            .footer>div {
                display: table-cell;
                width: 50%;
                vertical-align: bottom;
            }

            .footer .generated {
                color: #94a3b8;
                font-size: 7px;
            }

            .signature {
                padding-top: 29px;
                text-align: right;
            }

            .signature strong {
                display: inline-block;
                min-width: 130px;
                padding-top: 4px;
                border-top: 1px solid #64748b;
                text-align: center;
            }

            @media screen and (min-width: 900px) {
                body {
                    text-align: center !important;
                }

                .toolbar,
                .paper {
                    width: 210mm !important;
                    min-width: 210mm !important;
                    max-width: 210mm !important;
                    margin-left: auto !important;
                    margin-right: auto !important;
                }

                .paper {
                    display: inline-block !important;
                    min-height: 297mm !important;
                    text-align: left !important;
                }
            }

            @media screen and (max-width: 899px) {

                .toolbar,
                .paper {
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
                            $pdfMode ? '11mm 9mm 10mm': '0'
                        }
                    }

                    ;
                    text-align: center;
                }

                .toolbar {
                    display: none !important;
                }

                .paper {
                    display: block;
                    width: 100%;

                    min-height: {
                            {
                            $pdfMode ? '276mm': '0'
                        }
                    }

                    ;
                    margin: 0 auto;
                    border: 0;
                    box-shadow: none;
                    text-align: left;
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
            <strong>Customer Ledger Preview</strong>
            <div><a class="back" href="{{ route('customer-ledger.index', request()->query()) }}">Back</a><button type="button" onclick="window.print()">Print Ledger</button></div>
        </div>
        @endunless

        <main class="paper">
            <header class="header">
                <div class="company">
                    <h1>{{ $companyName }}</h1>
                    <p><strong>{{ $storeName }}</strong></p>@if($address)<p>{{ $address }}</p>@endif<p>@if($phone)Phone: {{ $phone }}@endif @if($phone && $gst)&nbsp; | &nbsp;@endif @if($gst)GSTIN: {{ $gst }}@endif</p>
                </div>
                <div class="title"><strong>CUSTOMER LEDGER</strong><span>ACCOUNT STATEMENT</span></div>
            </header>

            <section class="customer-band">
                <div class="customer-details"><strong>{{ $customer->customer_name }}</strong><span>{{ $customer->customer_code }}@if($customer->mobile) &nbsp;·&nbsp; {{ $customer->mobile }}@endif @if($customer->gst_number) &nbsp;·&nbsp; GSTIN {{ $customer->gst_number }}@endif</span></div>
                <div class="period"><small>Statement Period</small><strong>{{ \Illuminate\Support\Carbon::parse($filters['from_date'])->format('d-m-Y') }} to {{ \Illuminate\Support\Carbon::parse($filters['to_date'])->format('d-m-Y') }}</strong></div>
            </section>

            <div class="body">
                <section class="summary">
                    <div>
                        <div class="summary-box opening"><small>Opening Balance</small><strong>{{ $balanceText($summary['opening_balance']) }}</strong></div>
                    </div>
                    <div>
                        <div class="summary-box debit"><small>Period Debit</small><strong>₹ {{ number_format($summary['period_debit'], 2) }}</strong></div>
                    </div>
                    <div>
                        <div class="summary-box credit"><small>Period Credit</small><strong>₹ {{ number_format($summary['period_credit'], 2) }}</strong></div>
                    </div>
                    <div>
                        <div class="summary-box closing"><small>Closing Balance</small><strong>{{ $balanceText($summary['closing_balance']) }}</strong></div>
                    </div>
                </section>

                <table class="ledger">
                    <thead>
                        <tr>
                            <th class="serial">#</th>
                            <th class="date">Date</th>
                            <th class="type">Transaction</th>
                            <th class="reference">Reference</th>
                            <th class="remarks">Remarks</th>
                            <th class="money">Debit</th>
                            <th class="money">Credit</th>
                            <th class="money">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="opening-row">
                            <td class="serial">—</td>
                            <td class="date">{{ \Illuminate\Support\Carbon::parse($filters['from_date'])->format('d-m-Y') }}</td>
                            <td><span class="type-badge">Opening Balance</span></td>
                            <td>—</td>
                            <td>Balance brought forward</td>
                            <td class="money">—</td>
                            <td class="money">—</td>
                            <td class="money {{ $summary['opening_balance'] < 0 ? 'balance-cr' : 'balance-dr' }}">{{ $balanceText($summary['opening_balance']) }}</td>
                        </tr>
                        @forelse($entries as $entry)
                        <tr class="{{ $entry['is_reversal'] ? 'reversal-row' : '' }}">
                            <td class="serial">{{ $loop->iteration }}</td>
                            <td class="date">{{ \Illuminate\Support\Carbon::parse($entry['transaction_date'])->format('d-m-Y') }}</td>
                            <td><span class="type-badge">{{ $entry['type_label'] }}</span>@if($entry['is_reversal'])<span class="reversal-mark">REVERSAL</span>@endif</td>
                            <td>{{ $entry['reference_no'] ?: '—' }}</td>
                            <td>{{ $entry['remarks'] ?: '—' }}</td>
                            <td class="money debit-value">{{ $entry['debit'] > 0 ? '₹ '.number_format($entry['debit'], 2) : '—' }}</td>
                            <td class="money credit-value">{{ $entry['credit'] > 0 ? '₹ '.number_format($entry['credit'], 2) : '—' }}</td>
                            <td class="money {{ $entry['running_balance'] < 0 ? 'balance-cr' : 'balance-dr' }}">{{ $balanceText($entry['running_balance']) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="empty" colspan="8">No ledger transactions were found for the selected period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <table class="totals">
                    <tr>
                        <td>Opening Balance</td>
                        <td>{{ $balanceText($summary['opening_balance']) }}</td>
                    </tr>
                    <tr>
                        <td>Total Debit</td>
                        <td>₹ {{ number_format($summary['period_debit'], 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Credit</td>
                        <td>₹ {{ number_format($summary['period_credit'], 2) }}</td>
                    </tr>
                    <tr>
                        <td>Closing Balance</td>
                        <td>{{ $balanceText($summary['closing_balance']) }}</td>
                    </tr>
                </table>

                <div class="note">Dr means the customer owes the business. Cr means the business holds a customer advance or credit balance. Reversed transactions remain visible for audit.</div>
                <footer class="footer">
                    <div class="generated">Generated on {{ now()->format('d-m-Y h:i A') }} · Computer-generated account statement</div>
                    <div class="signature"><strong>Authorised Signatory</strong></div>
                </footer>
            </div>
        </main>

        @unless($pdfMode)<script>
            window.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p') {
                    e.preventDefault();
                    window.print();
                }
            });
        </script>@endunless
    </body>

    </html>