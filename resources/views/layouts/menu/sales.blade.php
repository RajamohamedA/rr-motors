<li class="nav-item">

    <a class="nav-link d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse"
       href="#salesMenu"
       role="button"
       aria-expanded="{{ request()->is('sales*','sales-returns*','quotations*') ? 'true' : 'false' }}">

        <span class="menu-text-wrapper">
            <i class="fas fa-cash-register me-2"></i>
            <span class="menu-text">Sales</span>
        </span>

        <i class="fas fa-angle-down"></i>

    </a>

    <div class="collapse {{  request()->is('sales*','sales-returns*','quotations*') ? 'show' : '' }}"
         id="salesMenu">

        <ul class="nav flex-column ms-4">

            <li class="nav-item">

            <a href="{{ route('sales.index') }}"
           class="nav-link {{ request()->routeIs('sales.index') ? 'active' : '' }}">

                    <i class="fas fa-file-invoice-dollar me-2"></i>

                    Sales Invoice

                </a>

            </li>

            <li class="nav-item">

                <a href="{{ route('sales-returns.index') }}"
                   class="nav-link">

                    <i class="fas fa-undo me-2"></i>

                    Sales Return
                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-file-alt me-2"></i>

                    Quotation

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-chart-line me-2"></i>

                    Sales Report

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

        </ul>

    </div>

</li>