const sidebar = document.getElementById('sidebar');

const toggle1 = document.getElementById('sidebarToggle');
const toggle2 = document.getElementById('sidebarToggleTop');

function toggleSidebar() {

    sidebar.classList.toggle('collapsed');

    document.body.classList.toggle('sidebar-collapse');

}

if (toggle1)
    toggle1.onclick = toggleSidebar;

if (toggle2)
    toggle2.onclick = toggleSidebar;