@php
$isPdf = (bool) ($pdfMode ?? false);
$paperSize = strtolower((string) ($paper ?? 'a4')) === 'a5' ? 'a5' : 'a4';
$isA5 = $paperSize === 'a5';
$mode = $filters['mode'] ?? 'daily';
$storeName = $store?->store_name ?? $store?->name ?? 'Branch';
$company = $store?->company;
$companyName = $company?->company_name ?? $company?->name ?? config('app.name');
$address = implode(', ', array_filter([
$store?->address,
$store?->city,
$store?->state,
$store?->pincode,
]));
$money = static fn ($value): string => 'Rs. ' . number_format((float) $value, 2);
$date = static function ($value): string {
if (!$value) {
return '-';
}

try {
return \Illuminate\Support\Carbon::parse($value)->format('d-m-Y');
} catch (\Throwable) {
return (string) $value;
}
};
$modeLabel = match ($mode) {
'yearly' => 'Yearly',
'monthly' => 'Monthly',
default => 'Day-wise',
};
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <style>
        @if ($isA5) @page {
            size: A5 portrait;
            margin: 9mm 8mm;
        }

        @else @page {
            size: A4 portrait;
            margin: 11mm 10mm;
        }

        @endif * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: #1e3548;
            background: #cbd5df;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            line-height: 1.35;
        }

        body.pdf-mode {
            background: #fff;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            min-height: 58px;
            padding: 7px 16px;
            border-bottom: 1px solid #0f2c41;
            color: #fff;
            background:
                radial-gradient(circle at 65% 0, rgba(65, 170, 223, .17), transparent 28%),
                linear-gradient(110deg, #102b40 0%, #164e6c 58%, #176378 100%);
            box-shadow: 0 4px 14px rgba(15, 38, 55, .28);
        }

        .toolbar-left,
        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .toolbar-left {
            min-width: 0;
        }

        .toolbar-brand {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 9px;
            margin-left: 3px;
            padding-left: 10px;
            border-left: 1px solid rgba(255, 255, 255, .22);
        }

        .toolbar-brand-icon {
            display: grid;
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 7px;
            color: #fff;
            background: linear-gradient(145deg, #3285b8, #3658ad);
            box-shadow: 0 4px 10px rgba(5, 24, 37, .24);
            font-family: Arial, sans-serif;
            font-size: 15px;
            font-weight: 800;
        }

        .toolbar-brand-copy {
            display: grid;
            min-width: 0;
            gap: 1px;
        }

        .toolbar-brand-copy strong {
            overflow: hidden;
            color: #fff;
            font-family: Arial, sans-serif;
            font-size: 12px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .toolbar-brand-copy small {
            overflow: hidden;
            color: #a9cee2;
            font-family: Arial, sans-serif;
            font-size: 9px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .paper-control {
            display: inline-flex;
            height: 33px;
            align-items: center;
            gap: 6px;
            margin-right: 3px;
            padding-left: 9px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 5px;
            background: rgba(255, 255, 255, .09);
        }

        .paper-control span {
            color: #b9d5e5;
            font-family: Arial, sans-serif;
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .toolbar a,
        .toolbar button,
        .toolbar select {
            height: 33px;
            padding: 0 11px;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 5px;
            color: #fff;
            background: rgba(255, 255, 255, .1);
            font-family: Arial, sans-serif;
            font-size: 10px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform .15s ease, filter .15s ease, background .15s ease;
        }

        .toolbar a,
        .toolbar button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .toolbar a:hover,
        .toolbar button:hover,
        .toolbar select:hover {
            color: #fff;
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        .toolbar select {
            min-width: 63px;
            height: 31px;
            padding: 0 22px 0 8px;
            border: 0;
            border-left: 1px solid rgba(255, 255, 255, .18);
            border-radius: 0;
            color: #fff;
            background-color: #235b78;
            box-shadow: none;
        }

        .toolbar select option {
            color: #233c4f;
            background: #fff;
        }

        .toolbar .back {
            border-color: rgba(255, 255, 255, .3);
            background: rgba(255, 255, 255, .12);
        }

        .toolbar .print {
            min-width: 78px;
            border-color: #4f98c7;
            background: linear-gradient(135deg, #287aae, #3157a4);
            box-shadow: 0 3px 8px rgba(10, 34, 55, .23);
        }

        .toolbar .pdf {
            border-color: #d87979;
            background: linear-gradient(135deg, #c94c4d, #a83c58);
        }

        .toolbar .csv {
            border-color: #5ab48f;
            background: linear-gradient(135deg, #16815b, #18786f);
        }

        .toolbar-button-icon {
            font-size: 12px;
            line-height: 1;
        }

        .preview {
            display: flex;
            min-height: calc(100vh - 48px);
            align-items: flex-start;
            justify-content: center;
            padding: 22px 10px 38px;
        }

        .paper {
            position: relative;
            margin: 0 auto;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 12px 34px rgba(14, 35, 51, .24);
        }

        .paper.a4 {
            width: 210mm;
            min-height: 297mm;
        }

        .paper.a5 {
            width: 148mm;
            min-height: 210mm;
        }

        .document {
            width: 100%;
            padding: 10mm;
        }

        .paper.a5 .document {
            padding: 8mm;
        }

        .pdf-mode .preview {
            display: block;
            min-height: 0;
            padding: 0;
        }

        .pdf-mode .paper {
            min-height: 0;
            margin: 0 auto;
            overflow: visible;
            box-shadow: none;
        }

        /*
         * Match the exact @page content box. Inner document padding is part
         * of these widths because global box-sizing is border-box.
         * A4: 210mm - 20mm page margins = 190mm available.
         * A5: 148mm - 16mm page margins = 132mm available.
         */
        .pdf-mode .paper.a4 {
            width: 190mm;
            max-width: 190mm;
            margin: 0;
        }

        .pdf-mode .paper.a5 {
            width: 132mm;
            max-width: 132mm;
            margin: 0;
        }

        .pdf-mode .document {
            width: 100%;
            max-width: 100%;
            padding: 10mm 5mm 4mm 10mm;
            overflow: visible;
        }

        .pdf-mode .paper.a5 .document {
            padding: 10mm 4mm 3mm 10mm;
        }

        /*
         * DomPDF does not reliably render CSS gradients or browser overflow.
         * These PDF-only rules keep every table inside the page box and use
         * solid header colours. Browser print-preview styles remain unchanged.
         */
        .pdf-mode table {
            width: 100%;
            max-width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        .pdf-mode .summary-table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        .pdf-mode .summary-table td {
            padding-left: 3px;
            padding-right: 3px;
            border-radius: 0;
            background-color: #f6fafc;
        }

        .pdf-mode .summary-table small {
            font-size: 5.8px;
        }

        .pdf-mode .summary-table strong {
            font-size: 7.4px;
        }

        .pdf-mode .summary-table td.return {
            background-color: #fff8f1;
        }

        .pdf-mode .summary-table td.net {
            background-color: #faf6fd;
        }

        .pdf-mode .summary-table td.paid {
            background-color: #f1fbf7;
        }

        .pdf-mode .summary-table td.due {
            background-color: #fff5f5;
        }

        .pdf-mode .summary-table td.cost {
            background-color: #f3f6f8;
        }

        .pdf-mode .summary-table td.profit {
            background-color: #eefaf5;
        }

        .pdf-mode .summary-table td.loss {
            background-color: #fff2f3;
        }

        .pdf-mode .summary-table td.commission {
            background-color: #f8f2fc;
        }

        .pdf-mode .summary-table td.cash {
            background-color: #eefaf5;
        }

        .pdf-mode .summary-table td.bank {
            background-color: #eef6fd;
        }

        .pdf-mode .summary-table td.upi {
            background-color: #f7f2fc;
        }

        .pdf-mode .summary-table td.card {
            background-color: #fff6ed;
        }

        .pdf-mode .section-title {
            color: #ffffff;
            background: #285f7f;
            background-color: #285f7f;
        }

        .pdf-mode table.data {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
        }

        .pdf-mode table.data thead {
            display: table-header-group;
        }

        .pdf-mode table.data th {
            color: #ffffff;
            background: #315f7d;
            background-color: #315f7d;
            font-size: 6.1px;
        }

        .pdf-mode table.data td {
            font-size: 6.6px;
        }

        .pdf-mode table.data th,
        .pdf-mode table.data td {
            padding-left: 3px;
            padding-right: 3px;
            overflow: hidden;
            word-break: break-word;
        }

        .pdf-mode table.data .number {
            white-space: nowrap;
        }

        .pdf-mode .paper.a5 table.data th {
            padding-left: 1px;
            padding-right: 1px;
            font-size: 4px;
        }

        .pdf-mode .paper.a5 table.data td {
            padding-left: 1px;
            padding-right: 1px;
            font-size: 4.4px;
        }

        .pdf-mode .paper.a5 table.data .number {
            white-space: normal;
            word-break: normal;
        }

        .pdf-mode .paper.a5 .summary-table td {
            padding: 4px 1px;
        }

        .pdf-mode .paper.a5 .summary-table small {
            font-size: 4.4px;
        }

        .pdf-mode .paper.a5 .summary-table strong {
            font-size: 5.5px;
        }

        .pdf-mode .paper.a5 .meta-table td {
            padding: 4px 3px;
        }

        .pdf-mode .paper.a5 .meta-table small {
            font-size: 4.8px;
        }

        .pdf-mode .paper.a5 .meta-table strong {
            font-size: 5.8px;
        }

        .pdf-mode .paper.a5 .company-name {
            font-size: 12px;
        }

        .pdf-mode .paper.a5 .report-title h1 {
            font-size: 11px;
        }

        .pdf-mode .paper.a5 .section-title {
            margin-top: 6px;
            padding: 4px 5px;
            font-size: 6px;
        }

        .report-head {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1c536f;
        }

        .report-head td {
            padding: 0 0 7px;
            vertical-align: top;
        }

        .company-name {
            margin: 0;
            color: #173b55;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
        }

        .paper.a5 .company-name {
            font-size: 14px;
        }

        .store-name {
            margin-top: 3px;
            color: #2f617e;
            font-size: 10px;
            font-weight: 700;
        }

        .address {
            max-width: 105mm;
            margin-top: 2px;
            color: #637787;
            font-size: 7.5px;
        }

        .report-title {
            text-align: right;
        }

        .report-title h1 {
            margin: 0;
            color: #173f5b;
            font-size: 17px;
            text-transform: uppercase;
        }

        .paper.a5 .report-title h1 {
            font-size: 13px;
        }

        .report-title span {
            color: #527086;
            font-size: 8px;
            font-weight: 700;
        }

        .meta-table {
            width: 100%;
            margin-top: 7px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta-table td {
            padding: 5px 7px;
            border: 1px solid #c5d4df;
            background: #f5f9fc;
        }

        .meta-table small {
            display: block;
            margin-bottom: 1px;
            color: #698092;
            font-size: 6.8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .meta-table strong {
            color: #233f54;
            font-size: 8.2px;
        }

        .summary-table {
            width: 100%;
            margin-top: 7px;
            border-collapse: separate;
            border-spacing: 4px 0;
            table-layout: fixed;
        }

        .secondary-summary {
            margin-top: 4px;
        }

        .summary-table td {
            position: relative;
            padding: 7px 6px;
            border: 1px solid #c5d4df;
            border-top: 3px solid #286da0;
            border-radius: 3px;
            background: #f6fafc;
            vertical-align: top;
        }

        .summary-table td.return {
            border-top-color: #c36b24;
            background: #fff8f1;
        }

        .summary-table td.net {
            border-top-color: #764ea4;
            background: #faf6fd;
        }

        .summary-table td.paid {
            border-top-color: #18805c;
            background: #f1fbf7;
        }

        .summary-table td.due {
            border-top-color: #bd474d;
            background: #fff5f5;
        }

        .summary-table td.cost {
            border-top-color: #687b8b;
            background: #f3f6f8;
        }

        .summary-table td.profit {
            border-top-color: #16805d;
            background: #eefaf5;
        }

        .summary-table td.loss {
            border-top-color: #bd474d;
            background: #fff2f3;
        }

        .summary-table td.commission {
            border-top-color: #7850a3;
            background: #f8f2fc;
        }

        .summary-table td.cash {
            border-top-color: #14805b;
            background: #eefaf5;
        }

        .summary-table td.bank {
            border-top-color: #286cad;
            background: #eef6fd;
        }

        .summary-table td.upi {
            border-top-color: #7750aa;
            background: #f7f2fc;
        }

        .summary-table td.card {
            border-top-color: #ba6823;
            background: #fff6ed;
        }

        .summary-table small {
            display: block;
            color: #647b8c;
            font-size: 6.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary-table strong {
            display: block;
            margin-top: 2px;
            color: #244e6b;
            font-size: 9px;
            white-space: nowrap;
        }

        .paper.a5 .summary-table td {
            padding: 5px 3px;
        }

        .paper.a5 .summary-table small {
            font-size: 5.2px;
        }

        .paper.a5 .summary-table strong {
            font-size: 6.7px;
        }

        .section-title {
            margin-top: 8px;
            padding: 5px 7px;
            border-left: 4px solid #2e709d;
            color: #fff;
            background: linear-gradient(90deg, #214e6d, #327ca1);
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .25px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.data th {
            padding: 5px 4px;
            border: 1px solid #8da8bb;
            color: #fff;
            background: #315f7d;
            font-size: 6.5px;
            font-weight: 800;
            text-align: left;
            text-transform: uppercase;
        }

        table.data td {
            padding: 5px 4px;
            border: 1px solid #cbd8e1;
            color: #2d4658;
            font-size: 7px;
            vertical-align: top;
            word-break: break-word;
        }

        table.data tbody tr:nth-child(even) td {
            background: #f5f9fb;
        }

        table.data .number {
            text-align: right;
            white-space: nowrap;
        }

        table.data .return {
            color: #aa591d;
            font-weight: 700;
        }

        table.data .net {
            color: #315d9b;
            font-weight: 700;
        }

        table.data .paid {
            color: #147354;
            font-weight: 700;
        }

        table.data .due {
            color: #b23d44;
            font-weight: 700;
        }

        table.data .profit {
            color: #147455;
            font-weight: 700;
        }

        table.data .loss {
            color: #b43d47;
            font-weight: 700;
        }

        .paper.a5 table.data th {
            padding: 4px 2px;
            font-size: 4.8px;
        }

        .paper.a5 table.data td {
            padding: 4px 2px;
            font-size: 5.2px;
        }

        .empty {
            padding: 16px !important;
            color: #6e8190 !important;
            text-align: center;
        }

        .footer-table {
            width: 100%;
            margin-top: 13px;
            border-collapse: collapse;
        }

        .footer-table td {
            width: 50%;
            padding-top: 8px;
            color: #657b8b;
            font-size: 7px;
            vertical-align: bottom;
        }

        .footer-table td:last-child {
            text-align: right;
        }

        .signature {
            display: inline-block;
            min-width: 38mm;
            padding-top: 5px;
            border-top: 1px solid #7e929f;
            color: #334f62;
            text-align: center;
            font-weight: 700;
        }

        @media print {

            html,
            body {
                background: #fff !important;
            }

            .no-print {
                display: none !important;
            }

            .preview {
                display: block;
                min-height: 0;
                padding: 0;
            }

            .paper {
                width: auto !important;
                min-height: 0 !important;
                box-shadow: none;
            }

            .document {
                padding: 0;
            }
        }

        @media screen and (max-width: 720px) {
            .toolbar {
                align-items: flex-start;
                flex-direction: column;
                padding: 8px 10px;
            }

            .toolbar-left,
            .toolbar-group {
                width: 100%;
            }

            .toolbar-group {
                justify-content: flex-end;
            }

            .toolbar-brand-copy small {
                display: none;
            }

            .paper-control {
                margin-right: auto;
            }
        }
    </style>
</head>

<body class="{{ $isPdf ? 'pdf-mode' : 'preview-mode' }}">
    @unless ($isPdf)
    @php
    $query = http_build_query(array_filter([
    ...$filters,
    'paper' => $paperSize,
    ], static fn ($value) => $value !== null && $value !== ''));
    @endphp
    <div class="toolbar no-print">
        <div class="toolbar-left">
            <a class="back" href="{{ route('sales-reports.index', $filters) }}">
                <span class="toolbar-button-icon">&#8592;</span>
                Back
            </a>

            <div class="toolbar-brand">
                <div class="toolbar-brand-icon">SR</div>
                <div class="toolbar-brand-copy">
                    <strong>Sales Report Preview</strong>
                    <small>{{ $storeName }} &middot; {{ $modeLabel }} report</small>
                </div>
            </div>
        </div>

        <div class="toolbar-group">
            <label class="paper-control">
                <span>Paper Size</span>
                <select onchange="window.location.href=this.value">
                    <option value="{{ route('sales-reports.print', [...$filters, 'paper' => 'a4']) }}" @selected(!$isA5)>A4</option>
                    <option value="{{ route('sales-reports.print', [...$filters, 'paper' => 'a5']) }}" @selected($isA5)>A5</option>
                </select>
            </label>

            <a class="csv" href="{{ route('sales-reports.excel') }}?{{ $query }}">
                <span class="toolbar-button-icon">&#8681;</span>
                CSV
            </a>
            <a class="pdf" href="{{ route('sales-reports.pdf') }}?{{ $query }}">
                <span class="toolbar-button-icon">PDF</span>
                Download
            </a>
            <button class="print" type="button" onclick="window.print()">
                <span class="toolbar-button-icon">&#9113;</span>
                Print
            </button>
        </div>
    </div>
    @endunless

    <main class="preview">
        <article class="paper {{ $paperSize }}">
            <div class="document">
                <table class="report-head">
                    <tr>
                        <td>
                            <h2 class="company-name">{{ $companyName }}</h2>
                            <div class="store-name">{{ $storeName }}</div>
                            @if ($address)
                            <div class="address">{{ $address }}</div>
                            @endif
                        </td>
                        <td class="report-title">
                            <h1>Sales Report</h1>
                            <span>{{ $modeLabel }} Analysis</span>
                        </td>
                    </tr>
                </table>

                <table class="meta-table">
                    <tr>
                        <td>
                            <small>Report Mode</small>
                            <strong>{{ $modeLabel }}</strong>
                        </td>
                        <td>
                            <small>From Date</small>
                            <strong>{{ $date($filters['from_date'] ?? null) }}</strong>
                        </td>
                        <td>
                            <small>To Date</small>
                            <strong>{{ $date($filters['to_date'] ?? null) }}</strong>
                        </td>
                        <td>
                            <small>Generated</small>
                            <strong>{{ $generatedAt?->format('d-m-Y h:i A') }}</strong>
                        </td>
                    </tr>
                </table>

                <table class="summary-table">
                    <tr>
                        <td>
                            <small>Bills</small>
                            <strong>{{ number_format((int) ($summary['bill_count'] ?? 0)) }}</strong>
                        </td>
                        <td>
                            <small>Gross Sales</small>
                            <strong>{{ $money($summary['gross_sales'] ?? 0) }}</strong>
                        </td>
                        <td class="return">
                            <small>Returns</small>
                            <strong>{{ $money($summary['return_amount'] ?? 0) }}</strong>
                        </td>
                        <td class="net">
                            <small>Net Sales</small>
                            <strong>{{ $money($summary['net_sales'] ?? 0) }}</strong>
                        </td>
                        <td class="paid">
                            <small>Paid</small>
                            <strong>{{ $money($summary['paid_amount'] ?? 0) }}</strong>
                        </td>
                        <td class="due">
                            <small>Outstanding</small>
                            <strong>{{ $money($summary['outstanding_amount'] ?? 0) }}</strong>
                        </td>
                    </tr>
                </table>

                <table class="summary-table secondary-summary">
                    <tr>
                        <td class="cost">
                            <small>Cost</small>
                            <strong>{{ $money($summary['cost_total'] ?? 0) }}</strong>
                        </td>
                        @php($netProfit = (float) ($summary['net_profit'] ?? 0))
                        <td class="{{ $netProfit < 0 ? 'loss' : 'profit' }}">
                            <small>Net Profit</small>
                            <strong>{{ $money($netProfit) }}</strong>
                        </td>
                        <td class="commission">
                            <small>Commission</small>
                            <strong>{{ $money($summary['commission_amount'] ?? 0) }}</strong>
                        </td>
                        <td class="cash">
                            <small>Cash</small>
                            <strong>{{ $money($summary['cash_amount'] ?? 0) }}</strong>
                        </td>
                        <td class="bank">
                            <small>Bank</small>
                            <strong>{{ $money($summary['bank_amount'] ?? 0) }}</strong>
                        </td>
                        <td class="upi">
                            <small>UPI</small>
                            <strong>{{ $money($summary['upi_amount'] ?? 0) }}</strong>
                        </td>
                        <td class="card">
                            <small>Card</small>
                            <strong>{{ $money($summary['card_amount'] ?? 0) }}</strong>
                        </td>
                    </tr>
                </table>

                <div class="section-title">{{ $modeLabel }} Sales Summary</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th style="width: 15%">Period</th>
                            <th class="number" style="width: 6%">Bills</th>
                            <th class="number" style="width: 12%">Gross</th>
                            <th class="number" style="width: 11%">Returns</th>
                            <th class="number" style="width: 12%">Net</th>
                            <th class="number" style="width: 11%">Paid</th>
                            <th class="number" style="width: 11%">Due</th>
                            <th class="number" style="width: 11%">Cost</th>
                            <th class="number" style="width: 11%">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                        <tr>
                            <td><strong>{{ $row['label'] }}</strong></td>
                            <td class="number">{{ number_format($row['bill_count']) }}</td>
                            <td class="number">{{ $money($row['gross_sales']) }}</td>
                            <td class="number return">{{ $money($row['return_amount']) }}</td>
                            <td class="number net">{{ $money($row['net_sales']) }}</td>
                            <td class="number paid">{{ $money($row['paid_amount']) }}</td>
                            <td class="number due">{{ $money($row['outstanding_amount']) }}</td>
                            <td class="number">{{ $money($row['cost_total']) }}</td>
                            <td class="number {{ (float) $row['net_profit'] < 0 ? 'loss' : 'profit' }}">{{ $money($row['net_profit']) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="empty" colspan="9">No sales summary available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="section-title">Sales Invoice Details</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th style="width: 10%">Date</th>
                            <th style="width: 13%">Invoice</th>
                            <th style="width: 20%">Customer</th>
                            <th style="width: 8%">Type</th>
                            <th class="number" style="width: 11%">Total</th>
                            <th class="number" style="width: 10%">Paid</th>
                            <th class="number" style="width: 10%">Balance</th>
                            <th class="number" style="width: 9%">Cost</th>
                            <th class="number" style="width: 9%">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $date($sale['invoice_date']) }}</td>
                            <td><strong>{{ $sale['invoice_no'] }}</strong></td>
                            <td>
                                {{ $sale['customer']['name'] ?? '-' }}
                                @if (!empty($sale['customer']['code']))
                                <br><small>{{ $sale['customer']['code'] }}</small>
                                @endif
                            </td>
                            <td>{{ ($sale['billing_type'] ?? '') === 'gst' ? 'GST' : 'Non-GST' }}</td>
                            <td class="number net">{{ $money($sale['grand_total']) }}</td>
                            <td class="number paid">{{ $money($sale['paid_amount']) }}</td>
                            <td class="number due">{{ $money($sale['balance_amount']) }}</td>
                            <td class="number">{{ $money($sale['cost_total']) }}</td>
                            <td class="number {{ (float) $sale['net_profit'] < 0 ? 'loss' : 'profit' }}">{{ $money($sale['net_profit']) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="empty" colspan="9">No sales invoices found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <table class="footer-table">
                    <tr>
                        <td>Computer-generated sales report</td>
                        <td><span class="signature">Authorised Signatory</span></td>
                    </tr>
                </table>
            </div>
        </article>
    </main>
</body>

</html>