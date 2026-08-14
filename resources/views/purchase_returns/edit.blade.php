@extends('layouts.app')

@section('title','Edit Purchase Return')

@section('content')

<div class="container-fluid">

    <form
        action="{{ route('purchase-returns.update', $purchaseReturn->id) }}"
        method="POST"
        id="purchaseReturnForm">

        @csrf
        @method('PUT')

        {{-- Hidden Header Values --}}
        <input
            type="hidden"
            name="purchase_id"
            value="{{ $purchaseReturn->purchase_id }}">

        <input
            type="hidden"
            name="supplier_id"
            value="{{ $purchaseReturn->supplier_id }}">

        <input
            type="hidden"
            name="company_id"
            value="{{ $purchaseReturn->company_id }}">


        {{-- =========================================================
             Header
        ========================================================== --}}

        <div class="card shadow-sm">

            <div class="card-header bg-warning text-dark">

                <div class="d-flex justify-content-between align-items-center">

                    <h4 class="mb-0">

                        <i class="fas fa-edit"></i>

                        Edit Purchase Return

                    </h4>

                    <div>

                        @can('purchase-return.view')

                        <a
                            href="{{ route('purchase-returns.show', $purchaseReturn->id) }}"
                            class="btn btn-primary btn-sm">

                            <i class="fas fa-eye"></i>

                            View

                        </a>

                        @endcan


                        <a
                            href="{{ route('purchase-returns.index') }}"
                            class="btn btn-secondary btn-sm">

                            <i class="fas fa-list"></i>

                            Back

                        </a>

                    </div>

                </div>

            </div>


            <div class="card-body">

                <div class="row">

                    {{-- Company --}}

                    <div class="col-md-3">

                        <label class="form-label">
                            Company
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->purchase->company->company_name ?? '-' }}"
                            readonly>

                    </div>


                    {{-- Return No --}}

                    <div class="col-md-3">

                        <label class="form-label">
                            Return No
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->return_no }}"
                            readonly>

                    </div>


                    {{-- Purchase No --}}

                    <div class="col-md-3">

                        <label class="form-label">
                            Purchase No
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->purchase->purchase_no }}"
                            readonly>

                    </div>


                    {{-- Return Date --}}

                    <div class="col-md-3">

                        <label class="form-label">
                            Return Date
                        </label>

                        <input
                            type="date"
                            name="return_date"
                            class="form-control"
                            value="{{ old(
                                'return_date',
                                \Carbon\Carbon::parse($purchaseReturn->return_date)->format('Y-m-d')
                            ) }}"
                            required>

                    </div>

                </div>


                <div class="row mt-3">

                    {{-- Store --}}

                    <div class="col-md-3">

                        <label class="form-label">
                            Store
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->purchase->store->store_name ?? '-' }}"
                            readonly>

                    </div>


                    {{-- Remarks --}}

                    <div class="col-md-9">

                        <label class="form-label">
                            Remarks
                        </label>

                        <input
                            type="text"
                            name="remarks"
                            class="form-control"
                            value="{{ old(
                                'remarks',
                                $purchaseReturn->remarks
                            ) }}">

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             Supplier Details
        ========================================================== --}}

        <div class="card mt-3 shadow-sm">

            <div class="card-header bg-light">

                <strong>

                    <i class="fas fa-user"></i>

                    Supplier Details

                </strong>

            </div>


            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">

                        <label class="form-label">
                            Supplier
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->supplier->supplier_name ?? '-' }}"
                            readonly>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">
                            Mobile
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->supplier->mobile ?? '-' }}"
                            readonly>

                    </div>


                    <div class="col-md-5">

                        <label class="form-label">
                            Address
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->supplier->billing_address ?? $purchaseReturn->supplier->address ?? '-' }}"
                            readonly>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             Returned Items
        ========================================================== --}}

        <div class="card mt-3 shadow-sm">

            <div class="card-header bg-secondary text-white">

                <strong>

                    <i class="fas fa-box"></i>

                    Returned Items

                </strong>

            </div>


            <div class="card-body p-0">

                <div class="table-responsive">

                    <table
                        class="table table-bordered table-striped table-hover mb-0"
                        id="returnTable">

                        <thead class="table-dark">

                            <tr>

                                <th width="4%">
                                    #
                                </th>

                                <th width="9%">
                                    Code
                                </th>

                                <th>
                                    Product
                                </th>

                                <th width="16%">
                                    Godown
                                </th>

                                <th width="8%"
                                    class="text-end">

                                    Purchased

                                </th>

                                <th width="8%"
                                    class="text-end">

                                    Returned

                                </th>

                                <th width="8%"
                                    class="text-end">

                                    Balance

                                </th>

                                <th width="110">

                                    Return Qty

                                </th>

                                <th width="110"
                                    class="text-end">

                                    Rate

                                </th>

                                <th width="80"
                                    class="text-end">

                                    GST %

                                </th>

                                <th width="110"
                                    class="text-end">

                                    GST Amt

                                </th>

                                <th width="120"
                                    class="text-end">

                                    Total

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($purchaseReturn->items as $item)

                            @php

                            /*
                            |--------------------------------------------------------------------------
                            | Purchase information
                            |--------------------------------------------------------------------------
                            */

                            $purchaseQty =
                            (float) ($item->purchaseItem->quantity ?? 0);

                            $alreadyReturned =
                            (float) ($item->purchaseItem->returned_qty ?? 0);

                            $balanceQty =
                            (float) ($item->purchaseItem->balance_qty ?? 0);

                            /*
                            |--------------------------------------------------------------------------
                            | Current return quantity
                            |--------------------------------------------------------------------------
                            */

                            $currentReturnQty =
                            (float) $item->quantity;

                            /*
                            |--------------------------------------------------------------------------
                            | Maximum editable quantity
                            |--------------------------------------------------------------------------
                            |
                            | Example:
                            |
                            | Purchase = 100
                            | Other returns = 20
                            | Current return = 10
                            |
                            | Balance excluding current = 80
                            |
                            | Maximum while editing = 80 + 10 = 90
                            |
                            */

                            $maxReturnQty =
                            $balanceQty + $currentReturnQty;

                            @endphp


                            <tr>

                                {{-- # --}}

                                <td>
                                    {{ $loop->iteration }}
                                </td>


                                {{-- Product Code --}}

                                <td>

                                    {{ $item->product->product_code ?? '-' }}

                                    <input
                                        type="hidden"
                                        name="product_id[]"
                                        value="{{ $item->product_id }}">

                                </td>


                                {{-- Product --}}

                                <td>

                                    <strong>
                                        {{ $item->product->product_name ?? '-' }}
                                    </strong>

                                    @if($item->product->unit)

                                    <br>

                                    <small class="text-muted">

                                        {{ $item->product->unit->unit_name }}

                                    </small>

                                    @endif

                                </td>


                                {{-- =====================================================
                                         GODOWN
                                    ====================================================== --}}

                                <td>

                                    <select
                                        name="godown_id[]"
                                        class="form-select form-select-sm"
                                        required>

                                        <option value="">

                                            Shop / No Godown

                                        </option>


                                        @if($item->godown)

                                        <option
                                            value="{{ $item->godown_id }}"
                                            selected>

                                            {{ $item->godown->godown_name }}

                                        </option>

                                        @endif

                                    </select>


                                    {{-- Keep current Godown ID visible for debugging if needed --}}

                                    {{--

                                        <small class="text-muted">
                                            ID:
                                            {{ $item->godown_id ?? 'NULL' }}
                                    </small>

                                    --}}

                                </td>


                                {{-- Purchased --}}

                                <td class="text-end">

                                    {{ number_format(
                                            $purchaseQty,
                                            2
                                        ) }}

                                </td>


                                {{-- Already Returned --}}

                                <td class="text-end">

                                    {{ number_format(
                                            $alreadyReturned,
                                            2
                                        ) }}

                                </td>


                                {{-- Balance --}}

                                <td class="text-end">

                                    <strong class="balanceQty">

                                        {{ number_format(
                                                $balanceQty,
                                                2
                                            ) }}

                                    </strong>

                                </td>


                                {{-- Return Qty --}}

                                <td>

                                    <input
                                        type="number"
                                        name="qty[]"
                                        class="form-control form-control-sm returnQty text-end"
                                        value="{{ number_format(
                                                $currentReturnQty,
                                                2,
                                                '.',
                                                ''
                                            ) }}"
                                        min="0"
                                        max="{{ $maxReturnQty }}"
                                        step="0.01"
                                        data-price="{{ $item->purchase_price }}"
                                        data-gst="{{ $item->gst_percent }}">


                                    <small class="text-muted">

                                        Max:
                                        {{ number_format(
                                                $maxReturnQty,
                                                2
                                            ) }}

                                    </small>

                                </td>


                                {{-- Rate --}}

                                <td class="text-end">

                                    {{ number_format(
                                            $item->purchase_price,
                                            2
                                        ) }}


                                    <input
                                        type="hidden"
                                        name="price[]"
                                        value="{{ $item->purchase_price }}">

                                </td>


                                {{-- GST --}}

                                <td class="text-end">

                                    {{ number_format(
                                            $item->gst_percent,
                                            2
                                        ) }}


                                    <input
                                        type="hidden"
                                        name="gst[]"
                                        value="{{ $item->gst_percent }}">

                                </td>


                                {{-- GST Amount --}}

                                <td class="text-end">

                                    <span class="gstAmount">

                                        {{ number_format(
                                                $item->gst_amount,
                                                2
                                            ) }}

                                    </span>


                                    <input
                                        type="hidden"
                                        name="gst_amount[]"
                                        class="gstAmountInput"
                                        value="{{ number_format(
                                                $item->gst_amount,
                                                2,
                                                '.',
                                                ''
                                            ) }}">

                                </td>


                                {{-- Total --}}

                                <td class="text-end">

                                    <span class="returnAmount">

                                        {{ number_format(
                                                $item->line_total,
                                                2
                                            ) }}

                                    </span>


                                    <input
                                        type="hidden"
                                        name="amount[]"
                                        class="amountInput"
                                        value="{{ number_format(
                                                $item->line_total,
                                                2,
                                                '.',
                                                ''
                                            ) }}">

                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- =========================================================
             Totals
        ========================================================== --}}

        <div class="row mt-3">

            <div class="col-md-8"></div>

            <div class="col-md-4">

                <table class="table table-bordered shadow-sm">

                    <tr>

                        <th width="50%">
                            Sub Total
                        </th>

                        <td>

                            <input
                                type="text"
                                id="purchase_return_sub_total"
                                class="form-control text-end"
                                value="{{ number_format(
                                    $purchaseReturn->sub_total,
                                    2,
                                    '.',
                                    ''
                                ) }}"
                                readonly>

                        </td>

                    </tr>


                    <tr>

                        <th>
                            GST Total
                        </th>

                        <td>

                            <input
                                type="text"
                                id="purchase_return_gst_total"
                                class="form-control text-end"
                                value="{{ number_format(
                                    $purchaseReturn->gst_total,
                                    2,
                                    '.',
                                    ''
                                ) }}"
                                readonly>

                        </td>

                    </tr>


                    <tr class="table-primary">

                        <th>
                            Grand Total
                        </th>

                        <td>

                            <input
                                type="text"
                                id="purchase_return_grand_total"
                                class="form-control text-end fw-bold"
                                value="{{ number_format(
                                    $purchaseReturn->total_amount,
                                    2,
                                    '.',
                                    ''
                                ) }}"
                                readonly>

                        </td>

                    </tr>


                    {{-- Values submitted to controller --}}

                    <input
                        type="hidden"
                        name="sub_total"
                        id="purchase_return_sub_total_hidden"
                        value="{{ $purchaseReturn->sub_total }}">

                    <input
                        type="hidden"
                        name="gst_total"
                        id="purchase_return_gst_total_hidden"
                        value="{{ $purchaseReturn->gst_total }}">

                    <input
                        type="hidden"
                        name="grand_total"
                        id="purchase_return_grand_total_hidden"
                        value="{{ $purchaseReturn->total_amount }}">

                </table>

            </div>

        </div>


        {{-- =========================================================
             Buttons
        ========================================================== --}}

        <div class="text-end mt-3 mb-4">

            <button
                type="submit"
                class="btn btn-success">

                <i class="fas fa-save"></i>

                Update Purchase Return

            </button>


            @can('purchase-return.view')

            <a
                href="{{ route(
                        'purchase-returns.show',
                        $purchaseReturn->id
                    ) }}"
                class="btn btn-info">

                <i class="fas fa-eye"></i>

                View

            </a>

            @endcan


            <a
                href="{{ route('purchase-returns.index') }}"
                class="btn btn-secondary">

                <i class="fas fa-times"></i>

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection


