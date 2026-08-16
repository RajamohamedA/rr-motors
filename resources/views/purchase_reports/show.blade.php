@extends('layouts.app')

@section('title', 'Purchase Details')

@section('content')

<div class="container-fluid">

    {{-- =========================================================
         Purchase Header
    ========================================================== --}}

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center">

                <h4 class="mb-0">

                    <i class="fas fa-file-invoice"></i>

                    Purchase Details

                </h4>


                <div>

                    <a
                        href="{{ route('purchase-reports.index') }}"
                        class="btn btn-secondary btn-sm">

                        <i class="fas fa-arrow-left"></i>

                        Back

                    </a>


                    @can('purchase-report.print')

                    <a
                        href="{{ route(
                                'purchase-reports.print',
                                $purchase->id
                            ) }}"
                        target="_blank"
                        class="btn btn-success btn-sm">

                        <i class="fas fa-print"></i>

                        Print

                    </a>

                    @endcan

                </div>

            </div>

        </div>


        <div class="card-body">

            <div class="row">


                {{-- =================================================
                     Purchase Information
                ================================================== --}}

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>

                            <th width="45%">
                                Purchase No
                            </th>

                            <td>
                                {{ $purchase->purchase_no ?? '-' }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Date
                            </th>

                            <td>

                                @if($purchase->purchase_date)

                                {{ \Carbon\Carbon::parse(
                                        $purchase->purchase_date
                                    )->format('d-m-Y') }}

                                @else

                                -

                                @endif

                            </td>

                        </tr>


                        <tr>

                            <th>
                                Invoice No
                            </th>

                            <td>
                                {{ $purchase->invoice_no ?? '-' }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Store
                            </th>

                            <td>
                                {{ $purchase->store->store_name ?? '-' }}
                            </td>

                        </tr>

                    </table>

                </div>


                {{-- =================================================
                     Supplier
                ================================================== --}}

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>

                            <th width="45%">
                                Supplier
                            </th>

                            <td>
                                {{ $purchase->supplier->supplier_name ?? '-' }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Mobile
                            </th>

                            <td>
                                {{ $purchase->supplier->mobile ?? '-' }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                GST No
                            </th>

                            <td>
                                {{ $purchase->supplier->gst_no ?? '-' }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Address
                            </th>

                            <td>
                                {{ $purchase->supplier->address ?? '-' }}
                            </td>

                        </tr>

                    </table>

                </div>


                {{-- =================================================
                     Company
                ================================================== --}}

                <div class="col-md-4">

                    <table class="table table-bordered">

                        <tr>

                            <th width="45%">
                                Company
                            </th>

                            <td>
                                {{ $purchase->company->company_name ?? '-' }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Created By
                            </th>

                            <td>
                                {{ $purchase->created_by ?? '-' }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Status
                            </th>

                            <td>

                                @if(
                                isset($purchase->status)
                                && $purchase->status
                                )

                                <span class="badge bg-success">

                                    {{ ucfirst(
                                            $purchase->status
                                        ) }}

                                </span>

                                @else

                                <span class="badge bg-success">

                                    Completed

                                </span>

                                @endif

                            </td>

                        </tr>


                        <tr>

                            <th>
                                Remarks
                            </th>

                            <td>
                                {{ $purchase->remarks ?? '-' }}
                            </td>

                        </tr>

                    </table>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         Purchase Items
    ========================================================== --}}

    <div class="card mt-3 shadow-sm">

        <div class="card-header bg-secondary text-white">

            <strong>

                <i class="fas fa-box"></i>

                Purchase Items

            </strong>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table
                    class="table table-bordered table-striped table-hover mb-0">

                    <thead class="table-dark">

                        <tr>

                            <th width="5%">
                                #
                            </th>

                            <th>
                                Code
                            </th>

                            <th>
                                Product
                            </th>

                            <th class="text-end">
                                Qty
                            </th>

                            <th class="text-end">
                                Rate
                            </th>

                            <th class="text-end">
                                GST %
                            </th>

                            <th class="text-end">
                                GST Amt
                            </th>

                            <th class="text-end">
                                Total
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($purchase->items as $item)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>


                            <td>

                                {{ $item->product->product_code
                                        ?? '-' }}

                            </td>


                            <td>

                                <strong>

                                    {{ $item->product->product_name
                                            ?? '-' }}

                                </strong>

                                @if(
                                isset($item->product->unit)
                                && $item->product->unit
                                )

                                <br>

                                <small class="text-muted">

                                    {{ $item->product->unit->unit_name }}

                                </small>

                                @endif

                            </td>


                            <td class="text-end">

                                {{ number_format(
                                        (float) $item->quantity,
                                        2
                                    ) }}

                            </td>


                            <td class="text-end">

                                {{ number_format(
                                        (float) $item->purchase_price,
                                        2
                                    ) }}

                            </td>


                            <td class="text-end">

                                {{ number_format(
                                        (float) $item->gst_percent,
                                        2
                                    ) }}

                            </td>


                            <td class="text-end">

                                {{ number_format(
                                        (float) $item->gst_amount,
                                        2
                                    ) }}

                            </td>


                            <td class="text-end">

                                {{ number_format(
                                        (float) $item->total_amount,
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

                </table>

            </div>

        </div>

    </div>


    {{-- =========================================================
         Purchase Totals
    ========================================================== --}}

    <div class="row mt-3">

        <div class="col-md-8"></div>


        <div class="col-md-4">

            <table class="table table-bordered shadow-sm">


                {{-- Sub Total --}}

                <tr>

                    <th width="50%">
                        Sub Total
                    </th>

                    <td class="text-end">

                        {{ number_format(
                            (float) (
                                $purchase->sub_total ?? 0
                            ),
                            2
                        ) }}

                    </td>

                </tr>


                {{-- GST Total --}}

                <tr>

                    <th>
                        GST Total
                    </th>

                    <td class="text-end">

                        {{ number_format(
                            (float) (
                                $purchase->gst_total ?? 0
                            ),
                            2
                        ) }}

                    </td>

                </tr>


                {{-- Grand Total --}}

                <tr class="table-primary">

                    <th>
                        Grand Total
                    </th>

                    <th class="text-end">

                        {{ number_format(
                            (float) (
                                $purchase->grand_total ?? 0
                            ),
                            2
                        ) }}

                    </th>

                </tr>

            </table>

        </div>

    </div>


    {{-- =========================================================
         Bottom Buttons
    ========================================================== --}}

    <div class="text-end mt-3 mb-4">

        @can('purchase-report.print')

        <a
            href="{{ route(
                    'purchase-reports.print',
                    $purchase->id
                ) }}"
            target="_blank"
            class="btn btn-success">

            <i class="fas fa-print"></i>

            Print

        </a>

        @endcan


        <a
            href="{{ route(
                'purchase-reports.index'
            ) }}"
            class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>

            Back to Report

        </a>

    </div>

</div>

@endsection