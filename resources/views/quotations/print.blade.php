@php
$pdfMode = $pdfMode ?? false;
$paper = strtolower($paper ?? request()->query('paper', 'a4'));
$paper = in_array($paper, ['a4', 'a5'], true) ? $paper : 'a4';
$isA5 = $paper === 'a5';
$isGstBill = ($quotation->billing_type ?? 'gst') === 'gst';
$company = $quotation->company;
$store = $quotation->store;
$customer = $quotation->customer;
$items = $quotation->items ?? collect();
$title = $quotation->documentTitle();
$columnCount = $isGstBill ? 7 : 5;
// PDF uses a conservative height estimate; browser printing measures actual content.
$rowHeight = $items->sum(function ($item) use ($isA5) {
$lines = max(1, (int) ceil(mb_strlen($item->product?->product_name ?? '') / ($isA5 ? 28 : 42)));
$details = ($item->product?->part_number ? 1 : 0)
+ ((float) $item->free_quantity > 0 ? 1 : 0)
+ ((float) $item->discount_amount > 0 ? 1 : 0);
return ($isA5 ? 3.3 : 4.2) * $lines + ($isA5 ? 2.5 : 3) * $details + 3;
});
$addressText = trim(($store?->address ?? '') . ' ' . ($store?->city ?? '') . ' ' . ($store?->state ?? '') . ' ' . ($store?->pincode ?? ''));
$extraHeader = max(0, (int) ceil(mb_strlen($addressText) / ($isA5 ? 42 : 65)) - 1) * 4;
$termsHeight = $quotation->terms_conditions ? 7 + ceil(mb_strlen($quotation->terms_conditions) / ($isA5 ? 82 : 120)) * 4 : 0;
$pdfFrameTop = ($isA5 ? 31 : 33) + $extraHeader;
$pdfFrameHeight = max(0, ($isA5 ? 196 : 283) - $pdfFrameTop - 28);
$pdfFixedPage = $pdfMode && $rowHeight < ($isA5 ? 85 : 165)
    && mb_strlen($quotation->terms_conditions ?? '') <= 200;
        $fillHeight=max(0, ($isA5 ? 181 : 267) - ($isA5 ? 64 : 68) - $rowHeight - $extraHeader - $termsHeight);
        $smallWords=['Zero','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
        $tensWords=['', '' , 'Twenty' ,'Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        $integerWords=function ($number) use (&$integerWords, $smallWords, $tensWords) {
        $number=(int) $number;
        if ($number < 20) return $smallWords[$number];
        if ($number < 100) return $tensWords[intdiv($number, 10)] . ($number % 10 ? ' ' . $smallWords[$number % 10] : '' );
        foreach ([10000000=> 'Crore', 100000 => 'Lakh', 1000 => 'Thousand', 100 => 'Hundred'] as $divisor => $label) {
        if ($number >= $divisor) return $integerWords(intdiv($number, $divisor)) . ' ' . $label . ($number % $divisor ? ' ' . $integerWords($number % $divisor) : '');
        }
        return '';
        };
        $totalPaise = (int) round(abs((float) $quotation->grand_total) * 100);
        $amountWords = ((float) $quotation->grand_total < 0 ? 'Minus ' : '' ) . 'INR ' . $integerWords(intdiv($totalPaise, 100))
            . ($totalPaise % 100 ? ' and ' . $integerWords($totalPaise % 100) . ' Paise' : '' ) . ' Only' ;
            @endphp
            <!doctype html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width,initial-scale=1">
                <title>{{ $title }} {{ $quotation->quotation_no }}</title>
                @if ($isA5)
                <style id="page-size-style">
                    @page {
                        size: A5 portrait;
                        margin: 7mm;
                    }
                </style>
                @else
                <style id="page-size-style">
                    @page {
                        size: A4 portrait;
                        margin: 7mm;
                    }
                </style>
                @endif
                <style>
                    * {
                        box-sizing: border-box;
                    }

                    body {
                        margin: 0;
                        color: #111;
                        background: #e9eef4;
                        font: 12px Arial, DejaVu Sans, sans-serif;
                    }

                    .toolbar {
                        display: flex;
                        flex-wrap: wrap;
                        justify-content: center;
                        gap: 7px;
                        padding: 10px;
                        background: white;
                        border-bottom: 1px solid #c8d4e0;
                    }

                    .toolbar a,
                    .toolbar button {
                        display: inline-block;
                        padding: 8px 13px;
                        border: 1px solid #b8cbe0;
                        border-radius: 5px;
                        color: #23496f;
                        background: #f3f7fd;
                        text-decoration: none;
                        cursor: pointer;
                        font: 600 12px Arial, sans-serif;
                    }

                    .toolbar .primary {
                        color: white;
                        background: #225e96;
                        border-color: #225e96;
                    }

                    .paper {
                        width: 210mm;
                        margin: 15px auto;
                        padding: 7mm;
                        background: white;
                        box-shadow: 0 5px 22px #18385720;
                    }

                    body.a5 .paper {
                        width: 148mm;
                        padding: 5mm;
                    }

                    .document-title {
                        text-align: center;
                        font-size: 18px;
                        margin: 0 0 9px;
                        text-transform: uppercase;
                        letter-spacing: .7px;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        table-layout: fixed;
                    }

                    .top {
                        border: 1px solid #666;
                    }

                    .top td {
                        padding: 9px 11px;
                        vertical-align: top;
                    }

                    .seller {
                        width: 65%;
                        border-right: 1px solid #777;
                        line-height: 1.4;
                    }

                    .seller strong {
                        display: block;
                        font-size: 18px;
                        text-transform: uppercase;
                    }

                    .meta {
                        line-height: 1.7;
                        font-size: 12px;
                    }

                    .meta strong {
                        font-size: 14px;
                    }

                    .buyer {
                        border: 1px solid #777;
                        border-top: 0;
                        padding: 7px 11px;
                        font-size: 11px;
                        line-height: 1.5;
                    }

                    .items {
                        border: 1px solid #777;
                        border-top: 0;
                    }

                    .items th {
                        padding: 9px 6px;
                        border: 1px solid #777;
                        border-top: 0;
                        font-size: 12px;
                        text-align: left;
                    }

                    .items td {
                        border-right: 1px solid #999;
                        padding: 6px;
                        vertical-align: top;
                        line-height: 1.4;
                    }

                    .serial {
                        width: 5%;
                        text-align: center !important;
                    }

                    .qty {
                        width: 14%;
                        text-align: right !important;
                    }

                    .rate {
                        width: 14%;
                        text-align: right !important;
                    }

                    .amount {
                        width: 18%;
                        text-align: right !important;
                    }

                    .gst-rate {
                        width: 8%;
                        text-align: right !important;
                    }

                    .gst-amount {
                        width: 12%;
                        text-align: right !important;
                    }

                    .gst .qty {
                        width: 10%;
                    }

                    .gst .rate {
                        width: 12%;
                    }

                    .gst .amount {
                        width: 16%;
                    }

                    .name {
                        font-weight: 600;
                        font-size: 12px;
                    }

                    .detail {
                        font-size: 10px;
                        color: #333;
                        margin-top: 3px;
                    }

                    .items .subtotal td {
                        border-top: 1px solid #888;
                        font-weight: bold;
                        padding-top: 8px;
                    }

                    .items .fill td {
                        padding: 0;
                        font-size: 0;
                        line-height: 0;
                    }

                    .fill-block {
                        display: block;
                        margin: 0;
                        padding: 0;
                        font-size: 1px;
                        line-height: 1px;
                    }

                    .items .grand td {
                        border-top: 1px solid #666;
                        border-bottom: 1px solid #666;
                        padding: 10px 7px;
                        font-size: 16px;
                        font-weight: bold;
                        vertical-align: middle;
                    }

                    .grand-label {
                        text-align: center;
                        font-style: italic;
                    }

                    .words {
                        padding: 10px;
                        border: 1px solid #777;
                        border-top: 0;
                        font-size: 13px;
                        font-weight: 700;
                    }

                    .notes {
                        border: 1px solid #777;
                        border-top: 0;
                        padding: 8px 10px;
                        line-height: 1.5;
                        font-size: 10px;
                    }

                    .footer {
                        display: table;
                        width: 100%;
                        margin-top: 8px;
                        font-size: 10px;
                    }

                    .footer span {
                        display: table-cell;
                    }

                    .footer span:last-child {
                        text-align: right;
                    }

                    body.a5 {
                        font-size: 10px;
                    }

                    body.a5 .seller strong {
                        font-size: 14px;
                    }

                    body.a5 .items th,
                    body.a5 .name {
                        font-size: 10px;
                    }

                    body.a5 .items td {
                        padding: 5px;
                    }

                    body.a5 .detail {
                        font-size: 8px;
                    }

                    body.a5 .items .grand td {
                        font-size: 13px;
                    }

                    body.a5 .words {
                        font-size: 11px;
                    }

                    body.a5 .meta {
                        font-size: 10px;
                    }

                    body.a5 .meta strong {
                        font-size: 11px;
                    }

                    body.pdf-mode {
                        background: white;
                    }

                    body.pdf-mode .paper {
                        width: 100%;
                        margin: 0;
                        padding: 0;
                        box-shadow: none;
                    }

                    tr {
                        page-break-inside: avoid;
                    }

                    thead {
                        display: table-header-group;
                    }

                    @media print {
                        body {
                            background: white;
                        }

                        .toolbar {
                            display: none;
                        }

                        body .paper,
                        body.a5 .paper {
                            width: 100%;
                            margin: 0;
                            padding: 0;
                            box-shadow: none;
                        }
                    }

                    @media screen and (max-width:600px) {
                        .paper {
                            margin: 10px 0;
                        }
                    }

                    /* Dompdf: short quotations use a physical page frame, independent of table-row sizing. */
                    body.pdf-fixed-page .document-title {
                        margin-bottom: 6px;
                    }

                    body.pdf-fixed-page .top td {
                        padding: 6px 8px;
                    }

                    body.pdf-fixed-page .seller strong {
                        font-size: 15px;
                    }

                    body.pdf-fixed-page .meta {
                        font-size: 10px;
                        line-height: 1.5;
                    }

                    body.pdf-fixed-page .buyer {
                        padding: 5px 8px;
                    }

                    body.pdf-fixed-page .items {
                        position: relative;
                        top: auto;
                        left: auto;
                        width: 100%;
                        margin-top: 0;
                        border: 1px solid #777;
                        border-bottom: 0;
                    }

                    body.pdf-fixed-page .items th {
                        border: 1px solid #777;
                    }

                    body.pdf-fixed-page .items tbody td {
                        border: 0;
                    }

                    body.pdf-fixed-page .items .fill,
                    body.pdf-fixed-page .items .grand,
                    body.pdf-fixed-page #quotation-sheet>.words,
                    body.pdf-fixed-page #quotation-sheet>.notes,
                    body.pdf-fixed-page #quotation-sheet>.footer {
                        display: none;
                    }

                    .pdf-rule-frame {
                        position: fixed;
                        left: 0;
                        right: 0;
                        border: 1px solid #777;
                        overflow: hidden;
                    }

                    .pdf-rule {
                        position: absolute;
                        top: 0;
                        width: 0;
                        border-left: 1px solid #999;
                    }

                    body.pdf-fixed-page .pdf-page-bottom {
                        display: none;
                    }

                    .pdf-page-bottom {
                        position: fixed;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        height: 28mm;
                        border: 1px solid #777;
                        background: white;
                    }

                    .pdf-page-bottom table {
                        table-layout: fixed;
                    }

                    .pdf-page-bottom td {
                        padding: 8px;
                        font-size: 15px;
                        border-bottom: 1px solid #777;
                        font-weight: bold;
                    }

                    .pdf-page-bottom .bottom-label {
                        text-align: center;
                        font-style: italic;
                    }

                    .pdf-page-bottom .bottom-amount {
                        width: 18%;
                        text-align: right;
                        border-left: 1px solid #777;
                    }

                    .pdf-page-bottom.gst .bottom-amount {
                        width: 16%;
                    }

                    .pdf-page-bottom .bottom-words {
                        font-size: 11px;
                        font-weight: bold;
                        padding: 8px;
                    }

                    .pdf-page-bottom .bottom-note {
                        padding: 0 8px;
                        font-size: 8px;
                    }

                    body.a5.pdf-fixed-page .items {
                        top: auto;
                    }

                    body.a5 .pdf-page-bottom td {
                        font-size: 12px;
                        padding: 6px;
                    }

                    body.a5 .pdf-page-bottom .bottom-words {
                        font-size: 9px;
                        padding: 6px;
                    }
                </style>
            </head>

            <body class="{{ $paper }} {{ $pdfMode ? 'pdf-mode' : 'screen-mode' }} {{ $pdfFixedPage ? 'pdf-fixed-page' : '' }}" data-paper="{{ $paper }}">
                @unless ($pdfMode)
                <div class="toolbar">
                    <a href="{{ route('quotations.show', $quotation) }}">Back</a>
                    <button type="button" data-paper="a4">A4</button><button type="button" data-paper="a5">A5</button>
                    <button type="button" class="primary" id="print-button">Print</button>
                    <a id="pdf-link" href="{{ route('quotations.pdf', ['quotation' => $quotation, 'paper' => $paper]) }}">PDF</a>
                </div>
                @endunless
                @if ($pdfFixedPage)
                <div id="pdf-bottom-total" class="pdf-page-bottom {{ $isGstBill ? 'gst' : '' }}">
                    <table>
                        <tr>
                            <td class="bottom-label">Grand Total</td>
                            <td class="bottom-amount">{{ number_format((float) $quotation->grand_total, 2) }}</td>
                        </tr>
                    </table>
                    <div class="bottom-words">{{ $amountWords }}</div>
                    <div class="bottom-note">{{ $quotation->terms_conditions }}</div>
                </div>
                @endif
                <main class="paper">
                    <div id="quotation-sheet">
                        <h1 class="document-title">{{ $title }}</h1>
                        <table class="top">
                            <tr>
                                <td class="seller"><strong>{{ $company?->company_name ?? 'Company' }}</strong>
                                    @if ($store?->store_name)<div>{{ $store->store_name }}</div>
                                    @endif
                                    <div>{{ $store?->address }} {{ $store?->city }} {{ $store?->state }} {{ $store?->pincode }}</div>
                                    @if ($store?->phone ?? $company?->phone)<div>Phone: {{ $store?->phone ?? $company?->phone }}</div>
                                    @endif
                                    @if ($isGstBill && $company?->gst_number)<div>GSTIN: {{ $company->gst_number }}</div>
                                    @endif
                                </td>
                                <td class="meta">No.: <strong>{{ $quotation->quotation_no }}</strong><br>Date: {{ optional($quotation->quotation_date)->format('d-m-Y') }}
                                    @if ($quotation->valid_until)<br>Valid until: {{ optional($quotation->valid_until)->format('d-m-Y') }}
                                    @endif
                                    @if ($isGstBill)<br>GST Quotation
                                    @endif
                                </td>
                            </tr>
                        </table>
                        @if ($customer)<div class="buyer"><strong>To: {{ $customer->customer_name }}</strong>
                            @if ($customer->mobile) · {{ $customer->mobile }}
                            @endif
                            @if ($isGstBill && $customer->gst_number) · GSTIN: {{ $customer->gst_number }}
                            @endif
                        </div>
                        @endif
                        <table class="items {{ $isGstBill ? 'gst' : '' }}">
                            <thead>
                                <tr id="quotation-column-headings">
                                    <th class="serial">Sl.</th>
                                    <th>Name of Item</th>
                                    <th class="qty">Quantity</th>
                                    <th class="rate">{{ $isGstBill ? 'Rate excl.' : 'Rate' }}</th>
                                    @if ($isGstBill)<th class="gst-rate">GST %</th>
                                    <th class="gst-amount">GST Amt</th>
                                    @endif
                                    <th class="amount">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                <tr>
                                    <td class="serial">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="name">{{ $item->product?->product_name ?? 'Product' }}</div>
                                        @if ($item->product?->part_number)<div class="detail">Part: {{ $item->product->part_number }}</div>
                                        @endif
                                        @if ((float) $item->free_quantity > 0)<div class="detail">Free: {{ number_format((float) $item->free_quantity, 2) }}</div>
                                        @endif
                                        @if ((float) $item->discount_amount > 0)<div class="detail">Discount: {{ number_format((float) $item->discount_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="qty">{{ number_format((float) $item->quantity, 2) }} {{ $item->product?->unit?->short_name ?? '' }}</td>
                                    <td class="rate">{{ number_format((float) $item->sale_price, 2) }}</td>
                                    @if ($isGstBill)<td class="gst-rate">{{ number_format((float) $item->gst_percent, 2) }}</td>
                                    <td class="gst-amount">{{ number_format((float) $item->gst_amount, 2) }}</td>
                                    @endif
                                    <td class="amount">{{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                                @endforeach
                                <tr class="fill" aria-hidden="true">
                                    @for ($column = 0; $column < $columnCount; $column++)
                                        <td>
                                        <div class="fill-block" @style(['height: ' . $fillHeight . ' mm'])>&nbsp;</div>
                                        </td>
                                        @endfor
                                </tr>
                                <tr class="grand">
                                    <td colspan="{{ $columnCount - 1 }}" class="grand-label">Grand Total
                                        @if ((float) $quotation->final_discount != 0 || (float) $quotation->round_off != 0)<div class="detail">Discount {{ number_format((float) $quotation->final_discount, 2) }} · Round off {{ number_format((float) $quotation->round_off, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="amount">{{ number_format((float) $quotation->grand_total, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="words">{{ $amountWords }}</div>
                        @if ($quotation->terms_conditions)<div class="notes"><strong>Terms:</strong> {{ $quotation->terms_conditions }}</div>
                        @endif
                        <div class="footer"><span>This is a quotation, not a tax invoice.</span><span>For {{ $company?->company_name }}</span></div>
                    </div>
                </main>
                @unless ($pdfMode)
                <script>
                    (() => {
                        const sheet = document.getElementById('quotation-sheet');
                        const cells = [...document.querySelectorAll('.fill-block')];
                        const pageStyle = document.getElementById('page-size-style');
                        const fit = () => {
                            cells.forEach(cell => cell.style.height = '0px');
                            const probe = document.createElement('div');
                            probe.style.cssText = 'position:absolute;visibility:hidden;height:100mm;width:1px';
                            document.body.append(probe);
                            const pixels = probe.getBoundingClientRect().height / 100;
                            probe.remove();
                            // A4: 297 - 14 mm margins - 2 mm safety. A5: 210 - 14 - 2.
                            const target = (document.body.classList.contains('a5') ? 194 : 281) * pixels;
                            const blank = Math.max(0, target - sheet.getBoundingClientRect().height);
                            cells.forEach(cell => cell.style.height = blank + 'px');
                        };
                        const select = paper => {
                            document.body.classList.remove('a4', 'a5');
                            document.body.classList.add(paper);
                            document.body.dataset.paper = paper;
                            pageStyle.textContent = '@page { size:' + paper.toUpperCase() + ' portrait; margin:7mm; }';
                            const link = document.getElementById('pdf-link');
                            const url = new URL(link.href);
                            url.searchParams.set('paper', paper);
                            link.href = url.href;
                            fit();
                        };
                        document.querySelectorAll('button[data-paper]').forEach(button => button.addEventListener('click', () => select(button.dataset.paper)));
                        document.getElementById('print-button').addEventListener('click', () => {
                            fit();
                            window.print();
                        });
                        window.addEventListener('beforeprint', fit);
                        window.addEventListener('resize', fit);
                        window.addEventListener('load', fit, {
                            once: true
                        });
                        if (document.fonts?.ready) document.fonts.ready.then(fit);
                        select(document.body.dataset.paper);
                    })();
                </script>
                @endunless
            </body>

            </html>