@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {

        /*
        |--------------------------------------------------------------------------
        | Elements
        |--------------------------------------------------------------------------
        */

        const rows =
            document.querySelectorAll(
                '#returnTable tbody tr'
            );


        /*
        |--------------------------------------------------------------------------
        | Calculate Row
        |--------------------------------------------------------------------------
        */

        function calculateRow(row) {

            const qtyInput =
                row.querySelector('.returnQty');

            const priceInput =
                row.querySelector(
                    'input[name="price[]"]'
                );

            const gstInput =
                row.querySelector(
                    'input[name="gst[]"]'
                );

            const gstAmountDisplay =
                row.querySelector('.gstAmount');

            const gstAmountInput =
                row.querySelector('.gstAmountInput');

            const amountDisplay =
                row.querySelector('.returnAmount');

            const amountInput =
                row.querySelector('.amountInput');


            if (
                !qtyInput ||
                !priceInput ||
                !gstInput
            ) {

                return;

            }


            let qty =
                parseFloat(
                    qtyInput.value
                ) || 0;


            const maxQty =
                parseFloat(
                    qtyInput.max
                ) || 0;


            /*
            |--------------------------------------------------------------------------
            | Validate Quantity
            |--------------------------------------------------------------------------
            */

            if (qty < 0) {

                qty = 0;

                qtyInput.value = '0.00';

            }


            if (qty > maxQty) {

                qty = maxQty;

                qtyInput.value =
                    maxQty.toFixed(2);

            }


            const price =
                parseFloat(
                    priceInput.value
                ) || 0;


            const gst =
                parseFloat(
                    gstInput.value
                ) || 0;


            /*
            |--------------------------------------------------------------------------
            | Sub Total
            |--------------------------------------------------------------------------
            */

            const subTotal =
                qty * price;


            /*
            |--------------------------------------------------------------------------
            | GST
            |--------------------------------------------------------------------------
            */

            const gstAmount =
                subTotal *
                gst /
                100;


            /*
            |--------------------------------------------------------------------------
            | Grand Total
            |--------------------------------------------------------------------------
            */

            const total =
                subTotal +
                gstAmount;


            /*
            |--------------------------------------------------------------------------
            | Update Display
            |--------------------------------------------------------------------------
            */

            gstAmountDisplay.textContent =
                gstAmount.toFixed(2);


            gstAmountInput.value =
                gstAmount.toFixed(2);


            amountDisplay.textContent =
                total.toFixed(2);


            amountInput.value =
                total.toFixed(2);

        }


        /*
        |--------------------------------------------------------------------------
        | Calculate All Totals
        |--------------------------------------------------------------------------
        */

        function calculateTotals() {

            let subTotal = 0;

            let gstTotal = 0;

            let grandTotal = 0;


            rows.forEach(function(row) {

                const qtyInput =
                    row.querySelector('.returnQty');

                const priceInput =
                    row.querySelector(
                        'input[name="price[]"]'
                    );

                const gstInput =
                    row.querySelector(
                        'input[name="gst[]"]'
                    );


                if (
                    !qtyInput ||
                    !priceInput ||
                    !gstInput
                ) {

                    return;

                }


                const qty =
                    parseFloat(
                        qtyInput.value
                    ) || 0;


                const price =
                    parseFloat(
                        priceInput.value
                    ) || 0;


                const gst =
                    parseFloat(
                        gstInput.value
                    ) || 0;


                const lineSubTotal =
                    qty * price;


                const lineGst =
                    lineSubTotal *
                    gst /
                    100;


                const lineTotal =
                    lineSubTotal +
                    lineGst;


                subTotal +=
                    lineSubTotal;


                gstTotal +=
                    lineGst;


                grandTotal +=
                    lineTotal;

            });


            /*
            |--------------------------------------------------------------------------
            | Display Totals
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                    'purchase_return_sub_total'
                ).value =
                subTotal.toFixed(2);


            document.getElementById(
                    'purchase_return_gst_total'
                ).value =
                gstTotal.toFixed(2);


            document.getElementById(
                    'purchase_return_grand_total'
                ).value =
                grandTotal.toFixed(2);


            /*
            |--------------------------------------------------------------------------
            | Hidden Values
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                    'purchase_return_sub_total_hidden'
                ).value =
                subTotal.toFixed(2);


            document.getElementById(
                    'purchase_return_gst_total_hidden'
                ).value =
                gstTotal.toFixed(2);


            document.getElementById(
                    'purchase_return_grand_total_hidden'
                ).value =
                grandTotal.toFixed(2);

        }


        /*
        |--------------------------------------------------------------------------
        | Quantity Change
        |--------------------------------------------------------------------------
        */

        rows.forEach(function(row) {

            const qtyInput =
                row.querySelector('.returnQty');


            if (!qtyInput) {

                return;

            }


            qtyInput.addEventListener(
                'input',
                function() {

                    calculateRow(row);

                    calculateTotals();

                }
            );


            qtyInput.addEventListener(
                'change',
                function() {

                    calculateRow(row);

                    calculateTotals();

                }
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Initial Calculation
        |--------------------------------------------------------------------------
        */

        rows.forEach(function(row) {

            calculateRow(row);

        });


        calculateTotals();


        /*
        |--------------------------------------------------------------------------
        | Form Validation
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            'purchaseReturnForm'
        ).addEventListener(
            'submit',
            function(event) {

                let hasQuantity = false;

                let invalidQuantity = false;


                rows.forEach(function(row) {

                    const qtyInput =
                        row.querySelector('.returnQty');

                    const godownSelect =
                        row.querySelector(
                            'select[name="godown_id[]"]'
                        );


                    if (!qtyInput) {

                        return;

                    }


                    const qty =
                        parseFloat(
                            qtyInput.value
                        ) || 0;


                    const maxQty =
                        parseFloat(
                            qtyInput.max
                        ) || 0;


                    /*
                    |--------------------------------------------------------------------------
                    | Quantity
                    |--------------------------------------------------------------------------
                    */

                    if (qty > 0) {

                        hasQuantity = true;

                    }


                    if (
                        qty < 0 ||
                        qty > maxQty
                    ) {

                        invalidQuantity = true;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Godown
                    |--------------------------------------------------------------------------
                    */

                    if (
                        qty > 0 &&
                        godownSelect &&
                        godownSelect.value === ''
                    ) {

                        invalidQuantity = true;

                        godownSelect.classList.add(
                            'is-invalid'
                        );

                    }

                });


                /*
                |--------------------------------------------------------------------------
                | No Items
                |--------------------------------------------------------------------------
                */

                if (!hasQuantity) {

                    event.preventDefault();

                    alert(
                        'Please enter return quantity for at least one product.'
                    );

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Invalid Quantity / Godown
                |--------------------------------------------------------------------------
                */

                if (invalidQuantity) {

                    event.preventDefault();

                    alert(
                        'Please check return quantity and Godown.'
                    );

                    return;

                }

            }
        );

    });
</script>

@endpush