<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Ledger - {{ $supplier->supplier_name }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 22px 12px;
            background: #dce3ea;
            color: #182230;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        .toolbar {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto 12px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .toolbar button {
            min-height: 34px;
            padding: 7px 16px;
            border: 1px solid #174ea6;
            border-radius: 4px;
            background: #1f5fbf;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .toolbar .close-button {
            border-color: #64748b;
            background: #fff;
            color: #334155;
        }

        .ledger-sheet {
            width: 100%;
            max-width: 1120px;
            min-height: 760px;
            margin: 0 auto;
            padding: 18px;
            border: 1px solid #aebccc;
            background: #fff;
            box-shadow: 0 7px 24px rgba(30, 41, 59, 0.14);
        }

        .document-header {
            overflow: hidden;
            margin-bottom: 12px;
            border: 1px solid #163f75;
            border-radius: 5px;
        }

        .header-main {
            padding: 13px 16px;
            background: #174f91;
            color: #fff;
        }

        .company-name {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.25px;
        }

        .branch-name {
            margin-top: 3px;
            font-size: 11px;
            color: #dbeafe;
        }

        .report-title {
            float: right;
            margin-top: -35px;
            text-align: right;
        }

        .report-title strong {
            display: block;
            font-size: 17px;
            letter-spacing: 0.6px;
        }

        .report-title span {
            font-size: 10px;
            color: #dbeafe;
        }

        .supplier-panel {
            display: table;
            width: 100%;
            padding: 9px 14px;
            background: #eef5ff;
        }

        .supplier-column,
        .period-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .period-column {
            text-align: right;
        }

        .label {
            color: #53657a;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .value {
            margin-top: 2px;
            color: #102a43;
            font-size: 12px;
            font-weight: 700;
        }

        .sub-value {
            margin-top: 2px;
            color: #475569;
            font-size: 10px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            table-layout: fixed;
            border-spacing: 6px 0;
        }

        .summary-card {
            display: table-cell;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-top: 3px solid #2874c6;
            border-radius: 4px;
            background: #f8fafc;
        }

        .summary-card.debit {
            border-top-color: #d9485f;
        }

        .summary-card.credit {
            border-top-color: #16865f;
        }

        .summary-card.balance {
            border-top-color: #7c3aed;
        }

        .summary-value {
            margin-top: 2px;
            font-size: 14px;
            font-weight: 800;
            color: #172033;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            padding: 7px 6px;
            border: 1px solid #315b88;
            background: #315f92;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            text-align: left;
        }

        td {
            padding: 6px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        tbody tr:nth-child(even) {
            background: #f4f7fa;
        }

        tbody tr.reversal-row {
            background: #fff1f2;
            color: #9f1239;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .center {
            text-align: center;
        }

        .muted {
            color: #64748b;
        }

        .transaction-type {
            font-weight: 700;
        }

        .reversal-badge {
            display: inline-block;
            margin-top: 2px;
            padding: 1px 5px;
            border-radius: 8px;
            background: #be123c;
            color: #fff;
            font-size: 8px;
            font-weight: 700;
        }

        tfoot td {
            padding: 7px 6px;
            border-color: #7890a9;
            background: #e7eef6;
            font-weight: 800;
        }

        .empty-row td {
            padding: 28px 10px;
            color: #64748b;
            text-align: center;
        }

        .footer {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 9px;
        }

        .footer-right {
            float: right;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .ledger-sheet {
                max-width: none;
                min-height: 0;
                margin: 0;
                padding: 0;
                border: 0;
                box-shadow: none;
            }

            .document-header,
            .summary-card,
            th,
            tfoot td {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @if($pdfMode ?? false) body {
            padding: 0;
            background: #fff;
        }

        .toolbar {
            display: none;
        }

        .ledger-sheet {
            max-width: none;
            min-height: 0;
            margin: 0;
            padding: 0;
            border: 0;
            box-shadow: none;
        }

        @endif
    </style>
</head>

<body>
    @unless($pdfMode ?? false)
    <div class="toolbar">
        <button type="button" class="close-button" onclick="window.close()">
            Close
        </button>

        <button type="button" onclick="window.print()">
            Print Ledger
        </button>
    </div>
    @endunless

    <main class="ledger-sheet">
        <section class="document-header">
            <div class="header-main">
                <h1 class="company-name">
                    {{ $store->company->company_name ?? $store->company->name ?? 'Company' }}
                </h1>

                <div class="branch-name">
                    {{ $store->store_name ?? $store->name ?? 'Branch' }}
                    @if($store->address ?? null)
                    · {{ $store->address }}
                    @endif
                </div>

                <div class="report-title">
                    <strong>SUPPLIER LEDGER</strong>
                    <span>Account statement</span>
                </div>
            </div>

            <div class="supplier-panel">
                <div class="supplier-column">
                    <div class="label">Supplier</div>
                    <div class="value">{{ $supplier->supplier_name }}</div>
                    <div class="sub-value">
                        {{ $supplier->supplier_code }}
                        @if($supplier->phone)
                        · {{ $supplier->phone }}
                        @endif
                    </div>
                </div>

                <div class="period-column">
                    <div class="label">Statement Period</div>
                    <div class="value">
                        {{ \Carbon\Carbon::parse($filters['from_date'])->format('d-m-Y') }}
                        to
                        {{ \Carbon\Carbon::parse($filters['to_date'])->format('d-m-Y') }}
                    </div>
                    <div class="sub-value">
                        Generated {{ now()->format('d-m-Y h:i A') }}
                    </div>
                </div>
            </div>
        </section>

        <section class="summary-grid">
            <div class="summary-card">
                <div class="label">Opening Balance</div>
                <div class="summary-value">{{ $summary['opening_balance_label'] }}</div>
            </div>

            <div class="summary-card debit">
                <div class="label">Total Debit</div>
                <div class="summary-value">{{ number_format($summary['total_debit'], 2) }}</div>
            </div>

            <div class="summary-card credit">
                <div class="label">Total Credit</div>
                <div class="summary-value">{{ number_format($summary['total_credit'], 2) }}</div>
            </div>

            <div class="summary-card balance">
                <div class="label">Closing Balance</div>
                <div class="summary-value">{{ $summary['closing_balance_label'] }}</div>
            </div>
        </section>

        <table>
            <colgroup>
                <col style="width: 9%">
                <col style="width: 16%">
                <col style="width: 14%">
                <col style="width: 27%">
                <col style="width: 11%">
                <col style="width: 11%">
                <col style="width: 12%">
            </colgroup>

            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transaction</th>
                    <th>Reference</th>
                    <th>Particulars</th>
                    <th class="number">Debit</th>
                    <th class="number">Credit</th>
                    <th class="number">Balance</th>
                </tr>
            </thead>

            <tbody>
                @forelse($entries as $entry)
                <tr class="{{ $entry['is_reversal'] ? 'reversal-row' : '' }}">
                    <td>
                        {{ \Carbon\Carbon::parse($entry['transaction_date'])->format('d-m-Y') }}
                    </td>

                    <td>
                        <span class="transaction-type">
                            {{ $entry['transaction_label'] }}
                        </span>

                        @if($entry['is_reversal'])
                        <br>
                        <span class="reversal-badge">REVERSAL</span>
                        @endif
                    </td>

                    <td>{{ $entry['reference_no'] ?: '—' }}</td>
                    <td>{{ $entry['remarks'] ?: '—' }}</td>
                    <td class="number">
                        {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '—' }}
                    </td>
                    <td class="number">
                        {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '—' }}
                    </td>
                    <td class="number">
                        <strong>{{ $entry['balance_label'] }}</strong>
                    </td>
                </tr>
                @empty
                <tr class="empty-row">
                    <td colspan="7">No supplier ledger transactions found for this period.</td>
                </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="4">Period Total / Closing Balance</td>
                    <td class="number">{{ number_format($summary['total_debit'], 2) }}</td>
                    <td class="number">{{ number_format($summary['total_credit'], 2) }}</td>
                    <td class="number">{{ $summary['closing_balance_label'] }}</td>
                </tr>
            </tfoot>
        </table>

        <footer class="footer">
            <span>This is a computer-generated supplier account statement.</span>
            <span class="footer-right">
                Current overall balance: <strong>{{ $summary['current_balance_label'] }}</strong>
            </span>
        </footer>
    </main>
</body>

</html>