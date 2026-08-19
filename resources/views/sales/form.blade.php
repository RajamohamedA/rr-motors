@php

$isEdit = $isEdit ?? false;

$sale = $sale ?? null;

$companies = $companies ?? collect();

$stores = $stores ?? collect();

$customers = $customers ?? collect();

@endphp


{{-- ================================================================ --}}
{{-- SALES HEADER --}}
{{-- ================================================================ --}}

<div class="card sales-card mb-3">

    <div class="card-header py-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <i class="fas fa-file-invoice me-2"></i>

                {{ $isEdit ? 'Edit Sales Invoice' : 'Sales Invoice' }}

            </div>

        </div>

    </div>


    <div class="card-body">

        <div class="row g-3">


            {{-- ==================================================== --}}
            {{-- COMPANY --}}
            {{-- ==================================================== --}}

            <div class="col-md-4">

                <label
                    for="company_id"
                    class="form-label">

                    Company
                    <span class="text-danger">*</span>

                </label>

                <select
                    id="company_id"
                    name="company_id"
                    class="form-select"
                    required>

                    <option value="">
                        Select Company
                    </option>

                    @foreach($companies as $company)

                    <option
                        value="{{ $company->id }}"
                        @selected(
                        old( 'company_id' ,
                        $sale->company_id ?? ''
                        ) == $company->id
                        )>

                        {{ $company->company_name }}

                    </option>

                    @endforeach

                </select>

                @error('company_id')

                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>

                @enderror

            </div>


            {{-- ==================================================== --}}
            {{-- STORE --}}
            {{-- ==================================================== --}}

            <div class="col-md-4">

                <label
                    for="sales_store_id"
                    class="form-label">

                    Store
                    <span class="text-danger">*</span>

                </label>

                <select
                    id="sales_store_id"
                    name="store_id"
                    class="form-select"
                    required>

                    @if($sale && $sale->store_id)

                    @foreach($stores as $store)

                    <option
                        value="{{ $store->id }}"
                        @selected(
                        $sale->store_id == $store->id
                        )>

                        {{ $store->store_name }}

                    </option>

                    @endforeach

                    @else

                    <option value="">
                        Select Company First
                    </option>

                    @endif

                </select>

                @error('store_id')

                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>

                @enderror

            </div>


            {{-- ==================================================== --}}
            {{-- CUSTOMER --}}
            {{-- ==================================================== --}}

            <div class="col-md-4">

                <label
                    for="sales_customer_id"
                    class="form-label">

                    Customer
                    <span class="text-danger">*</span>

                </label>

                <select
                    id="sales_customer_id"
                    name="customer_id"
                    class="form-select"
                    required>

                    @if($sale && $sale->customer_id)

                    @foreach($customers as $customer)

                    <option
                        value="{{ $customer->id }}"
                        @selected(
                        $sale->customer_id == $customer->id
                        )>

                        {{ $customer->customer_name }}

                    </option>

                    @endforeach

                    @else

                    <option value="">
                        Select Company First
                    </option>

                    @endif

                </select>

                @error('customer_id')

                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>

                @enderror

            </div>


            {{-- ==================================================== --}}
            {{-- INVOICE NUMBER --}}
            {{-- ==================================================== --}}

            <div class="col-md-4">

                <label
                    for="salesInvoiceNo"
                    class="form-label">

                    Invoice No
                    <span class="text-danger">*</span>

                </label>

                <input
                    type="text"
                    id="salesInvoiceNo"
                    name="invoice_no"
                    class="form-control"
                    value="{{ old(
                        'invoice_no',
                        $sale->invoice_no ?? ''
                    ) }}"
                    required>

            </div>


            {{-- ==================================================== --}}
            {{-- INVOICE DATE --}}
            {{-- ==================================================== --}}

            <div class="col-md-4">

                <label
                    for="invoice_date"
                    class="form-label">

                    Invoice Date
                    <span class="text-danger">*</span>

                </label>

                <input
                    type="date"
                    id="invoice_date"
                    name="invoice_date"
                    class="form-control"
                    value="{{ old(
                        'invoice_date',
                        isset($sale->invoice_date)
                            ? \Carbon\Carbon::parse(
                                $sale->invoice_date
                            )->format('Y-m-d')
                            : now()->format('Y-m-d')
                    ) }}"
                    required>

            </div>


            {{-- ==================================================== --}}
            {{-- PAYMENT METHOD --}}
            {{-- ==================================================== --}}

            <div class="col-md-4">

                <label
                    for="payment_method"
                    class="form-label">

                    Payment Method

                </label>

                <select
                    id="payment_method"
                    name="payment_method"
                    class="form-select">

                    <option
                        value="cash"
                        @selected(
                        old( 'payment_method' ,
                        $sale->payment_method ?? 'cash'
                        ) === 'cash'
                        )>

                        Cash

                    </option>

                    <option
                        value="credit"
                        @selected(
                        old( 'payment_method' ,
                        $sale->payment_method ?? ''
                        ) === 'credit'
                        )>

                        Credit

                    </option>

                    <option
                        value="upi"
                        @selected(
                        old( 'payment_method' ,
                        $sale->payment_method ?? ''
                        ) === 'upi'
                        )>

                        UPI

                    </option>

                    <option
                        value="bank"
                        @selected(
                        old( 'payment_method' ,
                        $sale->payment_method ?? ''
                        ) === 'bank'
                        )>

                        Bank

                    </option>

                </select>

            </div>

        </div>

    </div>

