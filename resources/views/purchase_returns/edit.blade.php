@extends('layouts.app')

@section('title', 'Edit Purchase Return')

@section('content')

<div class="container-fluid">

    <form
        action="{{ route('purchase-returns.update', $purchaseReturn->id) }}"
        method="POST"
        id="purchaseReturnEditForm">

        @csrf
        @method('PUT')

        {{-- =========================================================
             Hidden Header Values
        ========================================================== --}}

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

                    <div class="col-md-3 mb-3">

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

                    <div class="col-md-3 mb-3">

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

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Purchase No
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->purchase->purchase_no ?? '-' }}"
                            readonly>

                    </div>


                    {{-- Return Date --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Return Date
                        </label>

                        <input
                            type="date"
                            name="return_date"
                            class="form-control"
                            value="{{ old(
                                'return_date',
                                \Carbon\Carbon::parse(
                                    $purchaseReturn->return_date
                                )->format('Y-m-d')
                            ) }}"
                            required>

                    </div>

                </div>


                <div class="row">

                    {{-- Store --}}

                    <div class="col-md-3 mb-3">

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

                    <div class="col-md-9 mb-3">

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

                    {{-- Supplier --}}

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Supplier
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->supplier->supplier_name ?? '-' }}"
                            readonly>

                    </div>


                    {{-- Mobile --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Mobile
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->supplier->mobile ?? '-' }}"
                            readonly>

                    </div>


                    {{-- Address --}}

                    <div class="col-md-5 mb-3">

                        <label class="form-label">
                            Address
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchaseReturn->supplier->billing_address
                                ?? $purchaseReturn->supplier->address
                                ?? '-' }}"
                            readonly>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             Return Items
        ========================================================== --}}

        <div class="card mt-3 shadow-sm">

            <div class="card-header bg-secondary text-white">

                <strong>
                    <i class="fas fa-box"></i>
                    Return Items
                </strong>

            </div>


            <div class="card-body p-0">

                <div class="table-responsive">

                    <table
                        class="table table-bordered table-striped table-hover mb-0"
                        id="purchaseReturnEditTable">

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

                                <th width="8%" class="text-end">
                                    Purchased
                                </th>

                                <th width="8%" class="text-end">
                                    Returned
                                </th>

                                <th width="8%" class="text-end">
                                    Balance
                                </th>

                                <th width="110">
                                    Return Qty
                                </th>

                                <th width="110" class="text-end">
                                    Rate
                                </th>

                                <th width="80" class="text-end">
                                    GST %
                                </th>

                                <th width="110" class="text-end">
                                    GST Amt
                                </th>

                                <th width="120" class="text-end">
                                    Total
                                </th>

                            </tr>

                        </thead>


                        <tbody id="purchaseReturnEditItems">

                            @forelse($purchaseReturn->items as $item)

                            @php

                            /*
                            |--------------------------------------------------------------------------
                            | Purchase Item Information
                            |--------------------------------------------------------------------------
                            */

                            $purchaseItem =
                            $item->purchaseItem;


                            /*
                            |--------------------------------------------------------------------------
                            | Purchased Quantity
                            |--------------------------------------------------------------------------
                            */

                            $purchaseQty =
                            (float) (
                            $purchaseItem->quantity ?? 0
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | Previous Returned Quantity
                            |--------------------------------------------------------------------------
                            |
                            | This excludes the current Purchase Return.
                            |
                            */

                            $previousReturnedQty =
                            (float) (
                            $purchaseItem->returned_qty ?? 0
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | Balance
                            |--------------------------------------------------------------------------
                            */

                            $balanceQty =
                            (float) (
                            $purchaseItem->balance_qty ?? 0
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | Current Return Quantity
                            |--------------------------------------------------------------------------
                            */

                            $currentReturnQty =
                            (float) $item->quantity;


                            /*
                            |--------------------------------------------------------------------------
                            | Maximum During Edit
                            |--------------------------------------------------------------------------
                            |
                            | Current return quantity is added back.
                            |
                            */

                            $maxReturnQty =
                            $balanceQty +
                            $currentReturnQty;


                            /*
                            |--------------------------------------------------------------------------
                            | Godown
                            |--------------------------------------------------------------------------
                            */

                            $godownId =
                            $item->godown_id;


                            $godownName =
                            $item->godown->godown_name
                            ?? 'Shop / No Godown';


                            /*
                            |--------------------------------------------------------------------------
                            | Product
                            |--------------------------------------------------------------------------
                            */

                            $product =
                            $item->product;


                            /*
                            |--------------------------------------------------------------------------
                            | Price
                            |--------------------------------------------------------------------------
                            */

                            $price =
                            (float) (
                            $item->purchase_price ?? 0
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | GST
                            |--------------------------------------------------------------------------
                            */

                            $gstPercent =
                            (float) (
                            $item->gst_percent ?? 0
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | Line Calculation
                            |--------------------------------------------------------------------------
                            */

                            $lineSubTotal =
                            $currentReturnQty *
                            $price;


                            $gstAmount =
                            $lineSubTotal *
                            $gstPercent /
                            100;


                            $lineTotal =
                            $lineSubTotal +
                            $gstAmount;

                            @endphp


                            <tr>

                                {{-- =====================================================
                                     #
                                ====================================================== --}}

                                <td>

                                    {{ $loop->iteration }}

                                </td>


                                {{-- =====================================================
                                     Product Code
                                ====================================================== --}}

                                <td>

                                    {{ $product->product_code ?? '-' }}

                                    <input
                                        type="hidden"
                                        name="product_id[]"
                                        value="{{ $item->product_id }}">

                                </td>


                                {{-- =====================================================
                                     Product
                                ====================================================== --}}

                                <td>

                                    <strong>
                                        {{ $product->product_name ?? '-' }}
                                    </strong>


                                    @if(
                                    isset($product->unit) &&
                                    $product->unit
                                    )

                                    <br>

                                    <small class="text-muted">

                                        {{ $product->unit->unit_name }}

                                    </small>

                                    @endif

                                </td>


                                {{-- =====================================================
                                     Godown
                                ====================================================== --}}

                                <td>

                                    <select
                                        name="godown_id[]"
                                        class="form-select form-select-sm purchaseReturnEditGodown"
                                        required>

                                        <option value="">
                                            Shop / No Godown
                                        </option>


                                        @if($godownId)

                                        <option
                                            value="{{ $godownId }}"
                                            selected>

                                            {{ $godownName }}

                                        </option>

                                        @endif

                                    </select>


                                    <input
                                        type="hidden"
                                        class="purchaseReturnEditOriginalGodown"
                                        value="{{ $godownId }}">

                                </td>


                                {{-- =====================================================
                                     Purchased
                                ====================================================== --}}

                                <td class="text-end">

                                    {{ number_format(
                                        $purchaseQty,
                                        2
                                    ) }}

                                </td>


                                {{-- =====================================================
                                     Previous Returned
                                ====================================================== --}}

                                <td class="text-end">

                                    <span
                                        class="purchaseReturnEditReturnedQty">

                                        {{ number_format(
                                            $previousReturnedQty,
                                            2
                                        ) }}

                                    </span>

                                </td>


                                {{-- =====================================================
                                     Balance
                                ====================================================== --}}

                                <td class="text-end">

                                    <strong
                                        class="purchaseReturnEditBalanceQty">

                                        {{ number_format(
                                            $balanceQty,
                                            2
                                        ) }}

                                    </strong>

                                </td>


                                {{-- =====================================================
                                     Return Quantity
                                ====================================================== --}}

                                <td>

                                    <input
                                        type="number"
                                        name="qty[]"
                                        class="form-control form-control-sm purchaseReturnEditQty text-end"
                                        value="{{ number_format(
                                            $currentReturnQty,
                                            2,
                                            '.',
                                            ''
                                        ) }}"
                                        min="0"
                                        max="{{ number_format(
                                            $maxReturnQty,
                                            2,
                                            '.',
                                            ''
                                        ) }}"
                                        step="0.01">


                                    <small class="text-muted">

                                        Max:

                                        <span
                                            class="purchaseReturnEditMaxQtyText">

                                            {{ number_format(
                                                $maxReturnQty,
                                                2
                                            ) }}

                                        </span>

                                    </small>

                                </td>


                                {{-- =====================================================
                                     Rate
                                ====================================================== --}}

                                <td class="text-end">

                                    {{ number_format(
                                        $price,
                                        2
                                    ) }}


                                    <input
                                        type="hidden"
                                        name="price[]"
                                        class="purchaseReturnEditPrice"
                                        value="{{ number_format(
                                            $price,
                                            2,
                                            '.',
                                            ''
                                        ) }}">

                                </td>


                                {{-- =====================================================
                                     GST %
                                ====================================================== --}}

                                <td class="text-end">

                                    {{ number_format(
                                        $gstPercent,
                                        2
                                    ) }}


                                    <input
                                        type="hidden"
                                        name="gst[]"
                                        class="purchaseReturnEditGst"
                                        value="{{ number_format(
                                            $gstPercent,
                                            2,
                                            '.',
                                            ''
                                        ) }}">

                                </td>


                                {{-- =====================================================
                                     GST Amount
                                ====================================================== --}}

                                <td class="text-end">

                                    <span
                                        class="purchaseReturnEditGstAmount">

                                        {{ number_format(
                                            $gstAmount,
                                            2
                                        ) }}

                                    </span>


                                    <input
                                        type="hidden"
                                        name="gst_amount[]"
                                        class="purchaseReturnEditGstAmountInput"
                                        value="{{ number_format(
                                            $gstAmount,
                                            2,
                                            '.',
                                            ''
                                        ) }}">

                                </td>


                                {{-- =====================================================
                                     Total
                                ====================================================== --}}

                                <td class="text-end">

                                    <span
                                        class="purchaseReturnEditAmount">

                                        {{ number_format(
                                            $lineTotal,
                                            2
                                        ) }}

                                    </span>


                                    <input
                                        type="hidden"
                                        name="amount[]"
                                        class="purchaseReturnEditAmountInput"
                                        value="{{ number_format(
                                            $lineTotal,
                                            2,
                                            '.',
                                            ''
                                        ) }}">

                                </td>

                            </tr>


                            @empty

                            <tr>

                                <td
                                    colspan="12"
                                    class="text-center text-muted py-4">

                                    No return items found.

                                </td>

                            </tr>

                            @endforelse

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

                    {{-- Sub Total --}}

                    <tr>

                        <th width="50%">
                            Sub Total
                        </th>

                        <td>

                            <input
                                type="text"
                                id="purchaseReturnEditSubTotal"
                                class="form-control text-end"
                                value="{{ number_format(
                                    (float) $purchaseReturn->sub_total,
                                    2,
                                    '.',
                                    ''
                                ) }}"
                                readonly>

                        </td>

                    </tr>


                    {{-- GST Total --}}

                    <tr>

                        <th>
                            GST Total
                        </th>

                        <td>

                            <input
                                type="text"
                                id="purchaseReturnEditGstTotal"
                                class="form-control text-end"
                                value="{{ number_format(
                                    (float) $purchaseReturn->gst_total,
                                    2,
                                    '.',
                                    ''
                                ) }}"
                                readonly>

                        </td>

                    </tr>


                    {{-- Grand Total --}}

                    <tr class="table-primary">

                        <th>
                            Grand Total
                        </th>

                        <td>

                            <input
                                type="text"
                                id="purchaseReturnEditGrandTotal"
                                class="form-control text-end fw-bold"
                                value="{{ number_format(
                                    (float) $purchaseReturn->total_amount,
                                    2,
                                    '.',
                                    ''
                                ) }}"
                                readonly>

                        </td>

                    </tr>

                </table>


                {{-- =====================================================
                     Hidden Totals
                ====================================================== --}}

                <input
                    type="hidden"
                    name="sub_total"
                    id="purchaseReturnEditSubTotalHidden"
                    value="{{ number_format(
                        (float) $purchaseReturn->sub_total,
                        2,
                        '.',
                        ''
                    ) }}">


                <input
                    type="hidden"
                    name="gst_total"
                    id="purchaseReturnEditGstTotalHidden"
                    value="{{ number_format(
                        (float) $purchaseReturn->gst_total,
                        2,
                        '.',
                        ''
                    ) }}">


                <input
                    type="hidden"
                    name="grand_total"
                    id="purchaseReturnEditGrandTotalHidden"
                    value="{{ number_format(
                        (float) $purchaseReturn->total_amount,
                        2,
                        '.',
                        ''
                    ) }}">

            </div>

        </div>


        {{-- =========================================================
             Buttons
        ========================================================== --}}

        <div class="text-end mt-3 mb-4">

            <button
                type="submit"
                class="btn btn-success"
                id="purchaseReturnEditUpdateButton">

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


