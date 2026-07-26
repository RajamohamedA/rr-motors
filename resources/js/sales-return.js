document.addEventListener('DOMContentLoaded', function () {

    // ===========================================
    // Search Invoice
    // ===========================================

    $('#invoice_search').keyup(function () {

        let keyword = $(this).val();

        if (keyword.length < 2) {
            $('#invoiceResult').hide().html('');
            return;
        }

        $.get($('#searchUrl').val(), {
            search: keyword
        }, function (rows) {

            let html = '';

            $.each(rows, function (i, row) {

                html += `
                <a href="#"
                    class="list-group-item list-group-item-action invoiceItem"
                    data-id="${row.id}">

                    <strong>${row.invoice_no}</strong><br>

                    <small>${row.customer.customer_name}</small>

                </a>`;
            });

            $('#invoiceResult').html(html).show();

        });

    });

    // ===========================================
    // Select Invoice
    // ===========================================

    $(document).on('click', '.invoiceItem', function (e) {

        e.preventDefault();

        let id = $(this).data('id');

        $.get($('#invoiceUrl').val() + '/' + id, function (sale) {

            loadInvoice(sale);
            console.log(sale);

            $('#invoice_search').val(sale.invoice_no);

            $('#invoiceResult').hide();

        });

    });

    // ===========================================
    // Load Invoice
    // ===========================================

    function loadInvoice(sale) {

        $('#sale_id').val(sale.id);

        $('#customer_id').val(sale.customer_id);

        $('#customer_name').val(sale.customer.customer_name);

        $('#customer_mobile').val(sale.customer.mobile ?? '');

        $('#customer_address').val(sale.customer.billing_address ?? '');

        let html = '';

        sale.items.forEach(function (item) {

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

                <td>

                    Sold : ${item.quantity}<br>

                    Returned : ${item.returned_qty}<br>

                    <strong>Balance : ${item.balance_qty}</strong>

                </td>

                <td>

                    <input

                        type="number"

                        class="form-control returnQty"

                        name="qty[]"

                        value="0"

                        min="0"

                        max="${item.balance_qty}"

                        data-price="${item.sale_price}"

                        data-gst="${item.gst_percent}">

                </td>

                <td>

                    ${parseFloat(item.sale_price).toFixed(2)}

                    <input

                        type="hidden"

                        name="price[]"

                        value="${item.sale_price}">

                </td>

                <td>

                    ${parseFloat(item.gst_percent).toFixed(2)} %

                    <br>

                    GST :
                    <span class="gstDisplay">0.00</span>

                    <input

                        type="hidden"

                        name="gst[]"

                        value="${item.gst_percent}">

                    <input

                        type="hidden"

                        class="gstAmount"

                        name="gst_amount[]"

                        value="0">

                </td>

                <td>

                    <span class="returnAmount">0.00</span>

                    <input

                        type="hidden"

                        class="amount"

                        name="amount[]"

                        value="0">

                </td>

            </tr>
            `;

        });

        $('#returnItems').html(html);

        calculateReturnTotal();

    }

    // ===========================================
    // Qty Change
    // ===========================================

    $(document).on('keyup change', '.returnQty', function () {

        let row = $(this).closest('tr');

        let qty = parseFloat($(this).val()) || 0;

        let max = parseFloat($(this).attr('max'));

        if (qty > max) {

            alert('Return quantity exceeds available balance.');

            qty = max;

            $(this).val(max);

        }

        let price = parseFloat($(this).data('price')) || 0;

        let gst = parseFloat($(this).data('gst')) || 0;

        let basic = qty * price;

        let gstAmount = basic * gst / 100;

        let total = basic + gstAmount;

        row.find('.gstDisplay').text(gstAmount.toFixed(2));

        row.find('.gstAmount').val(gstAmount.toFixed(2));

        row.find('.returnAmount').text(total.toFixed(2));

        row.find('.amount').val(total.toFixed(2));

        calculateReturnTotal();

    });

    // ===========================================
    // Grand Total
    // ===========================================

    function calculateReturnTotal() {

        let total = 0;

        $('.amount').each(function () {

            total += parseFloat($(this).val()) || 0;

        });

        $('#grand_total').val(total.toFixed(2));

    }

    // ===========================================
    // Hide Search
    // ===========================================

    $(document).click(function (e) {

        if (!$(e.target).closest('#invoice_search,#invoiceResult').length) {

            $('#invoiceResult').hide();

        }

    });

});