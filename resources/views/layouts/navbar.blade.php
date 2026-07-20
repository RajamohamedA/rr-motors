<nav class="navbar navbar-expand-lg top-navbar">

    <div class="container-fluid">

        <!-- Left -->

        <div class="d-flex align-items-center">

            <button id="sidebarToggle"
                    class="btn btn-light shadow-sm">

                <i class="fas fa-bars"></i>

            </button>

            <h4 class="ms-4 mb-0 fw-semibold">

                @yield('title','Dashboard')

            </h4>

        </div>

        <!-- Right -->

        <div class="d-flex align-items-center">

            <!-- Search -->

            <div class="search-box me-4">

                <i class="fas fa-search"></i>

                <input type="text"
                       class="form-control"
                       placeholder="Search...">

            </div>

            <!-- Notification -->

            <div class="icon-btn me-3">

                <i class="far fa-bell"></i>

                <span class="badge bg-danger">5</span>

            </div>

            <!-- Fullscreen -->

            <div class="icon-btn me-3"
                 id="fullscreenBtn">

                <i class="fas fa-expand"></i>

            </div>

            <!-- Profile -->

            <div class="dropdown">

                <button class="btn profile-btn"

                        data-bs-toggle="dropdown">

                    <div class="avatar">

                        {{ strtoupper(substr(auth()->user()->name,0,1)) }}

                    </div>

                    <span class="ms-2">

                        {{ auth()->user()->name }}

                    </span>

                    <i class="fas fa-chevron-down ms-2"></i>

                </button>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>

                        <a class="dropdown-item"
                           href="{{ route('profile.edit') }}">

                            <i class="fas fa-user me-2"></i>

                            Profile

                        </a>

                    </li>

                    <li>

                        <hr class="dropdown-divider">

                    </li>

                    <li>

                        <form method="POST"
                              action="{{ route('logout') }}">

                            @csrf

                            <button class="dropdown-item">

                                <i class="fas fa-sign-out-alt me-2"></i>

                                Logout

                            </button>

                        </form>

                    </li>

                </ul>

            </div>

        </div>

    </div>

</nav>