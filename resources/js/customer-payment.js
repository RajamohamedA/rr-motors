document.addEventListener('DOMContentLoaded', function () {
    $(document).ready(function () {

        // ===========================
        // Invoice Search
        // ===========================

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

                        <small>

                            ${row.customer.customer_name}

                        </small>

                    </a>`;
                });

                $('#invoiceResult').html(html).show();

            });

        });

        // ===========================
        // Select Invoice
        // ===========================

        $(document).on('click', '.invoiceItem', function (e) {

            e.preventDefault();

            let id = $(this).data('id');

            $.get($('#invoiceUrl').val() + '/' + id, function (sale) {

                $('#sale_id').val(sale.id);

                $('#customer_id').val(sale.customer_id);

                $('#customer_name').val(sale.customer.customer_name);

                $('#customer_mobile').val(sale.customer.mobile ?? '');

                $('#grand_total').val(parseFloat(sale.grand_total).toFixed(2));

                $('#paid_amount').val(parseFloat(sale.paid_amount).toFixed(2));

                $('#balance_amount').val(parseFloat(sale.balance_amount).toFixed(2));

                $('#receive_amount').val(parseFloat(sale.balance_amount).toFixed(2));

                $('#invoice_search').val(sale.invoice_no);

                $('#invoiceResult').hide();

            });

        });

        // ===========================
        // Payment Validation
        // ===========================

        $('#receive_amount').keyup(function () {

            let receive = parseFloat($(this).val()) || 0;

            let balance = parseFloat($('#balance_amount').val()) || 0;

            if (receive > balance) {

                alert('Receive amount cannot exceed Balance Amount.');

                $(this).val(balance.toFixed(2));

            }

        });

        // ===========================
        // Hide Search Result
        // ===========================

        $(document).click(function (e) {

            if (!$(e.target).closest('#invoice_search,#invoiceResult').length) {

                $('#invoiceResult').hide();

            }

        });

    });
});