</div>


{{-- ================================================================ --}}
{{-- PRODUCT SEARCH --}}
{{-- ================================================================ --}}

<div class="card sales-card mb-3">

    <div class="card-header">

        <i class="fas fa-cart-plus me-2"></i>

        Add Product

    </div>


    <div class="card-body">

        <div class="product-search-wrapper">

            <div class="input-group">

                <span class="input-group-text">

                    <i class="fas fa-search"></i>

                </span>

                <input
                    type="text"
                    id="product_search"
                    class="form-control"
                    autocomplete="off"
                    placeholder="Search product by name, code or barcode...">

                <button
                    type="button"
                    id="clearSearch"
                    class="btn btn-outline-secondary">

                    <i class="fas fa-times"></i>

                </button>

            </div>


            {{-- Search result MUST be outside the table --}}
            <div
                id="searchResult"
                class="list-group">
            </div>

        </div>


        <div
            id="searchNotice"
            class="text-muted small mt-2">

            <i class="fas fa-info-circle me-1"></i>

            Select company, store and customer before searching products.

        </div>

    </div>

</div>


{{-- ================================================================ --}}
{{-- SALES ITEMS --}}
{{-- ================================================================ --}}

<div class="card sales-card mb-3">

    <div class="card-header">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <i class="fas fa-shopping-cart me-2"></i>

                Sales Items

            </div>

            <span
                id="itemCount"
                class="badge text-bg-secondary">

                0 Items

            </span>

        </div>

    </div>


    <div class="card-body p-0">

        <div class="sales-items-wrapper">

            <div class="sales-table-scroll">

                <table
                    id="salesItemsTable"
                    class="table table-bordered table-hover mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>
                                Code
                            </th>

                            <th>
                                Product
                            </th>

                            <th>
                                Location
                            </th>

                            <th>
                                Stock
                            </th>

                            <th>
                                Qty
                            </th>

                            <th>
                                Free
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Discount
                            </th>

                            <th>
                                GST %
                            </th>

                            <th>
                                GST Amount
                            </th>

                            <th>
                                Total
                            </th>

                            <th class="text-center">
                                Transfer
                            </th>

                            <th class="text-center">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody id="salesItems">

                        @if($sale && $sale->items)

                        @foreach($sale->items as $index => $item)

                        <tr
                            class="sale-item-row"
                            data-product-id="{{ $item->product_id }}">

                            <td>

                                <strong>
                                    {{ $item->product->product_code ?? '-' }}
                                </strong>

                                <input
                                    type="hidden"
                                    name="product_id[]"
                                    value="{{ $item->product_id }}">

                            </td>


                            <td>

                                <div class="fw-semibold">

                                    {{ $item->product->product_name ?? '' }}

                                </div>

                                @if($item->product->barcode ?? false)

                                <small class="text-muted">

                                    Barcode:
                                    {{ $item->product->barcode }}

                                </small>

                                @endif

                            </td>


                            {{-- LOCATION IS ALWAYS SHOP --}}
                            <td>

                                <span class="badge text-bg-primary">

                                    <i class="fas fa-store me-1"></i>

                                    Shop

                                </span>

                            </td>


                            <td>

                                <span
                                    class="badge bg-success stock">

                                    0.00

                                </span>

                            </td>


                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    name="qty[]"
                                    class="form-control form-control-sm qty"
                                    value="{{ $item->quantity ?? 1 }}">

                            </td>


                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="free_quantity[]"
                                    class="form-control form-control-sm free_qty"
                                    value="{{ $item->free_quantity ?? 0 }}">

                            </td>


                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="price[]"
                                    class="form-control form-control-sm price"
                                    value="{{ number_format(
                                                $item->price ?? 0,
                                                2,
                                                '.',
                                                ''
                                            ) }}">

                            </td>


                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="discount[]"
                                    class="form-control form-control-sm discount"
                                    value="{{ $item->discount ?? 0 }}">

                            </td>


                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="gst[]"
                                    class="form-control form-control-sm gst"
                                    value="{{ $item->gst ?? 0 }}">

                            </td>


                            <td>

                                <input
                                    type="text"
                                    name="gst_amount[]"
                                    class="form-control form-control-sm gst_amount"
                                    readonly
                                    value="{{ number_format(
                                                $item->gst_amount ?? 0,
                                                2,
                                                '.',
                                                ''
                                            ) }}">

                            </td>


                            <td>

                                <input
                                    type="text"
                                    name="line_total[]"
                                    class="form-control form-control-sm line_total fw-bold"
                                    readonly
                                    value="{{ number_format(
                                                $item->total ?? 0,
                                                2,
                                                '.',
                                                ''
                                            ) }}">

                            </td>


                            <td class="text-center">

                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm transfer-stock-btn"
                                    title="Transfer stock from Godown to Shop">

                                    <i class="fas fa-exchange-alt"></i>

                                </button>

                            </td>


                            <td class="text-center">

                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm removeRow"
                                    title="Remove">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </td>

                        </tr>

                        @endforeach

                        @endif


                        <tr
                            id="emptyItems"
                            class="{{ $sale && $sale->items && $sale->items->count()
                                ? 'd-none'
                                : '' }}">

                            <td
                                colspan="13"
                                class="text-center text-muted py-5">

                                <i
                                    class="fas fa-shopping-basket fa-2x mb-2 d-block">
                                </i>

                                No products added.

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