{{-- ================================================================
     EDIT PURCHASE RETURN JAVASCRIPT
     IMPORTANT:
     All selectors below are UNIQUE to EDIT PAGE.
================================================================ --}}

@push('scripts')

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {

            /*
            |--------------------------------------------------------------------------
            | EDIT FORM
            |--------------------------------------------------------------------------
            */

            const editForm =
                document.getElementById(
                    'purchaseReturnEditForm'
                );


            /*
            |--------------------------------------------------------------------------
            | Safety Check
            |--------------------------------------------------------------------------
            |
            | If this form does not exist, stop.
            |
            */

            if (!editForm) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Calculate Single Row
            |--------------------------------------------------------------------------
            */

            function calculatePurchaseReturnEditRow(row) {

                if (!row) {
                    return;
                }


                const qtyInput =
                    row.querySelector(
                        '.purchaseReturnEditQty'
                    );


                const priceInput =
                    row.querySelector(
                        '.purchaseReturnEditPrice'
                    );


                const gstInput =
                    row.querySelector(
                        '.purchaseReturnEditGst'
                    );


                const gstDisplay =
                    row.querySelector(
                        '.purchaseReturnEditGstAmount'
                    );


                const gstHidden =
                    row.querySelector(
                        '.purchaseReturnEditGstAmountInput'
                    );


                const amountDisplay =
                    row.querySelector(
                        '.purchaseReturnEditAmount'
                    );


                const amountHidden =
                    row.querySelector(
                        '.purchaseReturnEditAmountInput'
                    );


                /*
                |--------------------------------------------------------------------------
                | Required Fields
                |--------------------------------------------------------------------------
                */

                if (
                    !qtyInput ||
                    !priceInput ||
                    !gstInput
                ) {

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Quantity
                |--------------------------------------------------------------------------
                */

                let qty =
                    parseFloat(
                        qtyInput.value
                    ) || 0;


                const maxQty =
                    parseFloat(
                        qtyInput.getAttribute('max')
                    );


                /*
                |--------------------------------------------------------------------------
                | Negative Quantity
                |--------------------------------------------------------------------------
                */

                if (qty < 0) {

                    qty = 0;

                    qtyInput.value =
                        '0.00';

                }


                /*
                |--------------------------------------------------------------------------
                | Maximum Quantity
                |--------------------------------------------------------------------------
                */

                if (
                    !isNaN(maxQty) &&
                    qty > maxQty
                ) {

                    qty = maxQty;

                    qtyInput.value =
                        maxQty.toFixed(2);

                }


                /*
                |--------------------------------------------------------------------------
                | Price
                |--------------------------------------------------------------------------
                */

                const price =
                    parseFloat(
                        priceInput.value
                    ) || 0;


                /*
                |--------------------------------------------------------------------------
                | GST
                |--------------------------------------------------------------------------
                */

                const gst =
                    parseFloat(
                        gstInput.value
                    ) || 0;


                /*
                |--------------------------------------------------------------------------
                | Calculate Sub Total
                |--------------------------------------------------------------------------
                */

                const rowSubTotal =
                    qty * price;


                /*
                |--------------------------------------------------------------------------
                | Calculate GST
                |--------------------------------------------------------------------------
                */

                const rowGst =
                    rowSubTotal *
                    gst /
                    100;


                /*
                |--------------------------------------------------------------------------
                | Calculate Total
                |--------------------------------------------------------------------------
                */

                const rowGrandTotal =
                    rowSubTotal +
                    rowGst;


                /*
                |--------------------------------------------------------------------------
                | GST Display
                |--------------------------------------------------------------------------
                */

                if (gstDisplay) {

                    gstDisplay.textContent =
                        rowGst.toFixed(2);

                }


                /*
                |--------------------------------------------------------------------------
                | GST Hidden
                |--------------------------------------------------------------------------
                */

                if (gstHidden) {

                    gstHidden.value =
                        rowGst.toFixed(2);

                }


                /*
                |--------------------------------------------------------------------------
                | Amount Display
                |--------------------------------------------------------------------------
                */

                if (amountDisplay) {

                    amountDisplay.textContent =
                        rowGrandTotal.toFixed(2);

                }


                /*
                |--------------------------------------------------------------------------
                | Amount Hidden
                |--------------------------------------------------------------------------
                */

                if (amountHidden) {

                    amountHidden.value =
                        rowGrandTotal.toFixed(2);

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Calculate Edit Totals
            |--------------------------------------------------------------------------
            */

            function calculatePurchaseReturnEditTotals() {

                let subTotal = 0;

                let gstTotal = 0;

                let grandTotal = 0;


                /*
                |--------------------------------------------------------------------------
                | Only Edit Page Rows
                |--------------------------------------------------------------------------
                */

                document
                    .querySelectorAll(
                        '#purchaseReturnEditItems tr'
                    )
                    .forEach(
                        function(row) {

                            const qtyInput =
                                row.querySelector(
                                    '.purchaseReturnEditQty'
                                );


                            const priceInput =
                                row.querySelector(
                                    '.purchaseReturnEditPrice'
                                );


                            const gstInput =
                                row.querySelector(
                                    '.purchaseReturnEditGst'
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


                            const rowSubTotal =
                                qty * price;


                            const rowGst =
                                rowSubTotal *
                                gst /
                                100;


                            const rowGrandTotal =
                                rowSubTotal +
                                rowGst;


                            subTotal +=
                                rowSubTotal;


                            gstTotal +=
                                rowGst;


                            grandTotal +=
                                rowGrandTotal;

                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | Display Elements
                |--------------------------------------------------------------------------
                */

                const subTotalDisplay =
                    document.getElementById(
                        'purchaseReturnEditSubTotal'
                    );


                const gstTotalDisplay =
                    document.getElementById(
                        'purchaseReturnEditGstTotal'
                    );


                const grandTotalDisplay =
                    document.getElementById(
                        'purchaseReturnEditGrandTotal'
                    );


                /*
                |--------------------------------------------------------------------------
                | Hidden Elements
                |--------------------------------------------------------------------------
                */

                const subTotalHidden =
                    document.getElementById(
                        'purchaseReturnEditSubTotalHidden'
                    );


                const gstTotalHidden =
                    document.getElementById(
                        'purchaseReturnEditGstTotalHidden'
                    );


                const grandTotalHidden =
                    document.getElementById(
                        'purchaseReturnEditGrandTotalHidden'
                    );


                /*
                |--------------------------------------------------------------------------
                | Update Display
                |--------------------------------------------------------------------------
                */

                if (subTotalDisplay) {

                    subTotalDisplay.value =
                        subTotal.toFixed(2);

                }


                if (gstTotalDisplay) {

                    gstTotalDisplay.value =
                        gstTotal.toFixed(2);

                }


                if (grandTotalDisplay) {

                    grandTotalDisplay.value =
                        grandTotal.toFixed(2);

                }


                /*
                |--------------------------------------------------------------------------
                | Update Hidden Values
                |--------------------------------------------------------------------------
                */

                if (subTotalHidden) {

                    subTotalHidden.value =
                        subTotal.toFixed(2);

                }


                if (gstTotalHidden) {

                    gstTotalHidden.value =
                        gstTotal.toFixed(2);

                }


                if (grandTotalHidden) {

                    grandTotalHidden.value =
                        grandTotal.toFixed(2);

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Quantity Input Events
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll(
                    '.purchaseReturnEditQty'
                )
                .forEach(
                    function(qtyInput) {

                        qtyInput.addEventListener(
                            'input',
                            function() {

                                const row =
                                    this.closest('tr');


                                calculatePurchaseReturnEditRow(
                                    row
                                );


                                calculatePurchaseReturnEditTotals();

                            }
                        );


                        qtyInput.addEventListener(
                            'change',
                            function() {

                                const row =
                                    this.closest('tr');


                                calculatePurchaseReturnEditRow(
                                    row
                                );


                                calculatePurchaseReturnEditTotals();

                            }
                        );

                    }
                );


            /*
            |--------------------------------------------------------------------------
            | Initial Row Calculation
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll(
                    '#purchaseReturnEditItems tr'
                )
                .forEach(
                    function(row) {

                        calculatePurchaseReturnEditRow(
                            row
                        );

                    }
                );


            /*
            |--------------------------------------------------------------------------
            | Initial Total Calculation
            |--------------------------------------------------------------------------
            */

            calculatePurchaseReturnEditTotals();


            /*
            |--------------------------------------------------------------------------
            | Form Submit Validation
            |--------------------------------------------------------------------------
            */

            editForm.addEventListener(
                'submit',
                function(event) {

                    let hasQuantity =
                        false;


                    let invalid =
                        false;


                    document
                        .querySelectorAll(
                            '#purchaseReturnEditItems tr'
                        )
                        .forEach(
                            function(row) {

                                const qtyInput =
                                    row.querySelector(
                                        '.purchaseReturnEditQty'
                                    );


                                const godownSelect =
                                    row.querySelector(
                                        '.purchaseReturnEditGodown'
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
                                        qtyInput.getAttribute('max')
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | At Least One Quantity
                                |--------------------------------------------------------------------------
                                */

                                if (qty > 0) {

                                    hasQuantity =
                                        true;

                                }


                                /*
                                |--------------------------------------------------------------------------
                                | Quantity Validation
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    qty < 0 ||
                                    (
                                        !isNaN(maxQty) &&
                                        qty > maxQty
                                    )
                                ) {

                                    invalid =
                                        true;


                                    qtyInput.classList.add(
                                        'is-invalid'
                                    );

                                } else {

                                    qtyInput.classList.remove(
                                        'is-invalid'
                                    );

                                }


                                /*
                                |--------------------------------------------------------------------------
                                | Godown Validation
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    qty > 0 &&
                                    godownSelect &&
                                    godownSelect.value === ''
                                ) {

                                    invalid =
                                        true;


                                    godownSelect.classList.add(
                                        'is-invalid'
                                    );

                                } else if (godownSelect) {

                                    godownSelect.classList.remove(
                                        'is-invalid'
                                    );

                                }

                            }
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | No Quantity
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
                    | Invalid Data
                    |--------------------------------------------------------------------------
                    */

                    if (invalid) {

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