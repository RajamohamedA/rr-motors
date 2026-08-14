@extends('layouts.app')

@section('title', 'Purchase Return')

@section('content')

<div class="container-fluid">

    <form action="{{ route('purchase-returns.store') }}" method="POST" id="purchaseReturnForm">

        @csrf

        {{-- Hidden Header Values --}}
        <input type="hidden" name="purchase_id" id="purchase_id">
        <input type="hidden" name="supplier_id" id="supplier_id">
        <input type="hidden" name="company_id" id="company_id">

        {{-- =========================================================
             Purchase Return Header
        ========================================================== --}}

        <div class="card shadow-sm">

            <div class="card-header bg-danger text-white">

                <div class="d-flex justify-content-between align-items-center">

                    <h4 class="mb-0">
                        <i class="fas fa-undo"></i>
                        Purchase Return
                    </h4>

                    <a href="{{ route('purchase-returns.index') }}"
                        class="btn btn-light btn-sm">

                        <i class="fas fa-list"></i>
                        Return List

                    </a>

                </div>

            </div>

            <div class="card-body">

                <div class="row">

                    {{-- Company --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Company
                        </label>

                        <select
                            id="company_select"
                            class="form-select"
                            required>

                            <option value="">
                                Select Company
                            </option>

                            @if(isset($companies))

                            @foreach($companies as $company)

                            <option value="{{ $company->id }}">
                                {{ $company->company_name }}
                            </option>

                            @endforeach

                            @endif

                        </select>

                    </div>


                    {{-- Purchase Search --}}
                    <div class="col-md-5 mb-3">

                        <label class="form-label">
                            Search Purchase
                        </label>

                        <input
                            type="text"
                            id="purchase_search"
                            class="form-control"
                            autocomplete="off"
                            placeholder="Purchase No / Supplier">

                        <div
                            id="purchaseResult"
                            class="list-group mt-1"
                            style="
                                display:none;
                                max-height:300px;
                                overflow-y:auto;
                                position:absolute;
                                z-index:1050;
                                width:100%;
                            ">
                        </div>

                    </div>


                    {{-- Return Date --}}
                    <div class="col-md-2 mb-3">

                        <label class="form-label">
                            Return Date
                        </label>

                        <input
                            type="date"
                            name="return_date"
                            class="form-control"
                            value="{{ old('return_date', date('Y-m-d')) }}"
                            required>

                    </div>


                    {{-- Remarks --}}
                    <div class="col-md-2 mb-3">

                        <label class="form-label">
                            Remarks
                        </label>

                        <input
                            type="text"
                            name="remarks"
                            class="form-control">

                    </div>

                </div>


                {{-- URLs --}}

                <input
                    type="hidden"
                    id="searchUrl"
                    value="{{ route('purchase-return.search') }}">

                <input
                    type="hidden"
                    id="purchaseUrl"
                    value="{{ url('purchase-return/get-invoice') }}">

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
                            id="supplier_name"
                            class="form-control"
                            readonly>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">
                            Mobile
                        </label>

                        <input
                            type="text"
                            id="supplier_mobile"
                            class="form-control"
                            readonly>

                    </div>


                    <div class="col-md-5">

                        <label class="form-label">
                            Address
                        </label>

                        <input
                            type="text"
                            id="supplier_address"
                            class="form-control"
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
                        id="returnTable">

                        <thead class="table-dark">

                            <tr>

                                <th width="4%">#</th>

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

                                <th width="10%" class="text-end">
                                    Return Qty
                                </th>

                                <th width="10%" class="text-end">
                                    Rate
                                </th>

                                <th width="7%" class="text-end">
                                    GST %
                                </th>

                                <th width="10%" class="text-end">
                                    GST Amt
                                </th>

                                <th width="12%" class="text-end">
                                    Total
                                </th>

                            </tr>

                        </thead>

                        <tbody id="returnItems">

                            <tr>

                                <td
                                    colspan="12"
                                    class="text-center text-muted py-4">

                                    <i class="fas fa-search"></i>

                                    Search Purchase Invoice...

                                </td>

                            </tr>

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
                                name="total_sub_total"
                                id="purchase_return_sub_total"
                                class="form-control text-end"
                                value="0.00"
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
                                name="total_gst"
                                id="purchase_return_gst_total"
                                class="form-control text-end"
                                value="0.00"
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
                                name="total_grand_total"
                                id="purchase_return_grand_total"
                                class="form-control text-end fw-bold"
                                value="0.00"
                                readonly>

                        </td>

                    </tr>

                </table>

            </div>

        </div>


        {{-- =========================================================
             Buttons
        ========================================================== --}}

        <div class="text-end mt-3 mb-4">

            <button
                type="submit"
                class="btn btn-success"
                id="saveReturnBtn">

                <i class="fas fa-save"></i>

                Save Purchase Return

            </button>


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

        const companySelect =
            document.getElementById('company_select');

        const companyIdInput =
            document.getElementById('company_id');

        const purchaseSearch =
            document.getElementById('purchase_search');

        const purchaseResult =
            document.getElementById('purchaseResult');

        const purchaseIdInput =
            document.getElementById('purchase_id');

        const supplierIdInput =
            document.getElementById('supplier_id');

        const returnItems =
            document.getElementById('returnItems');

        const searchUrl =
            document.getElementById('searchUrl').value;

        const purchaseUrl =
            document.getElementById('purchaseUrl').value;


        /*
        |--------------------------------------------------------------------------
        | Company Change
        |--------------------------------------------------------------------------
        */

        companySelect.addEventListener('change', function() {

            const companyId = this.value;

            /*
            | Store selected company
            */

            companyIdInput.value = companyId;


            /*
            | Clear previous purchase
            */

            purchaseIdInput.value = '';

            supplierIdInput.value = '';


            /*
            | Clear search
            */

            purchaseSearch.value = '';

            purchaseResult.innerHTML = '';

            purchaseResult.style.display = 'none';


            /*
            | Clear supplier
            */

            document.getElementById('supplier_name').value = '';
            document.getElementById('supplier_mobile').value = '';
            document.getElementById('supplier_address').value = '';


            /*
            | Clear items
            */

            clearItems();

        });


        /*
        |--------------------------------------------------------------------------
        | Search Purchase
        |--------------------------------------------------------------------------
        */

        let searchTimer = null;


        purchaseSearch.addEventListener('input', function() {

            const keyword =
                this.value.trim();

            const companyId =
                companySelect.value;


            /*
            |--------------------------------------------------------------------------
            | Company Required
            |--------------------------------------------------------------------------
            */

            if (!companyId) {

                purchaseResult.innerHTML = `
                <div class="list-group-item text-danger">
                    Please select company first.
                </div>
            `;

                purchaseResult.style.display = 'block';

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Minimum Search Length
            |--------------------------------------------------------------------------
            */

            if (keyword.length < 2) {

                purchaseResult.innerHTML = '';

                purchaseResult.style.display = 'none';

                return;
            }


            clearTimeout(searchTimer);


            searchTimer = setTimeout(function() {

                /*
                |--------------------------------------------------------------------------
                | Build URL
                |--------------------------------------------------------------------------
                */

                const url =
                    searchUrl +
                    '?search=' +
                    encodeURIComponent(keyword) +
                    '&company_id=' +
                    encodeURIComponent(companyId);


                fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })

                    .then(response => {

                        if (!response.ok) {

                            throw new Error(
                                'Search failed'
                            );

                        }

                        return response.json();

                    })

                    .then(purchases => {

                        purchaseResult.innerHTML = '';


                        if (!purchases.length) {

                            purchaseResult.innerHTML = `
                        <div class="list-group-item text-muted">
                            No purchase found.
                        </div>
                    `;

                            purchaseResult.style.display =
                                'block';

                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Display Purchases
                        |--------------------------------------------------------------------------
                        */

                        purchases.forEach(function(purchase) {

                            const supplierName =
                                purchase.supplier ?
                                purchase.supplier.supplier_name :
                                '-';


                            const item = document.createElement('button');

                            item.type = 'button';

                            item.className =
                                'list-group-item list-group-item-action';


                            item.innerHTML = `
                        <strong>
                            ${escapeHtml(
                                purchase.purchase_no
                            )}
                        </strong>

                        <br>

                        <small class="text-muted">
                            Supplier:
                            ${escapeHtml(
                                supplierName
                            )}
                        </small>
                    `;


                            item.addEventListener(
                                'click',
                                function() {

                                    loadPurchase(
                                        purchase.id
                                    );

                                }
                            );


                            purchaseResult.appendChild(item);

                        });


                        purchaseResult.style.display =
                            'block';

                    })

                    .catch(function(error) {

                        console.error(error);

                        purchaseResult.innerHTML = `
                    <div class="list-group-item text-danger">
                        Unable to search purchase.
                    </div>
                `;

                        purchaseResult.style.display =
                            'block';

                    });

            }, 300);

        });


        /*
        |--------------------------------------------------------------------------
        | Load Purchase Invoice
        |--------------------------------------------------------------------------
        */

        function loadPurchase(purchaseId) {

            const companyId =
                companySelect.value;


            if (!companyId) {

                alert(
                    'Please select company first.'
                );

                return;
            }


            /*
            | Hide search result
            */

            purchaseResult.innerHTML = '';

            purchaseResult.style.display =
                'none';


            /*
            | Loading
            */

            returnItems.innerHTML = `
            <tr>
                <td colspan="12"
                    class="text-center py-4">

                    <div class="spinner-border text-danger"
                         role="status">

                    </div>

                    <div class="mt-2">
                        Loading purchase...
                    </div>

                </td>
            </tr>
        `;


            /*
            |--------------------------------------------------------------------------
            | URL
            |--------------------------------------------------------------------------
            |
            | /purchase-return/get-invoice/{id}?company_id=...
            |
            */

            const url =
                purchaseUrl +
                '/' +
                purchaseId +
                '?company_id=' +
                encodeURIComponent(companyId);


            fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })

                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            'Unable to load purchase'
                        );

                    }

                    return response.json();

                })

                .then(purchase => {

                    /*
                    |--------------------------------------------------------------------------
                    | Header
                    |--------------------------------------------------------------------------
                    */

                    purchaseIdInput.value =
                        purchase.id;


                    supplierIdInput.value =
                        purchase.supplier_id;


                    /*
                    |--------------------------------------------------------------------------
                    | Supplier
                    |--------------------------------------------------------------------------
                    */

                    if (purchase.supplier) {

                        document.getElementById(
                                'supplier_name'
                            ).value =
                            purchase.supplier.supplier_name ?? '';


                        document.getElementById(
                                'supplier_mobile'
                            ).value =
                            purchase.supplier.mobile ?? '';


                        document.getElementById(
                                'supplier_address'
                            ).value =
                            purchase.supplier.address ?? '';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Search Box
                    |--------------------------------------------------------------------------
                    */

                    purchaseSearch.value =
                        purchase.purchase_no;


                    /*
                    |--------------------------------------------------------------------------
                    | Items
                    |--------------------------------------------------------------------------
                    */

                    renderItems(
                        purchase.items || []
                    );


                    calculateTotals();

                })

                .catch(function(error) {

                    console.error(error);

                    returnItems.innerHTML = `
                <tr>
                    <td colspan="12"
                        class="text-center text-danger py-4">

                        Unable to load purchase invoice.

                    </td>
                </tr>
            `;

                });

        }


        /*
        |--------------------------------------------------------------------------
        | Render Purchase Items
        |--------------------------------------------------------------------------
        */

        function renderItems(items) {

            returnItems.innerHTML = '';


            if (!items.length) {

                returnItems.innerHTML = `
                <tr>
                    <td colspan="12"
                        class="text-center text-muted py-4">

                        No items found in this purchase.

                    </td>
                </tr>
            `;

                return;
            }


            items.forEach(function(item, index) {

                const product =
                    item.product || {};


                const unit =
                    product.unit || {};


                const godown =
                    item.godown || null;


                const purchasedQty =
                    parseFloat(
                        item.quantity || 0
                    );


                const returnedQty =
                    parseFloat(
                        item.returned_qty || 0
                    );


                const balanceQty =
                    parseFloat(
                        item.balance_qty ?? (
                            purchasedQty -
                            returnedQty
                        )
                    );


                /*
                |--------------------------------------------------------------------------
                | Original Godown
                |--------------------------------------------------------------------------
                */

                let godownId =
                    item.godown_id ?? '';


                let godownName =
                    'Shop / No Godown';


                if (godown) {

                    godownName =
                        godown.godown_name ??
                        godown.name ??
                        'Godown';

                }


                /*
                |--------------------------------------------------------------------------
                | Rate
                |--------------------------------------------------------------------------
                */

                const price =
                    parseFloat(
                        item.purchase_price || 0
                    );


                const gstPercent =
                    parseFloat(
                        item.gst_percent || 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Create Row
                |--------------------------------------------------------------------------
                */

                const row =
                    document.createElement('tr');


                row.innerHTML = `

                <td>
                    ${index + 1}
                </td>


                <td>
                    ${escapeHtml(
                        product.product_code || ''
                    )}

                    <input
                        type="hidden"
                        name="product_id[]"
                        value="${item.product_id}">
                </td>


                <td>

                    <strong>
                        ${escapeHtml(
                            product.product_name || ''
                        )}
                    </strong>

                    ${
                        unit.unit_name
                        ? `<br>
                           <small class="text-muted">
                               ${escapeHtml(
                                   unit.unit_name
                               )}
                           </small>`
                        : ''
                    }

                </td>


                {{-- Godown --}}

                <td>

                    <select
                        name="godown_id[]"
                        class="form-select form-select-sm godown-select"
                        required>

                        <option value="">
                            Shop / No Godown
                        </option>

                        ${
                            godownId
                            ? `
                                <option
                                    value="${godownId}"
                                    selected>

                                    ${escapeHtml(
                                        godownName
                                    )}

                                </option>
                              `
                            : ''
                        }

                    </select>

                </td>


                <td class="text-end">

                    ${purchasedQty.toFixed(2)}

                </td>


                <td class="text-end">

                    ${returnedQty.toFixed(2)}

                </td>


                <td class="text-end">

                    <span class="balance-qty">
                        ${balanceQty.toFixed(2)}
                    </span>

                    <input
                        type="hidden"
                        class="balance-value"
                        value="${balanceQty}">

                </td>


                <td>

                    <input
                        type="number"
                        name="qty[]"
                        class="form-control form-control-sm return-qty text-end"
                        min="0"
                        max="${balanceQty}"
                        step="0.01"
                        value="0">

                </td>


                <td>

                    <input
                        type="number"
                        name="price[]"
                        class="form-control form-control-sm price text-end"
                        step="0.01"
                        value="${price.toFixed(2)}"
                        readonly>

                </td>


                <td>

                    <input
                        type="number"
                        name="gst[]"
                        class="form-control form-control-sm gst text-end"
                        step="0.01"
                        value="${gstPercent.toFixed(2)}"
                        readonly>

                </td>


                <td>

                    <input
                        type="number"
                        name="gst_amount[]"
                        class="form-control form-control-sm gst-amount text-end"
                        step="0.01"
                        value="0.00"
                        readonly>

                </td>


                <td>

                    <input
                        type="number"
                        name="amount[]"
                        class="form-control form-control-sm amount text-end"
                        step="0.01"
                        value="0.00"
                        readonly>

                </td>

            `;


                returnItems.appendChild(row);

            });


            /*
            |--------------------------------------------------------------------------
            | Attach Quantity Events
            |--------------------------------------------------------------------------
            */

            attachItemEvents();

        }


        /*
        |--------------------------------------------------------------------------
        | Item Events
        |--------------------------------------------------------------------------
        */

        function attachItemEvents() {

            const rows =
                returnItems.querySelectorAll('tr');


            rows.forEach(function(row) {

                const qtyInput =
                    row.querySelector('.return-qty');


                if (!qtyInput) {
                    return;
                }


                qtyInput.addEventListener(
                    'input',
                    function() {

                        let qty =
                            parseFloat(
                                this.value
                            ) || 0;


                        const max =
                            parseFloat(
                                this.max
                            ) || 0;


                        /*
                        |--------------------------------------------------------------------------
                        | Prevent More Than Balance
                        |--------------------------------------------------------------------------
                        */

                        if (qty < 0) {

                            qty = 0;

                        }


                        if (qty > max) {

                            qty = max;

                            this.value =
                                max.toFixed(2);

                            alert(
                                'Return quantity cannot exceed balance quantity.'
                            );

                        }


                        calculateRow(
                            row
                        );

                        calculateTotals();

                    }
                );

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Row
        |--------------------------------------------------------------------------
        */

        function calculateRow(row) {

            const qtyInput =
                row.querySelector('.return-qty');

            const priceInput =
                row.querySelector('.price');

            const gstInput =
                row.querySelector('.gst');

            const gstAmountInput =
                row.querySelector('.gst-amount');

            const amountInput =
                row.querySelector('.amount');


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
            | Total
            |--------------------------------------------------------------------------
            */

            const total =
                subTotal +
                gstAmount;


            gstAmountInput.value =
                gstAmount.toFixed(2);


            amountInput.value =
                total.toFixed(2);

        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Totals
        |--------------------------------------------------------------------------
        */

        function calculateTotals() {

            let subTotal = 0;

            let gstTotal = 0;

            let grandTotal = 0;


            const rows =
                returnItems.querySelectorAll('tr');


            rows.forEach(function(row) {

                const amountInput =
                    row.querySelector('.amount');


                const gstAmountInput =
                    row.querySelector('.gst-amount');


                const qtyInput =
                    row.querySelector('.return-qty');


                if (
                    !amountInput ||
                    !gstAmountInput ||
                    !qtyInput
                ) {

                    return;

                }


                const qty =
                    parseFloat(
                        qtyInput.value
                    ) || 0;


                if (qty <= 0) {
                    return;
                }


                const amount =
                    parseFloat(
                        amountInput.value
                    ) || 0;


                const gstAmount =
                    parseFloat(
                        gstAmountInput.value
                    ) || 0;


                subTotal +=
                    amount -
                    gstAmount;


                gstTotal +=
                    gstAmount;


                grandTotal +=
                    amount;

            });


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

        }


        /*
        |--------------------------------------------------------------------------
        | Clear Items
        |--------------------------------------------------------------------------
        */

        function clearItems() {

            returnItems.innerHTML = `

            <tr>

                <td
                    colspan="12"
                    class="text-center text-muted py-4">

                    Search Purchase Invoice...

                </td>

            </tr>

        `;


            document.getElementById(
                'purchase_return_sub_total'
            ).value = '0.00';


            document.getElementById(
                'purchase_return_gst_total'
            ).value = '0.00';


            document.getElementById(
                'purchase_return_grand_total'
            ).value = '0.00';

        }


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

                const companyId =
                    companySelect.value;


                const purchaseId =
                    purchaseIdInput.value;


                if (!companyId) {

                    event.preventDefault();

                    alert(
                        'Please select company.'
                    );

                    companySelect.focus();

                    return;

                }


                if (!purchaseId) {

                    event.preventDefault();

                    alert(
                        'Please select a purchase invoice.'
                    );

                    purchaseSearch.focus();

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | At Least One Return Item
                |--------------------------------------------------------------------------
                */

                let hasReturnItem = false;


                returnItems
                    .querySelectorAll('.return-qty')
                    .forEach(function(input) {

                        const qty =
                            parseFloat(
                                input.value
                            ) || 0;


                        if (qty > 0) {

                            hasReturnItem = true;

                        }

                    });


                if (!hasReturnItem) {

                    event.preventDefault();

                    alert(
                        'Please enter return quantity for at least one item.'
                    );

                    return;

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Hide Search Result When Clicking Outside
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            function(event) {

                if (
                    !purchaseSearch.contains(event.target) &&
                    !purchaseResult.contains(event.target)
                ) {

                    purchaseResult.style.display =
                        'none';

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Escape HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            if (value === null ||
                value === undefined) {

                return '';

            }


            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

        }

    });
</script>

@endpush