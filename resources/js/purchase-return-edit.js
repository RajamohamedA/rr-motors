document.addEventListener('DOMContentLoaded', function () {

    calculateTotals();

    $(document).on('input', '.returnQty', function () {

        let row = $(this).closest('tr');

        let qty = parseFloat($(this).val()) || 0;
        let max = parseFloat($(this).attr('max')) || 0;

        if (qty > max) {
            alert('Return quantity exceeds available balance.');
            qty = max;
            $(this).val(max);
        }

        let price = parseFloat($(this).data('price')) || 0;
        let gstPercent = parseFloat($(this).data('gst')) || 0;

        let subTotal = qty * price;
        let gstAmount = subTotal * gstPercent / 100;
        let lineTotal = subTotal + gstAmount;

        row.find('.gstAmount').text(gstAmount.toFixed(2));
        row.find('.returnAmount').text(lineTotal.toFixed(2));

        row.find('input[name="gst_amount[]"]').val(gstAmount.toFixed(2));
        row.find('input[name="amount[]"]').val(lineTotal.toFixed(2));

        calculateTotals();
    });

    function calculateTotals() {

        let subTotal = 0;
        let gstTotal = 0;
        let grandTotal = 0;

        $('.returnQty').each(function () {

            let qty = parseFloat($(this).val()) || 0;
            let price = parseFloat($(this).data('price')) || 0;
            let gst = parseFloat($(this).data('gst')) || 0;

            let basic = qty * price;
            let gstAmount = basic * gst / 100;
            let total = basic + gstAmount;

            subTotal += basic;
            gstTotal += gstAmount;
            grandTotal += total;
        });

        $('#purchase_return_sub_total').val(subTotal.toFixed(2));
        $('#purchase_return_gst_total').val(gstTotal.toFixed(2));
        $('#purchase_return_grand_total').val(grandTotal.toFixed(2));

        $('#purchase_return_sub_total_hidden').val(subTotal.toFixed(2));
        $('#purchase_return_gst_total_hidden').val(gstTotal.toFixed(2));
    }

});