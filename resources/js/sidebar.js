document.addEventListener('DOMContentLoaded', function () {

    const toggle = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');

    if (toggle) {

        toggle.addEventListener('click', function () {

            sidebar.classList.toggle('collapsed');

        });

    }

});