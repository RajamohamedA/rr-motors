<!DOCTYPE html>
<html>

<head>

    <title>Payment Receipt</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-size: 14px;
        }

        .receipt {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #000;
        }
    </style>

</head>

<body onload="window.print()">

    <div class="receipt">

        <div class="text-center mb-4">

            <h2>{{ $payment->company->company_name }}</h2>

            <h4>Customer Payment Receipt</h4>

        </div>

        <table class="table table-bordered">

            <tr>
                <th width="220">Receipt No</th>
                <td>{{ $payment->receipt_no }}</td>
            </tr>

            <tr>
                <th>Date</th>
                <td>{{ date('d-m-Y',strtotime($payment->payment_date)) }}</td>
            </tr>

            <tr>
                <th>Customer</th>
                <td>{{ $payment->customer->customer_name }}</td>
            </tr>

            <tr>
                <th>Invoice No</th>
                <td>{{ $payment->sale->invoice_no }}</td>
            </tr>

            <tr>
                <th>Invoice Amount</th>
                <td>{{ number_format($payment->sale->grand_total,2) }}</td>
            </tr>

            <tr>
                <th>Received Amount</th>
                <td>{{ number_format($payment->amount,2) }}</td>
            </tr>

            <tr>
                <th>Payment Mode</th>
                <td>{{ $payment->payment_mode }}</td>
            </tr>

            <tr>
                <th>Reference No</th>
                <td>{{ $payment->reference_no }}</td>
            </tr>

            <tr>
                <th>Remarks</th>
                <td>{{ $payment->remarks }}</td>
            </tr>

        </table>

        <br><br>

        <div class="row">

            <div class="col-6 text-center">

                ______________________

                <br>

                Customer Signature

            </div>

            <div class="col-6 text-center">

                ______________________

                <br>

                Authorized Signature

            </div>

        </div>

    </div>

</body>

</html>