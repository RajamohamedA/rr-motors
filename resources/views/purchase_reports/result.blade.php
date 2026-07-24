@if($purchases->count())

@php
$grandTotal = 0;
@endphp

<div class="table-responsive">

    <table class="table table-bordered table-striped table-hover">

        <thead class="table-dark">

            <tr>

                <th width="5%">#</th>

                <th>Date</th>

                <th>Purchase No</th>

                <th>Supplier</th>

                <th>Store</th>

                <th class="text-center">Items</th>

                <th class="text-end">Amount</th>

                <th width="8%">Action</th>

            </tr>

        </thead>

        <tbody>

            @foreach($purchases as $purchase)

            @php
            $grandTotal += $purchase->grand_total;
            @endphp

            <tr>

                <td>{{ $loop->iteration }}</td>

                <td>{{ date('d-m-Y',strtotime($purchase->purchase_date)) }}</td>

                <td>{{ $purchase->purchase_no }}</td>

                <td>{{ $purchase->supplier->supplier_name ?? '-' }}</td>

                <td>{{ $purchase->store->store_name ?? '-' }}</td>

                <td class="text-center">

                    {{ $purchase->items->count() }}

                </td>

                <td class="text-end">

                    {{ number_format($purchase->grand_total,2) }}

                </td>

                <td class="text-center">

                    <a href="{{ route('purchases.show',$purchase->id) }}"
                        class="btn btn-sm btn-primary">

                        <i class="fas fa-eye"></i>

                    </a>

                    <a href="{{ route('purchases.print',$purchase->id) }}"
                        target="_blank"
                        class="btn btn-sm btn-success">

                        <i class="fas fa-print"></i>

                    </a>

                </td>

            </tr>

            @endforeach

        </tbody>

        <tfoot>

            <tr class="table-primary">

                <th colspan="6" class="text-end">

                    Grand Total

                </th>

                <th class="text-end">

                    {{ number_format($grandTotal,2) }}

                </th>

                <th></th>

            </tr>

        </tfoot>

    </table>

</div>

@else

<div class="alert alert-warning text-center mb-0">

    <i class="fas fa-exclamation-circle"></i>

    No Purchase Records Found

</div>

@endif