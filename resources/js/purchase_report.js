document.addEventListener('DOMContentLoaded', function () {

    $(function () {

        $('#btnSearch').click(function () {

            $.ajax({

                url: '/purchase-reports/search',

                type: 'POST',

                data: $('#reportForm').serialize(),

                success: function (response) {

                    $('#reportResult').html(response);

                },

                error: function () {

                    alert('Unable to load report.');

                }

            });

        });

    });

});