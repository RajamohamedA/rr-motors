<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Invoice {{ $sale->invoice_no }}</title>

    <style>
        @page {
            margin: {
                    {
                    ($paper ?? 'a4')==='a5' ? '7mm': '10mm'
                }
            }

            ;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #172033;
            font-family: DejaVu Sans, sans-serif;

            font-size: {
                    {
                    ($paper ?? 'a4')==='a5' ? '9px': '10px'
                }
            }

            ;
            line-height: 1.35;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: top;
        }

        .company-name {
            margin: 0 0 4px;
            color: #0c548c;

            font-size: {
                    {
                    ($paper ?? 'a4')==='a5' ? '18px': '22px'
                }
            }

            ;
            font-weight: bold;
            text-transform: uppercase;
        }

        .company-line {
            margin: 1px 0;
            color: #3f5063;
        }

        .invoice-heading {
            color: #17324d;

            font-size: {
                    {
                    ($paper ?? 'a4')==='a5' ? '16px': '20px'
                }
            }

            ;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
        }

        .bill-type {
            display: inline-block;
            margin: 5px 0;
            padding: 3px 8px;
            color: #fff;
            background: #0c609e;
            font-size: 8px;
            font-weight: bold;
        }

        .invoice-meta {
            margin-left: auto;
            width: 75%;
        }

        .invoice-meta td {
            padding: 2px 0 2px 7px;
            text-align: right;
        }

        .invoice-meta td:first-child {
            color: #64748b;
        }

        .blue-rule {
            margin: 8px 0;
            border: 0;
            border-top: 3px solid #1769aa;
        }

        .party-table {
            margin-bottom: 9px;
        }

        .party-table td {
            width: 50%;
            padding: 7px;
            border: 1px solid #bdc8d4;
            vertical-align: top;
        }

        .party-title {
            margin-bottom: 4px;
            color: #607086;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .party-name {
            margin-bottom: 3px;
            color: #172033;
            font-size: 11px;
            font-weight: bold;
        }

        .party-line {
            margin: 1px 0;
            color: #405166;
        }

        .items {
            table-layout: fixed;
        }

        .items th {
            padding: 5px 4px;
            border: 1px solid #0d568e;
            color: #fff;
            background: #1769aa;
            font-size: 8px;
            text-transform: uppercase;
        }

        .items td {
            padding: 5px 4px;
            border: 1px solid #ccd5df;
            vertical-align: top;
        }

        .items tbody tr:nth-child(even) {
            background: #f4f7fa;
        }

        .serial {
            width: 5%;
            text-align: center;
        }

        .product-column {
            width: {
                    {
                    ($sale->billing_type ?? 'gst')==='gst' ? '35%': '43%'
                }
            }

            ;
        }

        .quantity-column {
            width: 9%;
        }

        .rate-column {
            width: 14%;
        }

        .discount-column {
            width: 13%;
        }

        .gst-column {
            width: 11%;
        }

        .amount-column {
            width: 16%;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .product-name {
            display: block;
            font-weight: bold;
        }

        .product-meta,
        .item-note {
            display: block;
            margin-top: 2px;
            color: #68778a;
            font-size: 8px;
        }

        .bottom-table {
            margin-top: 10px;
        }

        .bottom-table>tbody>tr>td {
            width: 50%;
            vertical-align: top;
        }

        .notes {
            padding-right: 12px;
            color: #526175;
        }

        .notes-box {
            min-height: 55px;
            padding: 7px;
            border: 1px solid #ccd5df;
        }

        .notes-title {
            margin-bottom: 4px;
            color: #344256;
            font-weight: bold;
        }

        .totals td {
            padding: 4px 6px;
            border-bottom: 1px dotted #b6c0cc;
        }

        .totals td:last-child {
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
        }

        .totals .grand td {
            padding: 7px 6px;
            border: 0;
            color: #fff;
            background: #0d568e;
            font-size: 13px;
            font-weight: bold;
        }

        .totals .balance td {
            font-size: 11px;
            font-weight: bold;
        }

        .tax-heading {
            margin: 11px 0 4px;
            color: #344256;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .tax-table th,
        .tax-table td {
            padding: 4px;
            border: 1px solid #b9c4d0;
            font-size: 8px;
            text-align: right;
        }

        .tax-table th {
            color: #344256;
            background: #edf2f6;
        }

        .tax-table th:first-child,
        .tax-table td:first-child {
            text-align: left;
        }

        .footer {
            margin-top: 18px;
            padding-top: 7px;
            border-top: 1px solid #ccd5df;
            color: #6b7889;
            font-size: 8px;
            text-align: center;
        }
    </style>
</head>

<body>
    @php
    $isGstBill = ($sale->billing_type ?? 'gst') === 'gst';
    $company = $sale->company;
    $store = $sale->store;
    $customer = $sale->customer;
    $items = $sale->items ?? collect();

    $totalQuantity = $items->sum(
    fn ($item) =>
    (float) $item->quantity
    + (float) $item->free_quantity
    );

    $taxSummary = $items
    ->groupBy(
    fn ($item) => number_format(
    (float) $item->gst_percent,
    2,
    '.',
    ''
    )
    )
    ->map(function ($group, $rate) {
    $taxable = $group->sum(function ($item) {
    $gross =
    (float) $item->quantity
    * (float) $item->sale_price;

    return max(
    0,
    $gross - (float) $item->discount
    );
    });

    $gst = $group->sum(
    fn ($item) => (float) $item->gst_amount
    );

    return [
    'rate' => (float) $rate,
    'taxable' => $taxable,
    'cgst' => $gst / 2,
    'sgst' => $gst / 2,
    'gst' => $gst,
    ];
    });
    @endphp

    <table class="header">
        <tr>
            <td>
                <div class="company-name">
                    {{ $company->company_name ?? 'RR Motors' }}
                </div>

                @if($store?->store_name)
                <div class="company-line">
                    <strong>{{ $store->store_name }}</strong>
                </div>
                @endif

                @if($store?->address || $store?->city || $store?->pincode)
                <div class="company-line">
                    {{ $store->address }}
                    {{ $store->city ? ', ' . $store->city : '' }}
                    {{ $store->pincode ? ' - ' . $store->pincode : '' }}
                </div>
                @endif

                @if($store?->phone ?? $company?->phone)
                <div class="company-line">
                    Phone: {{ $store->phone ?? $company->phone }}
                </div>
                @endif

                @if($isGstBill && $company?->gst_number)
                <div class="company-line">
                    <strong>GSTIN: {{ $company->gst_number }}</strong>
                </div>
                @endif
            </td>

            <td>
                <div class="invoice-heading">
                    {{ $isGstBill ? 'Tax Invoice' : 'Invoice' }}
                </div>

                <div style="text-align: right">
                    <span class="bill-type">
                        {{ $isGstBill ? 'GST BILL' : 'WITHOUT GST' }}
                    </span>
                </div>

                <table class="invoice-meta">
                    <tr>
                        <td>Invoice</td>
                        <td><strong>{{ $sale->invoice_no }}</strong></td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>
                            {{ optional($sale->invoice_date)->format('d M Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>{{ $sale->payment_status }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <hr class="blue-rule">

    <table class="party-table">
        <tr>
            <td>
                <div class="party-title">Bill To</div>
                <div class="party-name">
                    {{ $customer->customer_name ?? 'Customer' }}
                </div>

                @if($customer?->customer_code)
                <div class="party-line">
                    Code: {{ $customer->customer_code }}
                </div>
                @endif

                @if($customer?->mobile ?? $customer?->phone)
                <div class="party-line">
                    Phone: {{ $customer->mobile ?? $customer->phone }}
                </div>
                @endif

                @if($isGstBill && $customer?->gst_number)
                <div class="party-line">
                    GSTIN: {{ $customer->gst_number }}
                </div>
                @endif

                @if($customer?->billing_address)
                <div class="party-line">
                    {{ $customer->billing_address }}
                </div>
                @endif
            </td>

            <td>
                <div class="party-title">Sale Information</div>
                <div class="party-name">
                    {{ $sale->sale_source === 'mechanic'
                        ? 'Mechanic Referral'
                        : 'Direct Customer' }}
                </div>

                @if($sale->mechanic)
                <div class="party-line">
                    Mechanic: {{ $sale->mechanic->mechanic_name }}
                </div>
                @endif

                <div class="party-line">
                    Items: {{ $items->count() }}
                </div>

                <div class="party-line">
                    Quantity: {{ number_format($totalQuantity, 2) }}
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="serial">#</th>
                <th class="product-column">Product</th>
                <th class="quantity-column number">Qty</th>
                <th class="rate-column number">Rate</th>
                <th class="discount-column number">Discount</th>

                @if($isGstBill)
                <th class="gst-column number">GST</th>
                @endif

                <th class="amount-column number">Amount</th>
            </tr>
        </thead>

        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td class="serial">{{ $index + 1 }}</td>
                <td>
                    <span class="product-name">
                        {{ $item->product->product_name ?? 'Product' }}
                    </span>
                    <span class="product-meta">
                        {{ $item->product->product_code ?? '' }}
                        {{ $item->product?->part_number
                                ? ' / ' . $item->product->part_number
                                : '' }}
                    </span>

                    @if((float) $item->free_quantity > 0)
                    <span class="item-note">
                        Free: {{ number_format((float) $item->free_quantity, 2) }}
                    </span>
                    @endif
                </td>
                <td class="number">
                    {{ number_format((float) $item->quantity, 2) }}
                </td>
                <td class="number">
                    {{ number_format((float) $item->sale_price, 2) }}
                </td>
                <td class="number">
                    {{ number_format((float) $item->discount, 2) }}
                    @if($item->discount_type === 'percentage')
                    <span class="item-note">
                        {{ number_format((float) $item->discount_value, 2) }}%
                    </span>
                    @endif
                </td>

                @if($isGstBill)
                <td class="number">
                    {{ number_format((float) $item->gst_percent, 2) }}%
                    <span class="item-note">
                        {{ number_format((float) $item->gst_amount, 2) }}
                    </span>
                </td>
                @endif

                <td class="number">
                    <strong>
                        {{ number_format((float) $item->line_total, 2) }}
                    </strong>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="bottom-table">
        <tr>
            <td class="notes">
                <div class="notes-box">
                    <div class="notes-title">Remarks</div>
                    {{ $sale->remarks ?: 'Thank you for your business.' }}
                </div>
            </td>

            <td>
                <table class="totals">
                    <tr>
                        <td>
                            {{ $isGstBill ? 'Taxable Amount' : 'Subtotal' }}
                        </td>
                        <td>
                            {{ number_format((float) $sale->sub_total, 2) }}
                        </td>
                    </tr>

                    @if($isGstBill)
                    <tr>
                        <td>GST Total</td>
                        <td>
                            {{ number_format((float) $sale->gst_total, 2) }}
                        </td>
                    </tr>
                    @endif

                    @if((float) $sale->final_discount > 0)
                    <tr>
                        <td>Discount</td>
                        <td>
                            -{{ number_format((float) $sale->final_discount, 2) }}
                        </td>
                    </tr>
                    @endif

                    @if((float) $sale->round_off !== 0.0)
                    <tr>
                        <td>Round Off</td>
                        <td>
                            {{ (float) $sale->round_off >= 0 ? '+' : '-' }}
                            {{ number_format(abs((float) $sale->round_off), 2) }}
                        </td>
                    </tr>
                    @endif

                    <tr class="grand">
                        <td>Net Amount</td>
                        <td>
                            Rs.{{ number_format((float) $sale->grand_total, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td>Paid Amount</td>
                        <td>
                            Rs.{{ number_format((float) $sale->paid_amount, 2) }}
                        </td>
                    </tr>
                    <tr class="balance">
                        <td>Due Balance</td>
                        <td>
                            Rs.{{ number_format((float) $sale->balance_amount, 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if($isGstBill && (float) $sale->gst_total > 0)
    <div class="tax-heading">GST Summary</div>
    <table class="tax-table">
        <thead>
            <tr>
                <th>Tax Rate</th>
                <th>Taxable Amount</th>
                <th>CGST</th>
                <th>SGST</th>
                <th>Total GST</th>
            </tr>
        </thead>
        <tbody>
            @foreach($taxSummary as $tax)
            <tr>
                <td>{{ number_format($tax['rate'], 2) }}%</td>
                <td>{{ number_format($tax['taxable'], 2) }}</td>
                <td>{{ number_format($tax['cgst'], 2) }}</td>
                <td>{{ number_format($tax['sgst'], 2) }}</td>
                <td>{{ number_format($tax['gst'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        Thank you for your business. This is a computer-generated invoice.
    </div>
</body>

</html>