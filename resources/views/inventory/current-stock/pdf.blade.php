<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Current Stock Register</title>

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
            color: #172b3d;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            line-height: 1.25;
        }

        .report-header,
        .summary-table,
        .stock-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-header {
            margin-bottom: 6px;
        }

        .report-header td {
            padding: 0;
            vertical-align: top;
        }

        .company-name {
            margin: 0 0 2px;
            color: #123f62;
            font-size: 15px;
            font-weight: bold;
        }

        .branch-name {
            color: #304e66;
            font-size: 10px;
            font-weight: bold;
        }

        .address {
            width: 155mm;
            margin-top: 2px;
            color: #5d6e7d;
            font-size: 8px;
        }

        .report-title {
            width: 70mm;
            text-align: right;
        }

        .report-title h1 {
            margin: 0 0 3px;
            color: #164f79;
            font-size: 14px;
            text-transform: uppercase;
        }

        .report-title div {
            color: #5c6d7d;
            font-size: 8px;
        }

        .summary-table {
            margin-bottom: 6px;
            table-layout: fixed;
        }

        .summary-table td {
            padding: 5px 6px;
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
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .summary-table strong {
            display: block;
            margin-top: 1px;
            color: #193e5b;
            font-size: 11px;
        }

        .filter-line {
            margin-bottom: 6px;
            padding: 4px 6px;
            border: 1px solid #c0ccd5;
            background: #edf3f7;
            color: #455c70;
            font-size: 8px;
        }

        .stock-table {
            table-layout: fixed;
        }

        .stock-table thead {
            display: table-header-group;
        }

        .stock-table tr {
            page-break-inside: avoid;
        }

        .stock-table th {
            padding: 4px 3px;
            border: 1px solid #174f79;
            color: #ffffff;
            background: #286b98;
            font-size: 7px;
            text-align: left;
            white-space: nowrap;
        }

        .stock-table td {
            padding: 4px 3px;
            border: 1px solid #c4ced6;
            color: #263d50;
            font-size: 7px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .stock-table tbody tr:nth-child(even) {
            background: #f3f6f8;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .product-name {
            display: block;
            color: #173d5b;
            font-size: 8px;
            font-weight: bold;
        }

        .subtext {
            display: block;
            margin-top: 1px;
            color: #6a7a88;
            font-size: 6px;
        }

        .status {
            display: inline-block;
            padding: 1px 3px;
            border: 1px solid;
            font-size: 6px;
            font-weight: bold;
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

        .footer {
            margin-top: 5px;
            color: #6b7b89;
            font-size: 6px;
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
    $statusLabel = match ($filters['stock_status'] ?? 'all') {
    'in_stock' => 'In Stock',
    'low_stock' => 'Low Stock',
    'out_of_stock' => 'Out of Stock',
    'negative' => 'Negative Stock',
    default => 'All stock',
    };
    @endphp

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

    <div class="footer">
        Current stock is based on the latest Stock Ledger balance for each
        product and location.
    </div>
</body>

</html>