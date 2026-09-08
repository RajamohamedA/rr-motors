@php
$isGstBill = ($salesReturn->billing_type ?? 'gst') === 'gst';
$isA5 = ($paper ?? 'a4') === 'a5';
$customer = $salesReturn->customer;
$company = $salesReturn->company;
$store = $salesReturn->store;
$sale = $salesReturn->sale;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sales Return {{ $salesReturn->return_no }}</title>

    <style>
        @page {
            margin: {
                    {
                    $isA5 ? '7mm': '10mm'
                }
            }

            ;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, sans-serif;

            font-size: {
                    {
                    $isA5 ? '8px': '10px'
                }
            }

            ;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            width: 50%;
            vertical-align: top;
        }

        .company-name {
            margin: 0 0 3px;
            color: #0b5790;

            font-size: {
                    {
                    $isA5 ? '16px': '21px'
                }
            }

            ;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .muted {
            color: #59687a;
        }

        .document-title {
            display: inline-block;

            min-width: {
                    {
                    $isA5 ? '125px': '180px'
                }
            }

            ;

            padding: {
                    {
                    $isA5 ? '5px 7px': '7px 9px'
                }
            }

            ;
            border: 1px solid #174f79;
            color: #17324d;
            background: #eef5fa;

            font-size: {
                    {
                    $isA5 ? '15px': '19px'
                }
            }

            ;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
        }

        .document-status {
            margin-top: 5px;

            font-size: {
                    {
                    $isA5 ? '7px': '9px'
                }
            }

            ;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
        }

        .document-side {
            text-align: right;
        }

        .document-status span {
            display: inline-block;
            padding: 3px 6px;
            border: 1px solid #7f9db7;
        }

        .rule {
            margin: 7px 0;
            border-top: 2px solid #17679f;
        }

        .info-grid td {
            width: 33.333%;
            padding: 0 4px 6px 0;
            vertical-align: top;
        }

        .info-box {
            height: {
                    {
                    $isA5 ? '72px': '86px'
                }
            }

            ;
            padding: 6px;
            border: 1px solid #8998a8;
        }

        .info-box h3 {
            margin: -6px -6px 5px;
            padding: 4px 6px;
            color: #fff;
            background: #286b9a;

            font-size: {
                    {
                    $isA5 ? '7px': '9px'
                }
            }

            ;
            text-transform: uppercase;
        }

        .info-row td {
            width: auto;
            padding: 1px 0;
            border: 0;
            border-bottom: 1px dotted #d0d7df;
        }

        .info-row td:last-child {
            font-weight: bold;
            text-align: right;
        }

        .info-primary {
            margin-bottom: 3px;
            color: #17324d;

            font-size: {
                    {
                    $isA5 ? '9px': '11px'
                }
            }

            ;
            font-weight: bold;
        }

        .items th {
            padding: {
                    {
                    $isA5 ? '3px': '5px'
                }
            }

            ;
            border: 1px solid #66798c;
            color: #fff;
            background: #285f88;

            font-size: {
                    {
                    $isA5 ? '7px': '8px'
                }
            }

            ;
            text-align: left;
        }

        .items td {
            padding: {
                    {
                    $isA5 ? '3px': '5px'
                }
            }

            ;
            border: 1px solid #aeb8c3;
            vertical-align: top;
        }

        .items tbody tr:nth-child(even) {
            background: #f1f5f8;
        }

        .number {
            text-align: right !important;
            white-space: nowrap;
        }

        .product-meta {
            display: block;
            color: #667587;

            font-size: {
                    {
                    $isA5 ? '6px': '8px'
                }
            }

            ;
        }

        .bottom-grid {
            margin-top: 7px;
        }

        .bottom-grid>tbody>tr>td {
            width: 50%;
            vertical-align: top;
        }

        .remarks {
            min-height: {
                    {
                    $isA5 ? '52px': '68px'
                }
            }

            ;
            padding: 6px;
            border: 1px solid #aeb8c3;
        }

        .totals {
            margin-left: auto;
            width: 96%;
        }

        .totals td {
            padding: 3px 5px;
            border-bottom: 1px dotted #aeb8c3;
        }

        .totals td:last-child {
            text-align: right;
        }

        .totals .grand td {
            border: 1px solid #164f79;
            color: #fff;
            background: #17679f;

            font-size: {
                    {
                    $isA5 ? '10px': '12px'
                }
            }

            ;
            font-weight: bold;
        }

        .cancelled-note {
            margin-top: 7px;
            padding: 6px;
            border: 1px solid #b42335;
            color: #9f1f30;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 12px;
            color: #526174;
            text-align: center;

            font-size: {
                    {
                    $isA5 ? '7px': '8px'
                }
            }

            ;
        }
    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td>
                <h1 class="company-name">
                    {{ $company->company_name ?? 'RR Motors' }}
                </h1>
                <div>{{ $store->store_name ?? '' }}</div>
                <div class="muted">
                    {{ collect([
                        $store->address ?? $company->address ?? null,
                        $store->city ?? $company->city ?? null,
                        $store->state ?? $company->state ?? null,
                        $store->pincode ?? $company->pincode ?? null,
                    ])->filter()->join(', ') }}
                </div>
                <div class="muted">
                    Phone: {{ $store->phone ?? $company->phone ?? '-' }}
                </div>
                @if ($isGstBill)
                <div>
                    <strong>GSTIN:</strong>
                    {{ $company->gst_number ?? '-' }}
                </div>
                @endif
            </td>
            <td class="document-side">
                <div class="document-title">Sales Return</div>
                <div class="document-status">
                    <span>
                        {{ strtoupper($salesReturn->status) }} ·
                        {{ $isGstBill ? 'GST' : 'NON-GST' }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    <table class="info-grid">
        <tr>
            <td>
                <div class="info-box">
                    <h3>Return Details</h3>
                    <table class="info-row">
                        <tr>
                            <td>Return No.</td>
                            <td>{{ $salesReturn->return_no }}</td>
                        </tr>
                        <tr>
                            <td>Return Date</td>
                            <td>
                                {{ optional($salesReturn->return_date)->format('d-m-Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td>Invoice No.</td>
                            <td>{{ $sale->invoice_no ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Bill Type</td>
                            <td>{{ $isGstBill ? 'GST' : 'Non-GST' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="info-box">
                    <h3>Customer</h3>
                    <div class="info-primary">
                        {{ $customer->customer_name ?? 'Walk-in Customer' }}
                    </div>
                    @if (filled($customer->mobile ?? $customer->phone ?? null))
                    <div>
                        Mobile: {{ $customer->mobile ?? $customer->phone }}
                    </div>
                    @endif
                    @if (filled($customer->customer_code ?? null))
                    <div>Code: {{ $customer->customer_code }}</div>
                    @endif
                    @if ($isGstBill && filled($customer->gst_number ?? null))
                    <div>GSTIN: {{ $customer->gst_number }}</div>
                    @endif
                </div>
            </td>
            <td>
                <div class="info-box">
                    <h3>Refund & Return</h3>
                    <table class="info-row">
                        <tr>
                            <td>Method</td>
                            <td>
                                {{ str($salesReturn->refund_method)->replace('_', ' ')->title() }}
                            </td>
                        </tr>
                        <tr>
                            <td>Refund</td>
                            <td>{{ str($salesReturn->refund_status)->title() }}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>{{ str($salesReturn->status)->title() }}</td>
                        </tr>
                        <tr>
                            <td>Total Qty</td>
                            <td>
                                {{ number_format((float) $salesReturn->items->sum('quantity'), 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th class="number">Qty</th>
                <th class="number">Free</th>
                <th class="number">Rate</th>
                <th class="number">Discount</th>
                <th class="number">Taxable</th>
                @if ($isGstBill)
                <th class="number">GST</th>
                @endif
                <th class="number">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($salesReturn->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <strong>
                        {{ $item->product->product_name ?? 'Product' }}
                    </strong>
                    <span class="product-meta">
                        {{ collect([
                                $item->product->product_code ?? null,
                                $item->product->model ?? null,
                                $item->product->model_name ?? null,
                                $item->product->part_number ?? null,
                            ])->filter()->unique()->join(' · ') }}
                    </span>
                </td>
                <td class="number">
                    {{ number_format((float) $item->quantity, 2) }}
                </td>
                <td class="number">
                    {{ number_format((float) $item->free_quantity, 2) }}
                </td>
                <td class="number">
                    {{ number_format((float) $item->sale_price, 2) }}
                </td>
                <td class="number">
                    {{ number_format((float) $item->discount_amount, 2) }}
                </td>
                <td class="number">
                    {{ number_format((float) $item->taxable_amount, 2) }}
                </td>
                @if ($isGstBill)
                <td class="number">
                    {{ number_format((float) $item->gst_percent, 2) }}%
                    <br>
                    {{ number_format((float) $item->gst_amount, 2) }}
                </td>
                @endif
                <td class="number">
                    <strong>
                        {{ number_format((float) $item->line_total, 2) }}
                    </strong>
                </td>
            </tr>
            @empty
            <tr>
                <td
                    colspan="{{ $isGstBill ? 9 : 8 }}"
                    style="padding: 16px; text-align: center;">
                    No returned products found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="bottom-grid">
        <tr>
            <td style="padding-right: 7px;">
                <div class="remarks">
                    <strong>Remarks</strong>
                    <div>{{ $salesReturn->remarks ?: 'No remarks.' }}</div>
                </div>
            </td>
            <td>
                <table class="totals">
                    <tr>
                        <td>Subtotal</td>
                        <td>
                            {{ number_format((float) $salesReturn->sub_total, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td>Discount</td>
                        <td>
                            - {{ number_format((float) $salesReturn->discount_total, 2) }}
                        </td>
                    </tr>
                    @if ($isGstBill)
                    <tr>
                        <td>GST Total</td>
                        <td>
                            {{ number_format((float) $salesReturn->gst_total, 2) }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td>Round Off</td>
                        <td>
                            {{ number_format((float) $salesReturn->round_off, 2) }}
                        </td>
                    </tr>
                    <tr class="grand">
                        <td>Return Total</td>
                        <td>
                            Rs. {{ number_format((float) $salesReturn->total_amount, 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if ($salesReturn->status === 'cancelled')
    <div class="cancelled-note">
        Cancelled — Stock movement reversed
    </div>
    @endif

    <div class="footer">
        Computer-generated sales return note · No signature required
    </div>
</body>

</html>