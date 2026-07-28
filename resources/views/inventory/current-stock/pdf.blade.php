<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <style>
        body {

            font-family: DejaVu Sans, sans-serif;

            font-size: 11px;

        }

        table {

            width: 100%;

            border-collapse: collapse;

        }

        table th,
        table td {

            border: 1px solid #000;

            padding: 5px;

        }

        table th {

            background: #f0f0f0;

        }

        .text-right {

            text-align: right;

        }

        .text-center {

            text-align: center;

        }
    </style>

</head>

<body>

    @include('inventory.current-stock.report')

</body>

</html>