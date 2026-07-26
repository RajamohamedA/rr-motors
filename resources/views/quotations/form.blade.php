

@csrf

<!-- Quotation Header -->

<div class="card shadow-sm mb-3">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">

            <i class="fas fa-file-signature"></i>

            {{ isset($quotation) ? 'Edit Quotation' : 'Create Quotation' }}

        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            <!-- Quotation No -->

            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Quotation No

                </label>

                <input type="text"
                    class="form-control"
                    name="quotation_no"
                    readonly
                    value="{{ old('quotation_no',$quotation->quotation_no ?? $quotationNo) }}">

            </div>

            <!-- Quotation Date -->

            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Quotation Date

                </label>

                <input type="date"
                    class="form-control"
                    name="quotation_date"
                    value="{{ old('quotation_date',$quotation->quotation_date ?? date('Y-m-d')) }}">

            </div>

            <!-- Valid Until -->

            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Valid Until

                </label>

                <input type="date"
                    class="form-control"
                    name="valid_until"
                    value="{{ old('valid_until',$quotation->valid_until ?? '') }}">

            </div>

            <!-- Company -->

            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Company

                </label>

                <select class="form-select"
                    name="company_id">

                    <option value="">

                        Select Company

                    </option>

                    @foreach($companies as $company)

                    <option value="{{ $company->id }}"
                        {{ old('company_id',$quotation->company_id ?? '')==$company->id ? 'selected':'' }}>

                        {{ $company->company_name }}

                    </option>

                    @endforeach

                </select>

            </div>

            <!-- Store -->

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Store

                </label>

                <select class="form-select"
                    name="store_id">

                    <option value="">

                        Select Store

                    </option>

                    @foreach($stores as $store)

                    <option value="{{ $store->id }}"
                        {{ old('store_id',$quotation->store_id ?? '')==$store->id ? 'selected':'' }}>

                        {{ $store->store_name }}

                    </option>

                    @endforeach

                </select>

            </div>

            <!-- Customer -->

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Customer

                </label>

                <select class="form-select"
                    name="customer_id">

                    <option value="">

                        Select Customer

                    </option>

                    @foreach($customers as $customer)

                    <option value="{{ $customer->id }}"
                        {{ old('customer_id',$quotation->customer_id ?? '')==$customer->id ? 'selected':'' }}>

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

<input type="hidden" id="transactionType" value="quotation">
<input type="hidden" id="itemContainer" value="quotationItems">
<!-- Quotation Items -->

<div class="card shadow-sm">

    <div class="card-header bg-info text-white">

        <h5 class="mb-0">

            <i class="fas fa-box"></i>

            Quotation Items

        </h5>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover" id="quotationTable">

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

                <tbody id="quotationItems">

                    @if(isset($quotation))

                    @foreach($quotation->items as $item)

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
                                value="{{ $item->quantity }}"
                                min="1"
                                step="0.01">

                        </td>

                        <td>

                            <input type="number"
                                class="form-control price"
                                name="price[]"
                                value="{{ $item->sale_price }}"
                                step="0.01">

                        </td>

                        <td>

                            <input type="number"
                                class="form-control gst"
                                name="gst[]"
                                value="{{ $item->gst_percent }}"
                                step="0.01">

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
                                name="grand_total"
                                class="form-control"
                                readonly
                                value="{{ old('grand_total',$quotation->grand_total ?? 0) }}">

                        </th>

                        <th></th>

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

</div>

<!-- Remarks -->

<div class="card shadow-sm mt-3">

    <div class="card-header bg-light">

        <h5 class="mb-0">

            <i class="fas fa-comment"></i>

            Remarks

        </h5>

    </div>

    <div class="card-body">

        <textarea class="form-control"
            name="remarks"
            rows="3"
            placeholder="Enter Remarks...">{{ old('remarks',$quotation->remarks ?? '') }}</textarea>

    </div>

</div>

<!-- Buttons -->

<div class="d-flex justify-content-end mt-3 mb-5">

    <a href="{{ route('quotations.index') }}"
        class="btn btn-secondary me-2">

        <i class="fas fa-times"></i>

        Cancel

    </a>

    <button type="reset"
        class="btn btn-warning me-2">

        <i class="fas fa-redo"></i>

        Reset

    </button>

    <button type="submit"
        class="btn btn-success">

        <i class="fas fa-save"></i>

        {{ isset($quotation) ? 'Update Quotation' : 'Save Quotation' }}

    </button>

</div>

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function calculateTotal() {

            let grandTotal = 0;

            $('#quotationItems tr').each(function() {

                let qty = parseFloat($(this).find('.qty').val()) || 0;

                let price = parseFloat($(this).find('.price').val()) || 0;

                let gst = parseFloat($(this).find('.gst').val()) || 0;

                let amount = qty * price;

                let gstAmount = amount * gst / 100;

                let total = amount + gstAmount;

                $(this).find('.amount').val(total.toFixed(2));

                grandTotal += total;

            });

            $('#grand_total').val(grandTotal.toFixed(2));

        }

        $(document).on('keyup change', '.qty,.price,.gst', function() {

            calculateTotal();

        });

        $(document).on('click', '.removeRow', function() {

            $(this).closest('tr').remove();

            calculateTotal();

        });

        calculateTotal();
    });
</script>

@endpush