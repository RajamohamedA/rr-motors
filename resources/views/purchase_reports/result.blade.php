@if($purchases->count())

@php
$purchaseReportGrandTotal = 0;
@endphp

<div class="table-responsive">

    <table class="table table-bordered table-striped table-hover mb-0">

        <thead class="table-dark">

            <tr>
                <th width="5%">#</th>
                <th>Company</th>
                <th>Date</th>
                <th>Purchase No</th>
                <th>Supplier</th>
                <th>Store</th>
                <th class="text-center">Items</th>
                <th class="text-end">Amount</th>
                <th width="9%" class="text-center">Action</th>
            </tr>

        </thead>

        <tbody>

            @foreach($purchases as $purchase)

            @php
            $purchaseAmount =
            (float) ($purchase->grand_total ?? 0);

            $purchaseReportGrandTotal +=
            $purchaseAmount;
            @endphp

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $purchase->company->company_name ?? '-' }}
                </td>

                <td>
                    @if($purchase->purchase_date)
                    {{ \Carbon\Carbon::parse(
                                    $purchase->purchase_date
                                )->format('d-m-Y') }}
                    @else
                    -
                    @endif
                </td>

                <td>
                    <strong>
                        {{ $purchase->purchase_no ?? '-' }}
                    </strong>
                </td>

                <td>
                    {{ $purchase->supplier->supplier_name ?? '-' }}
                </td>

                <td>
                    {{ $purchase->store->store_name ?? '-' }}
                </td>

                <td class="text-center">
                    {{ $purchase->items->count() }}
                </td>

                <td class="text-end">
                    {{ number_format(
                                $purchaseAmount,
                                2
                            ) }}
                </td>

                <td class="text-center">

                    @can('purchase-report.view')

                    <a
                        href="{{ route(
                                        'purchase-reports.show',
                                        $purchase->id
                                    ) }}"
                        class="btn btn-sm btn-primary"
                        title="View">

                        <i class="fas fa-eye"></i>

                    </a>

                    @endcan

                    @can('purchase-report.print')

                    <a
                        href="{{ route(
                                        'purchase-reports.print',
                                        $purchase->id
                                    ) }}"
                        target="_blank"
                        class="btn btn-sm btn-success"
                        title="Print">

                        <i class="fas fa-print"></i>

                    </a>

                    @endcan

                </td>

            </tr>

            @endforeach

        </tbody>

        <tfoot>

            <tr class="table-primary">

                <th colspan="7" class="text-end">
                    Grand Total
                </th>

                <th class="text-end">
                    {{ number_format(
                            $purchaseReportGrandTotal,
                            2
                        ) }}
                </th>

                <th></th>

            </tr>

        </tfoot>

    </table>

</div>

@else

<div class="alert alert-warning text-center mb-0">

    <i class="fas fa-exclamation-circle me-1"></i>

    No Purchase Records Found

</div>

@endif