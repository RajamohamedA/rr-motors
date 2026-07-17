<nav class="main-header navbar navbar-expand navbar-white navbar-light">

    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#">
                ☰
            </a>
        </li>
    </ul>

    <ul class="navbar-nav ms-auto">
        <li class="nav-item">
            <span class="nav-link">
                {{ auth()->user()->name }}
            </span>
        </li>
    </ul>

</nav>