@extends('layouts.app')

@section('title','Edit Sales Return')

@section('content')

<div class="container-fluid">

    <form action="{{ route('sales-returns.update',$return->id) }}"
        method="POST">

        @csrf
        @method('PUT')

        <input type="hidden"
            name="sale_id"
            value="{{ $return->sale_id }}">

        <input type="hidden"
            name="customer_id"
            value="{{ $return->customer_id }}">

        <div class="card shadow-sm">

            <div class="card-header bg-danger text-white">

                <div class="d-flex justify-content-between align-items-center">

                    <h4 class="mb-0">

                        <i class="fas fa-undo"></i>

                        Edit Sales Return

                    </h4>

                    <a href="{{ route('sales-returns.index') }}"
                        class="btn btn-light btn-sm">

                        <i class="fas fa-arrow-left"></i>

                        Back

                    </a>

                </div>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3">

                        <label class="form-label">

                            Return No

                        </label>

                        <input type="text"
                            class="form-control"
                            value="{{ $return->return_no }}"
                            readonly>

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">

                            Return Date

                        </label>

                        <input type="date"
                            name="return_date"
                            class="form-control"
                            value="{{ $return->return_date }}"
                            required>

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">

                            Invoice No

                        </label>

                        <input type="text"
                            class="form-control"
                            value="{{ $return->sale->invoice_no }}"
                            readonly>

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">

                            Invoice Date

                        </label>

                        <input type="text"
                            class="form-control"
                            value="{{ date('d-m-Y',strtotime($return->sale->invoice_date)) }}"
                            readonly>

                    </div>

                </div>

                <hr>

                <div class="row">

                    <div class="col-md-4">

                        <label class="form-label">

                            Customer

                        </label>

                        <input type="text"
                            class="form-control"
                            value="{{ $return->customer->customer_name }}"
                            readonly>

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">

                            Mobile

                        </label>

                        <input type="text"
                            class="form-control"
                            value="{{ $return->customer->mobile }}"
                            readonly>

                    </div>

                    <div class="col-md-5">

                        <label class="form-label">

                            Address

                        </label>

                        <input type="text"
                            class="form-control"
                            value="{{ $return->customer->address }}"
                            readonly>

                    </div>

                </div>

                <div class="row mt-3">

                    <div class="col-md-12">

                        <label class="form-label">

                            Remarks

                        </label>

                        <textarea name="remarks"
                            rows="2"
                            class="form-control">{{ $return->remarks }}</textarea>

                    </div>

                </div>

            </div>

        </div>

        <!-- Return Items Card starts here -->
        <!-- Return Items -->

        <div class="card mt-3 shadow-sm">

            <div class="card-header">

                <strong>

                    Return Items

                </strong>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover table-striped mb-0">

                        <thead class="table-dark">

                            <tr>

                                <th width="8%">

                                    Code

                                </th>

                                <th>

                                    Product

                                </th>

                                <th width="8%" class="text-end">

                                    Sold Qty

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

                                <th width="8%" class="text-end">

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

                            @foreach($return->items as $item)

                            <tr>

                                <td>

                                    {{ $item->product->product_code }}

                                    <input type="hidden"
                                        name="item_id[]"
                                        value="{{ $item->id }}">

                                    <input type="hidden"
                                        name="product_id[]"
                                        value="{{ $item->product_id }}">

                                </td>

                                <td>

                                    {{ $item->product->product_name }}

                                </td>

                                <td class="text-end">

                                    {{ number_format($item->sold_qty,2) }}

                                </td>

                                <td class="text-end">

                                    {{ number_format($item->returned_qty,2) }}

                                </td>

                                <td class="text-end fw-bold">

                                    {{ number_format($item->balance_qty,2) }}

                                </td>

                                <td>

                                    <input type="number"
                                        class="form-control text-end returnQty"
                                        name="qty[]"
                                        value="{{ $item->quantity }}"
                                        step="0.01"
                                        min="0"
                                        max="{{ $item->balance_qty + $item->quantity }}"
                                        data-price="{{ $item->sale_price }}"
                                        data-gst="{{ $item->gst_percent }}">

                                </td>

                                <td class="text-end">

                                    {{ number_format($item->sale_price,2) }}

                                    <input type="hidden"
                                        name="price[]"
                                        value="{{ $item->sale_price }}">

                                </td>

                                <td class="text-end">

                                    {{ number_format($item->gst_percent,2) }}

                                    <input type="hidden"
                                        name="gst[]"
                                        value="{{ $item->gst_percent }}">

                                </td>

                                <td class="text-end">

                                    <span class="gstAmount">

                                        {{ number_format($item->gst_amount,2) }}

                                    </span>

                                    <input type="hidden"
                                        name="gst_amount[]"
                                        value="{{ $item->gst_amount }}">

                                </td>

                                <td class="text-end">

                                    <span class="lineTotal">

                                        {{ number_format($item->line_total,2) }}
                                    </span>

                                    <input type="hidden"
                                        name="amount[]"
                                        value="{{ $item->lineTotal }}">

                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <!-- Totals -->

        <div class="row mt-3">

            <div class="col-md-8">

            </div>

            <div class="col-md-4">

                <table class="table table-bordered">

                    <tr>

                        <th width="50%">

                            Sub Total

                        </th>

                        <td>

                            <input type="text"
                                id="sub_total_sales_edit"
                                class="form-control text-end"
                                value="{{ number_format($return->sub_total,2,'.','') }}"
                                readonly>

                        </td>

                    </tr>

                    <tr>

                        <th>

                            GST Total

                        </th>

                        <td>

                            <input type="text"
                                id="gst_total_sales_edit"
                                class="form-control text-end"
                                value="{{ number_format($return->gst_total,2,'.','') }}"
                                readonly>

                        </td>

                    </tr>

                    <tr class="table-danger">

                        <th>

                            Grand Total

                        </th>

                        <td>

                            <input type="text"
                                id="grand_total_sales_edit"
                                name="grand_total"
                                class="form-control text-end fw-bold"
                                value="{{ number_format($return->total_amount,2,'.','') }}"
                                readonly>

                        </td>

                    </tr>

                </table>

            </div>

        </div>

        <!-- Buttons -->

        <div class="text-end mt-3 mb-4">

            <button type="submit"
                class="btn btn-success">

                <i class="fas fa-save"></i>

                Update Sales Return

            </button>

            <a href="{{ route('sales-returns.index') }}"
                class="btn btn-secondary">

                <i class="fas fa-times"></i>

                Cancel

            </a>

        </div>

    </form>

