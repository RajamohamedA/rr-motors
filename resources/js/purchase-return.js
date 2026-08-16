document.addEventListener('DOMContentLoaded', function () {

    // =============================================
    // SEARCH PURCHASE
    // =============================================

    $('#purchase_search').on('keyup', function () {

        let keyword = $(this).val().trim();

        if (keyword.length < 2) {

            $('#purchaseResult')
                .hide()
                .html('');

            return;
        }

        $.get($('#searchUrl').val(), {
            search: keyword
        })
        .done(function (rows) {

            let html = '';

            if (!rows.length) {

                html = `
                    <div class="list-group-item text-muted">
                        No purchase found
                    </div>
                `;

            } else {

                rows.forEach(function (row) {

                    html += `
                        <a href="#"
                           class="list-group-item list-group-item-action purchaseItem"
                           data-id="${row.id}">

                            <strong>
                                ${row.purchase_no}
                            </strong>

                            <br>

                            <small>
                                ${row.supplier?.supplier_name ?? ''}
                            </small>

                        </a>
                    `;
                });
            }

            $('#purchaseResult')
                .html(html)
                .show();

        })
        .fail(function () {

            $('#purchaseResult')
                .html(`
                    <div class="list-group-item text-danger">
                        Error loading purchase
                    </div>
                `)
                .show();

        });

    });


    // =============================================
    // SELECT PURCHASE
    // =============================================

    $(document).on('click', '.purchaseItem', function (e) {

        e.preventDefault();

        let id = $(this).data('id');

        $.get(
            $('#purchaseUrl').val() + '/' + id
        )
        .done(function (purchase) {

            loadPurchase(purchase);

            $('#purchase_search')
                .val(purchase.purchase_no);

            $('#purchaseResult')
                .hide()
                .html('');

        })
        .fail(function () {

            alert('Unable to load purchase.');

        });

    });


    // =============================================
    // LOAD PURCHASE
    // =============================================

    function loadPurchase(purchase) {

        $('#purchase_id').val(purchase.id);

        $('#supplier_id').val(purchase.supplier_id);

        $('#supplier_name').val(
            purchase.supplier?.supplier_name ?? ''
        );

        $('#supplier_mobile').val(
            purchase.supplier?.mobile ?? ''
        );

        $('#supplier_address').val(
            purchase.supplier?.address ??
            purchase.supplier?.billing_address ??
            ''
        );


        let html = '';

        if (!purchase.items || purchase.items.length === 0) {

            html = `
                <tr>
                    <td colspan="11"
                        class="text-center text-muted">

                        No items found in this purchase.

                    </td>
                </tr>
            `;

            $('#returnItems').html(html);

            calculateTotals();

            return;
        }


        purchase.items.forEach(function (item) {

            let purchased = parseFloat(item.quantity) || 0;

            let returned = parseFloat(item.returned_qty) || 0;

            let balance = parseFloat(item.balance_qty) || 0;

            let price = parseFloat(item.purchase_price) || 0;

            let gst = parseFloat(item.gst_percent) || 0;

            let godownId = item.godown_id ?? '';

            let godownName = item.godown?.godown_name ?? 'Shop / No Godown';


            html += `
                <tr>

                    <!-- Product Code -->
                    <td>

                        ${item.product?.product_code ?? ''}

                        <input
                            type="hidden"
                            name="product_id[]"
                            value="${item.product_id}">

                    </td>


                    <!-- Product -->
                    <td>

                        ${item.product?.product_name ?? ''}

                    </td>


                    <!-- Godown -->
                    <td>

                        ${godownName}

                        <input
                            type="hidden"
                            name="godown_id[]"
                            value="${godownId}">

                    </td>


                    <!-- Purchased -->
                    <td class="text-center">

                        ${purchased.toFixed(2)}

                    </td>


                    <!-- Returned -->
                    <td class="text-center">

                        ${returned.toFixed(2)}

                    </td>


                    <!-- Balance -->
                    <td class="text-center">

                        <strong>
                            ${balance.toFixed(2)}
                        </strong>

                    </td>


                    <!-- Return Qty -->
                    <td>

                        <input
                            type="number"
                            class="form-control returnQty text-end"
                            name="qty[]"
                            value="0"
                            min="0"
                            max="${balance}"
                            step="0.01"

                            data-price="${price}"
                            data-gst="${gst}">

                    </td>


                    <!-- Rate -->
                    <td class="text-end">

                        ${price.toFixed(2)}

                        <input
                            type="hidden"
                            name="price[]"
                            value="${price}">

                    </td>


                    <!-- GST -->
                    <td class="text-end">

                        ${gst.toFixed(2)}%

                        <input
                            type="hidden"
                            name="gst[]"
                            value="${gst}">

                    </td>


                    <!-- GST Amount -->
                    <td class="text-end">

                        <span class="gstAmount">
                            0.00
                        </span>

                        <input
                            type="hidden"
                            name="gst_amount[]"
                            value="0">

                    </td>


                    <!-- Total -->
                    <td class="text-end">

                        <span class="returnAmount">
                            0.00
                        </span>

                        <input
                            type="hidden"
                            name="amount[]"
                            value="0">

                    </td>

                </tr>
            `;

        });


        $('#returnItems').html(html);

        calculateTotals();

    }


    // =============================================
    // RETURN QTY CHANGE
    // =============================================

    $(document).on('input', '.returnQty', function () {

        let input = $(this);

        let row = input.closest('tr');

        let qty = parseFloat(input.val()) || 0;

        let max = parseFloat(input.attr('max')) || 0;


        // Prevent negative quantity
        if (qty < 0) {

            qty = 0;

            input.val(0);

        }


        // Prevent exceeding balance
        if (qty > max) {

            qty = max;

            input.val(max);

            alert(
                'Return quantity exceeds balance quantity.'
            );

        }


        let price =
            parseFloat(input.data('price')) || 0;

        let gst =
            parseFloat(input.data('gst')) || 0;


        // Basic amount
        let basic = qty * price;


        // GST amount
        let gstAmount =
            basic * gst / 100;


        // Grand total
        let total =
            basic + gstAmount;


        row.find('.gstAmount')
            .text(gstAmount.toFixed(2));


        row.find('.returnAmount')
            .text(total.toFixed(2));


        row.find('input[name="gst_amount[]"]')
            .val(gstAmount.toFixed(2));


        row.find('input[name="amount[]"]')
            .val(total.toFixed(2));


        calculateTotals();

    });


    // =============================================
    // CALCULATE TOTALS
    // =============================================

    function calculateTotals() {

        let subTotal = 0;

        let gstTotal = 0;

        let grandTotal = 0;


        $('#returnItems tr').each(function () {

            let input =
                $(this).find('.returnQty');


            if (!input.length) {
                return;
            }


            let qty =
                parseFloat(input.val()) || 0;


            let price =
                parseFloat(input.data('price')) || 0;


            let gst =
                parseFloat(input.data('gst')) || 0;


            let basic =
                qty * price;


            let gstAmount =
                basic * gst / 100;


            let total =
                basic + gstAmount;


            subTotal += basic;

            gstTotal += gstAmount;

            grandTotal += total;

        });


        $('#purchase_return_sub_total')
            .val(subTotal.toFixed(2));


        $('#purchase_return_gst_total')
            .val(gstTotal.toFixed(2));


        $('#purchase_return_grand_total')
            .val(grandTotal.toFixed(2));


        // For edit page hidden fields
        $('#purchase_return_sub_total_hidden')
            .val(subTotal.toFixed(2));


        $('#purchase_return_gst_total_hidden')
            .val(gstTotal.toFixed(2));

    }


    // =============================================
    // CLOSE SEARCH RESULT
    // =============================================

    $(document).on('click', function (e) {

        if (
            !$(e.target).closest(
                '#purchase_search, #purchaseResult'
            ).length
        ) {

            $('#purchaseResult').hide();

        }

    });


    // =============================================
    // INITIAL CALCULATION
    // FOR EDIT PAGE
    // =============================================

    calculateTotals();

});