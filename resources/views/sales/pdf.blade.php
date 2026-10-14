<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $sale->invoice_no }}</title>
    @php
    $isGstBill = ($sale->billing_type ?? 'gst') === 'gst';
    $smallWords = ['Zero','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
    $tensWords = ['', '', 'Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    $integerWords = function ($number) use (&$integerWords, $smallWords, $tensWords) {
    $number = (int) $number;
    if ($number < 20) return $smallWords[$number];
        if ($number < 100) return $tensWords[intdiv($number, 10)] . ($number % 10 ? ' ' . $smallWords[$number % 10] : '' );
        foreach ([10000000=> 'Crore', 100000 => 'Lakh', 1000 => 'Thousand', 100 => 'Hundred'] as $divisor => $label) {
        if ($number >= $divisor) return $integerWords(intdiv($number, $divisor)) . ' ' . $label . ($number % $divisor ? ' ' . $integerWords($number % $divisor) : '');
        }
        return '';
        };
        $totalPaise = (int) round(abs((float) $sale->grand_total) * 100);
        $amountWords = ((float) $sale->grand_total < 0 ? 'Minus ' : '' ) . 'INR ' . $integerWords(intdiv($totalPaise, 100))
            . ($totalPaise % 100 ? ' and ' . $integerWords($totalPaise % 100) . ' Paise' : '' ) . ' Only' ;
            $company=$sale->company;
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
            $taxable = $group->sum(fn ($item) => round(
            (float) $item->line_total - (float) $item->gst_amount, 2
            ));
            $gst = $group->sum(
            fn ($item) => (float) $item->gst_amount
            );
            return [
            'rate' => (float) $rate,
            'taxable' => $taxable,
            'gst' => $gst,
            ];
            });
            $isA5 = strtolower($paper ?? 'a4') === 'a5';
            // Dompdf does not run the browser's JavaScript page-fitting function.
            // Reserve a ruled blank area for short invoices, decreasing as items grow.
            $rowHeightMm = $items->sum(function ($item) use ($isA5) {
            $charactersPerLine = $isA5 ? 19 : 31;
            $lines = max(1, (int) ceil(mb_strlen($item->product?->product_name ?? 'Product') / $charactersPerLine));
            $lines += $item->product?->product_code ? 1 : 0;
            $lines += (float) $item->free_quantity > 0 ? 1 : 0;
            $lines += (float) $item->discount > 0 ? 1 : 0;
            return $lines * ($isA5 ? 3.3 : 3.8) + 3;
            });
            $headerExtraLines = max(0, (int) ceil(mb_strlen(($store?->address ?? '') . ' ' . ($store?->city ?? '')) / ($isA5 ? 38 : 55)) - 1);
            $headerExtraLines += max(0, (int) ceil(mb_strlen($customer?->billing_address ?? '') / ($isA5 ? 38 : 55)) - 1);
            $headerExtraLines += max(0, (int) ceil(mb_strlen($sale->remarks ?? '') / ($isA5 ? 22 : 35)) - 1);
            $fillHeightMm = max(0, ($isA5 ? 92 : 165) - $rowHeightMm - $headerExtraLines * 4);
            @endphp
            <style>
                @page invoice-a4 {
                    margin: 10mm;
                }

                @page invoice-a5 {
                    margin: 7mm;
                }

                body.a4 {
                    page: invoice-a4;
                }

                body.a5 {
                    page: invoice-a5;
                }

                body {
                    margin: 0;
                    font-family: DejaVu Sans, sans-serif;
                    font-size: 10px;
                    line-height: 1.3;
                    color: #000;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    table-layout: fixed;
                }

                .classic-title {
                    margin: 0 0 6px;
                    text-align: center;
                    font-size: 14px;
                    letter-spacing: .6px;
                }

                .classic-header {
                    border: 1px solid #555;
                }

                .classic-header>tbody>tr>td {
                    border: 1px solid #555;
                    vertical-align: top;
                }

                .classic-party {
                    width: 58%;
                    padding: 5px 7px;
                    word-wrap: break-word;
                }

                .classic-party strong,
                .classic-caption {
                    display: block;
                }

                .classic-business {
                    font-size: 14px;
                    text-transform: uppercase;
                }

                .classic-caption {
                    margin-bottom: 2px;
                    font-size: 9px;
                }

                .classic-invoice-meta {
                    padding: 0;
                }

                .classic-invoice-meta td {
                    border-bottom: 1px solid #777;
                    border-right: 1px solid #777;
                    padding: 4px 5px;
                    vertical-align: top;
                    word-wrap: break-word;
                }

                .classic-invoice-meta td:last-child {
                    border-right: 0;
                }

                .classic-invoice-meta tr:last-child td {
                    border-bottom: 0;
                }

                .classic-invoice-meta span,
                .classic-invoice-meta strong {
                    display: block;
                }

                .classic-invoice-meta strong {
                    margin-top: 2px;
                }

                .classic-items {
                    border: 1px solid #555;
                    margin-top: -1px;
                }

                .classic-items th {
                    border: 1px solid #555;
                    padding: 5px 3px;
                    font-weight: normal;
                    font-size: 9px;
                }

                .classic-items tbody td {
                    border-right: 1px solid #777;
                    padding: 4px 3px;
                    vertical-align: top;
                    word-wrap: break-word;
                }

                .c-serial {
                    width: 4%;
                    text-align: center;
                }

                .c-description {
                    text-align: left;
                }

                .c-description small {
                    display: block;
                    font-size: 8px;
                    margin-top: 2px;
                }

                .c-hsn {
                    width: 11%;
                    text-align: center;
                }

                .c-qty {
                    width: 9%;
                    text-align: right;
                }

                .c-rate {
                    width: 13%;
                    text-align: right;
                }

                .c-unit {
                    width: 7%;
                    text-align: center;
                }

                .c-amount {
                    width: 18%;
                    text-align: right;
                }

                .gst-invoice .c-hsn {
                    width: 9%;
                }

                .gst-invoice .c-qty {
                    width: 7%;
                }

                .gst-invoice .c-rate {
                    width: 11%;
                }

                .gst-invoice .c-unit {
                    width: 5%;
                }

                .c-gst-percent {
                    width: 8%;
                    text-align: right;
                }

                .c-gst {
                    width: 11%;
                    text-align: right;
                }

                .gst-invoice .c-amount {
                    width: 14%;
                }

                .classic-items tbody .classic-fill-row td {
                    padding: 0;
                    font-size: 0;
                    line-height: 0;
                }

                .classic-items tfoot td {
                    border: 1px solid #777;
                    padding: 6px 5px;
                }

                .classic-items tfoot {
                    display: table-row-group;
                }

                .classic-compact-total {
                    text-align: left;
                }

                .classic-compact-total>span {
                    display: inline-block;
                    margin-right: 7px;
                    font-size: 9px;
                }

                .classic-total-word {
                    float: right;
                    font-size: 11px;
                }

                .classic-compact-amount {
                    text-align: right;
                    font-weight: bold;
                    font-size: 13px;
                }

                .classic-grand td {
                    border-top: 2px solid #333 !important;
                    border-bottom: 2px solid #333 !important;
                }

                .classic-payment-strip {
                    border: 1px solid #555;
                    margin-top: -1px;
                }

                .classic-payment-strip td {
                    padding: 5px 6px;
                    font-size: 9px;
                }

                .number {
                    text-align: right;
                }

                .classic-bottom {
                    border: 1px solid #555;
                    margin-top: -1px;
                }

                .classic-bottom td {
                    padding: 6px 7px;
                    width: 55%;
                    vertical-align: top;
                }

                .classic-bottom td:last-child {
                    width: 45%;
                    border-left: 1px solid #777;
                }

                .classic-bottom strong,
                .classic-bottom span {
                    display: block;
                }

                .classic-bottom strong {
                    margin-top: 4px;
                }

                .classic-signature {
                    text-align: right;
                }

                .signature-space {
                    height: 12mm;
                }

                .classic-footer {
                    text-align: center;
                    margin: 5px 0 0;
                    font-size: 9px;
                }

                .classic-items tbody tr,
                .classic-items tfoot tr,
                .classic-bottom,
                .classic-payment-strip {
                    page-break-inside: avoid;
                }

                thead {
                    display: table-header-group;
                }



                /* A5 typography overrides */
                body.a5 {
                    font-size: 8px;
                }

                body.a5 .classic-title {
                    font-size: 11px;
                }

                body.a5 .classic-business {
                    font-size: 11px;
                }

                body.a5 .classic-caption {
                    font-size: 8px;
                }

                body.a5 .classic-items th {
                    font-size: 7px;
                }

                body.a5 .c-description small {
                    font-size: 7px;
                }

                body.a5 .classic-compact-total>span {
                    font-size: 7px;
                }

                body.a5 .classic-total-word {
                    font-size: 9px;
                }

                body.a5 .classic-compact-amount {
                    font-size: 10px;
                }

                body.a5 .classic-payment-strip td {
                    font-size: 7px;
                }

                body.a5 .signature-space {
                    height: 9mm;
                }

                body.a5 .classic-footer {
                    font-size: 7px;
                }
            </style>
