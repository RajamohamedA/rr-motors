<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3">

    <!-- Sidebar Toggle -->
    <button class="btn btn-outline-primary me-3" id="toggleSidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Page Title -->
    <h5 class="mb-0 flex-grow-1">
        @yield('title', 'Dashboard')
    </h5>

    <!-- Search -->
    <form class="d-none d-lg-flex me-3" style="width:300px;">
        <input type="search"
               class="form-control"
               placeholder="Search Product, Customer...">
    </form>

    <!-- Full Screen -->
    <button class="btn btn-light me-2" id="fullscreenBtn">
        <i class="fas fa-expand"></i>
    </button>

    <!-- Notification -->
    <button class="btn btn-light position-relative me-3">

        <i class="fas fa-bell"></i>

        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

            3

        </span>

    </button>

    <!-- User -->
    <div class="dropdown">

        <a href="#"
           class="text-decoration-none dropdown-toggle"
           data-bs-toggle="dropdown">

            <i class="fas fa-user-circle fa-lg"></i>

            {{ Auth::user()->name }}

        </a>

        <ul class="dropdown-menu dropdown-menu-end">

            <li>

                <a class="dropdown-item" href="#">

                    <i class="fas fa-user"></i>

                    Profile

                </a>

            </li>

            <li>

                <a class="dropdown-item" href="#">

                    <i class="fas fa-key"></i>

                    Change Password

                </a>

            </li>

            <li><hr class="dropdown-divider"></li>

            <li>

                <form action="{{ route('logout') }}" method="POST">

                    @csrf

                    <button class="dropdown-item">

                        <i class="fas fa-sign-out-alt"></i>

                        Logout

                    </button>

                </form>

            </li>

        </ul>

    </div>

</nav>