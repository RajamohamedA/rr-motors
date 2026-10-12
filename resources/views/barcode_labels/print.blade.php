@php
$paper = $paper ?? 'a4';
$pdfMode = (bool) ($pdfMode ?? false);
$autoPrint = (bool) ($autoPrint ?? false);
$priceDisplay = $priceDisplay ?? 'selling_price';
$showBranch = (bool) ($showBranch ?? true);
$showProductCode = (bool) ($showProductCode ?? true);
$showPartNumber = (bool) ($showPartNumber ?? true);

$isA5 = $paper === 'a5';
$isRoll50 = $paper === 'roll_50x25';
$isRoll40 = $paper === 'roll_40x25';
$isSmall = $paper === 'small_30x20';
$isRoll = $isRoll50 || $isRoll40 || $isSmall;

$paperLabel = match ($paper) {
'a5' => 'A5 Sheet',
'roll_50x25' => '50 × 25 mm Roll',
'roll_40x25' => '40 × 25 mm Roll',
'small_30x20' => 'Small Label 30 × 20 mm',
default => 'A4 Sheet',
};

$pageSize = match ($paper) {
'a5' => 'A5 portrait',
'roll_50x25' => '50mm 25mm',
'roll_40x25' => '40mm 25mm',
'small_30x20' => '30mm 20mm',
default => 'A4 portrait',
};

