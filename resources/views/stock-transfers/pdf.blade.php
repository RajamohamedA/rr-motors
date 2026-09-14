<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Stock Transfer {{ $transfer->transfer_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 24px;
        }

        body {
            margin: 0;
            color: #172033;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header {
            border-bottom: 3px solid #153e75;
        }

        .header td {
            padding: 0 0 8px;
            vertical-align: top;
        }

        h1 {
            margin: 0 0 3px;
            color: #102a56;
            font-size: 18px;
        }

        h2 {
            margin: 0 0 6px;
            color: #153e75;
            font-size: 14px;
            text-transform: uppercase;
        }

        p {
            margin: 2px 0;
            color: #475569;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .number {
            padding: 4px 7px;
            border: 1px solid #93c5fd;
            background: #eff6ff;
            color: #1e3a8a;
            font-weight: bold;
        }

        .status {
            margin-top: 5px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .cancelled {
            color: #b91c1c;
        }

        .completed {
            color: #166534;
        }

        .meta {
            margin: 10px 0;
            border: 1px solid #9daabe;
        }

        .meta td {
            width: 25%;
            padding: 6px;
            border-right: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .label {
            display: block;
            margin-bottom: 3px;
            color: #64748b;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .value {
            font-size: 9px;
            font-weight: bold;
        }

        .items th {
            padding: 6px 4px;
            border: 1px solid #718198;
            background: #173f73;
            color: #fff;
            font-size: 7px;
            text-align: left;
            text-transform: uppercase;
        }

        .items td {
            padding: 6px 4px;
            border: 1px solid #b8c4d4;
            vertical-align: top;
        }

        .items tbody tr:nth-child(even) {
            background: #f4f7fb;
        }

        .items .num {
            text-align: right;
            white-space: nowrap;
        }

        .product {
            font-weight: bold;
        }

        .sub {
            display: block;
            margin-top: 2px;
            color: #64748b;
            font-size: 7px;
        }

        .items tfoot td {
            background: #eaf0f8;
            font-weight: bold;
        }

        .remarks {
            margin-top: 10px;
            padding: 7px;
            border: 1px solid #b8c4d4;
            background: #f8fafc;
            min-height: 28px;
        }

        .remarks b {
            display: block;
            margin-bottom: 4px;
            color: #173f73;
            font-size: 7px;
            text-transform: uppercase;
        }

        .signatures {
            margin-top: 44px;
        }

        .signatures td {
            width: 33.33%;
            padding: 6px 18px 0;
            border-top: 1px solid #64748b;
            font-weight: bold;
            text-align: center;
        }

        .signatures td.space {
            width: 6%;
            border: 0;
        }

        .footer {
            margin-top: 14px;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 7px;
            text-align: center;
        }
    </style>
</head>

<body>
    @php
    $isCancelled = method_exists($transfer, 'isCancelled') ? $transfer->isCancelled() : (int) $transfer->status === 0;
    $totalQuantity = $transfer->items->sum(fn ($item) => (float) $item->quantity);
    $totalValue = $transfer->items->sum(fn ($item) => (float) $item->quantity * (float) $item->unit_cost);
    $location = fn ($godown) => $godown?->godown_name ?? 'Shop';
    @endphp

    <table class="header">
        <tr>
            <td>
                <h1>{{ $transfer->company?->company_name ?? config('app.name') }}</h1>
                <p><b>{{ $transfer->store?->store_name ?? 'Branch' }}</b>{{ $transfer->store?->store_code ? ' ['.$transfer->store->store_code.']' : '' }}</p>@if($transfer->store?->address)<p>{{ $transfer->store->address }}</p>@endif @if($transfer->company?->gst_number)<p>GSTIN: {{ $transfer->company->gst_number }}</p>@endif
            </td>
            <td class="right">
                <h2>Stock Transfer</h2><span class="number">{{ $transfer->transfer_no }}</span>
                <div class="status {{ $isCancelled ? 'cancelled' : 'completed' }}">{{ $isCancelled ? 'Cancelled' : 'Completed' }}</div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td><span class="label">Transfer date</span><span class="value">{{ \Carbon\Carbon::parse($transfer->transfer_date)->format('d-m-Y') }}</span></td>
            <td><span class="label">Created by</span><span class="value">{{ $transfer->creator?->name ?? '—' }}</span></td>
            <td><span class="label">Item lines</span><span class="value">{{ $transfer->items->count() }}</span></td>
            <td><span class="label">Total quantity</span><span class="value">{{ number_format($totalQuantity, 3) }}</span></td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="center">#</th>
                <th>Product</th>
                <th>From</th>
                <th>To</th>
                <th class="num">Qty</th>
                <th>Unit</th>
                <th class="num">Unit Cost</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfer->items as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td><span class="product">{{ $item->product?->product_name ?? 'Product unavailable' }}</span><span class="sub">{{ $item->product?->product_code ?? 'No code' }}{{ $item->product?->part_number ? ' • '.$item->product->part_number : '' }}</span></td>
                <td>{{ $location($item->fromGodown) }}</td>
                <td>{{ $location($item->toGodown) }}</td>
                <td class="num">{{ number_format((float) $item->quantity, 3) }}</td>
                <td>{{ $item->product?->unit?->short_name ?? $item->product?->unit?->unit_name ?? '—' }}</td>
                <td class="num">{{ number_format((float) $item->unit_cost, 2) }}</td>
                <td class="num">{{ number_format((float) $item->quantity * (float) $item->unit_cost, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="center">No transfer items found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="num">Grand Total</td>
                <td class="num">{{ number_format($totalQuantity, 3) }}</td>
                <td></td>
                <td></td>
                <td class="num">{{ number_format($totalValue, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="remarks"><b>Remarks</b>{{ $transfer->remarks ?: 'No remarks.' }}</div>
    <table class="signatures">
        <tr>
            <td>Prepared By</td>
            <td class="space"></td>
            <td>Stock Received By</td>
            <td class="space"></td>
            <td>Authorised By</td>
        </tr>
    </table>
    <div class="footer">Generated on {{ now()->format('d-m-Y h:i A') }} · Internal stock movement document</div>
</body>

</html>