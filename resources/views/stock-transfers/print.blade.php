<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer {{ $transfer->transfer_no }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #d8dee8;
            color: #172033;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 18px;
            background: #102a56;
            color: #fff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .25);
        }

        .toolbar strong {
            font-size: 13px;
        }

        .toolbar-actions {
            display: flex;
            gap: 7px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            min-height: 31px;
            padding: 0 14px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-light {
            border-color: #cbd5e1;
            background: #fff;
            color: #172033;
        }

        .btn-print {
            border-color: #1d4ed8;
            background: #2563eb;
            color: #fff;
        }

        .paper {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto;
            padding: 12mm;
            background: #fff;
            box-shadow: 0 4px 22px rgba(15, 23, 42, .22);
        }

        .head {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 9px;
            border-bottom: 3px solid #153e75;
        }

        .company h1 {
            margin: 0 0 4px;
            color: #102a56;
            font-size: 22px;
            letter-spacing: .2px;
        }

        .company p {
            margin: 2px 0;
            color: #475569;
            line-height: 1.35;
        }

        .document {
            min-width: 205px;
            text-align: right;
        }

        .document h2 {
            margin: 0 0 7px;
            color: #153e75;
            font-size: 17px;
            text-transform: uppercase;
        }

        .document .number {
            display: inline-block;
            padding: 5px 9px;
            border: 1px solid #93c5fd;
            background: #eff6ff;
            color: #1e3a8a;
            font-weight: 700;
        }

        .status {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status.completed {
            background: #dcfce7;
            color: #166534;
        }

        .status.cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            margin: 12px 0;
            border: 1px solid #b8c4d6;
        }

        .meta>div {
            min-height: 52px;
            padding: 7px 9px;
            border-right: 1px solid #d6deea;
        }

        .meta>div:last-child {
            border-right: 0;
        }

        .label {
            display: block;
            margin-bottom: 4px;
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .value {
            color: #172033;
            font-size: 12px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 7px 6px;
            border: 1px solid #8393aa;
            background: #173f73;
            color: #fff;
            font-size: 10px;
            text-align: left;
            text-transform: uppercase;
        }

        td {
            padding: 7px 6px;
            border: 1px solid #c4cedc;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background: #f4f7fb;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .center {
            text-align: center;
        }

        .product {
            font-weight: 700;
        }

        .sub {
            display: block;
            margin-top: 2px;
            color: #64748b;
            font-size: 10px;
        }

        tfoot td {
            background: #edf3fa;
            font-weight: 700;
        }

        .remarks {
            margin-top: 12px;
            padding: 9px 10px;
            border: 1px solid #c4cedc;
            background: #f8fafc;
            min-height: 48px;
        }

        .remarks strong {
            display: block;
            margin-bottom: 5px;
            color: #173f73;
            font-size: 10px;
            text-transform: uppercase;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin-top: 54px;
            text-align: center;
        }

        .signatures div {
            padding-top: 7px;
            border-top: 1px solid #64748b;
            font-size: 11px;
            font-weight: 700;
        }

        .foot {
            margin-top: 18px;
            padding-top: 7px;
            border-top: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 9px;
            text-align: center;
        }

        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .paper {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }
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

    <div class="toolbar">
        <strong>Stock Transfer Print Preview</strong>
        <div class="toolbar-actions">
            <a class="btn btn-light" href="{{ route('stock-transfers.show', $transfer) }}">Back</a>
            <a class="btn btn-light" href="{{ route('stock-transfers.pdf', $transfer) }}">Download PDF</a>
            <button class="btn btn-print" type="button" onclick="window.print()">Print</button>
        </div>
    </div>

    <main class="paper">
        <header class="head">
            <div class="company">
                <h1>{{ $transfer->company?->company_name ?? config('app.name') }}</h1>
                <p><strong>{{ $transfer->store?->store_name ?? 'Branch' }}</strong>{{ $transfer->store?->store_code ? ' ['.$transfer->store->store_code.']' : '' }}</p>
                @if($transfer->store?->address)<p>{{ $transfer->store->address }}</p>@endif
                @if($transfer->company?->gst_number)<p>GSTIN: {{ $transfer->company->gst_number }}</p>@endif
            </div>
            <div class="document">
                <h2>Stock Transfer</h2>
                <div class="number">{{ $transfer->transfer_no }}</div><br>
                <span class="status {{ $isCancelled ? 'cancelled' : 'completed' }}">{{ $isCancelled ? 'Cancelled' : 'Completed' }}</span>
            </div>
        </header>

        <section class="meta">
            <div><span class="label">Transfer date</span><span class="value">{{ \Carbon\Carbon::parse($transfer->transfer_date)->format('d-m-Y') }}</span></div>
            <div><span class="label">Created by</span><span class="value">{{ $transfer->creator?->name ?? '—' }}</span></div>
            <div><span class="label">Item lines</span><span class="value">{{ $transfer->items->count() }}</span></div>
            <div><span class="label">Total quantity</span><span class="value">{{ number_format($totalQuantity, 3) }}</span></div>
        </section>

        <table>
            <thead>
                <tr>
                    <th class="center">#</th>
                    <th>Product</th>
                    <th>From</th>
                    <th>To</th>
                    <th class="num">Quantity</th>
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

        <section class="remarks"><strong>Remarks</strong>{{ $transfer->remarks ?: 'No remarks.' }}</section>
        <section class="signatures">
            <div>Prepared By</div>
            <div>Stock Received By</div>
            <div>Authorised By</div>
        </section>
        <footer class="foot">Generated on {{ now()->format('d-m-Y h:i A') }} · Internal stock movement document</footer>
    </main>
</body>

</html>