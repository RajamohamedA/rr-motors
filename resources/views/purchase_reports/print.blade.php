<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Purchase Invoice</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-size: 14px;
            color: #000;
        }

        .table th,
        .table td {
            padding: 6px;
            vertical-align: middle;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
        }

        .signature {
            margin-top: 70px;
            text-align: right;
        }

        @media print {

            .no-print {
                display: none;
            }

            @page {
                size: A4;
                margin: 10mm;
            }

        }
    </style>

</head>

<body>

    <div class="container-fluid">

        <div class="text-end mb-3 no-print">
            <button class="btn btn-primary" onclick="window.print()">
                Print
            </button>

            <button class="btn btn-secondary" onclick="window.close()">
                Close
            </button>
        </div>

        <table class="table table-borderless">

            <tr>

                <td width="15%">

                    @if($purchase->company->logo)

                    <img src="{{ asset('storage/'.$purchase->company->logo) }}"
                        class="company-logo">

                    @endif

                </td>

                <td>

                    <h3 class="mb-1">
                        {{ $purchase->company->company_name }}
                    </h3>

                    {{ $purchase->company->address }} <br>

                    Phone :
                    {{ $purchase->company->phone }}

                    <br>

                    GST :
                    {{ $purchase->company->gst_number }}

                </td>

                <td class="text-end">

                    <div class="invoice-title">
                        PURCHASE INVOICE
                    </div>

                </td>

            </tr>

        </table>

        <hr>

        <div class="row">

            <div class="col-6">

                <strong>Supplier</strong>

                <br>

                {{ $purchase->supplier->supplier_name }}

                <br>

                {{ $purchase->supplier->mobile }}

                <br>

                {{ $purchase->supplier->address }}

            </div>

            <div class="col-6">

                <table class="table table-sm table-borderless">

                    <tr>
                        <th width="45%">Invoice No</th>
                        <td>{{ $purchase->invoice_no }}</td>
                    </tr>

                    <tr>
                        <th>Invoice Date</th>
                        <td>
                            {{ date('d-m-Y',strtotime($purchase->purchase_date)) }}
                        </td>
                    </tr>

                    <tr>
                        <th>Store</th>
                        <td>{{ $purchase->store->store_name }}</td>
                    </tr>

                </table>

            </div>

        </div>

        <table class="table table-bordered mt-3">

            <thead class="table-light">

                <tr>

                    <th>#</th>

                    <th>Product</th>

                    <th>Unit</th>

                    <th class="text-end">Qty</th>

                    <th class="text-end">Rate</th>

                    <th class="text-end">Discount</th>

                    <th class="text-end">Tax</th>

                    <th class="text-end">Amount</th>

                </tr>

            </thead>

            <tbody>

                @foreach($purchase->items as $item)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>

                        {{ $item->product->product_name }}

                    </td>

                    <td>

                        {{ $item->product->unit->short_name ?? '' }}

                    </td>

                    <td class="text-end">

                        {{ number_format($item->quantity,2) }}

                    </td>

                    <td class="text-end">

                        {{ number_format($item->unit_price,2) }}

                    </td>

                    <td class="text-end">

                        {{ number_format($item->discount_amount ?? 0,2) }}

                    </td>

                    <td class="text-end">

                        {{ number_format($item->tax_amount ?? 0,2) }}

                    </td>

                    <td class="text-end">

                        {{ number_format($item->total_amount,2) }}

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

        <div class="row">

            <div class="col-7">

                <strong>Remarks</strong>

                <p>

                    {{ $purchase->remarks }}

                </p>

            </div>

            <div class="col-5">

                <table class="table table-bordered">

                    <tr>

                        <th>Sub Total</th>

                        <td class="text-end">

                            {{ number_format($purchase->subtotal,2) }}

                        </td>

                    </tr>

                    <tr>

                        <th>Discount</th>

                        <td class="text-end">

                            {{ number_format($purchase->discount_amount,2) }}

                        </td>

                    </tr>

                    <tr>

                        <th>Tax</th>

                        <td class="text-end">

                            {{ number_format($purchase->tax_amount,2) }}

                        </td>

                    </tr>

                    <tr>

                        <th>Round Off</th>

                        <td class="text-end">

                            {{ number_format($purchase->round_off ?? 0,2) }}

                        </td>

                    </tr>

                    <tr class="table-secondary">

                        <th>Grand Total</th>

                        <th class="text-end">

                            {{ number_format($purchase->grand_total,2) }}

                        </th>

                    </tr>

                </table>

            </div>

        </div>

        <div class="row">

            <div class="col-6">

                <div class="signature">

                    _______________________

                    <br>

                    Prepared By

                </div>

            </div>

            <div class="col-6">

                <div class="signature">

                    _______________________

                    <br>

                    Authorized Signature

                </div>

            </div>

        </div>

    </div>

    <script>
        window.onload = function() {

            window.print();

        };
    </script>

</body>

</html>