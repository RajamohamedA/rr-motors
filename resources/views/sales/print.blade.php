<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $sale->invoice_no }}</title>
    <style id="dynamic-page-size">
        @page {
            size: A4 portrait;
            margin: 7mm;
        }
    </style>
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            color: #000;
            background: #dfe5eb;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.3;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 7px;
            padding: 10px;
            border-bottom: 1px solid #aebac6;
            background: linear-gradient(180deg, #f8fafc, #e5eaf0);
            box-shadow: 0 2px 8px rgb(20 35 55 / 14%);
        }

        .toolbar button {
            min-height: 34px;
            padding: 0 13px;
            border: 1px solid #07568f;
            border-radius: 4px;
            color: #fff;
            background: linear-gradient(180deg, #2785c7, #0a5d9b);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .toolbar button:hover {
            background: linear-gradient(180deg, #1978b9, #064d82);
        }

        .toolbar button.secondary {
            border-color: #a6b2bf;
            color: #2b3a4b;
            background: linear-gradient(180deg, #fff, #e5eaf0);
        }

        .paper {
            margin: 14px auto 24px;
            color: #000;
            background: #fff;
            box-shadow: 0 8px 28px rgb(20 35 55 / 20%);
        }

        .paper.a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm;
        }

        .paper.a5 {
            width: 148mm;
            min-height: 210mm;
            padding: 7mm;
        }

        .paper.thermal {
            width: 80mm;
            min-height: 80mm;
            padding: 3mm 2mm 5mm;
            font-size: 10px;
        }

        .company {
            text-align: center;
        }

        .company h1 {
            margin: 0 0 3px;
            font-size: 22px;
            font-weight: 900;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .company p {
            margin: 1px 0;
        }

        .bill-title {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 14px;
            color: #fff;
            background: #000;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 18px;
            margin: 9px 0;
            padding: 7px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .meta-row,
        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .meta-row strong {
            text-align: right;
        }

        .customer {
            margin: 7px 0;
            padding: 6px;
            border: 1px solid #777;
        }

        .customer strong,
        .customer span {
            display: block;
        }

        .customer .caption {
            margin-bottom: 2px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .items,
        .tax-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .items th,
        .items td {
            padding: 5px 4px;
            border: 1px solid #555;
            vertical-align: top;
        }

        .items th {
            font-size: 10px;
            text-transform: uppercase;
        }

        .items .serial {
            width: 7%;
            text-align: center;
        }

        .items .description {
            width: auto;
        }

        .items .qty {
            width: 11%;
        }

        .items .rate {
            width: 17%;
        }

        .items .amount {
            width: 19%;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .product-name {
            display: block;
            font-weight: 800;
        }

        .product-meta,
        .item-note {
            display: block;
            margin-top: 2px;
            font-size: 9px;
        }

        .summary {
            width: min(100%, 330px);
            margin: 9px 0 0 auto;
        }

        .total-row {
            padding: 3px 2px;
            border-bottom: 1px dotted #777;
        }

        .total-row.grand {
            margin-top: 3px;
            padding: 6px 3px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-size: 16px;
            font-weight: 900;
        }

        .total-row.balance {
            font-size: 13px;
            font-weight: 900;
        }

        .tax-title {
            margin: 10px 0 3px;
            font-size: 10px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .tax-table th,
        .tax-table td {
            padding: 3px;
            border: 1px solid #777;
            font-size: 9px;
            text-align: right;
        }

        .tax-table th:first-child,
        .tax-table td:first-child {
            text-align: left;
        }

        .footer {
            margin-top: 13px;
            padding-top: 7px;
            border-top: 1px dashed #000;
            text-align: center;
        }

        .footer strong,
        .footer span {
            display: block;
        }

        .footer strong {
            font-size: 13px;
        }

        .paper.thermal .company h1 {
            font-size: 16px;
        }

        .paper.thermal .company p {
            font-size: 9px;
        }

        .paper.thermal .bill-title {
            margin-top: 4px;
            padding: 2px 10px;
            font-size: 10px;
        }

        .paper.thermal .meta {
            gap: 2px 7px;
            margin: 5px 0;
            padding: 4px 0;
            font-size: 9px;
        }

        .paper.thermal .customer {
            padding: 4px 2px;
            border-right: 0;
            border-left: 0;
            font-size: 9px;
        }

        .paper.thermal .items th,
        .paper.thermal .items td {
            padding: 3px 1px;
            border-right: 0;
            border-left: 0;
            font-size: 9px;
        }

        .paper.thermal .items .serial {
            width: 6%;
        }

        .paper.thermal .items .qty {
            width: 11%;
        }

        .paper.thermal .items .rate {
            width: 17%;
        }

        .paper.thermal .items .amount {
            width: 20%;
        }

        .paper.thermal .product-meta,
        .paper.thermal .item-note {
            font-size: 8px;
        }

        .paper.thermal .summary {
            width: 100%;
            margin-top: 5px;
        }

        .paper.thermal .total-row {
            padding: 2px 1px;
            font-size: 9px;
        }

        .paper.thermal .total-row.grand {
            padding: 4px 2px;
            font-size: 14px;
        }

        .paper.thermal .total-row.balance {
            font-size: 11px;
        }

        .paper.thermal .footer {
            margin-top: 8px;
            font-size: 9px;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .paper {
                min-height: 0;
                margin: 0 auto;
                padding: 0;
                box-shadow: none;
            }

            .paper.a4 {
                width: 190mm;
            }

            .paper.a5 {
                width: 134mm;
            }

            .paper.thermal {
                width: 76mm;
            }

            .items tr,
            .tax-table tr,
            .total-row,
            .customer {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }

        /* Compact invoice with a clear print hierarchy. */
        body {
            background: #edf1f7;
            color: #172b46;
            font-size: 12px;
            line-height: 1.45;
        }

        .toolbar {
            justify-content: center;
            padding: 12px;
            gap: 9px;
            background: #fff;
            border-color: #d5dfeb;
            box-shadow: 0 2px 10px #2034500a;
        }

        .toolbar button {
            border-radius: 6px;
            background: #245ab5;
            border-color: #245ab5;
            font-size: 12px;
        }

        .toolbar button:hover {
            background: #164593;
        }

        .toolbar button:focus-visible {
            outline: 3px solid #8baef5;
            outline-offset: 3px;
        }

        .toolbar button.secondary {
            background: #f2f5fa;
            border-color: #c7d3e3;
        }

        .paper {
            border-top: 5px solid #214f92;
            box-shadow: 0 8px 30px #243c5c18;
        }

        .company {
            position: relative;
            padding-bottom: 13px;
            border-bottom: 2px solid #214f92;
        }

        .company h1 {
            font-size: 25px;
            letter-spacing: .4px;
            color: #153966;
        }

        .company p {
            font-size: 11px;
            color: #344b69;
        }

        .bill-title {
            margin-top: 9px;
            padding: 5px 16px;
            border: 1px solid #c2d4ed;
            border-radius: 4px;
            background: #eaf1fc;
            color: #214f92;
            font-size: 11px;
            letter-spacing: 1px;
        }

        .meta {
            margin: 12px 0;
            padding: 10px 12px;
            border: 1px solid #d0dbea;
            border-radius: 5px;
            background: #f6f9fe;
            gap: 7px 24px;
        }

        .meta-row span {
            color: #49607a;
            font-size: 11px;
        }

        .meta-row strong {
            color: #172b46;
            font-size: 12px;
            overflow-wrap: anywhere;
        }

        .customer {
            padding: 11px 12px;
            margin: 12px 0;
            border: 1px solid #d0dbea;
            border-left: 3px solid #3567ab;
            border-radius: 4px;
        }

        .customer .caption {
            color: #476385;
            font-size: 10px;
            letter-spacing: .7px;
        }

        .customer strong {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .customer span {
            font-size: 11px;
            overflow-wrap: anywhere;
        }

        .items th {
            padding: 8px 5px;
            background: #edf3fb;
            color: #234a7b;
            border-color: #b8cae1;
            font-size: 10px;
        }

        .items th small {
            font-size: 9px;
            text-transform: none;
            font-weight: 500;
        }

        .items td {
            padding: 8px 5px;
            border-color: #d2ddeb;
            color: #172b46;
            font-size: 11px;
        }

        .items tbody tr:nth-child(even) {
            background: #f8fafe;
        }

        .items .description {
            overflow-wrap: anywhere;
        }

        .gst-invoice .items .serial {
            width: 4%;
        }

        .gst-invoice .items .qty {
            width: 7%;
        }

        .gst-invoice .items .rate {
            width: 12%;
        }

        .gst-invoice .items .taxable {
            width: 13%;
        }

        .gst-invoice .items .gst-rate {
            width: 8%;
        }

        .gst-invoice .items .gst-amount {
            width: 12%;
        }

        .gst-invoice .items .amount {
            width: 14%;
        }

        .number {
            font-variant-numeric: tabular-nums;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .product-name {
            font-size: 12px;
            font-weight: 700;
        }

        .product-meta,
        .item-note {
            font-size: 10px;
            color: #49607a;
        }

        .summary {
            margin-top: 14px;
            padding: 10px 12px;
            border: 1px solid #d0dbea;
            border-radius: 5px;
            width: 330px;
            max-width: 100%;
        }

        .total-row {
            padding: 5px 0;
            border-color: #cbd6e6;
            font-size: 12px;
        }

        .total-row.grand {
            margin: 8px -12px;
            padding: 10px 12px;
            border-top: 1px solid #c0d1e8;
            border-bottom: 1px solid #c0d1e8;
            background: #eaf1fc;
            color: #153966;
            font-size: 18px;
        }

        .total-row.balance {
            font-size: 13px;
        }

        .tax-title {
            text-align: left;
            color: #254b7b;
            font-size: 11px;
            margin-top: 16px;
        }

        .tax-table th {
            background: #edf3fb;
            color: #234a7b;
        }

        .tax-table th,
        .tax-table td {
            padding: 6px;
            border-color: #c5d3e5;
            font-size: 10px;
        }

        .footer {
            border-color: #afbed2;
            padding-top: 12px;
            color: #49607a;
        }

        .footer strong {
            color: #214f92;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .footer span {
            font-size: 10px;
        }

        .paper.a5 .items th,
        .paper.a5 .items td {
            font-size: 9px;
            padding: 5px 3px;
        }

        .paper.a5 .product-name {
            font-size: 10px;
        }

        .paper.a5 .company h1 {
            font-size: 21px;
        }

        .paper.thermal {
            color: #000;
            border-top: 0;
        }

        .paper.thermal .company {
            border-color: #000;
            padding-bottom: 6px;
        }

        .paper.thermal .company h1,
        .paper.thermal .company p,
        .paper.thermal .bill-title {
            color: #000;
        }

        .paper.thermal .meta {
            padding: 4px;
            border-radius: 0;
        }

        .paper.thermal .meta-row strong {
            font-size: 9px;
        }

        .paper.thermal .summary {
            padding: 4px;
            border-radius: 0;
        }

        .paper.thermal .total-row.grand {
            margin: 4px -4px;
            padding: 5px 4px;
            color: #000;
        }

        .paper.thermal .items th,
        .paper.thermal .items td {
            font-size: 8px;
            padding: 4px 1px;
        }

        .paper.thermal .product-name {
            font-size: 9px;
        }

        .paper.thermal .items .gst-rate {
            width: 9%;
        }

        .paper.thermal .product-meta,
        .paper.thermal .item-note {
            font-size: 8px;
        }

        .paper.thermal .customer strong {
            font-size: 11px;
        }

        thead {
            display: table-header-group;
        }

        @media print {
            body {
                color: #000;
                background: #fff;
            }

            .paper {
                border-top: 0;
            }

            .paper.a4 {
                width: 190mm;
                padding: 0;
            }

            .paper.a5 {
                width: 134mm;
                padding: 0;
            }

            .paper.thermal {
                width: 76mm;
                padding: 0;
            }

            .company h1,
            .company p,
            .bill-title,
            .items th,
            .items td,
            .total-row.grand,
            .footer strong {
                color: #000;
            }

            .items th,
            .total-row.grand,
            .tax-table th {
                background: #f1f1f1;
            }

            .company,
            .items th,
            .items td,
            .tax-table th,
            .tax-table td {
                border-color: #777;
            }

            .summary,
            .footer {
                break-inside: avoid;
            }
        }

        .preview-control {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #344b69;
        }

        .preview-control select {
            height: 34px;
            border: 1px solid #becde0;
            border-radius: 5px;
            padding: 0 8px;
            background: #fff;
            color: #234a7b;
        }

        .receipt-layout {
            display: none;
        }

        .company {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            text-align: left;
        }

        .company-brand {
            flex: 1;
            min-width: 0;
        }

        .company-brand h1 {
            overflow-wrap: anywhere;
        }

        .invoice-heading {
            text-align: right;
            min-width: 150px;
        }

        .invoice-heading strong,
        .invoice-heading small {
            display: block;
        }

        .invoice-heading strong {
            margin-top: 9px;
            font-size: 15px;
        }

        .invoice-heading small {
            margin-top: 4px;
            font-size: 11px;
        }

        .invoice-heading .bill-title {
            margin: 0;
        }

        .signature-line {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding-top: 35px;
            margin-top: 12px;
            font-size: 10px;
            color: #405b7a;
        }

        .signature-line span {
            min-width: 130px;
            border-top: 1px solid #abbdd5;
            padding-top: 5px;
            text-align: center;
        }

        .paper.a5 {
            border: 1px solid #a4b7d0;
            border-top: 4px solid #264e80;
        }

        .paper.a5 .sheet-layout {
            border: 1px solid #c5d1e2;
            padding: 10px;
        }

        .paper.a5 .company {
            display: block;
            text-align: center;
            padding-bottom: 9px;
        }

        .paper.a5 .invoice-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-top: 9px;
            padding-top: 7px;
            border-top: 1px solid #d0dbea;
        }

        .paper.a5 .invoice-heading strong {
            margin: 0;
            font-size: 11px;
        }

        .paper.a5 .invoice-heading small {
            display: none;
        }

        .paper.a5 .bill-title {
            padding: 3px 8px;
            font-size: 9px;
        }

        .paper.a5 .meta {
            padding: 6px;
            margin: 8px 0;
            gap: 4px 10px;
            border-radius: 0;
        }

        .paper.a5 .meta-row strong {
            font-size: 10px;
        }

        .paper.a5 .customer {
            padding: 7px;
            margin: 8px 0;
        }

        .paper.a5 .customer strong {
            font-size: 12px;
        }

        .paper.a5 .summary {
            width: 250px;
            margin-top: 8px;
            padding: 6px 8px;
        }

        .paper.a5 .total-row {
            padding: 3px 0;
            font-size: 10px;
        }

        .paper.a5 .total-row.grand {
            margin: 6px -8px;
            padding: 7px 8px;
            font-size: 15px;
        }

        .paper.a5 .signature-line {
            padding-top: 24px;
            font-size: 9px;
        }

        .paper.a5 .tax-table th,
        .paper.a5 .tax-table td {
            font-size: 9px;
            padding: 4px;
        }

        .paper.thermal .sheet-layout {
            display: none;
        }

        .paper.thermal .receipt-layout {
            display: block;
            color: #000;
        }

        .receipt-brand {
            text-align: center;
            padding-bottom: 7px;
            border-bottom: 1px dashed #000;
        }

        .receipt-brand h1 {
            margin: 0 0 4px;
            font-size: 17px;
            line-height: 1.2;
            text-transform: uppercase;
            overflow-wrap: anywhere;
        }

        .receipt-brand p {
            margin: 3px 0;
            font-size: 9px;
            overflow-wrap: anywhere;
        }

        .receipt-title {
            margin: 7px 0 2px;
            padding: 4px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .receipt-meta {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 7px 0;
            font-size: 10px;
            overflow-wrap: anywhere;
        }

        .receipt-customer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 3px 8px;
            padding: 5px 0 8px;
            font-size: 9px;
            border-bottom: 1px dashed #000;
        }

        .receipt-item {
            padding: 8px 0;
            border-bottom: 1px dashed #666;
            break-inside: avoid;
        }

        .receipt-product {
            font-size: 11px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .receipt-code {
            margin-top: 2px;
            font-size: 8px;
        }

        .receipt-line-table {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .receipt-line-table th,
        .receipt-line-table td {
            font-size: 8px;
            padding: 3px 1px;
            text-align: right;
            overflow-wrap: anywhere;
            font-variant-numeric: tabular-nums;
        }

        .receipt-line-table th {
            border-bottom: 1px solid #bbb;
            font-weight: 500;
        }

        .receipt-line-table th:first-child,
        .receipt-line-table td:first-child {
            text-align: left;
        }

        .receipt-item>small {
            display: block;
            margin-top: 3px;
            font-size: 8px;
        }

        .receipt-summary {
            padding: 8px 0;
        }

        .receipt-summary>div {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 3px 0;
            font-size: 10px;
        }

        .receipt-summary .receipt-grand {
            padding: 8px 0;
            margin: 5px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-size: 17px;
            font-weight: 800;
        }

        .receipt-footer {
            padding: 9px 0 5px;
            border-top: 1px dashed #000;
            text-align: center;
        }

        .receipt-footer strong,
        .receipt-footer span {
            display: block;
            font-size: 10px;
            margin-top: 3px;
        }

        .receipt-remarks {
            font-size: 9px;
            overflow-wrap: anywhere;
        }

        @media print {
            .paper.a5 {
                border: 0;
            }

            .paper.a5 .sheet-layout {
                padding: 5px;
            }

            .signature-line {
                break-inside: avoid;
            }
        }

        /* Compact receipt table; GST stays in the bottom summary. */
        .receipt-items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 5px;
        }

        .receipt-items-table th {
            padding: 5px 2px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-size: 8px;
            text-align: right;
        }

        .receipt-items-table td {
            padding: 7px 2px;
            border-bottom: 1px dashed #aaa;
            vertical-align: top;
            font-size: 9px;
            text-align: right;
            font-variant-numeric: tabular-nums;
            overflow-wrap: anywhere;
        }

        .receipt-items-table .receipt-description {
            width: 46%;
            text-align: left;
        }

        .receipt-items-table .receipt-qty {
            width: 10%;
        }

        .receipt-items-table .receipt-rate {
            width: 21%;
        }

        .receipt-items-table .receipt-amount {
            width: 23%;
        }

        .receipt-description strong {
            font-weight: 650;
        }

        .receipt-description small {
            display: block;
            font-size: 8px;
            margin-top: 3px;
        }

        .receipt-tax-title {
            padding: 6px 0 4px;
            font-size: 10px;
            font-weight: 700;
            border-top: 1px dashed #000;
        }

        .receipt-tax-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 7px;
        }

        .receipt-tax-table th,
        .receipt-tax-table td {
            text-align: right;
            padding: 4px 2px;
            font-size: 9px;
            border-bottom: 1px solid #bbb;
        }

        .receipt-tax-table th:first-child,
        .receipt-tax-table td:first-child {
            text-align: left;
        }

        .receipt-summary>div {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            column-gap: 15px;
            align-items: baseline;
        }

        .receipt-summary>div strong {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        /* Short company/customer header for A4 and A5. */
        .paper.a4,
        .paper.a5 {
            min-height: 0;
        }

        .paper.a4 {
            padding: 7mm 10mm;
        }

        .paper.a5 {
            padding: 5mm;
        }

        .company {
            padding-bottom: 7px;
            gap: 12px;
        }

        .company h1 {
            font-size: 21px;
            margin-bottom: 2px;
        }

        .company p {
            margin: 1px 0;
            font-size: 10px;
            line-height: 1.3;
        }

        .invoice-heading strong {
            font-size: 13px;
            margin-top: 5px;
        }

        .invoice-heading small {
            margin-top: 2px;
            font-size: 10px;
        }

        .meta {
            padding: 6px 9px;
            margin: 7px 0;
            gap: 3px 20px;
        }

        .meta-row strong,
        .meta-row span {
            font-size: 10px;
        }

        .customer {
            margin: 7px 0;
            padding: 6px 9px;
            line-height: 1.5;
        }

        .customer .caption {
            display: inline;
            margin-right: 8px;
            font-size: 9px;
        }

        .customer strong {
            display: inline;
            font-size: 12px;
            margin-right: 10px;
        }

        .customer span:not(.caption) {
            display: inline;
            margin-right: 10px;
            font-size: 10px;
        }

        .paper.a5 .sheet-layout {
            padding: 6px;
        }

        .paper.a5 .company {
            display: flex;
            text-align: left;
            align-items: flex-start;
            gap: 9px;
            padding-bottom: 6px;
        }

        .paper.a5 .company h1 {
            font-size: 18px;
        }

        .paper.a5 .company p {
            font-size: 9px;
        }

        .paper.a5 .invoice-heading {
            display: block;
            min-width: 105px;
            margin: 0;
            padding: 0;
            border: 0;
            text-align: right;
        }

        .paper.a5 .invoice-heading strong {
            font-size: 10px;
            margin-top: 5px;
        }

        .paper.a5 .meta {
            padding: 4px 6px;
            margin: 5px 0;
            gap: 2px 8px;
        }

        .paper.a5 .customer {
            padding: 4px 6px;
            margin: 5px 0;
        }

        .paper.a5 .customer strong {
            font-size: 11px;
        }

        .invoice-bottom {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(230px, .85fr);
            gap: 18px;
            align-items: start;
            margin-top: 10px;
        }

        .invoice-bottom .summary {
            margin: 0;
            width: 100%;
            padding: 8px 10px;
        }

        .invoice-tax-block .tax-title {
            margin-top: 0;
        }

        .summary .total-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            column-gap: 20px;
            align-items: baseline;
            padding: 4px 0;
        }

        .summary .total-row strong {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .summary .total-row.grand {
            margin: 6px -10px;
            padding: 8px 10px;
            font-size: 17px;
        }

        .paper.a5 .invoice-bottom {
            grid-template-columns: minmax(0, 1fr) minmax(190px, 1fr);
            gap: 10px;
            margin-top: 7px;
        }

        .paper.a5 .invoice-bottom .summary {
            padding: 6px 8px;
        }

        .paper.a5 .summary .total-row {
            column-gap: 12px;
            padding: 3px 0;
        }

        .paper.a5 .summary .total-row.grand {
            margin: 5px -8px;
            padding: 6px 8px;
            font-size: 14px;
        }

        .signature-line {
            padding-top: 20px;
            margin-top: 9px;
        }

        .footer {
            margin-top: 9px;
            padding-top: 7px;
        }

        @media print {

            .paper.a4,
            .paper.a5 {
                padding: 0;
            }

            .invoice-bottom {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }

        /* A4/A5 use item-level GST columns, with no duplicate GST summary. */
        .invoice-bottom {
            display: flex;
            justify-content: flex-end;
            gap: 0;
        }

        .invoice-bottom .summary {
            width: 300px;
            max-width: 100%;
            border-radius: 4px;
        }

        .paper.a5 .invoice-bottom {
            display: flex;
            justify-content: flex-end;
        }

        .paper.a5 .invoice-bottom .summary {
            width: 235px;
            max-width: 100%;
        }

        .paper.a4 .items th {
            padding: 7px 5px;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .paper.a4 .items td {
            padding: 7px 5px;
        }

        .paper.a5 .items th,
        .paper.a5 .items td {
            padding: 5px 3px;
        }

        .company {
            border-bottom-width: 2px;
        }

        .cash-invoice .bill-title {
            background: #f3f6fa;
            border-color: #c5d2e2;
            color: #314f70;
        }

        .receipt-grand strong {
            letter-spacing: -.4px;
        }

        .receipt-items-table tbody tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        @media print {
            .invoice-bottom .summary {
                border-color: #aaa;
            }

            .meta,
            .customer {
                background: #fff;
            }

            .bill-title {
                border-color: #888;
            }
        }

        /* Boxed trade invoice: A4 and A5 reference layout. */
        .paper.a4,
        .paper.a5 {
            border: 0;
            color: #000;
        }

        .paper.a5 .sheet-layout {
            border: 0;
            padding: 0;
        }

        .classic-invoice {
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
        }

        .classic-title {
            margin: 0 0 8px;
            font-size: 15px;
            text-align: center;
            letter-spacing: .8px;
            font-weight: 700;
        }

        .classic-header,
        .classic-items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
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
            padding: 7px 9px;
            line-height: 1.4;
            font-size: 11px;
            overflow-wrap: anywhere;
        }

        .classic-party>strong,
        .classic-caption {
            display: block;
        }

        .classic-business {
            font-size: 15px;
            text-transform: uppercase;
        }

        .classic-caption {
            font-size: 10px;
            margin-bottom: 3px;
        }

        .classic-invoice-meta {
            padding: 0;
        }

        .classic-invoice-meta table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .classic-invoice-meta td {
            border-right: 1px solid #777;
            border-bottom: 1px solid #777;
            padding: 6px 7px;
            vertical-align: top;
            overflow-wrap: anywhere;
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
            font-size: 10px;
        }

        .classic-invoice-meta strong {
            margin-top: 3px;
            font-size: 11px;
        }

        .classic-items {
            margin-top: -1px;
            border: 1px solid #555;
        }

        .classic-items th {
            border: 1px solid #555;
            padding: 6px 4px;
            font-size: 10px;
            font-weight: 500;
            background: #fff;
        }

        .classic-items tbody td {
            border-right: 1px solid #777;
            padding: 5px 4px;
            font-size: 11px;
            vertical-align: top;
            overflow-wrap: anywhere;
            font-variant-numeric: tabular-nums;
        }

        .classic-items tbody tr:last-child td {
            padding-bottom: 16px;
        }

        .classic-items .c-serial {
            width: 4%;
            text-align: center;
        }

        .classic-items .c-description {
            text-align: left;
        }

        .classic-items .c-hsn {
            width: 11%;
            text-align: center;
        }

        .classic-items .c-qty {
            width: 9%;
            text-align: right;
        }

        .classic-items .c-rate {
            width: 13%;
            text-align: right;
        }

        .classic-items .c-unit {
            width: 7%;
            text-align: center;
        }

        .classic-items .c-amount {
            width: 18%;
            text-align: right;
        }

        .gst-invoice .classic-items .c-hsn {
            width: 9%;
        }

        .gst-invoice .classic-items .c-qty {
            width: 7%;
        }

        .gst-invoice .classic-items .c-rate {
            width: 11%;
        }

        .gst-invoice .classic-items .c-unit {
            width: 5%;
        }

        .classic-items .c-gst-percent {
            width: 8%;
            text-align: right;
        }

        .classic-items .c-gst {
            width: 11%;
            text-align: right;
        }

        .gst-invoice .classic-items .c-amount {
            width: 14%;
        }

        .classic-items .c-description small {
            display: block;
            font-size: 9px;
            margin-top: 2px;
        }

        .classic-items tfoot td {
            padding: 5px 8px;
            border: 1px solid #777;
            font-size: 11px;
            text-align: right;
        }

        .classic-items tfoot .classic-total-label {
            text-align: right;
            padding-right: 15px;
        }

        .classic-items tfoot .classic-grand td {
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            font-size: 14px;
            font-weight: 700;
        }

        .classic-bottom {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            border: 1px solid #555;
            border-top: 0;
        }

        .classic-bottom>div {
            padding: 8px 9px;
            font-size: 11px;
            overflow-wrap: anywhere;
        }

        .classic-bottom>div>span,
        .classic-bottom>div>strong {
            display: block;
        }

        .classic-bottom>div>strong {
            margin-top: 5px;
        }

        .classic-signature {
            text-align: right;
            border-left: 1px solid #777;
        }

        .classic-signature>span {
            padding-top: 30px;
            font-size: 10px;
        }

        .classic-footer {
            margin: 6px 0 0;
            font-size: 10px;
            text-align: center;
        }

        .paper.a5 .classic-title {
            font-size: 12px;
            margin-bottom: 6px;
        }

        .paper.a5 .classic-party {
            padding: 5px 6px;
            font-size: 9px;
        }

        .paper.a5 .classic-business {
            font-size: 12px;
        }

        .paper.a5 .classic-invoice-meta td {
            padding: 4px 5px;
        }

        .paper.a5 .classic-invoice-meta span,
        .paper.a5 .classic-invoice-meta strong {
            font-size: 9px;
        }

        .paper.a5 .classic-items th,
        .paper.a5 .classic-items tbody td {
            font-size: 9px;
            padding: 4px 2px;
        }

        .paper.a5 .classic-items .c-description small {
            font-size: 8px;
        }

        .paper.a5 .classic-items tfoot td {
            font-size: 9px;
            padding: 4px 6px;
        }

        .paper.a5 .classic-items tfoot .classic-grand td {
            font-size: 12px;
        }

        .paper.a5 .classic-bottom>div {
            font-size: 9px;
            padding: 6px;
        }

        .paper.a5 .classic-signature>span {
            padding-top: 22px;
            font-size: 9px;
        }

        @media print {

            .classic-header tr,
            .classic-items tbody tr,
            .classic-items tfoot tr {
                break-inside: avoid;
            }

            .classic-items thead {
                display: table-header-group;
            }

            .classic-items tfoot {
                display: table-row-group;
            }

            .classic-bottom {
                break-inside: avoid;
            }
        }

        /* Short bills use only the space needed for their contents. */
        .paper.a4,
        .paper.a5,
        .classic-invoice {
            height: auto;
            min-height: 0;
        }

        .classic-items tbody tr:last-child td {
            padding-bottom: 7px;
        }

        .classic-items tfoot .classic-grand td {
            font-size: 12px;
            padding: 7px 6px;
        }

        .classic-items tfoot .classic-compact-total {
            text-align: left;
            font-weight: 400;
        }

        .classic-compact-total>span {
            display: inline-block;
            margin-right: 10px;
            font-size: 10px;
            line-height: 1.5;
        }

        .classic-total-word {
            float: right;
            padding-left: 8px;
            font-size: 12px;
        }

        .classic-items tfoot .classic-compact-amount {
            text-align: right;
            font-size: 14px;
            font-weight: 800;
        }

        .classic-payment-strip {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 6px 9px;
            border: 1px solid #555;
            border-top: 0;
            font-size: 10px;
        }

        .classic-payment-strip>span {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .classic-payment-strip strong {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .classic-bottom>div {
            padding: 6px 9px;
        }

        .classic-signature>span {
            padding-top: 17px;
        }

        .paper.a5 .classic-items tfoot .classic-grand td {
            padding: 5px 4px;
            font-size: 10px;
        }

        .paper.a5 .classic-compact-total>span {
            font-size: 8px;
            margin-right: 5px;
        }

        .paper.a5 .classic-total-word {
            font-size: 10px;
        }

        .paper.a5 .classic-payment-strip {
            font-size: 9px;
            padding: 5px 6px;
            gap: 10px;
        }

        .paper.a5 .classic-signature>span {
            padding-top: 12px;
        }

        @media print {

            .paper.a4,
            .paper.a5 {
                height: auto;
                min-height: 0;
            }

            .classic-payment-strip {
                break-inside: avoid;
            }
        }

        /* Keep column rules running down to the bottom totals on short invoices. */
        .classic-items tbody .classic-fill-row td {
            padding: 0 !important;
            height: var(--invoice-fill-height, 0px);
            line-height: 0;
            font-size: 0;
            border-right: 1px solid #777;
            border-top: 0;
            border-bottom: 0;
        }

        .classic-items tbody .classic-fill-row {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        /* Supermarket-style quick receipt, isolated from A4/A5. */
        .paper.thermal .receipt-brand {
            border-bottom: 0;
            padding-bottom: 4px;
        }

        .paper.thermal .receipt-brand h1 {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 17px;
            line-height: 1.25;
            font-weight: 800;
        }

        .paper.thermal .receipt-brand p {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.35;
        }

        .paper.thermal .receipt-title {
            display: none;
        }

        .paper.thermal .receipt-meta {
            font-size: 10px;
            padding: 5px 0;
        }

        .paper.thermal .receipt-meta-second {
            padding-top: 0;
        }

        .paper.thermal .receipt-items-table th {
            font-size: 8px;
            text-transform: uppercase;
            padding: 6px 2px;
        }

        .paper.thermal .receipt-items-table td {
            font-size: 10px;
            padding: 8px 2px;
            border-bottom: 1px solid #aaa;
        }

        .paper.thermal .receipt-items-table .receipt-description {
            width: 40%;
        }

        .paper.thermal .receipt-items-table .receipt-mrp {
            width: 13%;
        }

        .paper.thermal .receipt-items-table .receipt-rate {
            width: 16%;
        }

        .paper.thermal .receipt-items-table .receipt-qty {
            width: 10%;
        }

        .paper.thermal .receipt-items-table .receipt-amount {
            width: 21%;
        }

        .paper.thermal .receipt-description strong {
            text-transform: uppercase;
            font-size: 10px;
            font-weight: 750;
        }

        .paper.thermal .receipt-grand {
            justify-content: end;
            grid-template-columns: auto auto;
            gap: 13px;
            border-top: 0;
            border-bottom: 1px solid #000;
            font-size: 21px;
            padding: 10px 0;
        }

        .paper.thermal .receipt-summary>div {
            font-size: 10px;
        }

        .paper.thermal .receipt-summary>.receipt-grand {
            font-size: 21px;
        }

        .paper.thermal .receipt-tax-table {
            margin-top: 4px;
        }

        .receipt-savings {
            display: flex;
            justify-content: center;
            gap: 9px;
            padding: 12px 0;
            border-top: 1px solid #aaa;
            font-size: 12px;
        }

        .receipt-email {
            text-align: center;
            font-weight: 700;
            overflow-wrap: anywhere;
            font-size: 10px;
            padding: 4px 0;
        }

        .paper.thermal .receipt-footer {
            font-family: Georgia, 'Times New Roman', serif;
            padding: 8px 0;
        }

        .paper.thermal .receipt-footer strong {
            font-size: 12px;
        }

        /* Four-column quick bill and compact payment summary. */
        .paper.thermal .receipt-items-table .receipt-description {
            width: 48%;
        }

        .paper.thermal .receipt-items-table .receipt-rate {
            width: 19%;
        }

        .paper.thermal .receipt-items-table .receipt-qty {
            width: 10%;
        }

        .paper.thermal .receipt-items-table .receipt-amount {
            width: 23%;
        }

        .paper.thermal .compact-receipt-summary {
            padding: 5px 0;
        }

        .paper.thermal .compact-receipt-summary .receipt-count-line,
        .paper.thermal .compact-receipt-summary .receipt-payment-line,
        .paper.thermal .compact-receipt-summary .receipt-adjustments {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            padding: 3px 0;
            font-size: 10px;
        }

        .receipt-count-line span,
        .receipt-payment-line span {
            display: inline-flex;
            gap: 6px;
        }

        .paper.thermal .compact-receipt-summary .receipt-adjustments {
            justify-content: flex-end;
            font-size: 9px;
            gap: 10px;
        }

        .paper.thermal .compact-receipt-summary .receipt-grand {
            margin: 3px 0;
            padding: 7px 0;
        }

        .paper.thermal .compact-receipt-summary .receipt-payment-line {
            border-bottom: 1px dashed #777;
            padding-bottom: 5px;
        }

        /* Reference-style receipt bottom. */
        .paper.thermal .compact-receipt-summary .receipt-count-line {
            padding: 5px 0;
            font-size: 10px;
        }

        .paper.thermal .compact-receipt-summary .receipt-grand {
            border-top: 0;
            border-bottom: 0;
            padding: 9px 0 7px;
            margin: 0;
            font-size: 23px;
            gap: 12px;
        }

        .paper.thermal .compact-receipt-summary .receipt-grand>span {
            font-weight: 500;
            letter-spacing: 1px;
        }

        .paper.thermal .compact-receipt-summary .receipt-grand>strong {
            font-size: 25px;
            font-weight: 800;
        }

        .paper.thermal .receipt-tax-table {
            margin: 4px 0 7px;
            border-top: 1px solid #777;
            border-bottom: 1px solid #777;
        }

        .paper.thermal .receipt-tax-table th {
            padding: 6px 3px;
            font-size: 9px;
            font-weight: 650;
            border-bottom: 1px solid #777;
        }

        .paper.thermal .receipt-tax-table td {
            padding: 5px 3px;
            font-size: 10px;
            border-bottom: 0;
        }

        .paper.thermal .receipt-tax-table tfoot td {
            border-top: 1px solid #777;
            font-weight: 700;
        }

        .paper.thermal .receipt-savings {
            font-size: 12px;
            border-top: 0;
            padding: 10px 0;
        }

        .paper.thermal .receipt-footer {
            border-top: 0;
            padding-top: 5px;
        }

        .invoice-gst-split {
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px;
            font-size: 10px;
            page-break-inside: avoid;
        }

        .invoice-gst-split th,
        .invoice-gst-split td {
            padding: 5px 6px;
            border: 1px solid #777;
            text-align: right;
            color: #000;
        }

        .invoice-gst-split th {
            font-size: 9px;
            background: #f4f4f4;
        }

        .invoice-gst-split tfoot td {
            font-weight: bold;
        }

        .paper.a5 .invoice-gst-split {
            font-size: 8px;
        }

        .paper.a5 .invoice-gst-split th {
            font-size: 7px;
        }

        .receipt-split-tax small {
            display: block;
            font-size: 9px;
            font-weight: normal;
            margin-top: 2px;
        }
    </style>
</head>

<body>
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
            $initialPaper=in_array(request()->query('paper'), ['a4', 'a5', 'thermal'], true)
            ? request()->query('paper') : 'a4';
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
            'cgst' => round($gst / 2, 2),
            'sgst' => round($gst - round($gst / 2, 2), 2),
            ];
            });
            @endphp
            <nav class="toolbar" aria-label="Invoice print options">
                <label class="preview-control">Preview
                    <select onchange="setPaperSize(this.value)" aria-label="Invoice preview format">
                        <option value="a4">A4 · Detailed invoice</option>
                        <option value="a5">A5 · Compact bill</option>
                        <option value="thermal">80mm · Quick receipt</option>
                    </select>
                </label>
                <button
                    type="button"
                    onclick="printBill('thermal')">
                    Quick Bill 80mm
                </button>
                <button
                    type="button"
                    onclick="printBill('a5')">
                    Print A5
                </button>
                <button
                    type="button"
                    onclick="printBill('a4')">
                    Print A4
                </button>
                <button
                    type="button"
                    class="secondary"
                    onclick="window.close()">
                    Close
                </button>
            </nav>
            <main
                id="invoice-paper"
                class="paper a4 {{ $isGstBill ? 'gst-invoice' : 'cash-invoice' }}"
                data-paper="{{ $initialPaper }}"
                data-auto-print="{{ ($autoPrint ?? request()->boolean('auto_print')) ? '1' : '0' }}">
                <div class="sheet-layout classic-invoice">
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
                            <tr class="classic-fill-row" aria-hidden="true">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                @if($isGstBill)<td></td>
                                <td></td>@endif
                                <td></td>
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
                    <div class="classic-payment-strip">
                        <span>Paid Amount <strong>Rs. {{ number_format((float) $sale->paid_amount, 2) }}</strong></span>
                        <span>Due Balance <strong>Rs. {{ number_format((float) $sale->balance_amount, 2) }}</strong></span>
                    </div>

                    @if ($isGstBill && (float) $sale->gst_total > 0)
                    <table class="invoice-gst-split" aria-label="CGST and SGST breakdown">
                        <thead>
                            <tr>
                                <th>Taxable Amount</th>
                                <th>CGST %</th>
                                <th>CGST Amount</th>
                                <th>SGST %</th>
                                <th>SGST Amount</th>
                                <th>Total GST</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($taxSummary as $tax)
                            <tr>
                                <td>{{ number_format($tax['taxable'], 2) }}</td>
                                <td>{{ number_format($tax['rate'] / 2, 2) }}%</td>
                                <td>{{ number_format($tax['cgst'], 2) }}</td>
                                <td>{{ number_format($tax['rate'] / 2, 2) }}%</td>
                                <td>{{ number_format($tax['sgst'], 2) }}</td>
                                <td>{{ number_format($tax['gst'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>{{ number_format($taxSummary->sum('taxable'), 2) }}</td>
                                <td></td>
                                <td>{{ number_format($taxSummary->sum('cgst'), 2) }}</td>
                                <td></td>
                                <td>{{ number_format($taxSummary->sum('sgst'), 2) }}</td>
                                <td>{{ number_format($taxSummary->sum('gst'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    @endif
                    <div class="classic-bottom">
                        <div><span>Amount Chargeable (in words)</span><strong>{{ $amountWords }}</strong></div>
                        <div class="classic-signature"><strong>For {{ $company->company_name ?? 'Company' }}</strong><span>Authorised Signatory</span></div>
                    </div>
                    <p class="classic-footer">This is a computer-generated invoice.</p>
                </div>
                <section class="receipt-layout" aria-label="Quick bill receipt">
                    <header class="receipt-brand">
                        <h1>{{ $company->company_name ?? 'Store' }}</h1>
                        @if($store?->store_name)<strong>{{ $store->store_name }}</strong>@endif
                        <p>{{ $store?->address }} {{ $store?->city }} {{ $store?->pincode }}</p>
                        @if($store?->phone ?? $company?->phone)<p>Phone: {{ $store->phone ?? $company->phone }}</p>@endif
                        @if($isGstBill && $company?->gst_number)<p>GSTIN: {{ $company->gst_number }}</p>@endif
                        <div class="receipt-title">{{ $isGstBill ? 'TAX INVOICE' : 'CASH BILL' }}</div>
                    </header>
                    <div class="receipt-meta"><strong>Bill No. {{ $sale->invoice_no }}</strong><span>Date: {{ optional($sale->invoice_date)->format('d/m/Y') }}</span></div>
                    <div class="receipt-meta receipt-meta-second"><strong>{{ $isGstBill ? 'TAX INVOICE' : 'CASH BILL' }}</strong><span>Time: {{ optional($sale->created_at)->format('h:i A') }}</span></div>
                    @if($customer)
                    <div class="receipt-customer">
                        <strong>{{ $customer->customer_name }}</strong>
                        @if($customer->mobile ?? $customer->phone)<span>{{ $customer->mobile ?? $customer->phone }}</span>@endif
                        @if($isGstBill && $customer->gst_number)<span>GSTIN: {{ $customer->gst_number }}</span>@endif
                    </div>
                    @endif
                    <table class="receipt-items-table">
                        <thead>
                            <tr>
                                <th class="receipt-description">Product Name</th>
                                <th class="receipt-rate">Rate</th>
                                <th class="receipt-qty">Qty</th>
                                <th class="receipt-amount">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $index => $item)
                            @php
                            $factor = 1 + ($isGstBill ? (float) $item->gst_percent : 0) / 100;
                            $discountType = $item->discount_type ?? 'fixed';
                            $discountValue = (float) ($item->discount_value ?? $item->discount ?? 0);
                            $inclusiveDiscount = round((float) $item->discount * $factor, 2);
                            $grossInclusive = $discountType === 'percentage' && $discountValue < 100
                                ? (float) $item->line_total / (1 - $discountValue / 100)
                                : (float) $item->line_total + $inclusiveDiscount;
                                $netRate = (float) $item->quantity > 0 && $grossInclusive > 0
                                ? round($grossInclusive / (float) $item->quantity, 2)
                                : round((float) $item->sale_price * $factor, 2);
                                @endphp
                                <tr>
                                    <td class="receipt-description">
                                        <strong>{{ $item->product?->product_name ?? 'Product' }}</strong>
                                        @if((float) $item->free_quantity > 0)<small>Free: {{ number_format((float) $item->free_quantity, 2) }}</small>@endif
                                        @if((float) $item->discount > 0)<small>Discount: {{ $discountType === 'percentage' ? number_format($discountValue, 2) . '%' : number_format($inclusiveDiscount, 2) }}</small>@endif
                                    </td>
                                    <td class="receipt-rate">{{ number_format($netRate, 2) }}</td>
                                    <td class="receipt-qty">{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td class="receipt-amount"><strong>{{ number_format((float) $item->line_total, 2) }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4">No items</td>
                                </tr>
                                @endforelse
                        </tbody>
                    </table>
                    <div class="receipt-summary compact-receipt-summary">
                        <div class="receipt-count-line"><span>Total Items = <strong>{{ $items->count() }}</strong></span><span>Total Qty = <strong>{{ number_format($totalQuantity, 2) }}</strong></span></div>
                        @if((float) $sale->final_discount > 0 || (float) $sale->round_off != 0)
                        <div class="receipt-adjustments">
                            @if((float) $sale->final_discount > 0)<span>Discount −{{ number_format((float) $sale->final_discount, 2) }}</span>@endif
                            @if((float) $sale->round_off != 0)<span>Round {{ (float) $sale->round_off > 0 ? '+' : '' }}{{ number_format((float) $sale->round_off, 2) }}</span>@endif
                        </div>
                        @endif
                        <div class="receipt-grand"><span>TOTAL</span><strong>{{ number_format((float) $sale->grand_total, 2) }}</strong></div>
                        <div class="receipt-payment-line"><span>Paid <strong>{{ number_format((float) $sale->paid_amount, 2) }}</strong></span><span>Balance <strong>{{ number_format((float) $sale->balance_amount, 2) }}</strong></span></div>
                    </div>
                    @if($isGstBill && (float) $sale->gst_total > 0)

                    <table class="receipt-tax-table receipt-split-tax">
                        <thead>
                            <tr>
                                <th>Taxable</th>
                                <th>CGST</th>
                                <th>SGST</th>
                                <th>GST</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($taxSummary as $tax)
                            <tr>
                                <td>{{ number_format($tax['taxable'], 2) }}</td>
                                <td>{{ number_format($tax['cgst'], 2) }}<small>{{ number_format($tax['rate'] / 2, 2) }}%</small></td>
                                <td>{{ number_format($tax['sgst'], 2) }}<small>{{ number_format($tax['rate'] / 2, 2) }}%</small></td>
                                <td>{{ number_format($tax['gst'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>{{ number_format($taxSummary->sum('taxable'), 2) }}</td>
                                <td>{{ number_format($taxSummary->sum('cgst'), 2) }}</td>
                                <td>{{ number_format($taxSummary->sum('sgst'), 2) }}</td>
                                <td>{{ number_format($taxSummary->sum('gst'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    @endif
                    @if($sale->remarks)<p class="receipt-remarks">{{ $sale->remarks }}</p>@endif
                    @php
                    $hasCompleteMrp = $items->isNotEmpty() && $items->every(fn ($item) => $item->product?->mrp !== null && (float) $item->product->mrp > 0);
                    $mrpTotal = $items->sum(fn ($item) => (float) ($item->product?->mrp ?? 0) * (float) $item->quantity);
                    $soldTotal = $items->sum(fn ($item) => (float) $item->line_total);
                    $savings = max(0, round($mrpTotal - $soldTotal + (float) $sale->final_discount, 2));
                    @endphp
                    @if($hasCompleteMrp && $savings > 0)
                    <div class="receipt-savings">Today's Saving <strong>Rs. {{ number_format($savings, 2) }}</strong></div>
                    @endif
                    @if($company?->email)<div class="receipt-email">{{ $company->email }}</div>@endif
                    <footer class="receipt-footer"><strong>Thank you. Visit again.</strong><span>Please retain this bill.</span></footer>
                </section>
            </main>
            <script>
                function fitInvoicePage() {
                    const paper = document.getElementById('invoice-paper');
                    const sheet = paper.querySelector('.classic-invoice');
                    const filler = paper.querySelector('.classic-fill-row');
                    if (!sheet || !filler) return;
                    paper.style.setProperty('--invoice-fill-height', '0px');
                    if (paper.classList.contains('thermal')) return;
                    // A4 297mm / A5 210mm, less 14mm page margins and 2mm rounding allowance.
                    const targetMm = paper.classList.contains('a5') ? 194 : 281;
                    const targetPx = targetMm * 96 / 25.4;
                    const contentHeight = sheet.getBoundingClientRect().height;
                    const remaining = Math.max(0, Math.floor(targetPx - contentHeight));
                    paper.style.setProperty('--invoice-fill-height', remaining + 'px');
                }

                function setPaperSize(size) {
                    const paper = document.getElementById('invoice-paper');
                    const pageStyle = document.getElementById('dynamic-page-size');
                    const allowed = ['a4', 'a5', 'thermal'];
                    size = allowed.includes(size) ? size : 'a4';
                    paper.classList.remove('a4', 'a5', 'thermal');
                    paper.classList.add(size);
                    const selector = document.querySelector('.preview-control select');
                    if (selector) selector.value = size;
                    if (size === 'thermal') {
                        const heightMm = Math.max(100, Math.ceil(paper.scrollHeight * 25.4 / 96) + 10);
                        pageStyle.textContent = `@page { size: 80mm ${heightMm}mm; margin: 2mm; }`;
                    } else if (size === 'a4') {
                        pageStyle.textContent = '@page { size: A4 portrait; margin: 7mm; }';
                    } else {
                        pageStyle.textContent = '@page { size: A5 portrait; margin: 7mm; }';
                    }
                    fitInvoicePage();
                }

                function printBill(size) {
                    setPaperSize(size);
                    requestAnimationFrame(() => requestAnimationFrame(() => {
                        fitInvoicePage();
                        window.print();
                    }));
                }
                const invoicePaper = document.getElementById('invoice-paper');
                const initialPaper = invoicePaper.dataset.paper || 'a4';
                setPaperSize(initialPaper);
                fitInvoicePage();
                window.addEventListener('resize', fitInvoicePage);
                window.addEventListener('beforeprint', fitInvoicePage);
                window.addEventListener('afterprint', fitInvoicePage);
                window.addEventListener('load', fitInvoicePage);
                if (document.fonts && document.fonts.ready) document.fonts.ready.then(fitInvoicePage);

                if (invoicePaper.dataset.autoPrint === '1') {
                    window.addEventListener('load', async function() {
                        if (document.fonts && document.fonts.ready) {
                            await document.fonts.ready;
                        }
                        printBill(initialPaper);
                    }, {
                        once: true
                    });
                }
            </script>
</body>

</html>