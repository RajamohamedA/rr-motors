<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Stock Ledger Report</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #263a4b;
            background: #dfe4e8;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
        }

        .toolbar {
            position: sticky;
            z-index: 10;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 14px;
            border-bottom: 1px solid #9ba8b2;
            background: #ffffff;
            box-shadow: 0 2px 9px rgb(31 48 61 / 17%);
        }

        .toolbar strong {
            display: block;
            color: #2c4355;
            font-size: 13px;
        }

        .toolbar span {
            color: #6b7c89;
            font-size: 10px;
        }

        .toolbar-actions {
            display: flex;
            gap: 6px;
        }

        .toolbar button {
            min-height: 33px;
            padding: 0 13px;
            border: 1px solid;
            border-radius: 5px;
            color: #ffffff;
            font: 700 11px "Segoe UI", Arial, sans-serif;
            cursor: pointer;
        }

        .toolbar .print {
            border-color: #18545c;
            background: #256f78;
        }

        .toolbar .close {
            border-color: #536675;
            background: #687b89;
        }

        .sheet {
            width: 283mm;
            min-height: 196mm;
            margin: 14px auto;
            padding: 7mm;
            border: 1px solid #abb7c0;
            background: #ffffff;
            box-shadow: 0 5px 20px rgb(31 48 61 / 22%);
        }

        .report-header,
        .summary,
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-header {
            margin-bottom: 7px;
        }

        .report-header td {
            padding: 0;
            vertical-align: top;
        }

        .company-name {
            margin: 0 0 2px;
            color: #263f52;
            font-size: 18px;
            font-weight: 800;
        }

        .branch-name {
            color: #315b60;
            font-size: 12px;
            font-weight: 700;
        }

        .address {
            max-width: 160mm;
            margin-top: 2px;
            color: #6a7986;
            font-size: 9px;
        }

        .title-cell {
            width: 72mm;
            text-align: right;
        }

        .title-cell h1 {
            margin: 0 0 3px;
            color: #2d4657;
            font-size: 17px;
            text-transform: uppercase;
        }

        .title-cell div {
            color: #6a7986;
            font-size: 9px;
        }

        .summary {
            margin-bottom: 7px;
            table-layout: fixed;
            border-spacing: 4px 0;
            border-collapse: separate;
        }

        .summary td {
            padding: 6px 7px;
            border: 1px solid #bdc7cf;
            border-bottom: 3px solid #718292;
            background: #f5f7f9;
        }

        .summary td.inward {
            border-bottom-color: #148052;
        }

        .summary td.outward {
            border-bottom-color: #c03547;
        }

        .summary td.closing {
            border-bottom-color: #256f78;
            background: #edf7f7;
        }

        .summary span {
            display: block;
            color: #687989;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary strong {
            display: block;
            margin-top: 1px;
            color: #263f52;
            font-size: 13px;
        }

        .filters {
            margin-bottom: 7px;
            padding: 5px 7px;
            border: 1px solid #c5ced5;
            color: #4b6070;
            background: #f2f5f7;
            font-size: 9px;
        }

        .filters strong {
            color: #2e4657;
        }

        .ledger-table {
            table-layout: fixed;
        }

        .ledger-table thead {
            display: table-header-group;
        }

        .ledger-table tr {
            page-break-inside: avoid;
        }

        .ledger-table th {
            padding: 5px 3px;
            border: 1px solid #263b4b;
            color: #ffffff;
            background: #374d5d;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .ledger-table td {
            padding: 5px 3px;
            border: 1px solid #cdd5db;
            color: #304555;
            font-size: 8px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .ledger-table tbody tr:nth-child(even) {
            background: #f6f8f9;
        }

        .ledger-table .number {
            text-align: right;
            white-space: nowrap;
        }

        .product {
            display: block;
            color: #263f52;
            font-size: 9px;
            font-weight: 700;
        }

        .subtext {
            display: block;
            margin-top: 1px;
            color: #71808d;
            font-size: 7px;
        }

        .qty-in {
            color: #087443 !important;
            font-weight: 800;
        }

        .qty-out {
            color: #a42637 !important;
            font-weight: 800;
        }

        .balance {
            color: #185d66 !important;
            font-size: 9px !important;
            font-weight: 800;
        }

        .badge {
            display: inline-block;
            padding: 2px 4px;
            border: 1px solid;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
        }

        .badge.inward {
            color: #087443;
            border-color: #8fc3a7;
            background: #eaf8f1;
        }

        .badge.outward {
            color: #a42637;
            border-color: #dda5ae;
            background: #fff0f2;
        }

        .badge.adjustment {
            color: #805b0d;
            border-color: #d4ba76;
            background: #fff6dc;
        }

        .footer {
            margin-top: 6px;
            color: #71808d;
            font-size: 7px;
            text-align: right;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                border: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    @php
    $company = $store->company;
    $locationLabel = match ($filters['location'] ?? 'all') {
    'shop' => 'Shop only',
    'godown' => 'Godowns only',
    default => 'All locations',
    };
    $typeLabel = filled($filters['transaction_type'] ?? null)
    ? ucwords(strtolower(str_replace('_', ' ', $filters['transaction_type'])))
    : 'All transaction types';
    @endphp

    <div class="toolbar">
        <div>
            <strong>Stock Ledger Print Preview</strong>
            <span>A4 landscape · All {{ $ledgers->count() }} filtered transactions</span>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="print" onclick="window.print()">Print</button>
            <button type="button" class="close" onclick="window.close()">Close</button>
        </div>
    </div>

    <main class="sheet">
        <table class="report-header">
            <tr>
                <td>
                    <h2 class="company-name">{{ $company->company_name ?? 'Company' }}</h2>
                    <div class="branch-name">{{ $store->store_name ?? 'Branch' }}</div>
                    <div class="address">
                        {{ collect([
                            $store->address ?? null,
                            $store->city ?? null,
                            $store->state ?? null,
                            $store->pincode ?? null,
                        ])->filter()->implode(', ') }}
                    </div>
                </td>
                <td class="title-cell">
                    <h1>Stock Ledger Report</h1>
                    <div>Generated: {{ $generatedAt->format('d-m-Y h:i A') }}</div>
                </td>
            </tr>
        </table>

        <table class="summary">
            <tr>
                <td><span>Opening Qty</span><strong>{{ number_format($summary['opening_quantity'], 2) }}</strong></td>
                <td class="inward"><span>Quantity In</span><strong>{{ number_format($summary['quantity_in'], 2) }}</strong></td>
                <td class="outward"><span>Quantity Out</span><strong>{{ number_format($summary['quantity_out'], 2) }}</strong></td>
                <td class="closing"><span>Closing Qty</span><strong>{{ number_format($summary['closing_quantity'], 2) }}</strong></td>
                <td><span>Inward Value</span><strong>Rs. {{ number_format($summary['inward_value'], 2) }}</strong></td>
                <td><span>Transactions</span><strong>{{ number_format($summary['transaction_count']) }}</strong></td>
            </tr>
        </table>

        <div class="filters">
            <strong>Filters:</strong>
            {{ $locationLabel }} · {{ $typeLabel }}
            @if (filled($filters['from_date'] ?? null))
            · From {{ date('d-m-Y', strtotime($filters['from_date'])) }}
            @endif
            @if (filled($filters['to_date'] ?? null))
            · To {{ date('d-m-Y', strtotime($filters['to_date'])) }}
            @endif
            @if (filled($filters['search'] ?? null))
            · Search: “{{ $filters['search'] }}”
            @endif
        </div>

        <table class="ledger-table">
            <colgroup>
                <col style="width: 3%">
                <col style="width: 7%">
                <col style="width: 16%">
                <col style="width: 9%">
                <col style="width: 11%">
                <col style="width: 10%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 7%">
                <col style="width: 7%">
                <col style="width: 8%">
                <col style="width: 10%">
            </colgroup>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Location</th>
                    <th>Transaction</th>
                    <th>Reference</th>
                    <th class="number">Qty In</th>
                    <th class="number">Qty Out</th>
                    <th class="number">Balance</th>
                    <th class="number">Unit Cost</th>
                    <th class="number">Total Cost</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ledgers as $index => $ledger)
                @php
                $movement = $ledger['qty_in'] > 0 && $ledger['qty_out'] > 0
                ? 'adjustment'
                : ($ledger['qty_in'] > 0 ? 'inward' : 'outward');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $ledger['transaction_date'] ? date('d-m-Y', strtotime($ledger['transaction_date'])) : '—' }}</td>
                    <td>
                        <span class="product">{{ $ledger['product']?->product_name ?? 'Product' }}</span>
                        <span class="subtext">{{ collect([$ledger['product']?->product_code, $ledger['product']?->part_number])->filter()->implode(' · ') ?: '—' }}</span>
                    </td>
                    <td>{{ $ledger['location_name'] }}</td>
                    <td>
                        <span class="badge {{ $movement }}">
                            {{ ucwords(strtolower(str_replace('_', ' ', $ledger['transaction_type']))) }}
                        </span>
                    </td>
                    <td>{{ $ledger['reference_no'] ?: '—' }}</td>
                    <td class="number qty-in">{{ $ledger['qty_in'] > 0 ? number_format($ledger['qty_in'], 2) : '—' }}</td>
                    <td class="number qty-out">{{ $ledger['qty_out'] > 0 ? number_format($ledger['qty_out'], 2) : '—' }}</td>
                    <td class="number balance">{{ number_format($ledger['balance_qty'], 2) }}</td>
                    <td class="number">{{ number_format($ledger['unit_cost'], 2) }}</td>
                    <td class="number">{{ number_format($ledger['total_cost'], 2) }}</td>
                    <td>{{ $ledger['remarks'] ?: '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" style="padding: 25px; text-align: center;">No stock ledger transactions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">Stock Ledger · Complete filtered transaction report</div>
    </main>
</body>

</html>