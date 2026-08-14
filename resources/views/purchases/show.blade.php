@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">

        <div>
            <h4 class="mb-1">
                <i class="fas fa-file-invoice"></i>
                Purchase Details
            </h4>

            <small class="text-muted">
                Purchase No: {{ $purchase->purchase_no }}
            </small>
        </div>

        <div class="d-flex gap-2">

            <a
                href="{{ route('purchases.index') }}"
                class="btn btn-secondary">

                <i class="fas fa-arrow-left"></i>
                Back

            </a>

            @can('purchase.edit')
            <a
                href="{{ route('purchases.edit', $purchase->id) }}"
                class="btn btn-warning">

                <i class="fas fa-edit"></i>
                Edit

            </a>

            @endcan
            
            @can('purchase.view')
            <a href="{{ route('purchases.print',$purchase->id) }}" target="_blank" class="btn btn-success btn-sm">
                <i class="fas fa-print"></i> Print </a>
            @endcan

        </div>

    </div>


    <!-- Purchase Details -->
    <div class="card shadow-sm">

        <!-- Header -->
        <div class="card-header bg-primary text-white">

            <div class="row align-items-center">

                <div class="col-md-6">

                    <h4 class="mb-0">
                        <i class="fas fa-shopping-cart"></i>
                        PURCHASE
                    </h4>

                </div>

                <div class="col-md-6 text-md-end">

                    <strong>
                        {{ $purchase->purchase_no }}
                    </strong>

                </div>

            </div>

        </div>


        <div class="card-body">


            <!-- Company / Purchase Information -->
            <div class="row mb-4">

                <!-- Company -->
                <div class="col-md-4">

                    <div class="border rounded p-3 h-100">

                        <h6 class="text-primary border-bottom pb-2">
                            <i class="fas fa-building"></i>
                            Company
                        </h6>

                        <h5 class="mb-1">

                            {{ $purchase->company->company_name ?? '-' }}

                        </h5>

                    </div>

                </div>


                <!-- Store -->
                <div class="col-md-4">

                    <div class="border rounded p-3 h-100">

                        <h6 class="text-primary border-bottom pb-2">
                            <i class="fas fa-store"></i>
                            Store
                        </h6>

                        <h5 class="mb-1">

                            {{ $purchase->store->store_name ?? '-' }}

                        </h5>

                    </div>

                </div>


                <!-- Purchase Date -->
                <div class="col-md-4">

                    <div class="border rounded p-3 h-100">

                        <h6 class="text-primary border-bottom pb-2">
                            <i class="fas fa-calendar"></i>
                            Purchase Date
                        </h6>

                        <h5 class="mb-1">

                            {{ $purchase->purchase_date
                                ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y')
                                : '-' }}

                        </h5>

                    </div>

                </div>

            </div>


            <!-- Supplier / Invoice Information -->
            <div class="row mb-4">

                <!-- Supplier -->
                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <h6 class="text-primary">
                            <i class="fas fa-user"></i>
                            Supplier
                        </h6>

                        <div class="fw-bold">

                            {{ $purchase->supplier->supplier_name ?? '-' }}

                        </div>

                    </div>

                </div>


                <!-- Invoice No -->
                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <h6 class="text-primary">
                            <i class="fas fa-file-alt"></i>
                            Invoice No
                        </h6>

                        <div class="fw-bold">

                            {{ $purchase->invoice_no ?: '-' }}

                        </div>

                    </div>

                </div>


                <!-- Invoice Date -->
                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <h6 class="text-primary">
                            <i class="fas fa-calendar-alt"></i>
                            Invoice Date
                        </h6>

                        <div class="fw-bold">

                            {{ $purchase->invoice_date
                                ? \Carbon\Carbon::parse($purchase->invoice_date)->format('d-m-Y')
                                : '-' }}

                        </div>

                    </div>

                </div>

            </div>


            <!-- Purchase Items -->
            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th width="50">
                                #
                            </th>

                            <th width="120">
                                Product Code
                            </th>

                            <th>
                                Product
                            </th>

                            <th width="180">
                                Godown
                            </th>

                            <th width="100" class="text-end">
                                Qty
                            </th>

                            <th width="130" class="text-end">
                                Price
                            </th>

                            <th width="90" class="text-end">
                                GST %
                            </th>

                            <th width="150" class="text-end">
                                Amount
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($purchase->items as $index => $item)

                        <tr>

                            <!-- S.No -->
                            <td>

                                {{ $index + 1 }}

                            </td>


                            <!-- Product Code -->
                            <td>

                                {{ $item->product->product_code ?? '-' }}

                            </td>


                            <!-- Product -->
                            <td>

                                <strong>
                                    {{ $item->product->product_name ?? '-' }}
                                </strong>

                            </td>


                            <!-- Godown -->
                            <td>

                                @if($item->godown)

                                <span class="badge bg-info text-dark">

                                    <i class="fas fa-warehouse"></i>

                                    {{ $item->godown->godown_name }}

                                </span>

                                @else

                                <span class="badge bg-secondary">

                                    Shop / No Godown

                                </span>

                                @endif

                            </td>


                            <!-- Quantity -->
                            <td class="text-end">

                                {{ number_format(
                                        $item->quantity,
                                        3,
                                        '.',
                                        ''
                                    ) }}

                            </td>


                            <!-- Price -->
                            <td class="text-end">

                                ₹{{ number_format(
                                        $item->purchase_price,
                                        2
                                    ) }}

                            </td>


                            <!-- GST -->
                            <td class="text-end">

                                {{ number_format(
                                        $item->gst_percent,
                                        2
                                    ) }}%

                            </td>


                            <!-- Amount -->
                            <td class="text-end fw-bold">

                                ₹{{ number_format(
                                        $item->line_total,
                                        2
                                    ) }}

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="8"
                                class="text-center text-muted py-4">

                                No purchase items found.

                            </td>

                        </tr>

                        @endforelse

                    </tbody>


                    <!-- Totals -->
                    <tfoot>

                        <tr>

                            <th
                                colspan="7"
                                class="text-end">

                                Grand Total

                            </th>

                            <th class="text-end">

                                ₹{{ number_format(
                                    $purchase->grand_total ?? 0,
                                    2
                                ) }}

                            </th>

                        </tr>

                    </tfoot>

                </table>

            </div>


            <!-- Remarks -->
            @if($purchase->remarks)

            <div class="border rounded p-3 mt-4">

                <h6 class="text-primary">

                    <i class="fas fa-comment-alt"></i>

                    Remarks

                </h6>

                <div class="text-muted">

                    {{ $purchase->remarks }}

                </div>

            </div>

            @endif


            <!-- Bottom Summary -->
            <div class="row justify-content-end mt-4">

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>

                            <th>
                                Total Items
                            </th>

                            <td class="text-end">

                                {{ $purchase->items->count() }}

                            </td>

                        </tr>

                        <tr>

                            <th>
                                Total Quantity
                            </th>

                            <td class="text-end">

                                {{ number_format(
                                    $purchase->items->sum('quantity'),
                                    3,
                                    '.',
                                    ''
                                ) }}

                            </td>

                        </tr>

                        <tr class="table-primary">

                            <th>
                                Grand Total
                            </th>

                            <th class="text-end">

                                ₹{{ number_format(
                                    $purchase->grand_total ?? 0,
                                    2
                                ) }}

                            </th>

                        </tr>

                    </table>

                </div>

            </div>


            <!-- Footer -->
            <div class="text-center text-muted mt-4">

                <small>
                    Purchase No:
                    {{ $purchase->purchase_no }}
                </small>

            </div>

        </div>

    </div>

</div>


<!-- Print CSS -->
<style>
    @media print {

        body {
            background: #fff !important;
        }

        .no-print,
        nav,
        .navbar,
        .sidebar,
        .main-sidebar,
        .app-sidebar,
        footer {
            display: none !important;
        }

        .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .card-header {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .table {
            width: 100% !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000 !important;
        }

        .badge {
            border: 1px solid #000;
            color: #000 !important;
            background: transparent !important;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

    }
</style>

@endsection