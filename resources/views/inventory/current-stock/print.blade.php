<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Current Stock Register</title>

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
            color: #172b3d;
            background: #d9e0e6;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 14px;
            border-bottom: 1px solid #788b9b;
            background: linear-gradient(#ffffff, #e4ebf0);
            box-shadow: 0 2px 8px rgb(30 48 65 / 18%);
        }

        .print-toolbar strong {
            font-size: 13px;
        }

        .print-toolbar span {
            color: #607286;
            font-size: 11px;
        }

        .toolbar-actions {
            display: flex;
            gap: 6px;
        }

        .toolbar-actions button {
            min-height: 32px;
            padding: 0 13px;
            border: 1px solid;
            border-radius: 4px;
            color: #ffffff;
            font: 700 11px "Segoe UI", Arial, sans-serif;
            cursor: pointer;
        }

        .toolbar-actions .print {
            border-color: #0b4e82;
            background: linear-gradient(#3488c3, #1769a8);
        }

        .toolbar-actions .close {
            border-color: #596a79;
            background: linear-gradient(#7c8c99, #596a79);
        }

        .sheet {
            width: 283mm;
            min-height: 196mm;
            margin: 14px auto;
            padding: 7mm;
            border: 1px solid #aebac4;
            background: #ffffff;
            box-shadow: 0 4px 18px rgb(32 48 62 / 22%);
        }

        .report-header {
            width: 100%;
            margin-bottom: 7px;
            border-collapse: collapse;
        }

        .report-header td {
            vertical-align: top;
        }

        .company-name {
            margin: 0 0 2px;
            color: #123f62;
            font-size: 18px;
            font-weight: 800;
        }

        .branch-name {
            margin-bottom: 2px;
            color: #2f4d64;
            font-size: 12px;
            font-weight: 700;
        }

        .address {
            max-width: 155mm;
            color: #5d6e7d;
            font-size: 10px;
        }

        .report-title {
            width: 70mm;
            text-align: right;
        }

        .report-title h1 {
            margin: 0 0 4px;
            color: #164f79;
            font-size: 17px;
            text-transform: uppercase;
        }

        .report-title div {
            color: #5c6d7d;
            font-size: 10px;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 7px;
            border-collapse: separate;
            border-spacing: 4px 0;
        }

        .summary-table td {
            width: 16.66%;
            padding: 6px 8px;
            border: 1px solid #aab8c4;
            border-bottom: 3px solid #1769a8;
            background: #f2f6f9;
        }

        .summary-table td.warning {
            border-bottom-color: #d0870c;
        }

        .summary-table td.danger {
            border-bottom-color: #bd3042;
        }

        .summary-table span {
            display: block;
            color: #627487;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary-table strong {
            display: block;
            margin-top: 1px;
            color: #193e5b;
            font-size: 14px;
        }

        .filter-line {
            margin-bottom: 7px;
            padding: 5px 7px;
            border: 1px solid #c0ccd5;
            background: #edf3f7;
            color: #455c70;
            font-size: 10px;
        }

        .filter-line strong {
            color: #223d54;
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .stock-table thead {
            display: table-header-group;
        }

        .stock-table tr {
            page-break-inside: avoid;
        }

        .stock-table th {
            padding: 5px 4px;
            border: 1px solid #174f79;
            color: #ffffff;
            background: #286b98;
            font-size: 9px;
            text-align: left;
            white-space: nowrap;
        }

        .stock-table td {
            padding: 5px 4px;
            border: 1px solid #c4ced6;
            color: #263d50;
            font-size: 9px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .stock-table tbody tr:nth-child(even) {
            background: #f3f6f8;
        }

        .stock-table .number {
            text-align: right;
            white-space: nowrap;
        }

        .product-name {
            display: block;
            color: #173d5b;
            font-size: 10px;
            font-weight: 700;
        }

        .subtext {
            display: block;
            margin-top: 1px;
            color: #6a7a88;
            font-size: 8px;
        }

        .status {
            display: inline-block;
            padding: 2px 4px;
            border: 1px solid;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status.in_stock {
            color: #087443;
            border-color: #85bca0;
            background: #eaf7f0;
        }

        .status.low_stock {
            color: #875707;
            border-color: #d5b968;
            background: #fff5d5;
        }

        .status.out_of_stock,
        .status.negative {
            color: #a12637;
            border-color: #d8a0a9;
            background: #fff0f2;
        }

        .report-footer {
            margin-top: 6px;
            color: #6b7b89;
            font-size: 8px;
            text-align: right;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .print-toolbar {
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
    $statusLabel = match ($filters['stock_status'] ?? 'all') {
    'in_stock' => 'In Stock',
    'low_stock' => 'Low Stock',
    'out_of_stock' => 'Out of Stock',
    'negative' => 'Negative Stock',
    default => 'All stock',
    };
    @endphp

    <div class="print-toolbar">
        <div>
            <strong>Current Stock Print Preview</strong>
            <span>A4 landscape · {{ $stocks->count() }} record(s)</span>
        </div>

        <div class="toolbar-actions">
            <button
                type="button"
                class="print"
                onclick="window.print()">
                Print
            </button>

            <button
                type="button"
                class="close"
                onclick="window.close()">
                Close
            </button>
        </div>
    </div>

    <main class="sheet">
        <table class="report-header">
            <tr>
                <td>
                    <h2 class="company-name">
                        {{ $company->company_name ?? 'Company' }}
                    </h2>

                    <div class="branch-name">
                        {{ $store->store_name ?? 'Branch' }}
                    </div>

                    <div class="address">
                        {{ collect([
                            $store->address ?? null,
                            $store->city ?? null,
                            $store->state ?? null,
                            $store->pincode ?? null,
                        ])->filter()->implode(', ') }}
                    </div>
                </td>

                <td class="report-title">
                    <h1>Current Stock Register</h1>
                    <div>
                        Generated: {{ $generatedAt->format('d-m-Y h:i A') }}
                    </div>
                </td>
            </tr>
        </table>

        <table class="summary-table">
            <tr>
                <td>
                    <span>Products</span>
                    <strong>{{ number_format($summary['product_count']) }}</strong>
                </td>
                <td>
                    <span>Total Quantity</span>
                    <strong>{{ number_format($summary['total_quantity'], 2) }}</strong>
                </td>
                <td>
                    <span>Stock Value</span>
                    <strong>Rs. {{ number_format($summary['stock_value'], 2) }}</strong>
                </td>
                <td class="warning">
                    <span>Low Stock</span>
                    <strong>{{ number_format($summary['low_stock_count']) }}</strong>
                </td>
                <td class="danger">
                    <span>Out of Stock</span>
                    <strong>{{ number_format($summary['out_of_stock_count']) }}</strong>
                </td>
                <td class="danger">
                    <span>Negative Stock</span>
                    <strong>{{ number_format($summary['negative_stock_count']) }}</strong>
                </td>
            </tr>
        </table>

        <div class="filter-line">
            <strong>Filters:</strong>
            {{ $locationLabel }} · {{ $statusLabel }}
            @if (filled($filters['search'] ?? null))
            · Search: “{{ $filters['search'] }}”
            @endif
        </div>

        <table class="stock-table">
            <colgroup>
                <col style="width: 4%">
                <col style="width: 19%">
                <col style="width: 11%">
                <col style="width: 10%">
                <col style="width: 7%">
                <col style="width: 8%">
                <col style="width: 8%">
                <col style="width: 9%">
                <col style="width: 10%">
                <col style="width: 8%">
                <col style="width: 6%">
            </colgroup>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category / Brand</th>
                    <th>Location</th>
                    <th>Rack</th>
                    <th class="number">Current Qty</th>
                    <th class="number">Minimum</th>
                    <th class="number">Unit Cost</th>
                    <th class="number">Stock Value</th>
                    <th>Status</th>
                    <th>Last Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stocks as $index => $stock)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <span class="product-name">
                            {{ $stock['product']?->product_name ?? 'Product' }}
                        </span>
                        <span class="subtext">
                            {{ collect([
                                    $stock['product']?->product_code,
                                    $stock['product']?->part_number,
                                ])->filter()->implode(' · ') ?: '—' }}
                        </span>
                    </td>
                    <td>
                        {{ $stock['product']?->category?->name ?? '—' }}
                        <span class="subtext">
                            {{ $stock['product']?->brand?->brand_name ?? 'No brand' }}
                        </span>
                    </td>
                    <td>
                        {{ $stock['location_name'] }}
                        <span class="subtext">
                            {{ ucfirst($stock['location_type']) }}
                        </span>
                    </td>
                    <td>{{ $stock['product']?->rack_location ?: '—' }}</td>
                    <td class="number">
                        <strong>{{ number_format($stock['quantity'], 2) }}</strong>
                        <span class="subtext">
                            {{ $stock['product']?->unit?->unit_name ?? '' }}
                        </span>
                    </td>
                    <td class="number">
                        {{ number_format($stock['minimum_stock'], 2) }}
                    </td>
                    <td class="number">
                        {{ number_format($stock['unit_cost'], 2) }}
                    </td>
                    <td class="number">
                        <strong>{{ number_format($stock['stock_value'], 2) }}</strong>
                    </td>
                    <td>
                        <span class="status {{ $stock['stock_status'] }}">
                            {{ ucwords(str_replace('_', ' ', $stock['stock_status'])) }}
                        </span>
                    </td>
                    <td>
                        {{ $stock['last_transaction_date']
                                ? date('d-m-Y', strtotime($stock['last_transaction_date']))
                                : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="padding: 25px; text-align: center;">
                        No current stock records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="report-footer">
            Current stock is based on the latest Stock Ledger balance for each
            product and location.
        </div>
    </main>
</body>

</html>