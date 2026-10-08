@php
$paper = strtolower((string) ($paper ?? 'a4')) === 'a5' ? 'a5' : 'a4';
$pdfMode = (bool) ($pdfMode ?? false);
$isA5 = $paper === 'a5';
$isCancelled = $expense->status === \App\Models\Expense::STATUS_CANCELLED;

$companyName = $expense->company?->company_name
?? $expense->company?->name
?? config('app.name');

$storeName = $expense->store?->store_name
?? $expense->store?->name
?? 'Branch';

$storeAddress = collect([
$expense->store?->address,
$expense->store?->city,
$expense->store?->state,
$expense->store?->pincode,
])->filter()->implode(', ');

$money = static fn ($value) => '₹ ' . number_format((float) $value, 2);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $expense->expense_no }} - Expense Voucher</title>

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
            color: #1c2d42;
            background: #ffffff;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
        }

        body.screen-mode {
            min-height: 100vh;
            background: #cbd4df;
        }

        body.pdf-mode.paper-a4 {
            padding: 7mm;
        }

        body.pdf-mode.paper-a5 {
            padding: 6mm;
            font-size: 9px;
        }

        .preview-shell {
            width: 100%;
            text-align: center;
        }

        .screen-mode .preview-shell {
            min-height: 100vh;
            padding: 24px 12px 42px;
        }

        .pdf-mode .preview-shell {
            min-height: 0;
            padding: 0;
        }

        .screen-toolbar {
            display: none;
        }

        .screen-mode .screen-toolbar {
            display: table;
            margin: 0 auto 8px;
            border: 1px solid #aab7c5;
            border-collapse: collapse;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 34, 56, 0.14);
            text-align: left;
        }

        .screen-mode.paper-a4 .screen-toolbar {
            width: 210mm;
        }

        .screen-mode.paper-a5 .screen-toolbar {
            width: 148mm;
        }

        .screen-toolbar td {
            padding: 7px 8px;
            vertical-align: middle;
        }

        .screen-toolbar .toolbar-actions {
            text-align: right;
        }

        .toolbar-title {
            color: #223a53;
            font-size: 10px;
            font-weight: 700;
        }

        .toolbar-button {
            display: inline-block;
            margin-left: 4px;
            padding: 6px 9px;
            color: #ffffff;
            border: 1px solid #155a88;
            border-radius: 3px;
            background: #206f9f;
            font-size: 9px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar-button.back {
            color: #263b51;
            border-color: #aebbc8;
            background: #edf1f5;
        }

        .paper {
            position: relative;
            margin: 0 auto;
            color: #1c2d42;
            background: #ffffff;
            text-align: left;
        }

        .screen-mode.paper-a4 .paper {
            width: 210mm;
            min-height: 297mm;
            box-shadow: 0 12px 34px rgba(15, 33, 55, 0.23);
        }

        .screen-mode.paper-a5 .paper {
            width: 148mm;
            min-height: 210mm;
            box-shadow: 0 12px 34px rgba(15, 33, 55, 0.23);
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
            color: #ffffff;
            background: #9f3f18;
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
            font-size: 15px;
        }

        .company-meta {
            margin-top: 4px;
            color: #ffe3d2;
            font-size: 8px;
        }

        .document-type {
            width: 41%;
            text-align: right;
        }

        .document-type strong {
            display: block;
            font-size: 16px;
            letter-spacing: 0.7px;
        }

        .document-type span {
            display: block;
            margin-top: 4px;
            color: #ffe3d2;
            font-weight: 700;
        }

        .paper-a5 .document-type strong {
            font-size: 12px;
        }

        .cancelled-banner {
            margin-top: 8px;
            padding: 7px 9px;
            color: #8d2825;
            border: 1px solid #dda09c;
            border-left: 4px solid #b5302d;
            background: #fff0ef;
            font-weight: 700;
            text-align: center;
        }

        .meta-table,
        .detail-table,
        .account-table,
        .audit-table,
        .footer-table {
            margin-top: 9px;
        }

        .paper-a5 .meta-table,
        .paper-a5 .detail-table,
        .paper-a5 .account-table,
        .paper-a5 .audit-table {
            margin-top: 7px;
        }

        .meta-table td {
            width: 50%;
            padding: 8px 9px;
            border: 1px solid #bec9d5;
            vertical-align: top;
        }

        .paper-a5 .meta-table td {
            padding: 6px 7px;
        }

        .label {
            display: block;
            margin-bottom: 2px;
            color: #607087;
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.35px;
            text-transform: uppercase;
        }

        .value {
            display: block;
            color: #1c2d43;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .section-title {
            padding: 6px 9px;
            color: #ffffff;
            background: #274f72;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.35px;
            text-transform: uppercase;
        }

        .paper-a5 .section-title {
            padding: 5px 7px;
            font-size: 7px;
        }

        .detail-table,
        .account-table {
            border: 1px solid #b9c5d2;
        }

        .detail-table td,
        .account-table td {
            width: 50%;
            padding: 8px 9px;
            border-right: 1px solid #d3dce5;
            border-bottom: 1px solid #d3dce5;
            vertical-align: top;
        }

        .detail-table td:last-child,
        .account-table td:last-child {
            border-right: 0;
        }

        .detail-table tr:last-child td,
        .account-table tr:last-child td {
            border-bottom: 0;
        }

        .paper-a5 .detail-table td,
        .paper-a5 .account-table td {
            padding: 6px 7px;
        }

        .amount-box {
            margin-top: 9px;
            color: #ffffff;
            background: #b54a19;
        }

        .amount-box td {
            padding: 12px 14px;
            vertical-align: middle;
        }

        .paper-a5 .amount-box td {
            padding: 9px 10px;
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

        .paper-a5 .amount-number {
            font-size: 17px;
        }

        .narration {
            margin-top: 9px;
            border: 1px solid #bec9d5;
        }

        .narration .section-title {
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

        .paper-a5 .narration-body {
            min-height: 38px;
            padding: 7px;
        }

        .audit-table th,
        .audit-table td {
            padding: 6px 7px;
            border: 1px solid #bec9d5;
        }

        .audit-table th {
            color: #ffffff;
            background: #274f72;
            font-size: 7px;
            text-align: left;
            text-transform: uppercase;
        }

        .audit-table td {
            font-size: 8px;
        }

        .paper-a5 .audit-table th,
        .paper-a5 .audit-table td {
            padding: 4px 5px;
            font-size: 7px;
        }

        .audit-table tr:nth-child(even) td {
            background: #f5f8fb;
        }

        .audit-table tr.reversal td {
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

        .paper-a5 .footer-table {
            margin-top: 10px;
        }

        .paper-a5 .footer-table td {
            padding-top: 13px;
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
            width: 110px;
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

        @@media print {

            html,
            body {
                background: #ffffff !important;
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

<body class="{{ $isA5 ? 'paper-a5' : 'paper-a4' }} {{ $pdfMode ? 'pdf-mode' : 'screen-mode' }}">
    <div class="preview-shell">
        <table class="screen-toolbar">
            <tr>
                <td class="toolbar-title">
                    {{ strtoupper($paper) }} Expense Voucher Preview
                </td>
                <td class="toolbar-actions">
                    <a
                        class="toolbar-button back"
                        href="{{ route('expenses.show', $expense) }}">
                        Back
                    </a>
                    <button
                        class="toolbar-button"
                        type="button"
                        onclick="window.print()">
                        Print {{ strtoupper($paper) }}
                    </button>
                </td>
            </tr>
        </table>

        <main class="paper">
            <div class="document">
                <table class="header">
                    <tr>
                        <td>
                            <h1 class="company-name">{{ $companyName }}</h1>
                            <div class="company-meta">
                                {{ $storeName }}
                                @if ($storeAddress)
                                • {{ $storeAddress }}
                                @endif
                            </div>
                        </td>
                        <td class="document-type">
                            <strong>EXPENSE VOUCHER</strong>
                            <span>{{ $expense->expense_no }}</span>
                        </td>
                    </tr>
                </table>

                @if ($isCancelled)
                <div class="cancelled-banner">
                    CANCELLED
                    @if ($expense->cancelled_at)
                    — {{ $expense->cancelled_at->timezone('Asia/Kolkata')->format('d-m-Y h:i A') }}
                    @endif
                </div>
                @endif

                <table class="meta-table">
                    <tr>
                        <td>
                            <span class="label">Expense Number</span>
                            <span class="value">{{ $expense->expense_no }}</span>
                        </td>
                        <td>
                            <span class="label">Expense Date</span>
                            <span class="value">{{ $expense->expense_date?->format('d-m-Y') ?? '—' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="label">Expense Category</span>
                            <span class="value">{{ $expense->expense_category }}</span>
                        </td>
                        <td>
                            <span class="label">Document Status</span>
                            <span class="value">{{ $expense->status }}</span>
                        </td>
                    </tr>
                </table>

                <div class="section-title">Payee and Expense Details</div>
                <table class="detail-table">
                    <tr>
                        <td>
                            <span class="label">Paid To</span>
                            <span class="value">{{ $expense->payee_name ?: 'General Expense' }}</span>
                        </td>
                        <td>
                            <span class="label">Reference Number</span>
                            <span class="value">{{ $expense->reference_no ?: '—' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="label">Payment Mode</span>
                            <span class="value">{{ ucwords(str_replace('_', ' ', (string) $expense->payment_mode)) }}</span>
                        </td>
                        <td>
                            <span class="label">Created By</span>
                            <span class="value">{{ $expense->creator?->name ?? 'System' }}</span>
                        </td>
                    </tr>
                </table>

                <div class="section-title">Payment Account</div>
                <table class="account-table">
                    <tr>
                        <td>
                            <span class="label">Account Name</span>
                            <span class="value">{{ $expense->account?->account_name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="label">Account Code</span>
                            <span class="value">{{ $expense->account?->account_code ?? '—' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="label">Account Type</span>
                            <span class="value">{{ ucwords(str_replace('_', ' ', (string) ($expense->account?->account_type ?? ''))) ?: '—' }}</span>
                        </td>
                        <td>
                            <span class="label">Accounting Effect</span>
                            <span class="value">Credit — Money Paid</span>
                        </td>
                    </tr>
                </table>

                <table class="amount-box">
                    <tr>
                        <td class="amount-title">Expense Amount</td>
                        <td class="amount-number">{{ $money($expense->amount) }}</td>
                    </tr>
                </table>

                <section class="narration">
                    <div class="section-title">Narration / Remarks</div>
                    <div class="narration-body">{{ $expense->remarks ?: 'No narration was entered for this expense.' }}</div>
                </section>

                @if ($expense->relationLoaded('accountTransactions') && $expense->accountTransactions->isNotEmpty())
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th style="width: 16%;">Date</th>
                            <th style="width: 25%;">Transaction</th>
                            <th style="width: 17%;">Status</th>
                            <th class="number" style="width: 21%;">Debit</th>
                            <th class="number" style="width: 21%;">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expense->accountTransactions as $transaction)
                        <tr class="{{ $transaction->is_reversal ? 'reversal' : '' }}">
                            <td>{{ $transaction->transaction_date?->format('d-m-Y') }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $transaction->transaction_type)) }}</td>
                            <td>
                                <span class="status {{ $transaction->is_reversal ? 'reversal' : '' }}">
                                    {{ $transaction->is_reversal ? 'Reversal' : 'Posted' }}
                                </span>
                            </td>
                            <td class="number">
                                {{ (float) $transaction->debit > 0 ? $money($transaction->debit) : '—' }}
                            </td>
                            <td class="number">
                                {{ (float) $transaction->credit > 0 ? $money($transaction->credit) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

                <table class="footer-table">
                    <tr>
                        <td class="prepared">
                            <span class="label">Prepared By</span>
                            <strong>{{ $expense->creator?->name ?? 'System' }}</strong>
                            <span>{{ $expense->created_at?->timezone('Asia/Kolkata')->format('d-m-Y h:i A') }}</span>
                        </td>
                        <td class="signature">
                            <span>Authorised Signature</span>
                        </td>
                    </tr>
                </table>

                <div class="note">
                    Computer-generated expense voucher • {{ $storeName }}
                </div>
            </div>
        </main>
    </div>
</body>

</html>