<li class="nav-item">



    <a class="nav-link d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse"
       href="#mastersMenu"
       role="button"
       aria-expanded="{{ request()->is('companies*','stores*','suppliers*','customers*','categories*','brands*','units*','products*') ? 'true' : 'false' }}">

       
       <span class="menu-text-wrapper">
            <i class="fas fa-database me-2"></i>
            <span class="menu-text">Masters</span>
       </span>
       <i class="fas fa-angle-down"></i>

    </a>

    <div class="collapse {{ request()->is('companies*','stores*','suppliers*','customers*','categories*','brands*','units*','products*') ? 'show' : '' }}"
         id="mastersMenu">

        <ul class="nav flex-column ms-4">

            <li class="nav-item">
                <a href="{{ route('companies.index') }}"
                   class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                    <i class="fas fa-building me-2"></i>
                    Company
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('stores.index') }}"
                   class="nav-link {{ request()->routeIs('stores.*') ? 'active' : '' }}">
                    <i class="fas fa-store me-2"></i>
                    Store
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('suppliers.index') }}"
                   class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <i class="fas fa-truck me-2"></i>
                    Supplier
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('customers.index') }}"
                   class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i>
                    Customer
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('categories.index') }}"
                   class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags me-2"></i>
                    Category
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('brands.index') }}"
                   class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                    <i class="fas fa-copyright me-2"></i>
                    Brand
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('units.index') }}"
                   class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
                    <i class="fas fa-ruler me-2"></i>
                    Unit
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('products.index') }}"
                   class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fas fa-box me-2"></i>
                    Product
                </a>
            </li>

        </ul>

    </div>

</li>