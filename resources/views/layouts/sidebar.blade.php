<aside id="sidebar" class="sidebar">

    <!-- Logo -->
    <div class="sidebar-header">

        <div class="logo">

            <!-- <img src="{{ asset('images/logo.png') }}"
                 alt="RR Motors"
                 class="logo-img"> -->

            <div class="logo-text">

                <h4>RR Motors</h4>

                <small>Inventory ERP</small>

            </div>

        </div>

        <button class="toggle-btn" id="sidebarToggle">

            <i class="fas fa-bars"></i>

        </button>

    </div>

    <!-- Menu -->

    <ul class="menu">

        <li>
            <a href="{{ route('dashboard') }}"
               class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="{{ route('companies.index') }}"
               class="{{ request()->routeIs('company.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>Company</span>
            </a>
        </li>

        <li>
            <a href="{{ route('stores.index') }}"
               class="{{ request()->routeIs('stores.*') ? 'active' : '' }}">
                <i class="fas fa-store"></i>
                <span>Stores</span>
            </a>
        </li>

        <li>
            <a href="{{ route('categories.index') }}">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
        </li>

        <li>
            <a href="{{ route('units.index') }}" class="{{ request()->routeIs('units.*') ? 'active' : '' }}">
                <i class="fas fa-ruler"></i>
                <span>Units</span>
            </a>
        </li>

        <li>

            <a href="{{ route('brands.index') }}"
            class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}">

                <i class="fas fa-tags"></i>

                <span>Brands</span>

            </a>

        </li>

        <li>
            <a href="{{ route('products.index') }}"
            class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">

                <i class="fas fa-box"></i>

                <span>Products</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('suppliers.index') }}"
            class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="fas fa-truck"></i>
                <span>Suppliers</span>
            </a>
        </li>

        <li>
            <a href="#">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
        </li>

        <li>
            <a href="#">
                <i class="fas fa-shopping-cart"></i>
                <span>Purchases</span>
            </a>
        </li>

        <li>
            <a href="#">
                <i class="fas fa-file-invoice"></i>
                <span>Sales</span>
            </a>
        </li>

        <li>
            <a href="#">
                <i class="fas fa-chart-line"></i>
                <span>Reports</span>
            </a>
        </li>

    </ul>

    <!-- Bottom -->

    <div class="sidebar-footer">

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button class="logout-btn">

                <i class="fas fa-sign-out-alt"></i>

                <span>Logout</span>

            </button>

        </form>

    </div>

</aside>