{{-- ================================================================ --}}
{{-- TOTALS + PAYMENT --}}
{{-- ================================================================ --}}

<div class="row g-3">


    {{-- ============================================================ --}}
    {{-- TOTALS --}}
    {{-- ============================================================ --}}

    <div class="col-lg-7">

        <div class="card sales-card">

            <div class="card-header">

                <i class="fas fa-calculator me-2"></i>

                Invoice Summary

            </div>


            <div class="card-body">

                <div class="d-flex justify-content-between sales-total-row mb-2">

                    <span>
                        Sub Total
                    </span>

                    <strong>
                        ₹ <span id="subTotalDisplay">0.00</span>
                    </strong>

                </div>


                <div class="d-flex justify-content-between sales-total-row mb-2">

                    <span>
                        Discount
                    </span>

                    <strong>
                        ₹ <span id="discountDisplay">0.00</span>
                    </strong>

                </div>


                <div class="d-flex justify-content-between sales-total-row mb-3">

                    <span>
                        GST
                    </span>

                    <strong>
                        ₹ <span id="gstDisplay">0.00</span>
                    </strong>

                </div>


                <hr>


                <div class="d-flex justify-content-between sales-grand-total">

                    <span>
                        Grand Total
                    </span>

                    <span>
                        ₹ <span id="grandTotalDisplay">0.00</span>
                    </span>

                </div>


                <input
                    type="hidden"
                    id="sub_total"
                    name="sub_total"
                    value="0.00">


                <input
                    type="hidden"
                    id="discount_total"
                    name="discount_total"
                    value="0.00">


                <input
                    type="hidden"
                    id="gst_total"
                    name="gst_total"
                    value="0.00">


                <input
                    type="hidden"
                    id="grand_total"
                    name="grand_total"
                    value="0.00">

            </div>

        </div>

    </div>


    {{-- ============================================================ --}}
    {{-- PAYMENT --}}
    {{-- ============================================================ --}}

    <div class="col-lg-5">

        <div class="card sales-card">

            <div class="card-header">

                <i class="fas fa-money-bill-wave me-2"></i>

                Payment

            </div>


            <div class="card-body">

                <div class="mb-3">

                    <label
                        for="paid_amount"
                        class="form-label">

                        Paid Amount

                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="paid_amount"
                        name="paid_amount"
                        class="form-control"
                        value="{{ old(
                            'paid_amount',
                            $sale->paid_amount ?? 0
                        ) }}">

                </div>


                <div class="mb-3">

                    <label
                        for="balance_amount"
                        class="form-label">

                        Balance

                    </label>

                    <input
                        type="text"
                        id="balance_amount"
                        name="balance_amount"
                        class="form-control"
                        readonly
                        value="0.00">

                </div>


                <input
                    type="hidden"
                    id="paymentGrandTotal"
                    value="0.00">

            </div>

        </div>

    </div>

