@php
$isPdf = (bool) ($pdfMode ?? false);
$paperSize = strtolower((string) ($paper ?? 'a4')) === 'a5' ? 'a5' : 'a4';
$isA5 = $paperSize === 'a5';
$storeName = $store?->store_name ?? $store?->name ?? 'Branch';
$company = $store?->company;
$companyName = $company?->company_name ?? $company?->name ?? config('app.name');
$address = implode(', ', array_filter([
$store?->address,
$store?->city,
$store?->state,
$store?->pincode,
]));
$selectedAccount = $accounts->count() === 1 ? $accounts->first() : null;
$accountTitle = $selectedAccount?->account_name ?? 'All Cash & Bank Accounts';
$money = static fn ($value): string => 'Rs. ' . number_format((float) $value, 2);
$displayDate = static function ($value): string {
if (!$value) {
return '-';
}

try {
return \Illuminate\Support\Carbon::parse($value)->format('d-m-Y');
} catch (\Throwable) {
return (string) $value;
}
};
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash &amp; Bank Book</title>
    <style>
        @if ($isA5) @page {
            size: A5 portrait;
            margin: 9mm 8mm;
        }

        @else @page {
            size: A4 portrait;
            margin: 11mm 10mm;
        }

        @endif * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: #1b3549;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            line-height: 1.35;
            background: #cbd5df;
        }

        body.a5 {
            font-size: 8px;
        }

        body.pdf-mode {
            background: #fff;
        }

        .preview {
            display: flex;
            min-height: 100vh;
            align-items: flex-start;
            justify-content: center;
            padding: 26px 12px 44px;
        }

        .paper {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm 9mm;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 12px 34px rgba(21, 44, 64, .24);
        }

        .a5 .paper {
            width: 148mm;
            min-height: 210mm;
            padding: 8mm 7mm;
        }

        .pdf-mode .preview {
            display: block;
            min-height: 0;
            padding: 3mm;
        }

        .pdf-mode .paper {
            width: auto;
            min-height: 0;
            margin: 0;
            padding: 0;
            box-shadow: none;
        }

        .header-table,
        .meta-table,
        .summary-table,
        .book-table,
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .header-table {
            color: #fff;
            background: #173d59;
        }

        .header-table td {
            padding: 11px 12px;
            vertical-align: middle;
        }

        .a5 .header-table td {
            padding: 8px 9px;
        }

        .company-name {
            font-size: 17px;
            font-weight: 700;
            line-height: 1.15;
        }

        .a5 .company-name {
            font-size: 14px;
        }

        .company-meta {
            margin-top: 3px;
            color: #d8e9f3;
            font-size: 7.6px;
        }

        .a5 .company-meta {
            font-size: 6.6px;
        }

        .document-title {
            text-align: right;
        }

        .document-title strong {
            display: block;
            font-size: 13px;
            letter-spacing: .45px;
        }

        .a5 .document-title strong {
            font-size: 10.5px;
        }

        .document-title span {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 6px;
            color: #173d59;
            font-size: 7px;
            font-weight: 700;
            border-radius: 8px;
            background: #fff;
        }

        .meta-table {
            margin-top: 9px;
            border: 1px solid #c7d5e0;
            background: #f5f9fc;
        }

        .a5 .meta-table {
            margin-top: 7px;
        }

        .meta-table td {
            width: 25%;
            padding: 7px 8px;
            border-right: 1px solid #d4dfe8;
            vertical-align: top;
        }

        .meta-table td:last-child {
            border-right: 0;
        }

        .a5 .meta-table td {
            padding: 5px 6px;
        }

        .meta-label {
            display: block;
            margin-bottom: 2px;
            color: #6b8091;
            font-size: 6.8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .meta-value {
            display: block;
            color: #1e3a50;
            font-size: 8.3px;
            font-weight: 700;
        }

        .a5 .meta-label {
            font-size: 5.8px;
        }

        .a5 .meta-value {
            font-size: 7px;
        }

        .summary-table {
            margin-top: 9px;
        }

        .a5 .summary-table {
            margin-top: 7px;
        }

        .summary-table td {
            width: 25%;
            padding-right: 5px;
            vertical-align: top;
        }

        .summary-table td:last-child {
            padding-right: 0;
        }

        .summary-box {
            min-height: 45px;
            padding: 7px 8px;
            color: #fff;
            border-radius: 2px;
            background: #176fa8;
        }

        .summary-box.in {
            background: #167957;
        }

        .summary-box.out {
            background: #b93c4e;
        }

        .summary-box.closing {
            background: #6749a2;
        }

        .summary-box span {
            display: block;
            color: rgba(255, 255, 255, .80);
            font-size: 6.6px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary-box strong {
            display: block;
            margin-top: 3px;
            color: #fff;
            font-size: 11px;
        }

        .a5 .summary-box {
            min-height: 38px;
            padding: 5px 6px;
        }

        .a5 .summary-box span {
            font-size: 5.4px;
        }

        .a5 .summary-box strong {
            font-size: 8.7px;
        }

        .section-title {
            margin-top: 11px;
            padding: 6px 8px;
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            background: #176fa8;
        }

        .a5 .section-title {
            margin-top: 8px;
            padding: 5px 6px;
            font-size: 6.6px;
        }

        .book-table {
            table-layout: fixed;
            border: 1px solid #bbcbd7;
            border-top: 0;
        }

        .book-table thead {
            display: table-header-group;
        }

        .book-table tr {
            page-break-inside: avoid;
        }

        .book-table th {
            padding: 5px 4px;
            color: #e6f0f6;
            font-size: 6.2px;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            border-right: 1px solid rgba(255, 255, 255, .14);
            background: #173d59;
        }

        .book-table th.money-in {
            background: #126849;
        }

        .book-table th.money-out {
            background: #a93345;
        }

        .book-table th.balance {
            background: #564087;
        }

        .book-table td {
            padding: 5px 4px;
            color: #2b4558;
            font-size: 7px;
            vertical-align: middle;
            word-wrap: break-word;
            border-right: 1px solid #d6e0e8;
            border-bottom: 1px solid #d6e0e8;
        }

        .book-table tbody tr:nth-child(even) td {
            background: #f5f8fb;
        }

        .book-table th:last-child,
        .book-table td:last-child {
            border-right: 0;
        }

        .book-table td.money-in {
            color: #126c4c;
            font-weight: 700;
            background: #edf8f3;
        }

        .book-table td.money-out {
            color: #ad3547;
            font-weight: 700;
            background: #fff0f2;
        }

        .book-table td.balance {
            color: #5c4392;
            font-weight: 700;
            background: #f3f0fa;
        }

        .book-table tr.reversal td {
            color: #8c5057;
            text-decoration: line-through;
        }

        .a5 .book-table th {
            padding: 4px 2px;
            font-size: 5px;
        }

        .a5 .book-table td {
            padding: 4px 2px;
            font-size: 5.7px;
        }

        .number {
            text-align: right !important;
            white-space: nowrap;
        }

        .center {
            text-align: center !important;
        }

        .account-name {
            color: #176fa8;
            font-weight: 700;
        }

        .reference {
            color: #233f54;
            font-weight: 700;
        }

        .empty-row td {
            height: 60px;
            color: #6b8091;
            text-align: center;
        }

        .total-row td {
            padding-top: 7px;
            padding-bottom: 7px;
            color: #fff;
            font-weight: 700;
            border-color: #173d59;
            background: #173d59 !important;
        }

        .a5 .total-row td {
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .footer-table {
            margin-top: 14px;
            border-top: 1px solid #c8d5df;
        }

        .footer-table td {
            padding-top: 7px;
            color: #718596;
            font-size: 6.6px;
        }

        .footer-table td:last-child {
            text-align: right;
        }

        .a5 .footer-table {
            margin-top: 10px;
        }

        .a5 .footer-table td {
            padding-top: 5px;
            font-size: 5.5px;
        }

        .toolbar {
            position: fixed;
            z-index: 20;
            top: 12px;
            right: 14px;
            display: flex;
            gap: 6px;
        }

        .toolbar a,
        .toolbar button {
            display: inline-flex;
            height: 34px;
            align-items: center;
            justify-content: center;
            padding: 0 13px;
            color: #fff;
            font: 700 10.5px "Segoe UI", Arial, sans-serif;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            border-radius: 5px;
            background: #176fa8;
            box-shadow: 0 4px 12px rgba(20, 48, 70, .20);
        }

        .toolbar .back {
            color: #254156;
            border-color: #abbcc9;
            background: #fff;
        }

        .toolbar .pdf {
            background: #b9384a;
        }

        .toolbar .csv {
            background: #167856;
        }

        @media print {

            html,
            body {
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .preview {
                display: block !important;
                min-height: 0 !important;
                padding: 0 !important;
            }

            .pdf-mode .preview {
                padding: 3mm !important;
            }

            .paper {
                width: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }

            .toolbar {
                display: none !important;
            }
        }
    </style>
</head>

<body class="{{ $paperSize }} {{ $isPdf ? 'pdf-mode' : 'print-mode' }}">
    @if (!$isPdf)
    <div class="toolbar">
        <a
            class="back"
            href="{{ route('accounts.cash-bank-book.index', [
                    'account_id' => $filters['account_id'] ?? null,
                    'from_date' => $filters['from_date'] ?? null,
                    'to_date' => $filters['to_date'] ?? null,
                    'transaction_type' => $filters['transaction_type'] ?? null,
                    'search' => $filters['search'] ?? null,
                ]) }}">&larr;&nbsp; Back</a>
        <a
            class="pdf"
            href="{{ route('accounts.cash-bank-book.pdf', [
                    'account_id' => $filters['account_id'] ?? null,
                    'from_date' => $filters['from_date'] ?? null,
                    'to_date' => $filters['to_date'] ?? null,
                    'transaction_type' => $filters['transaction_type'] ?? null,
                    'search' => $filters['search'] ?? null,
                    'paper' => $paperSize,
                ]) }}">PDF</a>
        <a
            class="csv"
            href="{{ route('accounts.cash-bank-book.excel', [
                    'account_id' => $filters['account_id'] ?? null,
                    'from_date' => $filters['from_date'] ?? null,
                    'to_date' => $filters['to_date'] ?? null,
                    'transaction_type' => $filters['transaction_type'] ?? null,
                    'search' => $filters['search'] ?? null,
                ]) }}">CSV</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>
    @endif

    <div class="preview">
        <article class="paper">
            <table class="header-table">
                <tr>
                    <td style="width: 65%;">
                        <div class="company-name">{{ $companyName }}</div>
                        <div class="company-meta">
                            {{ $storeName }}@if ($address !== '') · {{ $address }}@endif
                        </div>
                    </td>
                    <td class="document-title" style="width: 35%;">
                        <strong>CASH &amp; BANK BOOK</strong>
                        <span>{{ strtoupper($paperSize) }} REPORT</span>
                    </td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td><span class="meta-label">Account</span><span class="meta-value">{{ $accountTitle }}</span></td>
                    <td><span class="meta-label">From Date</span><span class="meta-value">{{ $displayDate($filters['from_date'] ?? null) }}</span></td>
                    <td><span class="meta-label">To Date</span><span class="meta-value">{{ $displayDate($filters['to_date'] ?? null) }}</span></td>
                    <td><span class="meta-label">Transaction Type</span><span class="meta-value">{{ ($filters['transaction_type'] ?? '') !== '' ? str($filters['transaction_type'])->replace('_', ' ')->title() : 'All Types' }}</span></td>
                </tr>
            </table>

            <table class="summary-table">
                <tr>
                    <td>
                        <div class="summary-box"><span>Opening Balance</span><strong>{{ $money($summary['opening_balance'] ?? 0) }}</strong></div>
                    </td>
                    <td>
                        <div class="summary-box in"><span>Money In</span><strong>{{ $money($summary['money_in'] ?? 0) }}</strong></div>
                    </td>
                    <td>
                        <div class="summary-box out"><span>Money Out</span><strong>{{ $money($summary['money_out'] ?? 0) }}</strong></div>
                    </td>
                    <td>
                        <div class="summary-box closing"><span>Closing Balance</span><strong>{{ $money($summary['closing_balance'] ?? 0) }}</strong></div>
                    </td>
                </tr>
            </table>

            <div class="section-title">Account Transaction Register</div>

            <table class="book-table">
                <colgroup>
                    <col style="width: 4%;">
                    <col style="width: 9%;">
                    <col style="width: 14%;">
                    <col style="width: 14%;">
                    <col style="width: 12%;">
                    <col style="width: 17%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="center">#</th>
                        <th>Date</th>
                        <th>Account</th>
                        <th>Transaction</th>
                        <th>Reference</th>
                        <th>Particulars</th>
                        <th class="number money-in">Money In</th>
                        <th class="number money-out">Money Out</th>
                        <th class="number balance">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $index => $row)
                    <tr class="{{ $row['is_reversal'] ? 'reversal' : '' }}">
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $displayDate($row['date']) }}</td>
                        <td><span class="account-name">{{ $row['account_name'] ?: '-' }}</span><br>{{ $row['account_code'] ?: '-' }}</td>
                        <td>{{ $row['transaction_type_label'] }}</td>
                        <td><span class="reference">{{ $row['reference_no'] ?: '-' }}</span></td>
                        <td>{{ $row['remarks'] ?: ($row['payment_mode'] ?: '-') }}</td>
                        <td class="number money-in">{{ (float) $row['debit'] > 0 ? $money($row['debit']) : '-' }}</td>
                        <td class="number money-out">{{ (float) $row['credit'] > 0 ? $money($row['credit']) : '-' }}</td>
                        <td class="number balance">{{ $money($row['running_balance']) }}</td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="9">No transactions found for the selected account and period.</td>
                    </tr>
                    @endforelse

                    @if ($rows->isNotEmpty())
                    <tr class="total-row">
                        <td colspan="6" class="number">PERIOD TOTAL</td>
                        <td class="number">{{ $money($summary['money_in'] ?? 0) }}</td>
                        <td class="number">{{ $money($summary['money_out'] ?? 0) }}</td>
                        <td class="number">{{ $money($summary['closing_balance'] ?? 0) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <table class="footer-table">
                <tr>
                    <td>Generated: {{ $generatedAt?->format('d-m-Y h:i A') }}</td>
                    <td>{{ $storeName }} · Cash &amp; Bank Book</td>
                </tr>
            </table>
        </article>
    </div>
</body>

</html>