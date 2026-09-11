<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>{{ $adjustment->adjustment_no }}</title>

    <style>
        @page {
            size: {
                    {
                    ($paperSize ?? 'a4')==='a5' ? 'A5': 'A4'
                }
            }

            portrait;

            margin: {
                    {
                    ($paperSize ?? 'a4')==='a5' ? '7mm': '9mm'
                }
            }

            ;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1d3442;
            font-family: DejaVu Sans, sans-serif;

            font-size: {
                    {
                    ($paperSize ?? 'a4')==='a5' ? '7.2pt': '8.5pt'
                }
            }

            ;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            margin-bottom: 10px;
            border-bottom: 2px solid #263f4d;
        }

        .header-table>tbody>tr>td {
            padding: 0 0 8px;
            border: 0;
            vertical-align: top;
        }

        .company-cell {
            width: 62%;
            padding-right: 12px !important;
        }

        .title-cell {
            width: 38%;
        }

        .company-name {
            margin: 0 0 2px;
            color: #153a4a;

            font-size: {
                    {
                    ($paperSize ?? 'a4')==='a5' ? '13pt': '16pt'
                }
            }

            ;
            font-weight: bold;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .branch-name {
            margin-bottom: 3px;
            color: #08736b;
            font-size: 9pt;
            font-weight: bold;
        }

        .address,
        .contact {
            color: #566b78;
            font-size: 7.3pt;
        }

        .contact {
            margin-top: 3px;
        }

        .document-box {
            width: 100%;
            border: 1px solid #607582;
        }

        .document-box-title {
            padding: 5px;
            color: #ffffff;
            background: #354f5e;
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-align: center;
            text-transform: uppercase;
        }

        .document-meta {
            width: 100%;
        }

        .document-meta td {
            padding: 3px 4px;
            border: 0;
            border-bottom: 1px solid #cbd4d9;
            font-size: 7.3pt;
        }

        .document-meta tr:last-child td {
            border-bottom: 0;
        }

        .document-meta td:first-child {
            width: 38%;
            color: #61737e;
            background: #e9eef0;
            font-weight: bold;
        }

        .document-meta td:last-child {
            color: #203b49;
            font-weight: bold;
        }

        .overview {
            margin-bottom: 10px;
            border: 1px solid #82949f;
        }

        .overview td {
            width: 25%;
            padding: 5px 6px;
            border: 0;
            border-right: 1px solid #c9d3d8;
            background: #f4f7f8;
        }

        .overview td:last-child {
            border-right: 0;
        }

        .overview span,
        .overview strong {
            display: block;
        }

        .overview span {
            color: #657782;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .overview strong {
            margin-top: 1px;
            color: #213b49;
            font-size: 8pt;
        }

        .overview strong.increase {
            color: #087047;
        }

        .overview strong.decrease,
        .overview strong.cancelled {
            color: #a72b3d;
        }

        .section-title {
            padding: 4px 6px;
            border: 1px solid #607582;
            border-bottom: 0;
            color: #ffffff;
            background: #405968;
            font-size: 7.5pt;
            font-weight: bold;
        }

        .items-table {
            table-layout: fixed;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table tr {
            page-break-inside: avoid;
        }

        .items-table th,
        .items-table td {
            padding: {
                    {
                    ($paperSize ?? 'a4')==='a5' ? '1.8px': '3.5px'
                }
            }

            ;
            border: 1px solid #9eacb4;
            overflow: hidden;
            vertical-align: middle;
            word-break: normal;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .items-table th {
            color: #253f4d;
            background: #e1e8eb;

            font-size: {
                    {
                    ($paperSize ?? 'a4')==='a5' ? '5.1pt': '5.8pt'
                }
            }

            ;
            font-weight: bold;
            line-height: 1.15;
            text-align: left;
        }

        .items-table td {
            color: #293f4c;

            font-size: {
                    {
                    ($paperSize ?? 'a4')==='a5' ? '5.2pt': '6.1pt'
                }
            }

            ;
            line-height: 1.2;
        }

        .items-table .serial {
            width: 4%;
            text-align: center;
        }

        .items-table .product {
            width: 30%;
        }

        .items-table .location {
            width: 12%;
        }

        .items-table.a5 .serial {
            width: 5%;
        }

        .items-table.a5 .product {
            width: 43%;
        }

        .items-table.a5 th {
            padding: 2px 1px;
            font-size: 4.7pt;
            line-height: 1.05;
            text-align: center;
            white-space: normal;
        }

        .items-table.a5 th.product {
            padding-left: 4px;
            text-align: left;
        }

        .items-table.a5 th span {
            display: block;
            width: 100%;
            text-align: center;
            white-space: normal;
        }

        .items-table .number {
            white-space: nowrap;
            text-align: right;
        }

        .product-name {
            color: #183949;

            font-size: {
                    {
                    ($paperSize ?? 'a4')==='a5' ? '5.4pt': '6.4pt'
                }
            }

            ;
            font-weight: bold;
            line-height: 1.18;
            white-space: normal;
            word-break: break-all;
            overflow-wrap: anywhere;
            word-wrap: break-word;
        }

        .product-meta {
            margin-top: 1px;
            color: #6a7b85;

            font-size: {
                    {
                    ($paperSize ?? 'a4')==='a5' ? '4.5pt': '5.2pt'
                }
            }

            ;
            line-height: 1.15;
            white-space: normal;
            word-break: break-all;
            overflow-wrap: anywhere;
            word-wrap: break-word;
        }

        .location-label {
            display: inline-block;
            max-width: 100%;
            padding: 1px 4px;
            border: 1px solid #a5b4bd;
            border-radius: 6px;
            color: #405967;
            background: #eef3f5;

            font-size: {
                    {
                    ($paperSize ?? 'a4')==='a5' ? '4.6pt': '5.3pt'
                }
            }

            ;
            font-weight: bold;
            line-height: 1.15;
            white-space: normal;
            word-break: break-all;
            overflow-wrap: anywhere;
            word-wrap: break-word;
        }

        .qty-in {
            color: #087047 !important;
            font-weight: bold;
        }

        .qty-out {
            color: #a82b3d !important;
            font-weight: bold;
        }

        .stock-after,
        .line-total {
            color: #155970 !important;
            font-weight: bold;
        }

        .items-table tfoot td {
            background: #e5ebee;
            font-weight: bold;
        }

        .a5-product-detail {
            margin-top: 2px;
            color: #5f727e;
            font-size: 4.6pt;
            line-height: 1.25;
            white-space: normal;
            word-break: break-all;
            word-wrap: break-word;
        }

        .a5-product-detail strong {
            color: #38515f;
        }

        .lower-table {
            margin-top: 10px;
        }

        .lower-table>tbody>tr>td {
            width: 50%;
            padding: 0;
            border: 0;
            vertical-align: top;
        }

        .lower-table>tbody>tr>td:first-child {
            padding-right: 5px;
        }

        .lower-table>tbody>tr>td:last-child {
            padding-left: 5px;
        }

        .box {
            border: 1px solid #7c909c;
        }

        .box-title {
            padding: 4px 5px;
            color: #ffffff;
            background: #405968;
            font-size: 7pt;
            font-weight: bold;
        }

        .remarks {
            min-height: 45px;
            padding: 5px;
            color: #425966;
            background: #fafbfb;
            font-size: 6.5pt;
            white-space: pre-wrap;
        }

        .audit td {
            padding: 3px 4px;
            border: 0;
            border-bottom: 1px solid #d3dce1;
            font-size: 6.2pt;
        }

        .audit tr:last-child td {
            border-bottom: 0;
        }

        .audit td:first-child {
            width: 40%;
            color: #657782;
            background: #edf2f4;
            font-weight: bold;
        }

        .audit td:last-child {
            color: #2d4756;
            font-weight: bold;
            text-align: right;
        }

        .cancel-box {
            margin-top: 9px;
            padding: 5px 6px;
            border: 1px solid #cc7e8a;
            color: #892a3b;
            background: #fff0f2;
            font-size: 6.5pt;
        }

        .cancel-box strong {
            display: block;
            margin-bottom: 2px;
        }

        .signatures {
            margin-top: 34px;
        }

        .signatures td {
            width: 33.333%;
            padding: 3px 14px 0;
            border: 0;
            color: #506571;
            font-size: 6.3pt;
            text-align: center;
        }

        .signature-line {
            padding-top: 3px;
            border-top: 1px solid #526a77;
        }

        .footer {
            margin-top: 14px;
            padding-top: 3px;
            border-top: 1px solid #b0bcc3;
            color: #75848c;
            font-size: 5.8pt;
            text-align: center;
        }
    </style>
</head>

@php
$companyName = $adjustment->company?->company_name
?? $adjustment->company?->name
?? 'Company';
$storeName = $adjustment->store?->store_name
?? $adjustment->store?->name
?? 'Branch';
$companyAddress = $adjustment->company?->address
?? $adjustment->company?->billing_address
?? null;
$companyPhone = $adjustment->company?->phone
?? $adjustment->company?->mobile
?? null;
$companyGst = $adjustment->company?->gst_number
?? $adjustment->company?->gstin
?? null;
$items = $adjustment->items ?? collect();
$totalQuantity = (float) $items->sum('qty');
$totalCost = (float) $items->sum('total_cost');
$isIncrease = $adjustment->type === 'increase';
$isCompleted = $adjustment->status === 'Completed';
$isA5 = ($paperSize ?? 'a4') === 'a5';
@endphp

<body>
    <table class="header-table">
        <tr>
            <td class="company-cell">
                <div class="company-name">{{ $companyName }}</div>
                <div class="branch-name">{{ $storeName }}</div>

                @if ($companyAddress)
                <div class="address">{{ $companyAddress }}</div>
                @endif

                <div class="contact">
                    @if ($companyPhone)
                    <strong>Phone:</strong> {{ $companyPhone }}
                    @endif

                    @if ($companyPhone && $companyGst)
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    @endif

                    @if ($companyGst)
                    <strong>GSTIN:</strong> {{ $companyGst }}
                    @endif
                </div>
            </td>

            <td class="title-cell">
                <div class="document-box">
                    <div class="document-box-title">
                        Stock Adjustment
                    </div>

                    <table class="document-meta">
                        <tr>
                            <td>Number</td>
                            <td>{{ $adjustment->adjustment_no }}</td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td>
                                {{ optional($adjustment->adjustment_date)->format('d-m-Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>{{ $adjustment->status }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="overview">
        <tr>
            <td>
                <span>Adjustment Type</span>
                <strong class="{{ $isIncrease ? 'increase' : 'decrease' }}">
                    {{ $isIncrease ? 'Stock Increase' : 'Stock Decrease' }}
                </strong>
            </td>
            <td>
                <span>Total Items</span>
                <strong>{{ number_format($items->count()) }}</strong>
            </td>
            <td>
                <span>Total Quantity</span>
                <strong>{{ number_format($totalQuantity, 2) }}</strong>
            </td>
            <td>
                <span>Stock Value</span>
                <strong>₹{{ number_format($totalCost, 2) }}</strong>
            </td>
        </tr>
    </table>

    <div class="section-title">Stock Movement Details</div>

    <table class="items-table {{ $isA5 ? 'a5' : 'a4' }}">
        <colgroup>
            @if ($isA5)
            <col style="width: 5%;">
            <col style="width: 43%;">
            <col style="width: 12%;">
            <col style="width: 13%;">
            <col style="width: 12%;">
            <col style="width: 15%;">
            @else
            <col style="width: 4%;">
            <col style="width: 30%;">
            <col style="width: 12%;">
            <col style="width: 9%;">
            <col style="width: 10%;">
            <col style="width: 9%;">
            <col style="width: 12%;">
            <col style="width: 14%;">
            @endif
        </colgroup>

        <thead>
            <tr>
                <th class="serial">#</th>
                <th class="product">Product</th>

                @if (!$isA5)
                <th class="location">Location</th>
                @endif

                <th class="number">
                    <span>Before</span>
                </th>
                <th class="number">
                    <span>
                        @if ($isA5)
                        Adj.<br>Qty
                        @else
                        Adjusted
                        @endif
                    </span>
                </th>
                <th class="number">
                    <span>After</span>
                </th>

                @if (!$isA5)
                <th class="number">Unit Cost</th>
                @endif

                <th class="number">
                    <span>{{ $isA5 ? 'Amount' : 'Total' }}</span>
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($items as $index => $item)
            @php
            $product = $item->product;
            $location = $item->godown_id === null
            ? 'Shop'
            : ($item->godown?->godown_name ?? 'Godown');
            $productName = \Illuminate\Support\Str::limit(
            (string) ($product?->product_name ?? 'Unknown Product'),
            $isA5 ? 26 : 52
            );
            $productCode = \Illuminate\Support\Str::limit(
            (string) ($product?->product_code ?? '—'),
            $isA5 ? 14 : 24
            );
            $partNumber = \Illuminate\Support\Str::limit(
            (string) ($product?->part_number ?? '—'),
            22
            );
            $location = \Illuminate\Support\Str::limit(
            (string) $location,
            $isA5 ? 13 : 24
            );
            @endphp

            <tr>
                <td class="serial">{{ $index + 1 }}</td>
                <td>
                    <div class="product-name">
                        {{ $productName }}
                    </div>
                    <div class="product-meta">
                        {{ $productCode }}

                        @if (!$isA5)
                        &nbsp;|&nbsp; Part: {{ $partNumber }}
                        @endif
                    </div>

                    @if ($isA5)
                    <div class="a5-product-detail">
                        <strong>Location:</strong> {{ $location }}
                        &nbsp;|&nbsp;
                        <strong>Rate:</strong>
                        ₹{{ number_format((float) $item->unit_cost, 2) }}
                    </div>
                    @endif
                </td>

                @if (!$isA5)
                <td>
                    <span class="location-label">{{ $location }}</span>
                </td>
                @endif

                <td class="number">
                    {{ number_format((float) $item->stock_before, 2) }}
                </td>
                <td class="number {{ $isIncrease ? 'qty-in' : 'qty-out' }}">
                    {{ $isIncrease ? '+' : '−' }}{{ number_format((float) $item->qty, 2) }}
                </td>
                <td class="number stock-after">
                    {{ number_format((float) $item->stock_after, 2) }}
                </td>

                @if (!$isA5)
                <td class="number">
                    ₹{{ number_format((float) $item->unit_cost, 2) }}
                </td>
                @endif

                <td class="number line-total">
                    ₹{{ number_format((float) $item->total_cost, 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td
                    colspan="{{ $isA5 ? 6 : 8 }}"
                    style="padding: 14px; text-align: center;">
                    No adjustment items found.
                </td>
            </tr>
            @endforelse
        </tbody>

        @if ($items->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="{{ $isA5 ? 3 : 4 }}">
                    Document Total
                </td>
                <td class="number">
                    {{ number_format($totalQuantity, 2) }}
                </td>

                @if (!$isA5)
                <td colspan="2"></td>
                @else
                <td></td>
                @endif

                <td class="number">
                    ₹{{ number_format($totalCost, 2) }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>

    <table class="lower-table">
        <tr>
            <td>
                <div class="box">
                    <div class="box-title">Reason / Remarks</div>
                    <div class="remarks">{{ $adjustment->remarks ?: 'No remarks entered.' }}</div>
                </div>
            </td>

            <td>
                <div class="box">
                    <div class="box-title">Audit Information</div>
                    <table class="audit">
                        <tr>
                            <td>Created By</td>
                            <td>{{ $adjustment->creator?->name ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <td>Created At</td>
                            <td>
                                {{ optional($adjustment->created_at)->format('d-m-Y h:i A') }}
                            </td>
                        </tr>
                        <tr>
                            <td>Last Updated</td>
                            <td>
                                {{ optional($adjustment->updated_at)->format('d-m-Y h:i A') }}
                            </td>
                        </tr>

                        @if (!$isCompleted)
                        <tr>
                            <td>Cancelled By</td>
                            <td>
                                {{ $adjustment->cancelledBy?->name ?? 'System' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Cancelled At</td>
                            <td>
                                {{ optional($adjustment->cancelled_at)->format('d-m-Y h:i A') }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    @if (!$isCompleted)
    <div class="cancel-box">
        <strong>Cancelled Adjustment</strong>
        {{ $adjustment->cancel_reason ?: 'No cancellation reason recorded.' }}
    </div>
    @endif

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line">Prepared By</div>
            </td>
            <td>
                <div class="signature-line">Verified By</div>
            </td>
            <td>
                <div class="signature-line">Authorised Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Computer-generated stock adjustment document.
        Stock movements are recorded in the inventory ledger.
    </div>
</body>

</html>