</div>


{{-- ================================================================ --}}
{{-- SAVE --}}
{{-- ================================================================ --}}

<div class="d-flex justify-content-end gap-2 mt-3 mb-4">

    <a
        href="{{ route('sales.index') }}"
        class="btn btn-outline-secondary">

        <i class="fas fa-times me-1"></i>

        Cancel

    </a>


    <button
        type="submit"
        id="saveSaleBtn"
        class="btn btn-primary">

        <i class="fas fa-save me-1"></i>

        {{ $isEdit
            ? 'Update Sales Invoice'
            : 'Save Sales Invoice'
        }}

    </button>

</div>


{{-- ================================================================ --}}
{{-- STOCK SHORTAGE MODAL --}}
{{-- ================================================================ --}}

<div
    class="modal fade"
    id="stockShortageModal"
    tabindex="-1"
    aria-labelledby="stockShortageModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">


            {{-- HEADER --}}

            <div class="modal-header bg-warning-subtle">

                <h5
                    class="modal-title"
                    id="stockShortageModalLabel">

                    <i class="fas fa-exclamation-triangle me-2"></i>

                    Insufficient Shop Stock

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>

            </div>


            {{-- BODY --}}

            <div class="modal-body">


                <input
                    type="hidden"
                    id="shortageProductId">

                <input
                    type="hidden"
                    id="shortageRowIndex">


                <div class="mb-3">

                    <div class="text-muted small">
                        Product
                    </div>

                    <div
                        id="shortageProductName"
                        class="fw-bold fs-5">

                        Product

                    </div>

                </div>


                <div class="row g-2 mb-3">


                    <div class="col-4">

                        <div class="border rounded p-2 text-center">

                            <div class="small text-muted">
                                Required
                            </div>

                            <div
                                id="shortageRequired"
                                class="fw-bold">

                                0

                            </div>

                        </div>

                    </div>


                    <div class="col-4">

                        <div class="border rounded p-2 text-center">

                            <div class="small text-muted">
                                Shop Stock
                            </div>

                            <div
                                id="shortageShopStock"
                                class="fw-bold text-danger">

                                0

                            </div>

                        </div>

                    </div>


                    <div class="col-4">

                        <div class="border rounded p-2 text-center">

                            <div class="small text-muted">
                                Shortage
                            </div>

                            <div
                                id="shortageQuantity"
                                class="fw-bold text-warning">

                                0

                            </div>

                        </div>

                    </div>

                </div>


                <div
                    id="noGodownStockMessage"
                    class="alert alert-danger d-none">

                    <i class="fas fa-times-circle me-1"></i>

                    No sufficient stock available in the godowns.

                </div>


                <div
                    id="cannotFulfillMessage"
                    class="alert alert-warning d-none">

                    <i class="fas fa-exclamation-circle me-1"></i>

                    The available godown stock cannot fulfill this requirement.

                </div>


                <div class="mb-2">

                    <label class="form-label fw-semibold">

                        <i class="fas fa-warehouse me-1"></i>

                        Available Godown Stock

                    </label>

                </div>


                <div
                    id="godownStockList"
                    class="mb-3">

                </div>


            </div>


            {{-- FOOTER --}}

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Cancel

                </button>


                <button
                    type="button"
                    id="transferStockBtn"
                    class="btn btn-primary">

                    <i class="fas fa-exchange-alt me-1"></i>

                    Transfer & Continue

                </button>

            </div>

        </div>

    </div>

</div>