@php

$requestedPaper = strtolower((string) request()->query('paper', $paper ?? 'a4'));

$paper = in_array($requestedPaper, ['a4','a5'], true) ? $requestedPaper : 'a4';

$isA5 = $paper === 'a5';

$isPdf = (bool) ($pdfMode ?? false);

$company = $payment->company;

$store = $payment->store;

$mechanic = $payment->mechanic;

$account = $payment->account;

$allocations = $payment->allocations ?? collect();

$cancelled = $payment->status === \App\Models\MechanicPayment::STATUS_CANCELLED;

$allocated = (float) $allocations->sum('allocated_amount');

$advance = max(0, round((float) $payment->amount - $allocated, 2));

$returnedCommission = round((float) $allocations->sum(
fn ($allocation) => (float) ($allocation->commission?->returned_commission_amount ?? 0)
), 2);

$effectiveCommission = round((float) $allocations->sum(
fn ($allocation) => (float) ($allocation->commission?->effective_commission_amount
?? $allocation->commission?->commission_amount
?? 0)
), 2);

$commissionAdvance = round((float) $allocations->sum(
fn ($allocation) => (float) ($allocation->commission?->advance_amount ?? 0)
), 2);

$money = static fn($v) => 'Rs. '.number_format((float) $v, 2);

