document.addEventListener('DOMContentLoaded', function () {

    $(function () {

        $('#btnSearch').click(function () {
            
            let hasValue = false;

            $('#reportForm')
                .find('input, select')
                .not('[name="_token"]')
                .each(function () {

                    if ($(this).val().trim() !== '') {
                        hasValue = true;
                        return false;
                    }

                });

            if (!hasValue) {
                alert('Please select at least one search filter.');
                return;
            }
        

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