</head>

<body class="{{ $isA5 ? 'a5' : 'a4' }}">
    <div class="classic-invoice {{ $isGstBill ? 'gst-invoice' : 'cash-invoice' }} {{ $isA5 ? 'a5' : 'a4' }}">
        <h1 class="classic-title">{{ $isGstBill ? 'TAX INVOICE' : 'CASH BILL' }}</h1>
        <table class="classic-header">
            <tbody>
                <tr>
                    <td class="classic-party">
                        <strong class="classic-business">{{ $company->company_name ?? 'Company' }}</strong>
                        @if($store?->store_name)<div>{{ $store->store_name }}</div>@endif
                        <div>{{ $store?->address }} {{ $store?->city }} {{ $store?->state }} {{ $store?->pincode }}</div>
                        @if($store?->phone ?? $company?->phone)<div>Phone: {{ $store->phone ?? $company->phone }}</div>@endif
                        @if($isGstBill && $company?->gst_number)<div>GSTIN: {{ $company->gst_number }}</div>@endif
                    </td>
                    <td class="classic-invoice-meta" rowspan="2">
                        <table>
                            <tbody>
                                <tr>
                                    <td><span>Invoice No.</span><strong>{{ $sale->invoice_no }}</strong></td>
                                    <td><span>Dated</span><strong>{{ optional($sale->invoice_date)->format('d-M-Y') }}</strong></td>
                                </tr>
                                <tr>
                                    <td><span>Payment Status</span><strong>{{ $sale->payment_status ?? '—' }}</strong></td>
                                    <td><span>Bill Type</span><strong>{{ $isGstBill ? 'GST' : 'Cash Bill' }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><span>Remarks</span>
                                        <div>{{ $sale->remarks ?: '—' }}</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="classic-party">
                        <span class="classic-caption">Buyer (Bill to)</span>
                        <strong>{{ $customer?->customer_name ?? 'Walk-in Customer' }}</strong>
                        @if($customer?->billing_address)<div>{{ $customer->billing_address }}</div>@endif
                        <div>{{ $customer?->city }} {{ $customer?->state }} {{ $customer?->pincode }}</div>
                        @if($customer?->mobile ?? $customer?->phone)<div>Phone: {{ $customer->mobile ?? $customer->phone }}</div>@endif
                        @if($isGstBill && $customer?->gst_number)<div>GSTIN: {{ $customer->gst_number }}</div>@endif
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="classic-items">
            <thead>
                <tr>
                    <th class="c-serial">Sl.</th>
                    <th class="c-description">Description of Goods</th>
                    <th class="c-hsn">HSN/SAC</th>
                    <th class="c-qty">Qty</th>
                    <th class="c-rate">Rate{{ $isGstBill ? ' excl.' : '' }}</th>
                    <th class="c-unit">Per</th>
                    @if($isGstBill)<th class="c-gst-percent">GST %</th>
                    <th class="c-gst">GST Amt</th>@endif
                    <th class="c-amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                <tr>
                    <td class="c-serial">{{ $index + 1 }}</td>
                    <td class="c-description"><strong>{{ $item->product?->product_name ?? 'Product' }}</strong>
                        @if($item->product?->product_code)<small>{{ $item->product->product_code }}</small>@endif
                        @if((float) $item->free_quantity > 0)<small>Free: {{ number_format((float) $item->free_quantity, 2) }}</small>@endif
                        @if((float) $item->discount > 0)<small>Discount: {{ $item->discount_type === 'percentage' ? number_format((float) $item->discount_value, 2) . '%' : number_format((float) $item->discount, 2) }}{{ $isGstBill && $item->discount_type !== 'percentage' ? ' excl. GST' : '' }}</small>@endif
                    </td>
                    <td class="c-hsn">{{ $item->product?->hsn_code ?? $item->product?->hsn_sac ?? '—' }}</td>
                    <td class="c-qty">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="c-rate">{{ number_format((float) $item->sale_price, 2) }}</td>
                    <td class="c-unit">{{ $item->product?->unit?->short_name ?? '—' }}</td>
                    @if($isGstBill)<td class="c-gst-percent">{{ number_format((float) $item->gst_percent, 2) }}%</td>
                    <td class="c-gst">{{ number_format((float) $item->gst_amount, 2) }}</td>@endif
                    <td class="c-amount"><strong>{{ number_format((float) $item->line_total, 2) }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isGstBill ? 9 : 7 }}">No items</td>
                </tr>
                @endforelse
                <tr class="classic-fill-row" aria-hidden="true" style="height: {{ $fillHeightMm }}mm">
                    <td style="height: {{ $fillHeightMm }}mm;"></td>
                    <td style="height: {{ $fillHeightMm }}mm;"></td>
                    <td style="height: {{ $fillHeightMm }}mm;"></td>
                    <td style="height: {{ $fillHeightMm }}mm;"></td>
                    <td style="height: {{ $fillHeightMm }}mm;"></td>
                    <td style="height: {{ $fillHeightMm }}mm;"></td>
                    @if($isGstBill)<td style="height: {{ $fillHeightMm }}mm;"></td>
                    <td style="height: {{ $fillHeightMm }}mm;"></td>@endif
                    <td style="height: {{ $fillHeightMm }}mm;"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="classic-grand">
                    <td colspan="{{ $isGstBill ? 8 : 6 }}" class="classic-compact-total">
                        <span class="classic-count">{{ $items->count() }} items · Qty {{ number_format($totalQuantity, 2) }}</span>
                        @if($isGstBill)<span>Taxable {{ number_format((float) $sale->sub_total, 2) }} · GST {{ number_format((float) $sale->gst_total, 2) }}</span>@endif
                        @if((float) $sale->final_discount > 0)<span>Discount −{{ number_format((float) $sale->final_discount, 2) }}</span>@endif
                        @if((float) $sale->round_off != 0)<span>Round {{ number_format((float) $sale->round_off, 2) }}</span>@endif
                        <strong class="classic-total-word">TOTAL Rs.</strong>
                    </td>
                    <td class="classic-compact-amount">{{ number_format((float) $sale->grand_total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        <table class="classic-payment-strip">
            <tr>
                <td>Paid Amount</td>
                <td class="number"><strong>Rs. {{ number_format((float) $sale->paid_amount, 2) }}</strong></td>
                <td>Due Balance</td>
                <td class="number"><strong>Rs. {{ number_format((float) $sale->balance_amount, 2) }}</strong></td>
            </tr>
        </table>
        <table class="classic-bottom">
            <tr>
                <td><span>Amount Chargeable (in words)</span><strong>{{ $amountWords }}</strong></td>
                <td class="classic-signature"><strong>For {{ $company->company_name ?? 'Company' }}</strong>
                    <div class="signature-space"></div><span>Authorised Signatory</span>
                </td>
            </tr>
        </table>
        <p class="classic-footer">This is a computer-generated invoice.</p>
    </div>

</body>

</html>