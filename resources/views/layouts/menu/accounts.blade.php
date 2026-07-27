<li class="nav-item">

    <a class="nav-link d-flex justify-content-between align-items-center"
        data-bs-toggle="collapse"
        href="#accountsMenu"
        role="button"
        aria-expanded="{{ request()->is('customer-payments*','supplier-payments*','customer-ledgers*','supplier-ledgers*','cash-book*','bank-book*') ? 'true' : 'false' }}">

        <span class="menu-text-wrapper">
            <i class="fas fa-wallet me-2"></i>
            <span class="menu-text">Accounts</span>
        </span>

        <i class="fas fa-angle-down"></i>

    </a>

    <div class="collapse {{ request()->is('customer-payments*','supplier-payments*','customer-ledgers*','supplier-ledgers*','cash-book*','bank-book*') ? 'show' : '' }}"
        id="accountsMenu">

        <ul class="nav flex-column ms-4">

            <li class="nav-item">

                <a href="{{ route('customer-payments.index') }}" class="nav-link {{ request()->routeIs('customer-payments.index') ? 'active' : '' }}">

                    <i class="fas fa-money-check-alt me-2"></i>

                    Customer Payment

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                    class="nav-link">

                    <i class="fas fa-hand-holding-usd me-2"></i>

                    Supplier Payment

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

            <li class="nav-item">

                <a href="{{ route('customer-ledger') }}" class="nav-link {{ request()->routeIs('customer-ledger') ? 'active' : '' }}">


                    <i class="fas fa-book-reader me-2"></i>

                    Customer Ledger

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                    class="nav-link">

                    <i class="fas fa-book me-2"></i>

                    Supplier Ledger

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                    class="nav-link">

                    <i class="fas fa-cash-register me-2"></i>

                    Cash Book

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                    class="nav-link">

                    <i class="fas fa-university me-2"></i>

                    Bank Book

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

        </ul>

    </div>

</li>