@php
$isPdf = (bool) ($pdfMode ?? false);
$paperSize = strtolower((string) ($paper ?? 'a4')) === 'a5' ? 'a5' : 'a4';
$isA5 = $paperSize === 'a5';
$mode = ($filters['mode'] ?? 'daily') === 'monthly' ? 'monthly' : 'daily';
$storeName = $store?->store_name ?? $store?->name ?? 'Branch';
$company = $store?->company;
$companyName = $company?->company_name ?? $company?->name ?? config('app.name');
$address = implode(', ', array_filter([$store?->address, $store?->city, $store?->state, $store?->pincode]));
$money = static fn ($value): string => 'Rs. ' . number_format((float) $value, 2);
$displayDate = static function ($value): string {
if (!$value) return '-';
try {
return \Illuminate\Support\Carbon::parse($value)->format('d-m-Y');
} catch (\Throwable) {
return (string) $value;
}
};
$finalProfit = (float) ($summary['final_profit'] ?? 0);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Day Book</title>
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
            color: #1c3549;
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
        .profit-table,
        .day-table,
        .expense-table,
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
            font-size: 7.5px;
        }

        .a5 .company-meta {
            font-size: 6.5px;
        }

        .document-title {
            text-align: right;
        }

        .document-title strong {
            display: block;
            font-size: 13px;
            letter-spacing: .5px;
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
            border: 1px solid #c8d5df;
            background: #f5f9fc;
        }

        .a5 .meta-table {
            margin-top: 7px;
        }

        .meta-table td {
            width: 25%;
            padding: 7px 8px;
            border-right: 1px solid #d5e0e8;
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
            background: #176fa8;
        }

        .summary-box.purchase {
            background: #bf5c1a;
        }

        .summary-box.expense {
            background: #b73a4c;
        }

        .summary-box.commission {
            background: #67499e;
        }

        .summary-box span {
            display: block;
            color: rgba(255, 255, 255, .80);
            font-size: 6.5px;
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
            font-size: 8.5px;
        }

        .profit-table {
            margin-top: 7px;
        }

        .profit-table td {
            width: 33.333%;
            padding-right: 5px;
            vertical-align: top;
        }

        .profit-table td:last-child {
            padding-right: 0;
        }

        .profit-box {
            min-height: 43px;
            padding: 6px 8px;
            border: 1px solid #a8cfbf;
            border-left: 3px solid #167957;
            background: #eaf7f1;
        }

        .profit-box.middle {
            border-color: #dfbd96;
            border-left-color: #c96b23;
            background: #fff5e9;
        }

        .profit-box.final {
            color: #fff;
            border-color: #167957;
            background: #167957;
        }

        .loss .profit-box.final {
            border-color: #b83b4d;
            background: #b83b4d;
        }

        .profit-box span {
            display: block;
            color: #647c8c;
            font-size: 6.4px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .profit-box strong {
            display: block;
            margin-top: 2px;
            color: #167957;
            font-size: 10px;
        }

        .profit-box.middle strong {
            color: #b75a19;
        }

        .profit-box.final span {
            color: rgba(255, 255, 255, .80);
        }

        .profit-box.final strong {
            color: #fff;
        }

        .a5 .profit-box {
            min-height: 36px;
            padding: 5px 6px;
        }

        .a5 .profit-box span {
            font-size: 5.2px;
        }

        .a5 .profit-box strong {
            font-size: 8px;
        }

        .section-title {
            margin-top: 10px;
            padding: 6px 8px;
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            background: #176fa8;
        }

        .section-title.expenses {
            background: #b83b4d;
        }

        .a5 .section-title {
            margin-top: 8px;
            padding: 5px 6px;
            font-size: 6.5px;
        }

        .day-table,
        .expense-table {
            table-layout: fixed;
            border: 1px solid #bbcbd7;
            border-top: 0;
        }

        .day-table thead,
        .expense-table thead {
            display: table-header-group;
        }

        .day-table tr,
        .expense-table tr {
            page-break-inside: avoid;
        }

        .day-table th,
        .expense-table th {
            padding: 5px 3px;
            color: #e7f0f6;
            font-size: 5.9px;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            border-right: 1px solid rgba(255, 255, 255, .14);
            background: #173d59;
        }

        .day-table th.sales {
            background: #176a9d;
        }

        .day-table th.purchase {
            background: #b55818;
        }

        .day-table th.expense,
        .expense-table th.amount {
            background: #a63345;
        }

        .day-table th.commission {
            background: #5d4389;
        }

        .day-table th.profit {
            background: #136e50;
        }

        .day-table td,
        .expense-table td {
            padding: 5px 3px;
            color: #2c4659;
            font-size: 6.8px;
            vertical-align: middle;
            word-wrap: break-word;
            border-right: 1px solid #d6e0e8;
            border-bottom: 1px solid #d6e0e8;
        }

        .day-table tbody tr:nth-child(even) td,
        .expense-table tbody tr:nth-child(even) td {
            background: #f5f8fb;
        }

        .day-table th:last-child,
        .day-table td:last-child,
        .expense-table th:last-child,
        .expense-table td:last-child {
            border-right: 0;
        }

        .day-table td.sales {
            color: #176fa8;
            font-weight: 700;
        }

        .day-table td.purchase {
            color: #b45818;
            font-weight: 700;
        }

        .day-table td.expense,
        .expense-table td.amount {
            color: #ac3446;
            font-weight: 700;
            background: #fff0f2;
        }

        .day-table td.commission {
            color: #654793;
            font-weight: 700;
        }

        .day-table td.profit {
            color: #167555;
            font-weight: 700;
            background: #edf8f3;
        }

        .day-table td.loss {
            color: #b83b4d;
            background: #fff0f2;
        }

        .number {
            text-align: right !important;
            white-space: nowrap;
        }

        .center {
            text-align: center !important;
        }

        .a5 .day-table th,
        .a5 .expense-table th {
            padding: 4px 2px;
            font-size: 4.7px;
        }

        .a5 .day-table td,
        .a5 .expense-table td {
            padding: 4px 2px;
            font-size: 5.5px;
        }

        .total-row td {
            padding-top: 7px;
            padding-bottom: 7px;
            color: #fff !important;
            font-weight: 700;
            border-color: #173d59;
            background: #173d59 !important;
        }

        .a5 .total-row td {
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .empty-row td {
            height: 55px;
            color: #6b8192;
            text-align: center;
        }

        .footer-table {
            margin-top: 13px;
            border-top: 1px solid #c8d5df;
        }

        .footer-table td {
            padding-top: 7px;
            color: #718596;
            font-size: 6.5px;
        }

        .footer-table td:last-child {
            text-align: right;
        }

        .a5 .footer-table {
            margin-top: 9px;
        }

        .a5 .footer-table td {
            padding-top: 5px;
            font-size: 5.4px;
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

<body class="{{ $paperSize }} {{ $mode }} {{ $finalProfit < 0 ? 'loss' : 'profit' }} {{ $isPdf ? 'pdf-mode' : 'print-mode' }}">
    @if (!$isPdf)
    <div class="toolbar">
        <a class="back" href="{{ route('accounts.day-book.index', ['mode' => $mode, 'date' => $filters['date'] ?? null, 'month' => $filters['month'] ?? null]) }}">&larr;&nbsp; Back</a>
        <a class="pdf" href="{{ route('accounts.day-book.pdf', ['mode' => $mode, 'date' => $filters['date'] ?? null, 'month' => $filters['month'] ?? null, 'paper' => $paperSize]) }}">PDF</a>
        <a class="csv" href="{{ route('accounts.day-book.excel', ['mode' => $mode, 'date' => $filters['date'] ?? null, 'month' => $filters['month'] ?? null]) }}">CSV</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>
    @endif

    <div class="preview">
        <article class="paper">
            <table class="header-table">
                <tr>
                    <td style="width: 65%;">
                        <div class="company-name">{{ $companyName }}</div>
                        <div class="company-meta">{{ $storeName }}@if ($address !== '') · {{ $address }}@endif</div>
                    </td>
                    <td class="document-title" style="width: 35%;">
                        <strong>{{ $mode === 'monthly' ? 'MONTHLY DAY BOOK' : 'DAILY DAY BOOK' }}</strong>
                        <span>{{ strtoupper($paperSize) }} REPORT</span>
                    </td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td><span class="meta-label">Report Mode</span><span class="meta-value">{{ ucfirst($mode) }}</span></td>
                    <td><span class="meta-label">From Date</span><span class="meta-value">{{ $displayDate($filters['from_date'] ?? null) }}</span></td>
                    <td><span class="meta-label">To Date</span><span class="meta-value">{{ $displayDate($filters['to_date'] ?? null) }}</span></td>
                    <td><span class="meta-label">Business Days</span><span class="meta-value">{{ number_format((int) ($summary['days_count'] ?? 0)) }}</span></td>
                </tr>
            </table>

            <table class="summary-table">
                <tr>
                    <td>
                        <div class="summary-box"><span>Sales Amount</span><strong>{{ $money($summary['sales_amount'] ?? 0) }}</strong></div>
                    </td>
                    <td>
                        <div class="summary-box purchase"><span>Purchase Amount</span><strong>{{ $money($summary['purchase_amount'] ?? 0) }}</strong></div>
                    </td>
                    <td>
                        <div class="summary-box expense"><span>Total Expenses</span><strong>{{ $money($summary['expense_amount'] ?? 0) }}</strong></div>
                    </td>
                    <td>
                        <div class="summary-box commission"><span>Mechanic Commission</span><strong>{{ $money($summary['mechanic_commission'] ?? 0) }}</strong></div>
                    </td>
                </tr>
            </table>

            <table class="profit-table">
                <tr>
                    <td>
                        <div class="profit-box"><span>Gross Profit</span><strong>{{ $money($summary['gross_profit'] ?? 0) }}</strong></div>
                    </td>
                    <td>
                        <div class="profit-box middle"><span>Before Expenses</span><strong>{{ $money($summary['net_profit_before_expenses'] ?? 0) }}</strong></div>
                    </td>
                    <td>
                        <div class="profit-box final"><span>{{ $finalProfit >= 0 ? 'Final Profit' : 'Final Loss' }}</span><strong>{{ $money($finalProfit) }}</strong></div>
                    </td>
                </tr>
            </table>

            <div class="section-title">Day-wise Business Register</div>
            <table class="day-table">
                <colgroup>
                    <col style="width:9%;">
                    <col style="width:12%;">
                    <col style="width:12%;">
                    <col style="width:11%;">
                    <col style="width:10%;">
                    <col style="width:11%;">
                    <col style="width:12%;">
                    <col style="width:11%;">
                    <col style="width:12%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="number sales">Sales</th>
                        <th class="number purchase">Purchases</th>
                        <th class="number expense">Expenses</th>
                        <th class="number commission">Commission</th>
                        <th class="number profit">Gross Profit</th>
                        <th class="number profit">Final Profit</th>
                        <th class="number">Money In</th>
                        <th class="number">Money Out</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                    <tr>
                        <td>{{ $displayDate($row['date']) }}<br>{{ $row['day_label'] }}</td>
                        <td class="number sales">{{ $money($row['sales_amount']) }}</td>
                        <td class="number purchase">{{ $money($row['purchase_amount']) }}</td>
                        <td class="number expense">{{ $money($row['expense_amount']) }}</td>
                        <td class="number commission">{{ $money($row['mechanic_commission']) }}</td>
                        <td class="number profit">{{ $money($row['gross_profit']) }}</td>
                        <td class="number profit {{ (float) $row['final_profit'] < 0 ? 'loss' : '' }}">{{ $money($row['final_profit']) }}</td>
                        <td class="number">{{ $money($row['money_in']) }}</td>
                        <td class="number">{{ $money($row['money_out']) }}</td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="9">No business activity found for the selected period.</td>
                    </tr>
                    @endforelse
                    @if ($rows->isNotEmpty())
                    <tr class="total-row">
                        <td>PERIOD TOTAL</td>
                        <td class="number">{{ $money($summary['sales_amount'] ?? 0) }}</td>
                        <td class="number">{{ $money($summary['purchase_amount'] ?? 0) }}</td>
                        <td class="number">{{ $money($summary['expense_amount'] ?? 0) }}</td>
                        <td class="number">{{ $money($summary['mechanic_commission'] ?? 0) }}</td>
                        <td class="number">{{ $money($summary['gross_profit'] ?? 0) }}</td>
                        <td class="number">{{ $money($finalProfit) }}</td>
                        <td class="number">{{ $money($summary['money_in'] ?? 0) }}</td>
                        <td class="number">{{ $money($summary['money_out'] ?? 0) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            @if ($expenses->isNotEmpty())
            <div class="section-title expenses">Expense Details</div>
            <table class="expense-table">
                <colgroup>
                    <col style="width:5%;">
                    <col style="width:11%;">
                    <col style="width:13%;">
                    <col style="width:17%;">
                    <col style="width:16%;">
                    <col style="width:20%;">
                    <col style="width:18%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="center">#</th>
                        <th>Date</th>
                        <th>No.</th>
                        <th>Category</th>
                        <th>Payee / Account</th>
                        <th>Remarks</th>
                        <th class="number amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenses as $index => $expense)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $displayDate($expense['date']) }}</td>
                        <td>{{ $expense['expense_no'] }}</td>
                        <td>{{ $expense['category'] }}</td>
                        <td>{{ $expense['payee_name'] ?: ($expense['account_name'] ?: '-') }}</td>
                        <td>{{ $expense['remarks'] ?: '-' }}</td>
                        <td class="number amount">{{ $money($expense['amount']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <table class="footer-table">
                <tr>
                    <td>Generated: {{ $generatedAt?->format('d-m-Y h:i A') }}</td>
                    <td>{{ $storeName }} · Day Book</td>
                </tr>
            </table>
        </article>
    </div>
</body>

</html>