$pageMargin = $isRoll ? '0' : '8mm';
$previewWidth = match ($paper) {
'a5' => '148mm',
'roll_50x25' => '50mm',
'roll_40x25' => '40mm',
'small_30x20' => '30mm',
default => '210mm',
};
$previewMinHeight = match ($paper) {
'a5' => '210mm',
'roll_50x25', 'roll_40x25' => '25mm',
'small_30x20' => '20mm',
default => '297mm',
};
$previewPadding = $isRoll ? '0' : '8mm';
$labelWidth = match ($paper) {
'a5' => '64mm',
'roll_50x25' => '50mm',
'roll_40x25' => '40mm',
'small_30x20' => '30mm',
default => '62mm',
};
$labelHeight = $isRoll ? '25mm' : ($isA5 ? '33mm' : '34mm');
$labelGap = $isRoll ? '0' : '2mm';
$labelPadding = $isRoll40 ? '1.2mm' : ($isRoll50 ? '1.5mm' : '2mm');
$nameSize = $isRoll40 ? '7px' : ($isRoll50 ? '8px' : '9px');
$metaSize = $isRoll ? '6px' : '7px';
$barcodeTextSize = $isRoll ? '6px' : '7px';
$priceSize = $isRoll40 ? '8px' : ($isRoll50 ? '9px' : '10px');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Labels</title>

    @if ($isSmall)
    <style>
        @page {
            size: 30mm 20mm;
            margin: 0;
        }
    </style>
    @elseif ($isRoll50)
    <style>
        @page {
            size: 50mm 25mm;
            margin: 0;
        }
    </style>
    @elseif ($isRoll40)
    <style>
        @page {
            size: 40mm 25mm;
            margin: 0;
        }
    </style>
    @elseif ($isA5)
    <style>
        @page {
            size: A5 portrait;
            margin: 8mm;
        }
    </style>
    @else
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
    </style>
    @endif

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: #102538;
            background: #d5dee7;
            font-family: "DejaVu Sans", Arial, sans-serif;
        }

        .preview-toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: table;
            width: 100%;
            padding: 9px 14px;
            border-bottom: 1px solid #1a3d5e;
            color: #ffffff;
            background: linear-gradient(135deg, #102d49, #176b9e);
            box-shadow: 0 4px 12px rgba(16, 45, 73, 0.25);
        }

        .toolbar-title,
        .toolbar-actions {
            display: table-cell;
            vertical-align: middle;
        }

        .toolbar-title strong,
        .toolbar-title small {
            display: block;
        }

        .toolbar-title strong {
            font-size: 14px;
        }

        .toolbar-title small {
            margin-top: 2px;
            color: #d5ebf8;
            font-size: 10px;
        }

        .toolbar-actions {
            text-align: right;
        }

        .toolbar-badge,
        .toolbar-button {
            display: inline-block;
            min-height: 30px;
            margin-left: 5px;
            padding: 7px 10px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 5px;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
            font-size: 10px;
            font-weight: 700;
            line-height: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar-button.print {
            border-color: #70d0ae;
            background: #137457;
        }

        .preview-stage {
            width: 100%;
            min-height: calc(100vh - 50px);
            padding: 20px 10px 36px;
            text-align: center;
        }

        .paper {
            margin: 0 auto;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 12px 32px rgba(16, 42, 65, 0.22);
            text-align: left;
        }

        .paper-a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 8mm;
        }

        .paper-a5 {
            width: 148mm;
            min-height: 210mm;
            padding: 8mm;
        }

        .paper-roll_50x25 {
            width: 50mm;
            min-height: 25mm;
            padding: 0;
        }

        .paper-roll_40x25 {
            width: 40mm;
            min-height: 25mm;
            padding: 0;
        }

        .paper-small_30x20 {
            width: 30mm;
            min-height: 20mm;
            padding: 0;
        }

        .labels {
            width: 100%;
            margin: 0;
            padding: 0;
            font-size: 0;
        }

        .label-card {
            position: relative;
            display: inline-block;
            overflow: hidden;
            border: 0.25mm solid #788c9d;
            border-radius: 1.2mm;
            color: #0d2436;
            background: #ffffff;
            vertical-align: top;
            font-size: 8px;
            page-break-inside: avoid;
        }

        .paper-a4 .label-card {
            width: 46.5mm;
            height: 34mm;
            margin: 0 1.5mm 2mm 0;
            padding: 1.5mm;
        }

        .paper-a4 .label-card:nth-child(4n) {
            margin-right: 0;
        }

        .paper-a5 .label-card {
            width: 64mm;
            height: 33mm;
            margin: 0 2mm 2mm 0;
            padding: 2mm;
        }

        .paper-roll_50x25 .label-card {
            width: 50mm;
            height: 25mm;
            margin: 0;
            padding: 1.5mm;
        }

        .paper-roll_40x25 .label-card {
            width: 40mm;
            height: 25mm;
            margin: 0;
            padding: 1.2mm;
        }

        .label-card::before {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 1.1mm;
            content: "";
            background: #176b9e;
        }

        .label-branch {
            height: 3.3mm;
            margin-top: 0.3mm;
            overflow: hidden;
            color: #17608e;
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.03em;
            line-height: 3.3mm;
            text-align: center;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .label-name {
            height: 5mm;
            margin-top: 0.3mm;
            overflow: hidden;
            color: #102b40;
            font-size: 9px;
            font-weight: 700;
            line-height: 2.5mm;
            text-align: center;
        }

        .label-meta {
            height: 3mm;
            margin-top: 0.2mm;
            overflow: hidden;
            color: #536b7d;
            font-size: 7px;
            line-height: 3mm;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .label-barcode {
            height: 10mm;
            margin-top: 0.2mm;
            overflow: hidden;
            text-align: center;
        }

        .label-barcode img {
            display: block;
            width: 94%;
            height: 9.6mm;
            margin: 0 auto;
        }

        .label-barcode-number {
            height: 2.7mm;
            overflow: hidden;
            color: #172d3e;
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.05em;
            line-height: 2.7mm;
            text-align: center;
            white-space: nowrap;
        }

        .label-prices {
            display: table;
            width: 100%;
            height: 4mm;
            margin-top: 0.2mm;
            border-top: 0.2mm solid #ccd6dd;
            line-height: 4mm;
        }

        .label-price {
            display: table-cell;
            color: #122c40;
            font-size: 7px;
            font-weight: 700;
            vertical-align: middle;
            white-space: nowrap;
        }

        .label-price:last-child {
            text-align: right;
        }

        .label-price strong {
            color: #075f43;
            font-size: 10px;
        }

        .roll .label-branch,
        .roll .label-meta,
        .roll .label-price {
            font-size: 6px;
        }

        .roll .label-name {
            height: 4.4mm;
            font-size: 8px;
            line-height: 2.2mm;
        }

        .paper-roll_40x25 .label-name {
            font-size: 7px;
        }

        .roll .label-barcode {
            height: 7.4mm;
        }

        .roll .label-barcode img {
            height: 7.2mm;
        }

        .roll .label-barcode-number {
            font-size: 6px;
        }

        .roll .label-price strong {
            font-size: 9px;
        }

        .paper-roll_40x25 .label-price strong {
            font-size: 8px;
        }

        /* Compact 30 x 20 mm label. Keep these rules after roll rules. */
        .paper-small_30x20 .label-card {
            width: 30mm;
            height: 20mm;
            margin: 0;
            padding: .8mm;
        }

        .paper-small_30x20 .label-branch {
            height: 2mm;
            font-size: 4.5px;
            line-height: 2mm;
        }

        .paper-small_30x20 .label-name {
            height: 3.2mm;
            font-size: 5.5px;
            line-height: 1.6mm;
        }

        .paper-small_30x20 .label-meta {
            height: 2mm;
            font-size: 4.5px;
            line-height: 2mm;
        }

        .paper-small_30x20 .label-barcode {
            height: 5mm;
        }

        .paper-small_30x20 .label-barcode img {
            height: 4.8mm;
        }

        .paper-small_30x20 .label-barcode-number {
            height: 2mm;
            font-size: 4.5px;
            line-height: 2mm;
        }

        .paper-small_30x20 .label-prices {
            height: 3mm;
            line-height: 3mm;
        }

        .paper-small_30x20 .label-price {
            font-size: 4.5px;
        }

        .paper-small_30x20 .label-price strong {
            font-size: 5.5px;
        }

        .label-price.mrp strong {
            color: #71440d;
        }

        .roll .label-card {
            display: block;
            margin: 0;
            border-radius: 0;
            page-break-after: always;
        }

        .roll .label-card:last-child {
            page-break-after: auto;
        }

        .empty-message {
            padding: 20mm 5mm;
            color: #637789;
            font-size: 12px;
            text-align: center;
        }

        @media print {

            html,
            body {
                background: #ffffff !important;
            }

            .no-print {
                display: none !important;
            }

            .preview-stage {
                min-height: 0;
                padding: 0;
            }

            .paper {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                overflow: visible;
                box-shadow: none;
            }
        }

        body.pdf-mode {
            background: #ffffff;
        }

        .pdf-mode .no-print {
            display: none !important;
        }

        .pdf-mode .preview-stage {
            min-height: 0;
            padding: 0;
        }

        .pdf-mode .paper {
            width: auto;
            min-height: 0;
            margin: 0;
            padding: 0;
            overflow: visible;
            box-shadow: none;
        }

        .pdf-mode .paper-a4,
        .pdf-mode .paper-a5 {
            padding: 5mm;
        }

        /* DomPDF uses slightly different millimetre rounding than browsers. */
        .pdf-mode .paper-a4 .label-card {
            width: 45mm;
            margin-right: 1mm;
        }

        .pdf-mode .paper-a4 .label-card:nth-child(4n) {
            margin-right: 0;
        }

        .pdf-mode .paper-a5 .label-card {
            width: 60mm;
            margin-right: 1mm;
        }

        .pdf-mode .paper-a5 .label-card:nth-child(2n) {
            margin-right: 0;
        }
    </style>
</head>

<body class="{{ $pdfMode ? 'pdf-mode' : 'preview-mode' }}">
    @unless ($pdfMode)
    <div class="preview-toolbar no-print">
        <div class="toolbar-title">
            <strong>Barcode Label Preview</strong>
            <small>{{ count($labels) }} label(s) prepared</small>
        </div>

        <div class="toolbar-actions">
            <span class="toolbar-badge">{{ $paperLabel }}</span>
            <button
                type="button"
                class="toolbar-button"
                onclick="window.close()">
                Back
            </button>
            <button
                type="button"
                class="toolbar-button print"
                onclick="window.print()">
                Print Labels
            </button>
        </div>
    </div>
    @endunless

    <div class="preview-stage">
        <main class="paper paper-{{ $paper }} {{ $isRoll ? 'roll' : 'sheet' }}">
            <section class="labels">
                @forelse ($labels as $label)
                <article class="label-card">
                    @if ($showBranch)
                    <div class="label-branch">
                        {{ $label['company_name'] ?: 'Company' }}

                        @if (!empty($label['branch_name']))
                        &nbsp;•&nbsp;{{ $label['branch_name'] }}
                        @endif
                    </div>
                    @endif

                    <div class="label-name">
                        {{ $label['product_name'] }}
                    </div>

                    @if ($showProductCode || ($showPartNumber && !empty($label['part_number'])))
                    <div class="label-meta">
                        @if ($showProductCode)
                        {{ $label['product_code'] }}
                        @endif

                        @if ($showProductCode && $showPartNumber && !empty($label['part_number']))
                        &nbsp;|&nbsp;
                        @endif

                        @if ($showPartNumber && !empty($label['part_number']))
                        {{ $label['part_number'] }}
                        @endif
                    </div>
                    @endif

                    <div class="label-barcode">
                        <img
                            src="{{ $label['barcode_image'] }}"
                            alt="Barcode {{ $label['barcode'] }}">
                    </div>

                    <div class="label-barcode-number">
                        {{ $label['barcode'] }}
                    </div>

                    @if ($priceDisplay !== 'none')
                    <div class="label-prices">
                        @if (in_array($priceDisplay, ['mrp', 'both'], true))
                        <span class="label-price mrp">
                            MRP <strong>₹{{ number_format((float) $label['mrp'], 2) }}</strong>
                        </span>
                        @endif

                        @if (in_array($priceDisplay, ['selling_price', 'both'], true))
                        <span class="label-price">
                            Rate <strong>₹{{ number_format((float) $label['selling_price'], 2) }}</strong>
                        </span>
                        @endif
                    </div>
                    @endif
                </article>
                @empty
                <div class="empty-message">
                    No labels are available to print.
                </div>
                @endforelse
            </section>
        </main>
    </div>

    @if (!$pdfMode && $autoPrint)
    <script>
        window.addEventListener("load", function() {
            window.setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
    @endif
</body>

</html>