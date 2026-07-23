document.addEventListener('DOMContentLoaded', function () {

    // =============================================
    // SEARCH PURCHASE
    // =============================================

    $('#purchase_search').on('keyup', function () {

        let keyword = $(this).val();

        if (keyword.length < 2) {

            $('#purchaseResult').hide().html('');

            return;

        }

        $.get($('#searchUrl').val(), {

            search: keyword

        }, function (rows) {

            let html = '';

            rows.forEach(function (row) {

                html += `
                <a href="#"
                   class="list-group-item list-group-item-action purchaseItem"
                   data-id="${row.id}">

                    <strong>${row.purchase_no}</strong><br>

                    <small>${row.supplier.supplier_name}</small>

                </a>`;
            });

            $('#purchaseResult').html(html).show();

        });

    });

    // =============================================
    // SELECT PURCHASE
    // =============================================

    $(document).on('click', '.purchaseItem', function (e) {

        e.preventDefault();

        let id = $(this).data('id');

        $.get($('#purchaseUrl').val() + '/' + id, function (purchase) {

            loadPurchase(purchase);

            $('#purchase_search').val(purchase.purchase_no);

            $('#purchaseResult').hide();

        });

    });

    // =============================================
    // LOAD PURCHASE
    // =============================================

    function loadPurchase(purchase) {

        $('#purchase_id').val(purchase.id);

        $('#supplier_id').val(purchase.supplier_id);

        $('#supplier_name').val(purchase.supplier.supplier_name);

        $('#supplier_mobile').val(purchase.supplier.mobile ?? '');

        $('#supplier_address').val(
            purchase.supplier.address ??
            purchase.supplier.billing_address ??
            ''
        );

        let html = '';

        purchase.items.forEach(function (item) {

            html += `
            <tr>

                <td>

                    ${item.product.product_code}

                    <input type="hidden"
                           name="product_id[]"
                           value="${item.product_id}">

                </td>

                <td>

                    ${item.product.product_name}

                </td>

                <td class="text-center">

                    ${item.quantity}

                </td>

                <td class="text-center">

                    ${item.returned_qty}

                </td>

                <td class="text-center">

                    <strong>${item.balance_qty}</strong>

                </td>

                <td>

                    <input
                        type="number"
                        class="form-control returnQty text-end"
                        name="qty[]"
                        value="0"
                        min="0"
                        max="${item.balance_qty}"
                        data-price="${item.purchase_price}"
                        data-gst="${item.gst_percent}">

                </td>

                <td class="text-end">

                    ${parseFloat(item.purchase_price).toFixed(2)}

                    <input
                        type="hidden"
                        name="price[]"
                        value="${item.purchase_price}">

                </td>

                <td class="text-end">

                    ${item.gst_percent}

                    <input
                        type="hidden"
                        name="gst[]"
                        value="${item.gst_percent}">

                </td>

                <td class="text-end">

                    <span class="gstAmount">0.00</span>

                    <input
                        type="hidden"
                        name="gst_amount[]"
                        value="0">

                </td>

                <td class="text-end">

                    <span class="returnAmount">0.00</span>

                    <input
                        type="hidden"
                        name="amount[]"
                        value="0">

                </td>

            </tr>`;
        });

        $('#returnItems').html(html);

        calculateTotals();

    }

    // =============================================
    // RETURN QTY CHANGED
    // =============================================

    $(document).on('input', '.returnQty', function () {

        let row = $(this).closest('tr');

        let qty = parseFloat($(this).val()) || 0;

        let max = parseFloat($(this).attr('max')) || 0;

        if (qty > max) {

            qty = max;

            $(this).val(max);

            alert('Return quantity exceeds balance quantity.');

        }

        let price = parseFloat($(this).data('price')) || 0;

        let gst = parseFloat($(this).data('gst')) || 0;

        let basic = qty * price;

        let gstAmount = basic * gst / 100;

        let total = basic + gstAmount;

        row.find('.gstAmount').text(gstAmount.toFixed(2));

        row.find('.returnAmount').text(total.toFixed(2));

        row.find('input[name="gst_amount[]"]').val(gstAmount.toFixed(2));

        row.find('input[name="amount[]"]').val(total.toFixed(2));

        calculateTotals();

    });

    // =============================================
    // TOTALS
    // =============================================

    function calculateTotals() {

        let subTotal = 0;

        let gstTotal = 0;

        let grandTotal = 0;

        $('#returnItems tr').each(function () {

            let qty = parseFloat($(this).find('.returnQty').val()) || 0;

            let price = parseFloat($(this).find('.returnQty').data('price')) || 0;

            let gst = parseFloat($(this).find('.returnQty').data('gst')) || 0;

            let basic = qty * price;

            let gstAmount = basic * gst / 100;

            subTotal += basic;

            gstTotal += gstAmount;

            grandTotal += basic + gstAmount;

        });

        $('#purchase_return_sub_total').val(subTotal.toFixed(2));

        $('#purchase_return_gst_total').val(gstTotal.toFixed(2));

        $('#purchase_return_grand_total').val(grandTotal.toFixed(2));

    }

    // =============================================
    // CLOSE SEARCH RESULT
    // =============================================

    $(document).click(function (e) {

        if (!$(e.target).closest('#purchase_search,#purchaseResult').length) {

            $('#purchaseResult').hide();

        }

    });

});