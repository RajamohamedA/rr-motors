@csrf

<!-- Sales Header -->
<div class="card shadow-sm mb-3">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">

            <i class="fas fa-file-invoice-dollar"></i>

            {{ isset($sale) ? 'Edit Sales Invoice' : 'Create Sales Invoice' }}

        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            <!-- Invoice No -->

            <div class="col-md-4 mb-3">

                <label class="form-label">Invoice No</label>

                <input type="text"
                       class="form-control"
                       name="invoice_no"
                       readonly
                       value="{{ old('invoice_no',$sale->invoice_no ?? $invoiceNo) }}">

            </div>

            <!-- Invoice Date -->

            <div class="col-md-4 mb-3">

                <label class="form-label">Invoice Date</label>

                <input type="date"
                       class="form-control"
                       name="invoice_date"
                       value="{{ old('invoice_date',$sale->invoice_date ?? date('Y-m-d')) }}">

            </div>

            <!-- Company -->

            <div class="col-md-4 mb-3">

                <label class="form-label">Company</label>

                <select class="form-select"
                        name="company_id">

                    <option value="">Select Company</option>

                    @foreach($companies as $company)

                        <option value="{{ $company->id }}"
                            {{ old('company_id',$sale->company_id ?? '')==$company->id ? 'selected':'' }}>

                            {{ $company->company_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Store -->

            <div class="col-md-6 mb-3">

                <label class="form-label">Store</label>

                <select class="form-select"
                        name="store_id">

                    <option value="">Select Store</option>

                    @foreach($stores as $store)

                        <option value="{{ $store->id }}"
                            {{ old('store_id',$sale->store_id ?? '')==$store->id ? 'selected':'' }}>

                            {{ $store->store_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Customer -->

            <div class="col-md-6 mb-3">

                <label class="form-label">Customer</label>

                <select class="form-select"
                        name="customer_id">

                    <option value="">Select Customer</option>

                    @foreach($customers as $customer)

                        <option value="{{ $customer->id }}"
                            {{ old('customer_id',$sale->customer_id ?? '')==$customer->id ? 'selected':'' }}>

                            {{ $customer->customer_name }}

                        </option>

                    @endforeach

                </select>

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

        <input type="text"
               id="product_search"
               class="form-control form-control-lg"
               autocomplete="off"
               placeholder="Search Product Code / Barcode / Product Name">

        <div id="searchResult"
             class="list-group mt-2"
             style="display:none;max-height:300px;overflow-y:auto;">
        </div>

    </div>

</div>

<input type="hidden"
       id="productSearchUrl"
       value="{{ route('products.search') }}">

<!-- Sales Items -->

<div class="card shadow-sm">

    <div class="card-header bg-info text-white">

        <h5 class="mb-0">

            <i class="fas fa-shopping-basket"></i>

            Sales Items

        </h5>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover"
                   id="purchaseTable">

                <thead>

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

                @if(isset($sale))

                    @foreach($sale->items as $item)

                    <tr>

                        <td>

                            {{ $item->product->product_code }}

                            <input type="hidden"
                                   name="product_id[]"
                                   value="{{ $item->product_id }}">

                        </td>

                        <td>

                            {{ $item->product->product_name }}

                        </td>

                        <td>

                            <input type="number"
                                   class="form-control qty"
                                   name="qty[]"
                                   value="{{ $item->quantity }}">

                        </td>

                        <td>

                            <input type="number"
                                   class="form-control price"
                                   name="price[]"
                                   value="{{ $item->sale_price }}">

                        </td>

                        <td>

                            <input type="number"
                                   class="form-control gst"
                                   name="gst[]"
                                   value="{{ $item->gst_percent }}">

                        </td>

                        <td>

                            <input type="text"
                                   class="form-control amount"
                                   readonly
                                   value="{{ number_format($item->line_total,2,'.','') }}">

                        </td>

                        <td>

                            <button type="button"
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

                        <input type="text"
                               id="grand_total"
                               class="form-control"
                               name="grand_total"
                               readonly
                               value="{{ old('grand_total',$sale->grand_total ?? 0) }}">

                    </th>

                    <th></th>

                </tr>

                </tfoot>

            </table>

        </div>

    </div>

</div>

<!-- Payment -->

<div class="card shadow-sm mt-3">

    <div class="card-body">

        <div class="row">

            <div class="col-md-3">

                <label>Paid Amount</label>

                <input type="number"
                       step="0.01"
                       id="paid_amount"
                       name="paid_amount"
                       class="form-control"
                       value="{{ old('paid_amount',$sale->paid_amount ?? 0) }}">

            </div>

            <div class="col-md-3">

                <label>Balance Amount</label>

                <input type="text"
                       id="balance_amount"
                       class="form-control"
                       readonly
                       value="{{ old('balance_amount',$sale->balance_amount ?? 0) }}">

            </div>

        </div>

    </div>

</div>

<!-- Remarks -->

<div class="card shadow-sm mt-3">

    <div class="card-body">

        <label>Remarks</label>

        <textarea class="form-control"
                  rows="3"
                  name="remarks">{{ old('remarks',$sale->remarks ?? '') }}</textarea>

    </div>

</div>

<!-- Buttons -->

<div class="d-flex justify-content-end mt-3">

    <a href="{{ route('sales.index') }}"
       class="btn btn-secondary me-2">

        Cancel

    </a>

    <button class="btn btn-success">

        <i class="fas fa-save"></i>

        {{ isset($sale) ? 'Update Sales Invoice' : 'Save Sales Invoice' }}

    </button>

</div>  