<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Mechanic Ledger</title>

    <style>
        @if ($paper ==='a5') @page {
            size: A5 portrait;
            margin: 7mm;
        }

        @else @page {
            size: A4 portrait;
            margin: 9mm;
        }

        @endif * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: #172033;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body.preview {
            min-height: 100vh;
            padding: 28px 12px;
            background: #cbd4df;
        }

        .paper {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 9mm;
            background: #ffffff;
        }

        body.a5 {
            font-size: 8.5px;
        }

        .paper.a5 {
            width: 148mm;
            min-height: 210mm;
            padding: 7mm;
        }

        body.preview .paper {
            box-shadow: 0 12px 32px rgba(15, 35, 60, .22);
        }

        body.pdf {
            background: #ffffff;
        }

        body.pdf .paper {
            width: auto;
            min-height: 0;
            margin: 0;
            box-shadow: none;
        }

        body.pdf.a4 .paper.a4 {
            padding: 8mm;
        }

        body.pdf.a5 .paper.a5 {
            padding: 6mm;
        }

        .screen-toolbar {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 0 auto 14px;
        }

        .screen-toolbar button {
            min-height: 34px;
            padding: 0 14px;
            border: 1px solid #1b4f86;
            border-radius: 4px;
            background: #1f65ad;
            color: #ffffff;
            font: 700 12px "Segoe UI", Arial, sans-serif;
            cursor: pointer;
        }

        .document-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .document-header td {
            padding: 11px;
            vertical-align: middle;
            background: #173b67;
            color: #ffffff;
        }

        .company-name {
            margin: 0 0 2px;
            font-size: 18px;
            font-weight: 800;
        }

        .branch-name {
            color: #dcecff;
            font-size: 9px;
        }

        .document-title {
            text-align: right;
        }

        .document-title strong {
            display: block;
            font-size: 16px;
            letter-spacing: .6px;
        }

        .document-title span {
            color: #dcecff;
            font-size: 9px;
        }

        .information-grid,
        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin: 0 -6px 7px;
        }

        .information-grid td,
        .summary-grid td {
            padding: 8px;
            border: 1px solid #c8d3df;
            background: #f7f9fc;
            vertical-align: top;
        }

        .information-grid .label,
        .summary-grid .label {
            display: block;
            margin-bottom: 2px;
            color: #617187;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .information-grid .value,
        .summary-grid .value {
            color: #173654;
            font-weight: 800;
        }

        .summary-grid td {
            border-top: 3px solid #2b6eb7;
            background: #ffffff;
        }

        .summary-grid td.debit {
            border-top-color: #bd4141;
        }

        .summary-grid td.credit {
            border-top-color: #20865a;
        }

        .summary-grid td.closing {
            border-top-color: #7149b2;
        }

        .summary-grid .value {
            font-size: 11px;
        }

        .section-title {
            margin: 9px 0 5px;
            padding: 6px 8px;
            border-left: 4px solid #276bb3;
            background: #eaf1f8;
            color: #173b67;
            font-weight: 800;
            text-transform: uppercase;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .ledger-table thead {
            display: table-header-group;
        }

        .ledger-table tr {
            page-break-inside: avoid;
        }

        .ledger-table th {
            padding: 6px 5px;
            border: 1px solid #aebdcd;
            background: #dfe9f4;
            color: #1c3d62;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
        }

        .ledger-table td {
            padding: 6px 5px;
            border: 1px solid #d4dce5;
            vertical-align: top;
            word-wrap: break-word;
        }

        .ledger-table tbody tr:nth-child(even) td {
            background: #f7f9fc;
        }

        .ledger-table .date {
            width: 12%;
        }

        .ledger-table .type {
            width: 17%;
        }

        .ledger-table .reference {
            width: 15%;
        }

        .ledger-table .remarks {
            width: 23%;
        }

        .ledger-table .amount {
            width: 11%;
            text-align: right;
            white-space: nowrap;
        }

        .ledger-table .debit-value {
            color: #a93434;
        }

        .ledger-table .credit-value {
            color: #177249;
        }

        .ledger-table .balance-value {
            color: #173e6e;
            font-weight: 800;
        }

        .opening-row td {
            background: #eef4fa !important;
            color: #234e7d;
            font-weight: 700;
        }

        .reversal-row td {
            background: #fff1f1 !important;
            color: #8f3838;
        }

        .sales-return-row td {
            background: #fff8e8 !important;
        }

        .transaction-label {
            display: inline-block;
            padding: 2px 5px;
            border: 1px solid #b7c8dc;
            border-radius: 8px;
            background: #edf4fc;
            color: #24578d;
            font-size: 7.5px;
            font-weight: 800;
            line-height: 1.3;
        }

        .transaction-label.commission {
            border-color: #9bc9ae;
            background: #edf9f1;
            color: #176b3f;
        }

        .transaction-label.payment {
            border-color: #adc4e0;
            background: #edf5ff;
            color: #1d5f9f;
        }

        .transaction-label.sales-return {
            border-color: #e5bd72;
            background: #fff6df;
            color: #8b5207;
        }

        .transaction-label.reversal,
        .reversal-row .transaction-label {
            border-color: #e3b4b4;
            background: #fff0f0;
            color: #a63b3b;
        }

        .transaction-label.adjustment {
            border-color: #c6afe5;
            background: #f6f0ff;
            color: #6c3ca4;
        }

        .empty-row td {
            padding: 24px 8px;
            text-align: center;
            color: #68778a;
        }

        .footer-note {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #bdc8d4;
            color: #6b7888;
            font-size: 8px;
            text-align: center;
        }

        .paper.a5 .document-header {
            margin-bottom: 7px;
        }

        .paper.a5 .document-header td {
            padding: 8px;
        }

        .paper.a5 .company-name {
            font-size: 15px;
        }

        .paper.a5 .branch-name,
        .paper.a5 .document-title span {
            font-size: 8px;
        }

        .paper.a5 .document-title strong {
            font-size: 13px;
        }

        .paper.a5 .information-grid,
        .paper.a5 .summary-grid {
            border-spacing: 4px;
            margin: 0 -4px 5px;
        }

        .paper.a5 .information-grid td,
        .paper.a5 .summary-grid td {
            padding: 6px;
        }

        .paper.a5 .information-grid .label,
        .paper.a5 .summary-grid .label {
            font-size: 7px;
        }

        .paper.a5 .summary-grid .value {
            font-size: 9px;
        }

        .paper.a5 .section-title {
            margin: 6px 0 5px;
            padding: 5px 7px;
        }

        .paper.a5 .ledger-table th,
        .paper.a5 .ledger-table td {
            padding: 5px 4px;
        }

        .paper.a5 .ledger-table th {
            font-size: 7px;
        }

        .paper.a5 .footer-note {
            margin-top: 8px;
            padding-top: 6px;
            font-size: 7px;
        }

        @media print {

            html,
            body,
            body.preview {
                width: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
            }

            .screen-toolbar {
                display: none !important;
            }

            body.preview .paper {
                width: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body class="{{ ($pdfMode ?? false) ? 'pdf' : 'preview' }} {{ $paper }}">
    @unless ($pdfMode ?? false)
    <div class="screen-toolbar">
        <button type="button" onclick="window.print()">
            Print {{ strtoupper($paper) }}
        </button>
    </div>
    @endunless

    <main class="paper {{ $paper }}">
        <table class="document-header">
            <tr>
                <td>
                    <div class="company-name">
                        {{ $company_name }}
                    </div>
                    <div class="branch-name">
                        {{ $store_name }}
                    </div>
                </td>
                <td class="document-title">
                    <strong>MECHANIC LEDGER</strong>
                    <span>
                        {{ strtoupper($paper) }} ·
                        {{ now()->format('d-m-Y h:i A') }}
                    </span>
                </td>
            </tr>
        </table>

        <table class="information-grid">
            <tr>
                <td width="34%">
                    <span class="label">Mechanic</span>
                    <span class="value">
                        {{ $mechanic->mechanic_name }}
                    </span>
                </td>
                <td width="20%">
                    <span class="label">Mechanic Code</span>
                    <span class="value">
                        {{ $mechanic->mechanic_code ?: '-' }}
                    </span>
                </td>
                <td width="20%">
                    <span class="label">Mobile</span>
                    <span class="value">
                        {{ $mechanic->mobile ?: '-' }}
                    </span>
                </td>
                <td width="26%">
                    <span class="label">Ledger Period</span>
                    <span class="value">
                        {{ $from_date_text }}
                        to
                        {{ $to_date_text }}
                    </span>
                </td>
            </tr>
        </table>

        <table class="summary-grid">
            <tr>
                <td width="25%">
                    <span class="label">Opening Balance</span>
                    <span class="value">
                        {{ $opening_balance_label }}
                    </span>
                </td>
                <td class="debit" width="25%">
                    <span class="label">Period Debit</span>
                    <span class="value">
                        ₹{{ number_format((float) ($summary['period_debit'] ?? 0), 2) }}
                    </span>
                </td>
                <td class="credit" width="25%">
                    <span class="label">Period Credit</span>
                    <span class="value">
                        ₹{{ number_format((float) ($summary['period_credit'] ?? 0), 2) }}
                    </span>
                </td>
                <td class="closing" width="25%">
                    <span class="label">Closing Payable</span>
                    <span class="value">
                        {{ $closing_balance_label }}
                    </span>
                </td>
            </tr>
        </table>

        <div class="section-title">
            Transaction Register
        </div>

        <table class="ledger-table">
            <thead>
                <tr>
                    <th class="date">Date</th>
                    <th class="type">Transaction</th>
                    <th class="reference">Reference</th>
                    <th class="remarks">Remarks</th>
                    <th class="amount">Debit</th>
                    <th class="amount">Credit</th>
                    <th class="amount">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="opening-row">
                    <td>
                        {{ $from_date_text }}
                    </td>
                    <td>Opening Balance</td>
                    <td>-</td>
                    <td>Balance brought forward</td>
                    <td class="amount">-</td>
                    <td class="amount">-</td>
                    <td class="amount">
                        {{ $opening_balance_label }}
                    </td>
                </tr>

                @forelse ($entries as $entry)
                @php
                $transactionType = (string) ($entry['transaction_type'] ?? '');
                $isReturn = in_array($transactionType, [
                'sales_return_commission',
                'sales_return_commission_reversal',
                ], true);

                $transactionTone = match (true) {
                $transactionType === 'commission' => 'commission',
                $transactionType === 'mechanic_payment' => 'payment',
                $transactionType === 'sales_return_commission' => 'sales-return',
                str_contains($transactionType, 'reversal') => 'reversal',
                str_contains($transactionType, 'adjustment') => 'adjustment',
                default => 'default',
                };

                $rowClasses = collect([
                !empty($entry['is_reversal']) ? 'reversal-row' : null,
                $isReturn && empty($entry['is_reversal']) ? 'sales-return-row' : null,
                ])->filter()->implode(' ');
                @endphp

                <tr class="{{ $rowClasses }}">
                    <td>
                        {{ !empty($entry['transaction_date'])
                                ? date(
                                    'd-m-Y',
                                    strtotime((string) $entry['transaction_date'])
                                )
                                : '-' }}
                    </td>
                    <td>
                        <span class="transaction-label {{ $transactionTone }}">
                            {{ $entry['type_label'] ?? '-' }}
                        </span>
                    </td>
                    <td>
                        {{ $entry['reference_no']
                                ?? (!empty($entry['reference_id'])
                                    ? '#' . $entry['reference_id']
                                    : '-') }}
                    </td>
                    <td>
                        {{ $entry['remarks'] ?: '-' }}
                    </td>
                    <td class="amount debit-value">
                        {{ (float) ($entry['debit'] ?? 0) > 0
                                ? '₹' . number_format((float) $entry['debit'], 2)
                                : '-' }}
                    </td>
                    <td class="amount credit-value">
                        {{ (float) ($entry['credit'] ?? 0) > 0
                                ? '₹' . number_format((float) $entry['credit'], 2)
                                : '-' }}
                    </td>
                    <td class="amount balance-value">
                        {{ $entry['balance_label']
                                ?? (
                                    '₹'
                                    . number_format(
                                        abs((float) ($entry['running_balance'] ?? 0)),
                                        2
                                    )
                                ) }}
                    </td>
                </tr>
                @empty
                <tr class="empty-row">
                    <td colspan="7">
                        No ledger entries found for the selected period.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer-note">
            Computer-generated mechanic ledger ·
            {{ $company_name }} · {{ $store_name }}
        </div>
    </main>
</body>

</html>