<li class="nav-item">

    <a class="nav-link d-flex justify-content-between align-items-center"
        data-bs-toggle="collapse"
        href="#purchaseMenu"
        role="button"
        aria-expanded="{{ request()->is('purchases*','purchase-returns*') ? 'true' : 'false' }}">

        <span class="menu-text-wrapper">
            <i class="fas fa-shopping-cart me-2"></i>
            <span class="menu-text">Purchase</span>
        </span>

        <i class="fas fa-angle-down"></i>

    </a>

    <div class="collapse {{ request()->is('purchases*','purchase-returns*') ? 'show' : '' }}"
        id="purchaseMenu">

        <ul class="nav flex-column ms-4">

            <li class="nav-item">

                <a href="{{ route('purchases.index') }}"
                    class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">

                    <i class="fas fa-file-invoice me-2"></i>

                    Purchase Entry

                </a>

            </li>

            <li class="nav-item">

                <a href="{{ route('purchase-returns.index') }}"
                    class="nav-link {{ request()->routeIs('purchase-returns.*') ? 'active' : '' }}">

                    <i class="fas fa-undo-alt me-2"></i>

                    Purchase Return

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                    class="nav-link">

                    <i class="fas fa-chart-line me-2"></i>

                    Purchase Report

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

        </ul>

    </div>

</li>