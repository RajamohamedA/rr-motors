@canany([
'company.view',
'store.view',
'godown.view',
'supplier.view',
'customer.view',
'category.view',
'brand.view',
'unit.view',
'product.view'
])

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

            @can('company.view')
            <li class="nav-item">
                <a href="{{ route('companies.index') }}"
                    class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                    <i class="fas fa-building me-2"></i>
                    Company
                </a>
            </li>
            @endcan

            @can('store.view')
            <li class="nav-item">
                <a href="{{ route('stores.index') }}"
                    class="nav-link {{ request()->routeIs('stores.*') ? 'active' : '' }}">
                    <i class="fas fa-store me-2"></i>
                    Store
                </a>
            </li>
            @endcan

            @can('godown.view')
            <li class="nav-item">
                <a href="{{ route('godowns.index') }}"
                    class="nav-link {{ request()->routeIs('godowns.*') ? 'active' : '' }}">

                    <i class="nav-icon bi bi-building"></i>
                        Godowns
                </a>
            </li>
            @endcan

            @can('supplier.view')
            <li class="nav-item">
                <a href="{{ route('suppliers.index') }}"
                    class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <i class="fas fa-truck me-2"></i>
                    Supplier
                </a>
            </li>
            @endcan

            @can('customer.view')
            <li class="nav-item">
                <a href="{{ route('customers.index') }}"
                    class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i>
                    Customer
                </a>
            </li>
            @endcan

            @can('category.view')
            <li class="nav-item">
                <a href="{{ route('categories.index') }}"
                    class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags me-2"></i>
                    Category
                </a>
            </li>
            @endcan

            @can('brand.view')
            <li class="nav-item">
                <a href="{{ route('brands.index') }}"
                    class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                    <i class="fas fa-copyright me-2"></i>
                    Brand
                </a>
            </li>
            @endcan

            @can('unit.view')
            <li class="nav-item">
                <a href="{{ route('units.index') }}"
                    class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
                    <i class="fas fa-ruler me-2"></i>
                    Unit
                </a>
            </li>
            @endcan

            @can('product.view')
            <li class="nav-item">
                <a href="{{ route('products.index') }}"
                    class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fas fa-box me-2"></i>
                    Product
                </a>
            </li>
            @endcan

        </ul>

    </div>

</li>

@endcanany