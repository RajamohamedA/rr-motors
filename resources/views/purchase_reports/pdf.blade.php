<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>Purchase Invoice</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000;
            padding: 6px;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .company-logo {
            width: 70px;
            height: 70px;
        }

        h2,
        h3,
        h4 {
            margin: 0;
        }

        .heading {
            background: #efefef;
            font-weight: bold;
        }
    </style>

</head>

<body>

    <table class="mb-20">

        <tr>

            <td width="15%">

                @if($purchase->company->logo)

                <img src="{{ public_path('storage/'.$purchase->company->logo) }}"
                    class="company-logo">

                @endif

            </td>

            <td width="55%">

                <h2>{{ $purchase->company->company_name }}</h2>

                {{ $purchase->company->address }}<br>

                Phone :
                {{ $purchase->company->phone }}<br>

                GST :
                {{ $purchase->company->gst_number }}

            </td>

            <td width="30%" class="text-end">

                <h2>Purchase Invoice</h2>

            </td>

        </tr>

    </table>

    <hr>

    <table class="mb-20">

        <tr>

            <td width="50%">

                <b>Supplier</b><br>

                {{ $purchase->supplier->supplier_name }}<br>

                {{ $purchase->supplier->mobile }}<br>

                {{ $purchase->supplier->address }}

            </td>

            <td width="50%">

                <table>

                    <tr>

                        <td width="45%"><b>Invoice No</b></td>

                        <td>{{ $purchase->invoice_no }}</td>

                    </tr>

                    <tr>

                        <td><b>Date</b></td>

                        <td>{{ date('d-m-Y',strtotime($purchase->purchase_date)) }}</td>

                    </tr>

                    <tr>

                        <td><b>Store</b></td>

                        <td>{{ $purchase->store->store_name }}</td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

    <table class="table-bordered">

        <thead>

            <tr class="heading">

                <th width="5%">#</th>

                <th>Product</th>

                <th width="10%">Unit</th>

                <th width="10%">Qty</th>

                <th width="12%">Rate</th>

                <th width="12%">Discount</th>

                <th width="12%">Tax</th>

                <th width="15%">Amount</th>

            </tr>

        </thead>

        <tbody>

            @foreach($purchase->items as $item)

            <tr>

                <td class="text-center">

                    {{ $loop->iteration }}

                </td>

                <td>

                    {{ $item->product->product_name }}

                </td>

                <td class="text-center">

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

    <br>

    <table>

        <tr>

            <td width="60%">

                <b>Remarks</b><br>

                {{ $purchase->remarks }}

            </td>

            <td width="40%">

                <table class="table-bordered">

                    <tr>

                        <td><b>Sub Total</b></td>

                        <td class="text-end">

                            {{ number_format($purchase->subtotal,2) }}

                        </td>

                    </tr>

                    <tr>

                        <td><b>Discount</b></td>

                        <td class="text-end">

                            {{ number_format($purchase->discount_amount,2) }}

                        </td>

                    </tr>

                    <tr>

                        <td><b>Tax</b></td>

                        <td class="text-end">

                            {{ number_format($purchase->tax_amount,2) }}

                        </td>

                    </tr>

                    <tr>

                        <td><b>Round Off</b></td>

                        <td class="text-end">

                            {{ number_format($purchase->round_off ?? 0,2) }}

                        </td>

                    </tr>

                    <tr>

                        <td><b>Grand Total</b></td>

                        <td class="text-end">

                            <b>{{ number_format($purchase->grand_total,2) }}</b>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

    <br><br><br>

    <table>

        <tr>

            <td width="50%" class="text-center">

                _____________________<br>

                Prepared By

            </td>

            <td width="50%" class="text-center">

                _____________________<br>

                Authorized Signature

            </td>

        </tr>

    </table>

</body>

</html>