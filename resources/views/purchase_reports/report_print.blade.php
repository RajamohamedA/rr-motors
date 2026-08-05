<!DOCTYPE html>
<html>

<head>
    <title>Purchase Report</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 8px;
        }

        th {
            background: #f2f2f2;
        }

        .text-end {
            text-align: right;
        }
    </style>
</head>

<body>

    <h2 style="text-align:center;">Purchase Report</h2>

    <table>

        <thead>

            <tr>

                <th>#</th>

                <th>Invoice No</th>

                <th>Date</th>

                <th>Supplier</th>

                <th>Store</th>

                <th class="text-end">Grand Total</th>

            </tr>

        </thead>

        <tbody>

            @foreach($purchases as $purchase)

            <tr>

                <td>{{ $loop->iteration }}</td>

                <td>{{ $purchase->invoice_no }}</td>

                <td>{{ $purchase->purchase_date }}</td>

                <td>{{ $purchase->supplier->supplier_name ?? '-' }}</td>

                <td>{{ $purchase->store->store_name ?? '-' }}</td>

                <td class="text-end">
                    {{ number_format($purchase->grand_total,2) }}
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>

</body>

</html>