<!-- Purchase Header -->
<div class="card shadow-sm mb-3">

    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-shopping-cart"></i>

            {{ isset($purchase) ? 'Edit Purchase' : 'Create Purchase' }}

        </h5>
    </div>

    <div class="card-body">

        <div class="row">

            <!-- Purchase No -->

            <div class="col-md-3 mb-3">

                <label class="form-label">Purchase No</label>

                <input type="text"
                       class="form-control"
                       name="purchase_no"
                       readonly
                       value="{{ old('purchase_no',$purchase->purchase_no ?? $purchaseNo) }}">

            </div>

            <!-- Purchase Date -->

            <div class="col-md-3 mb-3">

                <label class="form-label">Purchase Date</label>

                <input type="date"
                       class="form-control"
                       name="purchase_date"
                       value="{{ old('purchase_date',$purchase->purchase_date ?? date('Y-m-d')) }}">

            </div>

            <!-- Company -->

            <div class="col-md-3 mb-3">

                <label class="form-label">Company</label>

                <select
                    class="form-select"
                    name="company_id"
                    id="company_id">

                    <option value="">Select Company</option>

                    @foreach($companies as $company)

                        <option
                            value="{{ $company->id }}"
                            {{ old('company_id',$purchase->company_id ?? '')==$company->id?'selected':'' }}>

                            {{ $company->company_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Store -->

            <div class="col-md-3 mb-3">

                <label class="form-label">Store</label>

                <select
                    class="form-select"
                    name="store_id"
                    id="store_id">

                    <option value="">Select Store</option>

                    @foreach($stores as $store)

                        <option
                            value="{{ $store->id }}"
                            {{ old('store_id',$purchase->store_id ?? '')==$store->id?'selected':'' }}>

                            {{ $store->store_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Supplier -->

            <div class="col-md-4 mb-3">

                <label class="form-label">Supplier</label>

                <select
                    class="form-select"
                    name="supplier_id">

                    <option value="">Select Supplier</option>

                    @foreach($suppliers as $supplier)

                        <option
                            value="{{ $supplier->id }}"
                            {{ old('supplier_id',$purchase->supplier_id ?? '')==$supplier->id?'selected':'' }}>

                            {{ $supplier->supplier_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Invoice No -->

            <div class="col-md-4 mb-3">

                <label class="form-label">Invoice No</label>

                <input
                    type="text"
                    class="form-control"
                    name="invoice_no"
                    value="{{ old('invoice_no',$purchase->invoice_no ?? '') }}">

            </div>

            <!-- Invoice Date -->

            <div class="col-md-4 mb-3">

                <label class="form-label">Invoice Date</label>

                <input
                    type="date"
                    class="form-control"
                    name="invoice_date"
                    value="{{ old('invoice_date',$purchase->invoice_date ?? '') }}">

            </div>

        </div>

    </div>

</div>

<!-- Product Search -->
<div class="card shadow-sm mb-3">

    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">
            <i class="fas fa-search"></i>
            Product Search
        </h5>
    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-12">

                <label class="form-label">
                    Search Product
                </label>

                <input
                    type="text"
                    id="product_search"
                    class="form-control form-control-lg"
                    autocomplete="off"
                    placeholder="Search Product Code / Barcode / Product Name">

                <div id="searchResult"
                     class="list-group mt-2"
                     style="display:none;
                            max-height:300px;
                            overflow-y:auto;">
                </div>

            </div>

        </div>

    </div>

</div>

<input type="hidden"
       id="productSearchUrl"
       value="{{ route('products.search') }}">


       <!-- Purchase Items -->

<div class="card shadow-sm">

    <div class="card-header bg-info text-white">

        <h5 class="mb-0">

            <i class="fas fa-box"></i>

            Purchase Items

        </h5>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table
                class="table table-bordered table-hover align-middle"
                id="purchaseTable">

                <thead class="table-light">

                <tr>

                    <th width="120">Code</th>

                    <th>Product</th>

                    <th width="90">Qty</th>

                    <th width="120">Price</th>

                    <th width="80">GST%</th>

                    <th width="130">Amount</th>

                    <th width="60"></th>

                </tr>

                </thead>

                <tbody id="purchaseItems">

                @if(isset($purchase))

                    @foreach($purchase->items as $item)

                    <tr>

                        <td>

                            {{ $item->product->product_code }}

                            <input
                                type="hidden"
                                name="product_id[]"
                                value="{{ $item->product_id }}">

                        </td>

                        <td>

                            {{ $item->product->product_name }}

                        </td>

                        <td>

                            <input
                                type="number"
                                class="form-control qty"
                                name="qty[]"
                                value="{{ $item->quantity }}"
                                min="1">

                        </td>

                        <td>

                            <input
                                type="number"
                                class="form-control price"
                                name="price[]"
                                value="{{ $item->purchase_price }}"
                                step="0.01">

                        </td>

                        <td>

                            <input
                                type="number"
                                class="form-control gst"
                                name="gst[]"
                                value="{{ $item->gst_percent }}">

                        </td>

                        <td>

                            <input
                                type="text"
                                class="form-control amount"
                                readonly
                                value="{{ number_format($item->line_total,2,'.','') }}">

                        </td>

                        <td>

                            <button
                                type="button"
                                class="btn btn-danger btn-sm removeRow">

                                <i class="fas fa-trash"></i>

                            </button>

                        </td>

                    </tr>

                    @endforeach

                @endif

                </tbody>

                <tfoot>

                <tr>

                    <th colspan="5" class="text-end">

                        Grand Total

                    </th>

                    <th>

                        <input
                            type="text"
                            class="form-control"
                            id="grand_total"
                            name="grand_total"
                            readonly
                            value="{{ old('grand_total',$purchase->grand_total ?? 0) }}">

                    </th>

                    <th></th>

                </tr>

                </tfoot>

            </table>

            <!-- Remarks -->
            <div class="card shadow-sm mt-3">

                <div class="card-body">

                    <label class="form-label">Remarks</label>

                    <textarea
                        name="remarks"
                        class="form-control"
                        rows="3">{{ old('remarks', $purchase->remarks ?? '') }}</textarea>

                </div>

            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end mt-4">

                <a href="{{ route('purchases.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>

                <button type="submit" class="btn btn-success">

                    <i class="fas fa-save"></i>

                    {{ isset($purchase) ? 'Update Purchase' : 'Save Purchase' }}

                </button>

            </div>

        </div>

    </div>

</div>