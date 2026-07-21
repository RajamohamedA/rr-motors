document.addEventListener('DOMContentLoaded', function () {

    $(function () {

        calculateGrandTotal();

        // ==========================
        // Product Search
        // ==========================
        $('#product_search').keyup(function () {

            let keyword = $(this).val();

            if (keyword.length < 2) {
                $('#searchResult').hide().html('');
                return;
            }

            $.get($('#productSearchUrl').val(), {
                search: keyword
            }, function (products) {

                let html = '';

                let type = $('#transactionType').val();

                $.each(products, function (i, product) {

                    let price = type === 'sale'
                        ? parseFloat(product.sale_price)
                        : parseFloat(product.purchase_price);

                    html += `
                    <a href="#"
                       class="list-group-item list-group-item-action productItem"

                       data-id="${product.id}"
                       data-code="${product.product_code}"
                       data-name="${product.product_name}"
                       data-price="${price}"
                       data-gst="${product.gst_percent}"
                       data-stock="${product.current_stock}">

                        <strong>${product.product_code}</strong>

                        - ${product.product_name}

                        <br>

                        <small>

                            Stock : ${product.current_stock}

                            | ₹${price.toFixed(2)}

                        </small>

                    </a>`;
                });

                $('#searchResult').html(html).show();

            });

        });

        // ==========================
        // Add Product
        // ==========================
        $(document).on('click', '.productItem', function (e) {

            e.preventDefault();

            let id = $(this).data('id');
            let code = $(this).data('code');
            let name = $(this).data('name');
            let price = parseFloat($(this).data('price'));
            let gst = parseFloat($(this).data('gst'));
            let stock = parseFloat($(this).data('stock'));

            // Duplicate Check
            let exists = false;

            $('input[name="product_id[]"]').each(function () {

                if ($(this).val() == id) {
                    exists = true;
                }

            });

            if (exists) {

                alert('Product already added.');

                return;
            }

            let row = `

            <tr>

                <td>

                    ${code}

                    <input type="hidden"
                           name="product_id[]"
                           value="${id}">

                </td>

                <td>

                    ${name}

                    <br>

                    <small class="text-success">

                        Stock :
                        <span class="stock">${stock}</span>

                    </small>

                </td>

                <td>

                    <input type="number"
                           class="form-control qty"
                           name="qty[]"
                           value="1"
                           min="1">

                </td>

                <td>

                    <input type="number"
                           class="form-control price"
                           name="price[]"
                           value="${price}"
                           step="0.01">

                </td>

                <td>

                    <input type="number"
                           class="form-control gst"
                           name="gst[]"
                           value="${gst}">

                </td>

                <td>

                    <input type="text"
                           class="form-control amount"
                           readonly
                           value="${price.toFixed(2)}">

                </td>

                <td>

                    <button type="button"
                            class="btn btn-danger btn-sm removeRow">

                        <i class="fas fa-trash"></i>

                    </button>

                </td>

            </tr>`;

            $('#purchaseItems').append(row);

            $('#product_search').val('');

            $('#searchResult').hide();

            calculateGrandTotal();

        });

        // ==========================
        // Qty / Price Change
        // ==========================
        $(document).on('keyup change', '.qty,.price', function () {

            let row = $(this).closest('tr');

            let qty = parseFloat(row.find('.qty').val()) || 0;

            let price = parseFloat(row.find('.price').val()) || 0;

            // Sales Stock Validation
            if ($('#transactionType').val() === 'sale') {

                let stock = parseFloat(row.find('.stock').text()) || 0;

                if (qty > stock) {

                    alert('Only ' + stock + ' stock available.');

                    qty = stock;

                    row.find('.qty').val(stock);

                }

            }

            let amount = qty * price;

            row.find('.amount').val(amount.toFixed(2));

            calculateGrandTotal();

        });

        // ==========================
        // Remove Row
        // ==========================
        $(document).on('click', '.removeRow', function () {

            $(this).closest('tr').remove();

            calculateGrandTotal();

        });

        // ==========================
        // Grand Total
        // ==========================
        function calculateGrandTotal() {

            let total = 0;

            $('.amount').each(function () {

                total += parseFloat($(this).val()) || 0;

            });

            $('#grand_total').val(total.toFixed(2));

            calculateBalance();

        }

        // ==========================
        // Balance
        // ==========================
        function calculateBalance() {

            if ($('#paid_amount').length === 0)
                return;

            let grand = parseFloat($('#grand_total').val()) || 0;

            let paid = parseFloat($('#paid_amount').val()) || 0;

            let balance = grand - paid;

            $('#balance_amount').val(balance.toFixed(2));

        }

        $(document).on('keyup change', '#paid_amount', calculateBalance);

        // ==========================
        // Hide Search
        // ==========================
        $(document).click(function (e) {

            if (!$(e.target).closest('#product_search,#searchResult').length) {

                $('#searchResult').hide();

            }

        });

    });

});