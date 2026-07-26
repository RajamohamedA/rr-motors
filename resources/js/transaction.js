document.addEventListener('DOMContentLoaded', function () {

    $(function () {

        const transactionType = $('#transactionType').val() || 'sale';

        const itemContainer = $('#itemContainer').val() || 'purchaseItems';

        calculateGrandTotal();

        /*
        |--------------------------------------------------------------------------
        | Product Search
        |--------------------------------------------------------------------------
        */

        $('#product_search').keyup(function () {

            let keyword = $(this).val();

            if (keyword.length < 2) {
                $('#searchResult').hide().html('');
                return;
            }

            $.ajax({

                url: $('#productSearchUrl').val(),

                type: 'GET',

                data: {
                    search: keyword
                },

                success: function (products) {

                    let html = '';
                    const type = $('#transactionType').val() || 'sale';

                    $.each(products, function (i, product) {

                        let price = (type === 'purchase')
                            ? parseFloat(product.purchase_price)
                            : parseFloat(product.selling_price);

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

                                | Price : ${price.toFixed(2)}

                            </small>

                        </a>`;
                    });

                    $('#searchResult').html(html).show();

                }

            });

        });

        /*
        |--------------------------------------------------------------------------
        | Add Product
        |--------------------------------------------------------------------------
        */

        $(document).on('click', '.productItem', function (e) {

            e.preventDefault();

            let id = $(this).data('id');

            let code = $(this).data('code');

            let name = $(this).data('name');

            let price = parseFloat($(this).data('price'));

            let gst = parseFloat($(this).data('gst'));

            let stock = parseFloat($(this).data('stock'));

            /*
            |--------------------------------------------------------------------------
            | Duplicate Product Check
            |--------------------------------------------------------------------------
            */

            let exists = false;

            $('input[name="product_id[]"]').each(function () {

                if ($(this).val() == id) {

                    exists = true;

                    return false;

                }

            });

            if (exists) {

                alert('Product already added.');

                return;

            }

            /*
            |--------------------------------------------------------------------------
            | Row HTML
            |--------------------------------------------------------------------------
            */

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

            </tr>
            `;

            $('#' + itemContainer).append(row);

            $('#product_search').val('');

            $('#searchResult').hide();

            calculateGrandTotal();

        });

        /*
        |--------------------------------------------------------------------------
        | Qty / Price / GST Change
        |--------------------------------------------------------------------------
        */

        $(document).on('keyup change', '.qty,.price,.gst', function () {

            let row = $(this).closest('tr');

            let qty = parseFloat(row.find('.qty').val()) || 0;

            let price = parseFloat(row.find('.price').val()) || 0;

            let gst = parseFloat(row.find('.gst').val()) || 0;

            /*
            |--------------------------------------------------------------------------
            | Sales Stock Validation
            |--------------------------------------------------------------------------
            */

            if (transactionType === 'sale') {

                let stock = parseFloat(row.find('.stock').text()) || 0;

                if (qty > stock) {

                    alert('Only ' + stock + ' stock available.');

                    qty = stock;

                    row.find('.qty').val(stock);

                }

            }

            /*
            |--------------------------------------------------------------------------
            | Amount Calculation
            |--------------------------------------------------------------------------
            */

            let basic = qty * price;

            let gstAmount = basic * gst / 100;

            let total = basic + gstAmount;

            row.find('.amount').val(total.toFixed(2));

            calculateGrandTotal();

        });

        /*
        |--------------------------------------------------------------------------
        | Remove Item
        |--------------------------------------------------------------------------
        */

        $(document).on('click', '.removeRow', function () {

            if (confirm('Remove this product?')) {

                $(this).closest('tr').remove();

                calculateGrandTotal();

            }

        });

        /*
        |--------------------------------------------------------------------------
        | Grand Total
        |--------------------------------------------------------------------------
        */

        function calculateGrandTotal() {

            let grandTotal = 0;

            $('.amount').each(function () {

                grandTotal += parseFloat($(this).val()) || 0;

            });

            $('#grand_total').val(grandTotal.toFixed(2));

            /*
            |--------------------------------------------------------------------------
            | Sales Payment
            |--------------------------------------------------------------------------
            */

            if ($('#paid_amount').length) {

                calculateBalance();

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Balance
        |--------------------------------------------------------------------------
        */

        function calculateBalance() {

            let grand = parseFloat($('#grand_total').val()) || 0;

            let paid = parseFloat($('#paid_amount').val()) || 0;

            let balance = grand - paid;

            console.log("Qty :", qty);
            console.log("Price :", price);
            console.log("GST :", gst);

            $('#balance_amount').val(balance.toFixed(2));

        }

        $(document).on('keyup change', '#paid_amount', function () {

            calculateBalance();

        });        /*
        |--------------------------------------------------------------------------
        | Qty / Price / GST Change
        |--------------------------------------------------------------------------
        */

        $(document).on('keyup change', '.qty,.price,.gst', function () {

            let row = $(this).closest('tr');

            let qty = parseFloat(row.find('.qty').val()) || 0;

            let price = parseFloat(row.find('.price').val()) || 0;

            let gst = parseFloat(row.find('.gst').val()) || 0;

            /*
            |--------------------------------------------------------------------------
            | Sales Stock Validation
            |--------------------------------------------------------------------------
            */

            if (transactionType === 'sale') {

                let stock = parseFloat(row.find('.stock').text()) || 0;

                if (qty > stock) {

                    alert('Only ' + stock + ' stock available.');

                    qty = stock;

                    row.find('.qty').val(stock);

                }

            }

            /*
            |--------------------------------------------------------------------------
            | Amount Calculation
            |--------------------------------------------------------------------------
            */

            let basic = qty * price;

            let gstAmount = basic * gst / 100;

            let total = basic + gstAmount;

            row.find('.amount').val(total.toFixed(2));

            calculateGrandTotal();

        });

        /*
        |--------------------------------------------------------------------------
        | Remove Item
        |--------------------------------------------------------------------------
        */

        $(document).on('click', '.removeRow', function () {

            if (confirm('Remove this product?')) {

                $(this).closest('tr').remove();

                calculateGrandTotal();

            }

        });

        /*
        |--------------------------------------------------------------------------
        | Grand Total
        |--------------------------------------------------------------------------
        */

        function calculateGrandTotal() {

            let grandTotal = 0;

            $('.amount').each(function () {

                grandTotal += parseFloat($(this).val()) || 0;

            });

            $('#grand_total').val(grandTotal.toFixed(2));

            /*
            |--------------------------------------------------------------------------
            | Sales Payment
            |--------------------------------------------------------------------------
            */

            if ($('#paid_amount').length) {

                calculateBalance();

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Balance
        |--------------------------------------------------------------------------
        */

        function calculateBalance() {

            let grand = parseFloat($('#grand_total').val()) || 0;

            let paid = parseFloat($('#paid_amount').val()) || 0;

            let balance = grand - paid;

            $('#balance_amount').val(balance.toFixed(2));

        }

        $(document).on('keyup change', '#paid_amount', function () {

            calculateBalance();

        });

        /*
        |--------------------------------------------------------------------------
        | Recalculate Existing Rows (Edit Mode)
        |--------------------------------------------------------------------------
        */

        function recalculateRows() {

            $('#' + itemContainer + ' tr').each(function () {

                let row = $(this);

                let qty = parseFloat(row.find('.qty').val()) || 0;

                let price = parseFloat(row.find('.price').val()) || 0;

                let gst = parseFloat(row.find('.gst').val()) || 0;

                let basic = qty * price;

                let gstAmount = basic * gst / 100;

                let total = basic + gstAmount;

                row.find('.amount').val(total.toFixed(2));

            });

            calculateGrandTotal();

        }

        /*
        |--------------------------------------------------------------------------
        | Hide Product Search
        |--------------------------------------------------------------------------
        */

        $(document).click(function (e) {

            if (!$(e.target).closest('#product_search,#searchResult').length) {

                $('#searchResult').hide();

            }

        });

        /*
        |--------------------------------------------------------------------------
        | ESC Key Hide Search
        |--------------------------------------------------------------------------
        */

        $(document).keyup(function (e) {

            if (e.key === "Escape") {

                $('#searchResult').hide();

            }

        });

        /*
        |--------------------------------------------------------------------------
        | Enter Key Prevent Submit
        |--------------------------------------------------------------------------
        */

        $('#product_search').keypress(function (e) {

            if (e.which === 13) {

                e.preventDefault();

            }

        });

        /*
        |--------------------------------------------------------------------------
        | Initial Load
        |--------------------------------------------------------------------------
        */

        recalculateRows();

        calculateGrandTotal();

    });

});