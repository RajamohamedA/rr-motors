<!DOCTYPE html>
<html>

<head>

    <title>Sales Report</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-size: 13px;
        }

        table {
            font-size: 12px;
        }

        @media print {

            .no-print {
                display: none;
            }

        }
    </style>

</head>

<body onload="window.print()">

    <div class="container mt-3">

        <div class="text-center">

            <h2>RR Motors</h2>

            <h4>Sales Report</h4>

        </div>

        <hr>

        <table class="table table-bordered">

            <thead class="table-dark">

                <tr>

                    <th>Date</th>

                    <th>Invoice</th>

                    <th>Customer</th>

                    <th>Company</th>

                    <th>Store</th>

                    <th class="text-end">Grand Total</th>

                    <th class="text-end">Paid</th>

                    <th class="text-end">Balance</th>

                </tr>

            </thead>

            <tbody>

                @foreach($sales as $sale)

                <tr>

                    <td>{{ date('d-m-Y',strtotime($sale->invoice_date)) }}</td>

                    <td>{{ $sale->invoice_no }}</td>

                    <td>{{ $sale->customer->customer_name }}</td>

                    <td>{{ $sale->company->company_name }}</td>

                    <td>{{ $sale->store->store_name }}</td>

                    <td class="text-end">

                        {{ number_format($sale->grand_total,2) }}

                    </td>

                    <td class="text-end">

                        {{ number_format($sale->paid_amount,2) }}

                    </td>

                    <td class="text-end">

                        {{ number_format($sale->balance_amount,2) }}

                    </td>

                </tr>

                @endforeach

            </tbody>

            <tfoot>

                <tr class="table-secondary">

                    <th colspan="5" class="text-end">

                        Total

                    </th>

                    <th class="text-end">

                        {{ number_format($summary['grand'],2) }}

                    </th>

                    <th class="text-end">

                        {{ number_format($summary['paid'],2) }}

                    </th>

                    <th class="text-end">

                        {{ number_format($summary['balance'],2) }}

                    </th>

                </tr>

            </tfoot>

        </table>

        <br><br>

        <div class="row">

            <div class="col-6">

                Printed On:

                {{ now()->format('d-m-Y H:i') }}

            </div>

            <div class="col-6 text-end">

                _______________________

                <br>

                Authorized Signature

            </div>

        </div>

    </div>

</body>

</html>