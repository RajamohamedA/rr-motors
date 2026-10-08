@php
$paper = strtolower((string) ($paper ?? 'a4')) === 'a5' ? 'a5' : 'a4';
$pdfMode = (bool) ($pdfMode ?? false);
$isA5 = $paper === 'a5';
$isReceipt = $voucher->voucher_type === \App\Models\Voucher::TYPE_RECEIPT;
$isCancelled = $voucher->status === \App\Models\Voucher::STATUS_CANCELLED;
$companyName = $voucher->company?->company_name ?? $voucher->company?->name ?? config('app.name');
$storeName = $voucher->store?->store_name ?? $voucher->store?->name ?? 'Branch';
$storeAddress = collect([$voucher->store?->address, $voucher->store?->city, $voucher->store?->state, $voucher->store?->pincode])->filter()->implode(', ');
$typeTitle = $isReceipt ? 'RECEIPT VOUCHER' : 'PAYMENT VOUCHER';
$partyLabel = $isReceipt ? 'Received From' : 'Paid To';
$amountLabel = $isReceipt ? 'Amount Received' : 'Amount Paid';
$money = static fn ($value): string => '₹ ' . number_format((float) $value, 2);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $voucher->voucher_no }} - {{ $typeTitle }}</title>
    <style>
        @if ($isA5) @@page {
            size: A5 portrait;
            margin: 0;
        }

        @else @@page {
            size: A4 portrait;
            margin: 0;
        }

        @endif * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: #1d2b3d;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.38;
        }

        body.screen-mode {
            background: #cbd4df;
        }

        body.pdf-mode {
            background: #fff;
        }

        body.pdf-mode.paper-a4 {
            padding: 7mm;
        }

        body.pdf-mode.paper-a5 {
            padding: 6mm;
        }

        body.paper-a5 {
            font-size: 9px;
        }

        .preview-shell {
            width: 100%;
            text-align: center;
        }

        .screen-mode .preview-shell {
            min-height: 100vh;
            padding: 28px 12px 44px;
        }

        .pdf-mode .preview-shell {
            min-height: 0;
            padding: 0;
        }

        .paper {
            position: relative;
            margin: 0 auto;
            color: #1d2b3d;
            background: #fff;
            text-align: left;
        }

        .screen-mode.paper-a4 .paper {
            width: 210mm;
            min-height: 297mm;
            box-shadow: 0 12px 34px rgba(15, 33, 55, .23);
        }

        .screen-mode.paper-a5 .paper {
            width: 148mm;
            min-height: 210mm;
            box-shadow: 0 12px 34px rgba(15, 33, 55, .23);
        }

        .pdf-mode .paper {
            width: 100%;
            min-height: 0;
            box-shadow: none;
        }

        .document {
            width: 100%;
        }

        .screen-mode.paper-a4 .document {
            padding: 10mm;
        }

        .screen-mode.paper-a5 .document {
            padding: 8mm;
        }

        .pdf-mode .document {
            padding: 0;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header {
            color: #fff;
            background: #086b50;
        }

        .payment-document .header {
            background: #a93631;
        }

        .header td {
            padding: 12px 14px;
            vertical-align: middle;
        }

        .paper-a5 .header td {
            padding: 9px 10px;
        }

        .company-name {
            margin: 0;
            font-size: 20px;
            line-height: 1.1;
        }

        .paper-a5 .company-name {
            font-size: 16px;
        }

        .company-meta {
            margin-top: 4px;
            color: #e8f5f1;
            font-size: 8px;
        }

        .document-type {
            width: 42%;
            text-align: right;
        }

        .document-type strong {
            display: block;
            font-size: 16px;
            letter-spacing: .8px;
        }

        .document-type span {
            display: block;
            margin-top: 4px;
            color: #e8f5f1;
            font-weight: 700;
        }

        .paper-a5 .document-type strong {
            font-size: 13px;
        }

        .cancelled-banner {
            margin-top: 8px;
            padding: 7px 9px;
            color: #8b2424;
            border: 1px solid #d89793;
            border-left: 4px solid #b5302d;
            background: #fff0ef;
            font-weight: 700;
            text-align: center;
        }

        .info-table,
        .party-table,
        .audit-table {
            margin-top: 9px;
        }

        .paper-a5 .info-table,
        .paper-a5 .party-table,
        .paper-a5 .audit-table {
            margin-top: 7px;
        }

        .info-table td {
            width: 50%;
            padding: 8px 9px;
            border: 1px solid #bcc7d3;
            vertical-align: top;
        }

        .paper-a5 .info-table td {
            padding: 6px 7px;
        }

        .label {
            display: block;
            margin-bottom: 2px;
            color: #607087;
            font-size: 7px;
            font-weight: 700;
            letter-spacing: .35px;
            text-transform: uppercase;
        }

        .value {
            display: block;
            color: #1c2d43;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .party-table td {
            width: 50%;
            vertical-align: top;
        }

        .party-table td:first-child {
            padding-right: 4px;
        }

        .party-table td:last-child {
            padding-left: 4px;
        }

        .box {
            min-height: 72px;
            border: 1px solid #b9c5d2;
            background: #f8fafc;
        }

        .paper-a5 .box {
            min-height: 62px;
        }

        .box-title,
        .section-title {
            padding: 6px 9px;
            color: #fff;
            background: #274f72;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .35px;
            text-transform: uppercase;
        }

        .box-body {
            padding: 9px;
        }

        .box-body strong,
        .box-body span {
            display: block;
        }

        .box-body strong {
            font-size: 12px;
        }

        .box-body span {
            margin-top: 3px;
            color: #5c6b80;
            font-size: 8px;
        }

        .paper-a5 .box-title,
        .paper-a5 .section-title {
            padding: 5px 7px;
            font-size: 7px;
        }

        .paper-a5 .box-body {
            padding: 7px;
        }

        .paper-a5 .box-body strong {
            font-size: 10px;
        }

        .amount-box {
            margin-top: 9px;
            padding: 12px 14px;
            color: #fff;
            background: #087b5a;
        }

        .payment-document .amount-box {
            background: #b63a35;
        }

        .amount-box td {
            vertical-align: middle;
        }

        .amount-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .amount-number {
            font-size: 23px;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .paper-a5 .amount-box {
            margin-top: 7px;
            padding: 9px 10px;
        }

        .paper-a5 .amount-number {
            font-size: 17px;
        }

        .narration {
            margin-top: 9px;
            border: 1px solid #bec9d5;
        }

        .section-title {
            color: #263c55;
            border-bottom: 1px solid #bec9d5;
            background: #e7edf4;
        }

        .narration-body {
            min-height: 48px;
            padding: 9px;
            color: #34465c;
            white-space: pre-wrap;
        }

        .audit-table th,
        .audit-table td {
            padding: 6px 8px;
            border: 1px solid #bec9d5;
        }

        .audit-table th {
            color: #fff;
            background: #274f72;
            font-size: 7px;
            text-align: left;
            text-transform: uppercase;
        }

        .audit-table td {
            font-size: 8px;
        }

        .audit-table tr:nth-child(even) td {
            background: #f5f8fb;
        }

        .audit-table .reversal td {
            color: #8e3834;
            background: #fff2f1;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .status {
            display: inline-block;
            padding: 2px 5px;
            color: #086047;
            border: 1px solid #86c8b0;
            background: #e9f8f2;
            font-size: 7px;
            font-weight: 700;
        }

        .status.reversal {
            color: #932f2c;
            border-color: #dda09c;
            background: #fff0ef;
        }

        .footer-table {
            margin-top: 15px;
        }

        .footer-table td {
            width: 50%;
            padding-top: 18px;
            vertical-align: bottom;
        }

        .prepared {
            color: #536176;
        }

        .prepared strong {
            display: block;
            color: #273b53;
        }

        .signature {
            text-align: right;
        }

        .signature span {
            display: inline-block;
            width: 105px;
            padding-top: 4px;
            border-top: 1px solid #46566b;
            font-size: 8px;
            text-align: center;
        }

        .note {
            margin-top: 10px;
            padding-top: 5px;
            color: #748094;
            border-top: 1px solid #d5dce4;
            font-size: 7px;
            text-align: center;
        }

        .screen-toolbar {
            display: none;
        }

        .screen-mode .screen-toolbar {
            display: flex;
            margin: 0 auto 8px;
            align-items: center;
            justify-content: space-between;
            padding: 7px 8px;
            border: 1px solid #aebbc9;
            background: #fff;
            box-shadow: 0 4px 12px rgba(20, 37, 60, .12);
            text-align: left;
        }

        .screen-mode.paper-a4 .screen-toolbar {
            width: 210mm;
        }

        .screen-mode.paper-a5 .screen-toolbar {
            width: 148mm;
        }

        .actions {
            display: flex;
            gap: 5px;
        }

        .button {
            display: inline-block;
            padding: 6px 9px;
            color: #fff;
            border: 0;
            border-radius: 3px;
            background: #256699;
            font-size: 9px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .button.back {
            color: #263950;
            border: 1px solid #aebbc9;
            background: #edf1f5;
        }

        @@media print {

            html,
            body {
                background: #fff !important;
            }

            .preview-shell {
                min-height: 0;
                padding: 0;
            }

            .screen-toolbar {
                display: none !important;
            }

            .paper {
                width: 100% !important;
                min-height: 0 !important;
                box-shadow: none !important;
            }

            .screen-mode.paper-a4 .document {
                padding: 10mm !important;
            }

            .screen-mode.paper-a5 .document {
                padding: 8mm !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body class="{{ $isA5 ? 'paper-a5' : 'paper-a4' }} {{ $pdfMode ? 'pdf-mode' : 'screen-mode' }} {{ $isReceipt ? 'receipt-document' : 'payment-document' }}">
    <div class="preview-shell">
        <div class="screen-toolbar">
            <strong>{{ strtoupper($paper) }} {{ $typeTitle }}</strong>
            <div class="actions">
                <a class="button back" href="{{ route('vouchers.show', $voucher) }}">Back</a>
                <button class="button" type="button" onclick="window.print()">Print {{ strtoupper($paper) }}</button>
            </div>
        </div>
        <main class="paper">
            <div class="document">
                <table class="header">
                    <tr>
                        <td>
                            <h1 class="company-name">{{ $companyName }}</h1>
                            <div class="company-meta">{{ $storeName }}@if ($storeAddress) • {{ $storeAddress }}@endif</div>
                        </td>
                        <td class="document-type"><strong>{{ $typeTitle }}</strong><span>{{ $voucher->voucher_no }}</span></td>
                    </tr>
                </table>
                @if ($isCancelled)<div class="cancelled-banner">CANCELLED @if ($voucher->cancelled_at) — {{ $voucher->cancelled_at->timezone('Asia/Kolkata')->format('d-m-Y h:i A') }}@endif</div>@endif
                <table class="info-table">
                    <tr>
                        <td><span class="label">Voucher Number</span><span class="value">{{ $voucher->voucher_no }}</span></td>
                        <td><span class="label">Voucher Date</span><span class="value">{{ $voucher->voucher_date?->format('d-m-Y') ?? '—' }}</span></td>
                    </tr>
                    <tr>
                        <td><span class="label">Payment Mode</span><span class="value">{{ ucwords(str_replace('_', ' ', (string) $voucher->payment_mode)) }}</span></td>
                        <td><span class="label">Reference Number</span><span class="value">{{ $voucher->reference_no ?: '—' }}</span></td>
                    </tr>
                </table>
                <table class="party-table">
                    <tr>
                        <td>
                            <div class="box">
                                <div class="box-title">{{ $partyLabel }}</div>
                                <div class="box-body"><strong>{{ $voucher->party_name ?: 'General' }}</strong><span>{{ $voucher->party_type ? class_basename($voucher->party_type) : 'Other / General' }}</span></div>
                            </div>
                        </td>
                        <td>
                            <div class="box">
                                <div class="box-title">Payment Account</div>
                                <div class="box-body"><strong>{{ $voucher->account?->account_name ?? '—' }}</strong><span>Account Code: {{ $voucher->account?->account_code ?? '—' }}</span></div>
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="amount-box">
                    <table>
                        <tr>
                            <td class="amount-title">{{ $amountLabel }}</td>
                            <td class="amount-number">{{ $money($voucher->amount) }}</td>
                        </tr>
                    </table>
                </div>
                <section class="narration">
                    <div class="section-title">Narration / Remarks</div>
                    <div class="narration-body">{{ $voucher->remarks ?: 'No narration was entered for this voucher.' }}</div>
                </section>
                @if ($voucher->relationLoaded('accountTransactions') && $voucher->accountTransactions->isNotEmpty())
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction</th>
                            <th>Nature</th>
                            <th class="number">Debit</th>
                            <th class="number">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($voucher->accountTransactions as $transaction)<tr class="{{ $transaction->is_reversal ? 'reversal' : '' }}">
                            <td>{{ $transaction->transaction_date?->format('d-m-Y') }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $transaction->transaction_type)) }}</td>
                            <td><span class="status {{ $transaction->is_reversal ? 'reversal' : '' }}">{{ $transaction->is_reversal ? 'Reversal' : 'Posted' }}</span></td>
                            <td class="number">{{ (float) $transaction->debit > 0 ? $money($transaction->debit) : '—' }}</td>
                            <td class="number">{{ (float) $transaction->credit > 0 ? $money($transaction->credit) : '—' }}</td>
                        </tr>@endforeach
                    </tbody>
                </table>
                @endif
                <table class="footer-table">
                    <tr>
                        <td class="prepared"><span class="label">Prepared By</span><strong>{{ $voucher->creator?->name ?? 'System' }}</strong><span>{{ $voucher->created_at?->timezone('Asia/Kolkata')->format('d-m-Y h:i A') }}</span></td>
                        <td class="signature"><span>Authorised Signature</span></td>
                    </tr>
                </table>
                <div class="note">Computer-generated {{ strtolower($typeTitle) }} • {{ $storeName }}</div>
            </div>
        </main>
    </div>
</body>

</html>