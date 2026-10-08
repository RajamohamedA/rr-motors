@php
$isPdf = (bool) ($pdfMode ?? false);
$paperSize = strtolower((string) ($paper ?? 'a4')) === 'a5' ? 'a5' : 'a4';
$isA5 = $paperSize === 'a5';
$type = ($filters['type'] ?? 'customer') === 'supplier' ? 'supplier' : 'customer';
$isSupplier = $type === 'supplier';
$partyLabel = $isSupplier ? 'Supplier' : 'Customer';
$balanceLabel = $isSupplier ? 'Payable' : 'Receivable';
$balanceType = $isSupplier ? 'Cr' : 'Dr';
$storeName = $store?->store_name ?? $store?->name ?? 'Branch';
$company = $store?->company;
$companyName = $company?->company_name ?? $company?->name ?? config('app.name');
$address = implode(', ', array_filter([
$store?->address,
$store?->city,
$store?->state,
$store?->pincode,
]));
$formatMoney = static fn ($value): string => 'Rs. ' . number_format((float) $value, 2);
$formatDate = static function ($value): string {
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
    <title>{{ $partyLabel }} Outstanding Report</title>
    <style>
        @if ($isA5) @page {
            size: A5 portrait;
            margin: 10mm 9mm;
        }

        @else @page {
            size: A4 portrait;
            margin: 12mm 11mm;
        }

        @endif * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: #183247;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            line-height: 1.35;
            background: #cfd8e2;
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

        .pdf-mode .preview {
            display: block;
            min-height: 0;
            padding: 6mm;
        }

        .pdf-mode.a5 .preview {
            padding: 5mm;
        }

        .paper {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm 9mm;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 12px 34px rgba(22, 45, 65, .24);
        }

        .a5 .paper {
            width: 148mm;
            min-height: 210mm;
            padding: 8mm 7mm;
        }

        .pdf-mode .paper {
            width: auto;
            min-height: 0;
            margin: 0;
            padding: 0;
            box-shadow: none;
        }

        .pdf-mode.a5 .paper {
            padding: 0;
        }

        .report-header,
        .info-table,
        .summary-table,
        .data-table,
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .report-header {
            color: #fff;
            background: #173c59;
        }

        .supplier .report-header {
            background: #70401f;
        }

        .report-header td {
            padding: 11px 12px;
            vertical-align: middle;
        }

        .a5 .report-header td {
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
            color: #dbeaf4;
            font-size: 7.8px;
        }

        .a5 .company-meta {
            font-size: 6.8px;
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
            font-size: 11px;
        }

        .document-title span {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 6px;
            color: #173c59;
            font-size: 7.5px;
            font-weight: 700;
            border-radius: 8px;
            background: #fff;
        }

        .supplier .document-title span {
            color: #70401f;
        }

        .a5 .document-title span {
            font-size: 6.5px;
        }

        .info-table {
            margin-top: 9px;
            border: 1px solid #c9d6e1;
            background: #f6f9fc;
        }

        .a5 .info-table {
            margin-top: 7px;
        }

        .info-table td {
            width: 25%;
            padding: 7px 8px;
            border-right: 1px solid #d5e0e9;
            vertical-align: top;
        }

        .a5 .info-table td {
            padding: 5px 6px;
        }

        .info-table td:last-child {
            border-right: 0;
        }

        .meta-label {
            display: block;
            margin-bottom: 2px;
            color: #698093;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .25px;
        }

        .a5 .meta-label {
            font-size: 6px;
        }

        .meta-value {
            display: block;
            color: #1c394f;
            font-size: 8.4px;
            font-weight: 700;
        }

        .a5 .meta-value {
            font-size: 7.2px;
        }

        .summary-table {
            margin-top: 9px;
        }

        .a5 .summary-table {
            margin-top: 7px;
        }

        .summary-table td {
            width: 33.333%;
            padding-right: 6px;
            vertical-align: top;
        }

        .a5 .summary-table td {
            padding-right: 4px;
        }

        .summary-table td:last-child {
            padding-right: 0;
        }

        .summary-box {
            min-height: 45px;
            padding: 8px 9px;
            border: 1px solid #c7d5e0;
            border-left: 3px solid #176ba5;
            background: #eaf5fc;
        }

        .supplier .summary-box {
            border-left-color: #c55e18;
            background: #fff1e5;
        }

        .a5 .summary-box {
            min-height: 38px;
            padding: 6px 7px;
        }

        .summary-box span {
            display: block;
            color: #5c7488;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .a5 .summary-box span {
            font-size: 6px;
        }

        .summary-box strong {
            display: block;
            margin-top: 3px;
            color: #173c59;
            font-size: 12px;
        }

        .supplier .summary-box strong {
            color: #70401f;
        }

        .a5 .summary-box strong {
            font-size: 10px;
        }

        .section-title {
            margin-top: 11px;
            padding: 6px 8px;
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
            background: #176ba5;
        }

        .supplier .section-title {
            background: #c55e18;
        }

        .a5 .section-title {
            margin-top: 8px;
            padding: 5px 7px;
            font-size: 7px;
        }

        .data-table {
            table-layout: fixed;
            border: 1px solid #bacad7;
            border-top: 0;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .data-table th {
            padding: 5px 4px;
            color: #e9f2f7;
            font-size: 6.6px;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            border-right: 1px solid rgba(255, 255, 255, .16);
            background: #173c59;
        }

        .supplier .data-table th {
            background: #70401f;
        }

        .data-table td {
            padding: 5px 4px;
            color: #29465a;
            font-size: 7.3px;
            vertical-align: middle;
            border-right: 1px solid #d6e0e8;
            border-bottom: 1px solid #d6e0e8;
            word-wrap: break-word;
        }

        .a5 .data-table th {
            padding: 4px 3px;
            font-size: 5.6px;
        }

        .a5 .data-table td {
            padding: 4px 3px;
            font-size: 6.2px;
        }

        .data-table tbody tr:nth-child(even) td {
            background: #f5f8fb;
        }

        .data-table th:last-child,
        .data-table td:last-child {
            border-right: 0;
        }

        .data-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .number {
            text-align: right !important;
            white-space: nowrap;
        }

        .center {
            text-align: center !important;
        }

        .party-name {
            color: #17364d;
            font-weight: 700;
        }

        .amount {
            color: #176ba5 !important;
            font-weight: 700;
        }

        .supplier .amount {
            color: #c55e18 !important;
        }

        .balance-pill {
            display: inline-block;
            min-width: 22px;
            padding: 1px 4px;
            color: #173c59;
            font-size: 6.4px;
            font-weight: 700;
            text-align: center;
            border: 1px solid #176ba5;
            border-radius: 7px;
            background: #eaf5fc;
        }

        .supplier .balance-pill {
            color: #70401f;
            border-color: #c55e18;
            background: #fff1e5;
        }

        .a5 .balance-pill {
            font-size: 5.5px;
        }

        .total-row td {
            padding-top: 7px;
            padding-bottom: 7px;
            color: #fff;
            font-weight: 700;
            border-color: #173c59;
            background: #173c59 !important;
        }

        .supplier .total-row td {
            border-color: #70401f;
            background: #70401f !important;
        }

        .a5 .total-row td {
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .empty-row td {
            height: 60px;
            color: #6b8192;
            text-align: center;
        }

        .footer-table {
            margin-top: 14px;
            border-top: 1px solid #c7d4df;
        }

        .a5 .footer-table {
            margin-top: 10px;
        }

        .footer-table td {
            padding-top: 7px;
            color: #718596;
            font-size: 6.8px;
            vertical-align: top;
        }

        .a5 .footer-table td {
            padding-top: 5px;
            font-size: 5.8px;
        }

        .footer-table td:last-child {
            text-align: right;
        }

        .no-print {
            position: fixed;
            z-index: 20;
            top: 12px;
            right: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .no-print button,
        .no-print a {
            display: inline-flex;
            height: 34px;
            align-items: center;
            justify-content: center;
            padding: 0 13px;
            color: #fff;
            font: 700 11px "Segoe UI", Arial, sans-serif;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            border-radius: 5px;
            background: #176ba5;
            box-shadow: 0 4px 12px rgba(20, 48, 70, .22);
        }

        .no-print .back-button {
            color: #203b51;
            border-color: #aebdca;
            background: #fff;
        }

        .no-print .pdf-button {
            border-color: #d85b6b;
            background: #bd3548;
        }

        .no-print .csv-button {
            border-color: #3e9a78;
            background: #167653;
        }

        .no-print button:hover,
        .no-print a:hover {
            filter: brightness(1.06);
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
                padding: 6mm !important;
            }

            .pdf-mode.a5 .preview {
                padding: 5mm !important;
            }

            .paper {
                width: 100% !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }

            .pdf-mode .paper {
                width: auto !important;
                padding: 0 !important;
            }

            .pdf-mode.a5 .paper {
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body class="{{ $paperSize }} {{ $type }} {{ $isPdf ? 'pdf-mode' : 'print-mode' }}">
    @if (!$isPdf)
    <div class="no-print">
        <a
            href="{{ route('accounts.outstanding.index', [
                    'type' => $type,
                    'search' => $filters['search'] ?? null,
                    'as_of_date' => $filters['as_of_date'] ?? null,
                    'minimum_balance' => $filters['minimum_balance'] ?? 0,
                ]) }}"
            class="back-button">
            &larr;&nbsp; Back
        </a>
        <a
            href="{{ route('accounts.outstanding.pdf', [
                    'type' => $type,
                    'search' => $filters['search'] ?? null,
                    'as_of_date' => $filters['as_of_date'] ?? null,
                    'minimum_balance' => $filters['minimum_balance'] ?? 0,
                    'paper' => $paperSize,
                ]) }}"
            class="pdf-button">
            PDF
        </a>
        <a
            href="{{ route('accounts.outstanding.excel', [
                    'type' => $type,
                    'search' => $filters['search'] ?? null,
                    'as_of_date' => $filters['as_of_date'] ?? null,
                    'minimum_balance' => $filters['minimum_balance'] ?? 0,
                ]) }}"
            class="csv-button">
            CSV
        </a>
        <button type="button" onclick="window.print()">Print Report</button>
    </div>
    @endif

    <div class="preview">
        <article class="paper">
            <table class="report-header">
                <tr>
                    <td style="width: 66%;">
                        <div class="company-name">{{ $companyName }}</div>
                        <div class="company-meta">
                            {{ $storeName }}@if ($address !== '') · {{ $address }}@endif
                        </div>
                    </td>
                    <td class="document-title" style="width: 34%;">
                        <strong>{{ strtoupper($partyLabel) }} OUTSTANDING</strong>
                        <span>{{ strtoupper($paperSize) }} REPORT</span>
                    </td>
                </tr>
            </table>

            <table class="info-table">
                <tr>
                    <td>
                        <span class="meta-label">Report Type</span>
                        <span class="meta-value">{{ $partyLabel }} {{ $balanceLabel }}</span>
                    </td>
                    <td>
                        <span class="meta-label">As Of Date</span>
                        <span class="meta-value">{{ $formatDate($filters['as_of_date'] ?? null) }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Minimum Balance</span>
                        <span class="meta-value">{{ $formatMoney($filters['minimum_balance'] ?? 0) }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Search Filter</span>
                        <span class="meta-value">{{ ($filters['search'] ?? '') !== '' ? $filters['search'] : 'All parties' }}</span>
                    </td>
                </tr>
            </table>

            <table class="summary-table">
                <tr>
                    <td>
                        <div class="summary-box"><span>Outstanding Parties</span><strong>{{ number_format((int) ($summary['count'] ?? 0)) }}</strong></div>
                    </td>
                    <td>
                        <div class="summary-box"><span>Total {{ $balanceLabel }}</span><strong>{{ $formatMoney($summary['total'] ?? 0) }}</strong></div>
                    </td>
                    <td>
                        <div class="summary-box"><span>Highest Balance</span><strong>{{ $formatMoney($summary['highest'] ?? 0) }}</strong></div>
                    </td>
                </tr>
            </table>

            <div class="section-title">{{ $partyLabel }} Outstanding Register</div>

            <table class="data-table">
                <colgroup>
                    <col style="width: 5%;">
                    <col style="width: 13%;">
                    <col style="width: 24%;">
                    <col style="width: 15%;">
                    <col style="width: 13%;">
                    <col style="width: 9%;">
                    <col style="width: 16%;">
                    <col style="width: 5%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="center">#</th>
                        <th>Code</th>
                        <th>{{ $partyLabel }} Name</th>
                        <th>Contact</th>
                        <th>City</th>
                        <th class="number">Days</th>
                        <th class="number">Outstanding</th>
                        <th class="center">Bal.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $index => $row)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $row['code'] ?: '-' }}</td>
                        <td>
                            <span class="party-name">{{ $row['name'] }}</span>
                            @if (!empty($row['contact_person']))<br>{{ $row['contact_person'] }}@endif
                        </td>
                        <td>{{ $row['mobile'] ?: '-' }}</td>
                        <td>{{ $row['city'] ?: '-' }}</td>
                        <td class="number">{{ number_format((int) $row['credit_days']) }}</td>
                        <td class="number amount">{{ $formatMoney($row['outstanding_balance']) }}</td>
                        <td class="center"><span class="balance-pill">{{ $balanceType }}</span></td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="8">No outstanding balances found for the selected filters.</td>
                    </tr>
                    @endforelse

                    @if ($rows->isNotEmpty())
                    <tr class="total-row">
                        <td colspan="6" class="number">TOTAL {{ strtoupper($balanceLabel) }}</td>
                        <td class="number">{{ $formatMoney($summary['total'] ?? 0) }}</td>
                        <td class="center">{{ $balanceType }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <table class="footer-table">
                <tr>
                    <td>Generated: {{ $generatedAt?->format('d-m-Y h:i A') }}</td>
                    <td>{{ $storeName }} · {{ $partyLabel }} Outstanding Report</td>
                </tr>
            </table>
        </article>
    </div>
</body>

</html>