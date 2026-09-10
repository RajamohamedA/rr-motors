<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Stock Ledger Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #263a4b;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
            line-height: 1.25;
        }

        .header,
        .summary,
        .ledger {
            width: 100%;
            border-collapse: collapse;
        }

        .header {
            margin-bottom: 6px;
        }

        .header td {
            padding: 0;
            vertical-align: top;
        }

        .company {
            margin: 0 0 2px;
            color: #263f52;
            font-size: 15px;
            font-weight: bold;
        }

        .branch {
            color: #315b60;
            font-size: 9px;
            font-weight: bold;
        }

        .address {
            width: 155mm;
            margin-top: 2px;
            color: #6a7986;
            font-size: 7px;
        }

        .title {
            width: 70mm;
            text-align: right;
        }

        .title h1 {
            margin: 0 0 3px;
            color: #2d4657;
            font-size: 14px;
            text-transform: uppercase;
        }

        .title div {
            color: #6a7986;
            font-size: 7px;
        }

        .summary {
            margin-bottom: 6px;
            table-layout: fixed;
        }

        .summary td {
            padding: 5px;
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
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .summary strong {
            display: block;
            margin-top: 1px;
            color: #263f52;
            font-size: 10px;
        }

        .filters {
            margin-bottom: 6px;
            padding: 4px 6px;
            border: 1px solid #c5ced5;
            color: #4b6070;
            background: #f2f5f7;
            font-size: 7px;
        }

        .ledger {
            table-layout: fixed;
        }

        .ledger thead {
            display: table-header-group;
        }

        .ledger tr {
            page-break-inside: avoid;
        }

        .ledger th {
            padding: 4px 2px;
            border: 1px solid #263b4b;
            color: #fff;
            background: #374d5d;
            font-size: 6px;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .ledger td {
            padding: 4px 2px;
            border: 1px solid #cdd5db;
            color: #304555;
            font-size: 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .ledger tbody tr:nth-child(even) {
            background: #f6f8f9;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .product {
            display: block;
            color: #263f52;
            font-size: 7px;
            font-weight: bold;
        }

        .subtext {
            display: block;
            margin-top: 1px;
            color: #71808d;
            font-size: 5px;
        }

        .qty-in {
            color: #087443 !important;
            font-weight: bold;
        }

        .qty-out {
            color: #a42637 !important;
            font-weight: bold;
        }

        .balance {
            color: #185d66 !important;
            font-size: 7px !important;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 1px 3px;
            border: 1px solid;
            font-size: 5px;
            font-weight: bold;
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
            margin-top: 5px;
            color: #71808d;
            font-size: 5px;
            text-align: right;
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

    <table class="header">
        <tr>
            <td>
                <h2 class="company">{{ $company->company_name ?? 'Company' }}</h2>
                <div class="branch">{{ $store->store_name ?? 'Branch' }}</div>
                <div class="address">
                    {{ collect([$store->address ?? null, $store->city ?? null, $store->state ?? null, $store->pincode ?? null])->filter()->implode(', ') }}
                </div>
            </td>
            <td class="title">
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
        <strong>Filters:</strong> {{ $locationLabel }} · {{ $typeLabel }}
        @if (filled($filters['from_date'] ?? null)) · From {{ date('d-m-Y', strtotime($filters['from_date'])) }} @endif
        @if (filled($filters['to_date'] ?? null)) · To {{ date('d-m-Y', strtotime($filters['to_date'])) }} @endif
        @if (filled($filters['search'] ?? null)) · Search: “{{ $filters['search'] }}” @endif
    </div>

    <table class="ledger">
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
                <td><span class="product">{{ $ledger['product']?->product_name ?? 'Product' }}</span><span class="subtext">{{ collect([$ledger['product']?->product_code, $ledger['product']?->part_number])->filter()->implode(' · ') ?: '—' }}</span></td>
                <td>{{ $ledger['location_name'] }}</td>
                <td><span class="badge {{ $movement }}">{{ ucwords(strtolower(str_replace('_', ' ', $ledger['transaction_type']))) }}</span></td>
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
                <td colspan="12" style="padding: 20px; text-align: center;">No stock ledger transactions found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Stock Ledger · Complete filtered transaction report</div>
</body>

</html>