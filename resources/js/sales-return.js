document.addEventListener('DOMContentLoaded', function () {
  $(function () {

        // ===============================
        // Search Invoice
        // ===============================

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

                        <strong>${row.invoice_no}</strong>

                        <br>

                        <small>${row.customer.customer_name}</small>

                    </a>

                    `;

                });

                $('#invoiceResult')

                    .html(html)

                    .show();

            });

        });

        // ===============================
        // Select Invoice
        // ===============================

        $(document).on('click', '.invoiceItem', function (e) {

            e.preventDefault();

            let id = $(this).data('id');

            $.get($('#invoiceUrl').val() + '/' + id, function (sale) {

                loadInvoice(sale);

                $('#invoiceResult').hide();

                $('#invoice_search').val(sale.invoice_no);

            });

        });


        function loadInvoice(sale) {

            $('#sale_id').val(sale.id);

            $('#customer_id').val(sale.customer_id);
            
            let html = `

            <div class="card mt-3">

            <div class="card-header bg-primary text-white">

            Invoice Details

            </div>

            <div class="card-body">

            <div class="row">

            <div class="col-md-6">

            <b>Invoice :</b> ${sale.invoice_no}

            </div>

            <div class="col-md-6">

            <b>Date :</b> ${sale.invoice_date}

            </div>

            <div class="col-md-6 mt-2">

            <b>Customer :</b>

            ${sale.customer.customer_name}

            </div>

            <div class="col-md-6 mt-2">

            <b>Mobile :</b>

            ${sale.customer.mobile ?? ''}

            </div>

            </div>

            <hr>

            <table class="table table-bordered">

            <thead>

            <tr>

            <th>Code</th>

            <th>Product</th>

            <th>Sold Qty</th>

            <th>Return Qty</th>

            <th>Price</th>

            <th>Total</th>

            </tr>

            </thead>

            <tbody>
            `;

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

                            Sold : ${item.quantity}

                            <br>

                            Returned : ${item.returned_qty}

                            <br>

                            Balance : <strong>${item.balance_qty}</strong>

                        </td>

                        <td>

                        <input type="number"

                        class="form-control returnQty"

                        name="qty[]"

                        value="0"

                        min="0"

                        max="${item.balance_qty}"

                        data-price="${item.sale_price}"

                        data-gst="${item.gst_percent}">

                        </td>

                        <td>

                        <input type="hidden"

                        name="price[]"

                        value="${item.sale_price}">

                        ${parseFloat(item.sale_price).toFixed(2)}

                        </td>

                        <td>

                        <input type="hidden"

                        name="gst[]"

                        value="${item.gst_percent}">

                        ${item.gst_percent}

                        </td>

                        <td class="returnAmount">

                        0.00

                        <input type="hidden"

                        name="amount[]"

                        value="0">

                        </td>

                        </tr>

                        `;

                    });

                    html += `

            </tbody>

            </table>

            <div class="row mt-3">

                <div class="col-md-8"></div>

                <div class="col-md-4">

                <table class="table">

                <tr>

                <th>Total Return</th>

                <td>

                <input type="text"

                readonly

                class="form-control"

                id="grand_total"

                name="grand_total"

                value="0">

                </td>

                </tr>

                </table>

                </div>

                </div>

            </div>

            </div>

            `;

                    $('#invoiceDetails').html(html);

        }

        $(document).on('keyup change', '.returnQty', function () {

            let row = $(this).closest('tr');

            let qty = parseFloat($(this).val()) || 0;

            let price = parseFloat($(this).data('price'));

            let total = qty * price;

            let max = parseFloat($(this).attr('max'));

            if (qty > max) {

                alert('Return quantity exceeds balance quantity.');

                $(this).val(max);

                qty = max;
            }

            row.find('.returnAmount').html(total.toFixed(2));

            row.find('input[name="amount[]"]').val(total.toFixed(2));

            calculateReturnTotal();

        });

        function calculateReturnTotal() {
            let total = 0;

            $('input[name="amount[]"]').each(function () {

                total += parseFloat($(this).val()) || 0;

            });

            $('#grand_total').val(total.toFixed(2));
        }

    });
});