$date = static fn($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d-m-Y') : '-';

$companyName = $company?->company_name ?? $company?->name ?? config('app.name','RR Motors');

$storeName = $store?->store_name ?? $store?->name ?? 'Branch';

@endphp

<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Mechanic Payment {{ $payment->payment_no }}</title>

    <style>
        @page {

            size: {
                    {

                    strtoupper($paper)
                }
            }



            portrait;

            margin:0 !important
        }



        * {

            box-sizing: border-box;

            -webkit-print-color-adjust: exact !important;

            print-color-adjust: exact !important
        }



        html,

        body {

            margin: 0;

            padding: 0;

            color: #172033;

            font-family: DejaVu Sans, Arial, sans-serif;



            font-size: {
                    {

                    $isA5?'8px': '10px'

                }
            }



            ;

            line-height:1.45
        }



        body {

            background: {
                    {

                    $isPdf?'#fff': '#cfd8e4'

                }
            }

        }



        .clear:after,

        .toolbar:after,

        .header:after,

        .columns:after,

        .row:after,

        .summary:after,

        .audit:after,

        .signatures:after {

            display: block;

            clear: both;

            content: ''

        }



        .toolbar {

            width: {
                    {

                    $isA5?'148mm': '210mm'

                }
            }



            ;

            max-width:calc(100vw - 24px);

            margin:14px auto 10px;

            padding:9px 10px;

            border:1px solid #aeb9c8;

            border-radius:6px;

            background:#fff;

            box-shadow:0 4px 14px rgba(15, 35, 60, .11)
        }



        .toolbar-copy {

            float: left
        }



        .actions {

            float: right;

            white-space: nowrap
        }



        .toolbar strong,

        .toolbar small {

            display: block
        }



        .toolbar strong {

            color: #183b63;

            font-size: 12px
        }



        .toolbar small {

            color: #64748b;

            font-size: 9px
        }



        .toolbar select,

        .toolbar a,

        .toolbar button {

            display: inline-block;

            height: 32px;

            margin-left: 5px;

            padding: 0 11px;

            border: 1px solid #1f5f9f;

            border-radius: 4px;

            color: #fff;

            background: #1f5f9f;

            font: 700 11px/30px Arial;

            text-decoration: none;

            vertical-align: middle;

            cursor: pointer
        }



        .toolbar select {

            color: #24364d;

            border-color: #aeb9c8;

            background: #fff
        }



        .toolbar .back {

            color: #334155;

            border-color: #aeb9c8;

            background: #f8fafc
        }



        .toolbar .pdf {

            border-color: #a92e35;

            background: #bd3740
        }



        .preview {

            height: calc(100vh - 76px);

            display: flex;

            justify-content: center;

            align-items: flex-start;

            margin: 0;

            padding: 12px 12px 32px;

            overflow: auto
        }



        .paper {

            position: relative;

            flex: 0 0 auto;

            margin: 0 auto;

            border: 1px solid #8d9aaa;

            background: #fff;

            box-shadow: 0 12px 34px rgba(15, 35, 60, .2)
        }



        .paper.a4 {

            width: 210mm;

            min-height: 297mm;

            padding: 0 !important
        }



        .paper.a5 {

            width: 148mm;

            min-height: 210mm;

            padding: 0 !important
        }



        .document {

            position: relative;

            overflow: hidden;

            border: 0;

            border-radius: 0;

            background: #fff
        }



        .paper.a4 .document {

            min-height: 297mm
        }



        .paper.a5 .document {

            min-height: 210mm
        }



        .paper.a4 .header {

            padding: 8mm 10mm
        }



        .paper.a5 .header {

            padding: 6mm 8mm
        }



        .paper.a4 .status {

            padding: 3mm 10mm
        }



        .paper.a5 .status {

            padding: 2.5mm 8mm
        }



        .paper.a4 .content {

            padding: 6mm 10mm 18mm !important
        }



        .paper.a5 .content {

            padding: 5mm 8mm 14mm !important
        }



        .paper.a4 .footer {

            right: 10mm;

            left: 10mm
        }



        .paper.a5 .footer {

            right: 8mm;

            left: 8mm
        }



        /* Plain CSS padding rules: no Blade expressions are used here. */

        .paper.a4 .card-title {

            padding: 9px 12px !important
        }



        .paper.a5 .card-title {

            padding: 7px 9px !important
        }



        .paper.a4 .card-body {

            padding: 12px !important
        }



        .paper.a5 .card-body {

            padding: 9px !important
        }



        .paper.a4 .detail {

            padding: 4px 0 !important
        }



        .paper.a5 .detail {

            padding: 3px 0 !important
        }



        .paper.a4 .amount {

            padding: 10px 12px !important
        }



        .paper.a5 .amount {

            padding: 8px 10px !important
        }



        .paper.a4 .section-title {

            padding: 9px 12px !important
        }



        .paper.a5 .section-title {

            padding: 7px 9px !important
        }



        .paper.a4 .cell {

            padding: 9px 10px !important
        }



        .paper.a5 .cell {

            padding: 7px 6px !important
        }



        .paper.a4 .notice {

            padding: 10px 12px !important
        }



        .paper.a5 .notice {

            padding: 8px 9px !important
        }



        .paper.a4 .sum>div {

            padding: 10px !important
        }



        .paper.a5 .sum>div {

            padding: 8px !important
        }



        .paper.a4 .remarks {

            padding: 10px 12px !important
        }



        .paper.a5 .remarks {

            padding: 8px 9px !important
        }



        .paper.a4 .audit {

            padding: 10px 12px !important
        }



        .paper.a5 .audit {

            padding: 8px 9px !important
        }



        .paper.a4 .signatures {

            margin-top: 42px !important
        }



        .paper.a5 .signatures {

            margin-top: 28px !important
        }



        .header {

            padding: {
                    {

                    $isA5?'9px 10px': '15px 17px'

                }
            }



            ;

            color:#fff;

            border-bottom:4px solid #3b82c4;

            background:#183b63
        }



        .business {

            float: left;

            width: 65%
        }



        .doc-id {

            float: right;

            width: 35%;

            text-align: right
        }



        .business h1 {

            margin: 0 0 3px;



            font-size: {
                    {

                    $isA5?'14px': '20px'

                }
            }

        }



        .business p {

            margin: 1px 0;

            color: #dbeafe;



            font-size: {
                    {

                    $isA5?'7px': '8.5px'

                }
            }

        }



        .doc-id b,

        .doc-id span {

            display: block
        }



        .doc-id b {

            font-size: {
                    {

                    $isA5?'10px': '15px'

                }
            }

        }



        .doc-id span {

            margin-top: 5px;

            font-weight: 700
        }



        .status {

            padding: {
                    {

                    $isA5?'6px 9px': '8px 17px'

                }
            }



            ;

            overflow:hidden;

            border-bottom:1px solid #d5deea;

            background:#f1f6fb
        }



        .status>div {

            float: left;

            width: 33.333%
        }



        .center {

            text-align: center
        }



        .right {

            text-align: right
        }



        .label {

            display: block;

            margin-bottom: 2px;

            color: #68778b;



            font-size: {
                    {

                    $isA5?'6.5px': '8px'

                }
            }



            ;

            font-weight:700;

            letter-spacing:.35px;

            text-transform:uppercase
        }



        .value {

            font-weight: 700
        }



        .content {

            padding: {
                    {

                    $isA5?'9px 9px 34px': '14px 16px 48px'

                }
            }



            !important
        }



        .columns {

            margin: 0 -5px 10px
        }



        .column {

            float: left;

            width: 50%;

            padding: 0 5px
        }



        .card {

            min-height: {
                    {

                    $isA5?'86px': '114px'

                }
            }



            ;

            overflow:hidden;

            border:1px solid #d5deea;

            border-radius:5px
        }



        .card-title {

            padding: {
                    {

                    $isA5?'6px 9px': '9px 12px'

                }
            }



            ;

            color:#183b63;

            border-bottom:1px solid #d5deea;

            background:#eaf2fa;

            font-weight:700;

            text-transform:uppercase
        }



        .card-body {

            padding: {
                    {

                    $isA5?'8px 9px': '12px'

                }
            }



            !important
        }



        .name {

            margin-bottom: 5px;

            color: #12385f;



            font-size: {
                    {

                    $isA5?'10px': '13px'

                }
            }



            ;

            font-weight:700
        }



        .muted {

            padding: 1px 0;

            color: #64748b
        }



        .detail {

            padding: 3px 0;

            overflow: hidden
        }



        .detail span {

            float: left;

            width: 43%;

            color: #64748b
        }



        .detail strong {

            float: right;

            width: 57%;

            text-align: right
        }



        .amount {

            margin-bottom: 10px;



            padding: {
                    {

                    $isA5?'7px 9px': '10px 12px'

                }
            }



            !important;

            overflow:hidden;

            border:1px solid #a9d4c5;

            border-radius:5px;

            background:#edf9f5
        }



        .amount-copy {

            float: left;

            width: 65%;

            color: #365f52
        }



        .amount-total {

            float: right;

            width: 35%;

            color: #0f766e;



            font-size: {
                    {

                    $isA5?'13px': '19px'

                }
            }



            ;

            font-weight:700;

            text-align:right
        }



        .section-title {

            padding: {
                    {

                    $isA5?'7px 9px': '9px 12px'

                }
            }



            ;

            color:#fff;

            background:#294f76;

            font-weight:700;

            text-transform:uppercase
        }



        .list {

            margin-bottom: 10px;

            border: 1px solid #d5deea;

            border-top: 0
        }



        .row {

            border-bottom: 1px solid #e1e7ef
        }



        .row:last-child {

            border-bottom: 0
        }



        .row:nth-child(odd) {

            background: #f7f9fc
        }



        .cell {

            float: left;



            min-height: {
                    {

                    $isA5?'24px': '32px'

                }
            }



            ;



            padding: {
                    {

                    $isA5?'6px 5px': '9px 10px'

                }
            }



            !important;

            border-right:1px solid #e1e7ef
        }



        .cell:last-child {

            border: 0
        }



        .n {

            width: 7%;

            text-align: center
        }



        .inv {

            width: 16%
        }



        .dt {

            width: 12%
        }



        .commission {

            width: 15%;

            text-align: right
        }



        .allocated {

            width: 13%;

            text-align: right
        }

        .returned {

            width: 13%;

            color: #a52a24;

            text-align: right
        }

        .effective {

            width: 14%;

            color: #174d91;

            text-align: right
        }

        .advance-col {

            width: 12%;

            color: #9a5a08;

            text-align: right
        }



        .head,

        .total {

            background: #eaf0f6 !important;

            font-weight: 700;

            text-transform: uppercase
        }



        .total {

            text-transform: none
        }



        .notice {

            margin-bottom: 10px;



            padding: {
                    {

                    $isA5?'6px 8px': '9px 10px'

                }
            }



            !important;

            color:#805d12;

            border:1px solid #efd28b;

            border-radius:4px;

            background:#fff9e8
        }



        .summary {

            margin: 0 -4px 10px
        }



        .sum {

            float: left;

            width: 33.333%;

            padding: 0 4px
        }



        .sum>div {

            padding: {
                    {

                    $isA5?'6px': '9px'

                }
            }



            !important;

            border:1px solid #cdd8e4;

            border-radius:4px;

            background:#f5f8fc;

            text-align:center
        }



        .sum span,

        .sum strong {

            display: block
        }



        .sum span {

            color: #63758a;



            font-size: {
                    {

                    $isA5?'6px': '8px'

                }
            }



            ;

            font-weight:700;

            text-transform:uppercase
        }



        .sum strong {

            margin-top: 3px;

            color: #173b62;



            font-size: {
                    {

                    $isA5?'9px': '12px'

                }
            }

        }



        .remarks {

            margin-bottom: 12px;



            padding: {
                    {

                    $isA5?'6px 8px': '8px 10px'

                }
            }



            !important;

            border-left:3px solid #8292a6;

            background:#f6f8fb
        }



        .remarks p {

            margin: 3px 0 0;

            white-space: pre-line
        }



        .audit {

            margin-bottom: 12px;



            padding: {
                    {

                    $isA5?'6px 8px': '8px 10px'

                }
            }



            !important;

            border:1px solid #d7e0ea;

            border-radius:4px;

            background:#fafbfd
        }



        .audit>div {

            float: left;

            width: 33.333%
        }



        .signatures {

            margin-top: {
                    {

                    $isA5?'28px': '42px'

                }
            }

        }



        .signature {

            float: left;

            width: 33.333%;

            padding: 0 8px;

            text-align: center
        }



        .signature span {

            display: block;

            padding-top: 4px;

            border-top: 1px solid #718096;

            color: #5d6d82
        }



        .footer {

            position: absolute;



            right: {
                    {

                    $isA5?'9px': '16px'

                }
            }



            ;

            bottom:7px;



            left: {
                    {

                    $isA5?'9px': '16px'

                }
            }



            ;

            padding-top:5px;

            color:#8996a7;

            border-top:1px solid #e0e6ed;

            text-align:center
        }



        .watermark {

            position: absolute;

            z-index: 8;

            top: 42%;

            left: 10%;

            width: 80%;

            color: rgba(185, 28, 28, .12);



            font-size: {
                    {

                    $isA5?'38px': '62px'

                }
            }



            ;

            font-weight:700;

            letter-spacing:7px;

            text-align:center;

            transform:rotate(-24deg)
        }



        .cancelled {

            color: #b4232b
        }



        .completed {

            color: #137044
        }



        @media screen {

            .paper {

                zoom: {
                        {

                        $isA5?'.82': '.70'

                    }
                }

            }
        }



        @media screen and (max-width:760px) {



            .toolbar-copy,

            .actions {

                float: none;

                width: 100%
            }



            .actions {

                margin-top: 8px;

                white-space: normal
            }



            .toolbar select,

            .toolbar a,

            .toolbar button {

                margin: 3px 2px
            }



            .preview {

                height: auto;

                min-height: calc(100vh - 120px)
            }



            .paper {

                zoom: {
                        {

                        $isA5?'.64': '.48'

                    }
                }

            }
        }



        @media print {



            html,

            body {

                background: #fff !important
            }



            .toolbar {

                display: none !important
            }



            .preview {

                display: block !important;

                width: auto !important;

                height: auto !important;

                margin: 0 !important;

                padding: 0 !important;

                overflow: visible !important
            }



            .paper {

                margin: 0 !important;

                border: 0 !important;

                box-shadow: none !important;

                zoom: 1 !important
            }



            .paper.a4 {

                width: 210mm !important;

                min-height: 297mm !important;

                padding: 0 !important
            }



            .paper.a5 {

                width: 148mm !important;

                min-height: 210mm !important;

                padding: 0 !important
            }



            .paper.a4 .document {

                min-height: 297mm !important
            }



            .paper.a5 .document {

                min-height: 210mm !important
            }
        }



        @if($isPdf) .toolbar {

            display: none !important
        }



        .preview {

            display: block !important;

            width: 100% !important;

            height: auto !important;

            margin: 0 !important;

            padding: 0 !important;

            overflow: visible !important
        }



        .paper,

        .paper.a4,

        .paper.a5 {

            width: 100% !important;

            min-height: 0 !important;

            margin: 0 !important;

            padding: 0 !important;

            border: 0 !important;

            box-shadow: none !important
        }



        .document,

        .paper.a4 .document,

        .paper.a5 .document {

            width: 100% !important;

            min-height: 0 !important;

            overflow: visible !important
        }



        .header {

            background: #183b63 !important;

            background-color: #183b63 !important;

            color: #fff !important
        }



        .header {

            display: block !important;

            clear: both !important;

            overflow: hidden !important
        }



        .business p {

            color: #dbeafe !important
        }



        .status {

            display: block !important;

            clear: both !important;



            height: {
                    {

                    $isA5?'44px': '56px'

                }
            }



            !important;



            min-height: {
                    {

                    $isA5?'44px': '56px'

                }
            }



            !important;

            overflow:hidden !important
        }



        .columns {

            position: relative !important;

            z-index: 2 !important;

            display: block !important;

            clear: both !important;

            width: 100% !important;

            margin: 8px 0 12px !important;

            padding-top: 2px !important;

            overflow: hidden !important
        }



        .column {

            display: block !important;

            width: 49% !important;

            padding: 0 !important;

            margin: 0 !important;

            clear: none !important
        }



        .column:first-child {

            float: left !important;

            margin-right: 2% !important
        }



        .column:last-child {

            float: left !important
        }



        .card {

            display: block !important;

            width: 100% !important;

            min-height: 112px !important;

            height: 112px !important;

            overflow: hidden !important;

            page-break-inside: avoid !important
        }



        .card-title {

            background: #eaf2fa !important;

            background-color: #eaf2fa !important
        }



        .section-title {

            display: block !important;

            clear: both !important;

            padding: 8px 10px !important;

            background: #294f76 !important;

            background-color: #294f76 !important;

            color: #fff !important;

            font-weight: bold !important
        }



        .detail {

            height: auto !important;

            overflow: visible !important;

            padding: 3px 0 !important
        }



        .detail span {

            float: left !important;

            display: block !important;

            width: 42% !important;

            padding-right: 4px !important;

            text-align: left !important
        }



        .detail strong {

            float: right !important;

            display: block !important;

            width: 58% !important;

            margin: 0 !important;

            padding: 0 !important;

            text-align: right !important
        }



        .amount,

        .section-title,

        .list,

        .notice,

        .summary,

        .remarks,

        .audit,

        .signatures {

            display: block !important;

            clear: both !important
        }



        .amount,

        .row,

        .summary,

        .audit,

        .signatures {

            overflow: hidden !important
        }



        .list {

            margin-top: 0 !important;

            margin-bottom: 12px !important
        }



        .list .cell {

            padding: 7px 6px !important
        }



        .footer {

            position: static !important;

            display: block !important;

            clear: both !important;



            margin:12px {
                    {

                    $isA5?'8mm': '10mm'

                }
            }



            0 !important
        }



        /* DomPDF-safe two-column card layout without HTML tables. */

        .columns {

            display: table !important;

            float: none !important;

            clear: both !important;

            width: 100% !important;

            table-layout: fixed !important;

            overflow: visible !important
        }



        .column,

        .column:first-child,

        .column:last-child {

            display: table-cell !important;

            float: none !important;

            clear: none !important;

            width: 50% !important;

            margin: 0 !important;

            padding: 0 5px !important;

            vertical-align: top !important
        }



        .card {

            display: block !important;

            width: 100% !important;

            min-height: 112px !important;

            height: 112px !important
        }



        /* DomPDF-safe allocation grid so every column prints completely. */

        .list {

            display: table !important;

            width: 100% !important;

            table-layout: fixed !important;

            border-collapse: collapse !important
        }



        .list .row {

            display: table-row !important;

            float: none !important;

            clear: none !important;

            width: 100% !important;

            overflow: visible !important
        }



        .list .cell {

            display: table-cell !important;

            float: none !important;

            vertical-align: middle !important;

            overflow: visible !important
        }



        .list .n {

            width: 5% !important
        }



        .list .inv {

            width: 16% !important
        }



        .list .dt {

            width: 12% !important
        }



        .list .commission {

            width: 15% !important
        }



        .list .allocated {

            width: 13% !important
        }

        .list .returned {

            width: 13% !important
        }

        .list .effective {

            width: 14% !important
        }

        .list .advance-col {

            width: 12% !important
        }



        /* Payment Account rows: stable label/value alignment in DomPDF. */

        .detail {

            display: table !important;

            width: 100% !important;

            table-layout: fixed !important;

            padding: 4px 0 !important;

            overflow: visible !important
        }



        .detail span,

        .detail strong {

            display: table-cell !important;

            float: none !important;

            margin: 0 !important;

            padding: 0 !important;

            vertical-align: top !important
        }



        .detail span {

            width: 42% !important;

            padding-right: 6px !important;

            text-align: left !important
        }



        .detail strong {

            width: 58% !important;

            text-align: right !important
        }



        /* Keep Amount Paid outside and above the allocation grid. */

        .amount {

            display: table !important;

            float: none !important;

            clear: both !important;

            width: 100% !important;

            table-layout: fixed !important;

            margin: 0 0 12px !important;

            overflow: visible !important
        }



        .amount-copy,

        .amount-total {

            display: table-cell !important;

            float: none !important;

            vertical-align: middle !important
        }



        .amount-copy {

            width: 65% !important;

            padding-right: 8px !important
        }



        .amount-total {

            width: 35% !important;

            text-align: right !important
        }



        /* Prevent the allocation footer from collapsing. */

        .list .total {

            display: table-row !important
        }



        .list .total .cell:first-child {

            display: table-cell !important;

            width: 74% !important;

            padding: 8px 10px !important;

            text-align: right !important;

            white-space: nowrap !important
        }



        .list .total .cell:last-child {

            display: table-cell !important;

            width: 13% !important;

            padding: 8px 10px !important;

            text-align: right !important;

            white-space: nowrap !important
        }



        .column:last-child .card-body {

            padding-bottom: 18px !important
        }



        .pdf-total {

            display: table !important;

            width: 100% !important;

            table-layout: fixed !important;

            border: 1px solid #d5deea !important;

            border-top: 0 !important;

            background: #eaf0f6 !important;

            font-weight: bold !important
        }



        .pdf-total-label,

        .pdf-total-value,

        .pdf-total-advance {

            display: table-cell !important;

            padding: 9px 10px !important;

            vertical-align: middle !important;

            white-space: nowrap !important
        }



        .pdf-total-label {

            width: 74% !important;

            text-align: right !important
        }



        .pdf-total-value {

            width: 13% !important;

            text-align: right !important;

            border-left: 1px solid #d5deea !important
        }

        .pdf-total-advance {

            width: 13% !important;

            color: #9a5a08 !important;

            text-align: right !important;

            border-left: 1px solid #d5deea !important
        }



        @endif
    </style>

</head>



<body>

    @unless($isPdf)

    <div class="toolbar">

        <div class="toolbar-copy"><strong>Mechanic Payment Preview</strong><small>{{ $payment->payment_no }} · {{ strtoupper($paper) }} portrait</small></div>

        <div class="actions"><select onchange="changePaper(this.value)">

                <option value="a4" @selected($paper==='a4' )>A4 · 210 × 297 mm</option>

                <option value="a5" @selected($paper==='a5' )>A5 · 148 × 210 mm</option>

            </select><a class="back" href="{{ route('mechanic-payments.index') }}">Back</a><a class="pdf" href="{{ route('mechanic-payments.pdf',['mechanicPayment'=>$payment->id,'paper'=>$paper]) }}">PDF {{ strtoupper($paper) }}</a><button onclick="window.print()">Print {{ strtoupper($paper) }}</button></div>

    </div>

    @endunless

    <div class="preview">

        <main class="paper {{ $paper }}">

            <article class="document">

                @if($cancelled)<div class="watermark">CANCELLED</div>@endif

                <header class="header">

                    <div class="business">

                        <h1>{{ $companyName }}</h1>

                        <p><strong>{{ $storeName }}</strong></p>@if($store?->address)<p>{{ $store->address }}</p>@endif<p>@if($store?->phone)Phone: {{ $store->phone }}@endif @if($store?->gst_number) | GSTIN: {{ $store->gst_number }}@endif</p>

                    </div>

                    <div class="doc-id"><b>MECHANIC PAYMENT</b><span>{{ $payment->payment_no }}</span></div>

                </header>

                <section class="status">

                    <div><span class="label">Payment Date</span><span class="value">{{ $date($payment->payment_date) }}</span></div>

                    <div class="center"><span class="label">Payment Mode</span><span class="value">{{ ucwords(str_replace('_',' ',$payment->payment_mode)) }}</span></div>

                    <div class="right"><span class="label">Status</span><span class="value {{ $cancelled?'cancelled':'completed' }}">{{ strtoupper($payment->status) }}</span></div>

                </section>

                <div class="content">

                    <section class="columns" @if($isPdf) style="display:block;clear:both;width:100%;margin:0 0 10px 0;" @endif>

                        <div class="column" @if($isPdf) style="display:block;float:none;clear:both;width:100%;padding:0;margin:0 0 8px 0;" @endif>

                            <div class="card" @if($isPdf) style="display:block;width:100%;height:auto;min-height:0;border:1px solid #d5deea;border-radius:4px;overflow:visible;background:#ffffff;" @endif>

                                <div class="card-title" @if($isPdf) style="display:block;padding:7px 9px;border-bottom:1px solid #d5deea;background-color:#eaf2fa;color:#183b63;font-weight:bold;" @endif>Paid To</div>

                                <div class="card-body" @if($isPdf) style="display:block;padding:9px;" @endif>

                                    <div class="name">{{ $mechanic?->mechanic_name ?? '-' }}</div>

                                    <div class="muted">Code: {{ $mechanic?->mechanic_code ?? '-' }}</div>@if($mechanic?->mobile)<div class="muted">Mobile: {{ $mechanic->mobile }}</div>@endif @if($mechanic?->address)<div class="muted">{{ $mechanic->address }}</div>@endif

                                </div>

                            </div>

                        </div>

                        <div class="column" @if($isPdf) style="display:block;float:none;clear:both;width:100%;padding:0;margin:0;" @endif>

                            <div class="card" @if($isPdf) style="display:block;width:100%;height:auto;min-height:0;border:1px solid #d5deea;border-radius:4px;overflow:visible;background:#ffffff;" @endif>

                                <div class="card-title" @if($isPdf) style="display:block;padding:7px 9px;border-bottom:1px solid #d5deea;background-color:#eaf2fa;color:#183b63;font-weight:bold;" @endif>Payment Account</div>

                                <div class="card-body" @if($isPdf) style="display:block;padding:9px;" @endif>

                                    <div class="detail"><span>Account</span><strong>{{ $account?->account_name ?? '-' }}</strong></div>

                                    <div class="detail"><span>Account Code</span><strong>{{ $account?->account_code ?? '-' }}</strong></div>

                                    <div class="detail"><span>Reference</span><strong>{{ $payment->reference_no ?: '-' }}</strong></div>

                                    <div class="detail"><span>Created By</span><strong>{{ $payment->creator?->name ?? 'System' }}</strong></div>

                                </div>

                            </div>

                        </div>

                    </section>

                    <section class="amount">

                        <div class="amount-copy"><span class="label">Amount Paid</span>Payment issued through {{ ucwords(str_replace('_',' ',$payment->payment_mode)) }}.</div>

                        <div class="amount-total">{{ $money($payment->amount) }}</div>

                    </section>

                    <div class="section-title">Commission Allocation</div>

                    @if($allocations->isNotEmpty())

                    <section class="list">

                        <div class="row head">

                            <div class="cell n">#</div>

                            <div class="cell inv">Invoice</div>

                            <div class="cell dt">Date</div>

                            <div class="cell commission">Original</div>

                            <div class="cell returned">Returned</div>

                            <div class="cell effective">Effective</div>

                            <div class="cell allocated">Allocated</div>

                            <div class="cell advance-col">Advance</div>

                        </div>

                        @foreach($allocations as $allocation)

                        @php
                        $commission = $allocation->commission;
                        $originalAmount = (float) ($commission?->commission_amount ?? 0);
                        $returnedAmount = (float) ($commission?->returned_commission_amount ?? 0);
                        $effectiveAmount = (float) ($commission?->effective_commission_amount ?? $originalAmount);
                        $advanceAmount = (float) ($commission?->advance_amount ?? 0);
                        @endphp

                        <div class="row">

                            <div class="cell n">{{ $loop->iteration }}</div>

                            <div class="cell inv"><strong>{{ $commission?->sale?->invoice_no ?? '-' }}</strong></div>

                            <div class="cell dt">{{ $date($commission?->sale?->invoice_date) }}</div>

                            <div class="cell commission">{{ $money($commission?->commission_amount ?? 0) }}</div>

                            <div class="cell returned">{{ $returnedAmount > 0 ? $money($returnedAmount) : '-' }}</div>

                            <div class="cell effective"><strong>{{ $money($effectiveAmount) }}</strong></div>

                            <div class="cell allocated"><strong>{{ $money($allocation->allocated_amount) }}</strong></div>

                            <div class="cell advance-col"><strong>{{ $advanceAmount > 0 ? $money($advanceAmount) : '-' }}</strong></div>

                        </div>

                        @endforeach

                        @if($isPdf)

                    </section>

                    <div class="pdf-total">

                        <div class="pdf-total-label">Allocated / Return Advance</div>

                        <div class="pdf-total-value">{{ $money($allocated) }}</div>

                        <div class="pdf-total-advance">{{ $money($commissionAdvance) }}</div>

                    </div>

                    @else

                    <div class="row total">

                        <div class="cell" style="width:74%;text-align:right">Totals</div>

                        <div class="cell allocated">{{ $money($allocated) }}</div>

                        <div class="cell advance-col">{{ $money($commissionAdvance) }}</div>

                    </div>

                    </section>

                    @endif

                    @else <div class="notice"><strong>Advance Payment:</strong> No commission has been allocated.</div> @endif

                    <section class="summary">

                        <div class="sum">

                            <div><span>Payment Amount</span><strong>{{ $money($payment->amount) }}</strong></div>

                        </div>

                        <div class="sum">

                            <div><span>Allocated</span><strong>{{ $money($allocated) }}</strong></div>

                        </div>

                        <div class="sum">

                            <div><span>Mechanic Advance</span><strong>{{ $money($advance) }}</strong></div>

                        </div>

                    </section>

                    @if($payment->remarks)<section class="remarks"><span class="label">Remarks</span>

                        <p>{{ $payment->remarks }}</p>

                    </section>@endif

                    @if($cancelled)<div class="notice"><strong>Cancellation:</strong> Cancelled by {{ $payment->canceller?->name ?? 'System' }}. Reversing ledger entries were created.</div>@endif

                    <section class="audit">

                        <div><span class="label">Created By</span><span class="value">{{ $payment->creator?->name ?? 'System' }}</span></div>

                        <div class="center"><span class="label">Created At</span><span class="value">{{ optional($payment->created_at)->format('d-m-Y h:i A') }}</span></div>

                        <div class="right"><span class="label">Branch</span><span class="value">{{ $storeName }}</span></div>

                    </section>

                    <section class="signatures">

                        <div class="signature"><span>Prepared By</span></div>

                        <div class="signature"><span>Mechanic Signature</span></div>

                        <div class="signature"><span>Authorised Signatory</span></div>

                    </section>

                </div>

                <footer class="footer">Computer-generated mechanic payment voucher · {{ $companyName }} · {{ $payment->payment_no }}</footer>

            </article>

        </main>

    </div>

    @unless($isPdf)<script>
        function changePaper(p) {

            const u = new URL(location.href);

            u.searchParams.set('paper', p);

            location.href = u.toString()

        }

        addEventListener('keydown', e => {

            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p') {

                e.preventDefault();

                print()

            }
        })
    </script>@endunless

</body>



</html>