</div>

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $(document).ready(function() {
            calculateTotals();

            $('.returnQty').on('input', function() {

                let row = $(this).closest('tr');

                let qty = parseFloat($(this).val()) || 0;

                let maxQty = parseFloat($(this).attr('max')) || 0;

                if (qty > maxQty) {

                    alert('Return Quantity cannot exceed Balance Quantity.');

                    qty = maxQty;

                    $(this).val(qty);

                }

                let rate = parseFloat($(this).data('price')) || 0;

                let gstPercent = parseFloat($(this).data('gst')) || 0;

                let subTotal = qty * rate;

                let gstAmount = (subTotal * gstPercent) / 100;

                let total = subTotal + gstAmount;

                row.find('.gstAmount').text(gstAmount.toFixed(2));
                row.find('.lineTotal').text(total.toFixed(2));

                row.find('input[name="gst_amount[]"]').val(gstAmount.toFixed(2));
                row.find('input[name="amount[]"]').val(total.toFixed(2));

                calculateTotals();

            });

        });

        function calculateTotals() {
            console.log('test');
            let subTotal = 0;
            let gstTotal = 0;
            let grandTotal = 0;

            $('#returnItems tr').each(function() {

                let qty = parseFloat($(this).find('.returnQty').val()) || 0;

                let rate = parseFloat($(this).find('input[name="price[]"]').val()) || 0;

                let gstPercent = parseFloat($(this).find('input[name="gst[]"]').val()) || 0;

                let lineSub = qty * rate;
                console.log(lineSub);

                let lineGST = (lineSub * gstPercent) / 100;

                let lineGrand = lineSub + lineGST;

                subTotal += lineSub;
                gstTotal += lineGST;
                grandTotal += lineGrand;

            });

            $('#sub_total_sales_edit').val(subTotal.toFixed(2));
            $('#gst_total_sales_edit').val(gstTotal.toFixed(2));
            $('#grand_total_sales_edit').val(grandTotal.toFixed(2));
        }
    });
</script>

@endpush

@endsection