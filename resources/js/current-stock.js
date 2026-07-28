document.addEventListener('DOMContentLoaded', function () {

    import 'datatables.net-bs5';
    import 'datatables.net-buttons-bs5';

    // Search Table

    let search = document.getElementById('stockSearch');

    if (search) {

        search.addEventListener('keyup', function () {

            let value = this.value.toLowerCase();

            let rows = document.querySelectorAll("#stockTable tbody tr");

            rows.forEach(function (row) {

                row.style.display =
                    row.innerText.toLowerCase().indexOf(value) > -1
                        ? ""
                        : "none";

            });

        });

    }

});

function printStock() {

    window.print();

}



