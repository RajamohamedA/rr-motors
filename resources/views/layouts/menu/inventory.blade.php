@canany([
'current-stock.view',
'stock-ledger.view',
'stock-adjustment.view'
])

<li class="nav-item">

    <a class="nav-link d-flex justify-content-between align-items-center"
        data-bs-toggle="collapse"
        href="#inventoryMenu"
        role="button"
        aria-expanded="{{ request()->is('stock*','stock-ledgers*','stock-adjustments*','stock-transfers*') ? 'true' : 'false' }}">

        <span class="menu-text-wrapper">
            <i class="fas fa-boxes me-2"></i>
            <span class="menu-text">Inventory</span>
        </span>

        <i class="fas fa-angle-down"></i>

    </a>

    <div class="collapse {{ request()->is('stock*','stock-ledgers*','stock-adjustments*','stock-transfers*') ? 'show' : '' }}"
        id="inventoryMenu">

        <ul class="nav flex-column ms-4">

            <!-- Current Stock -->

            <li class="nav-item">

                @can('current-stock.view')
                <a href="{{ route('inventory.current-stock') }}"
                    class="nav-link {{ request()->routeIs('inventory.current-stock') ? 'active' : '' }}">

                    <i class="fas fa-box me-2"></i>

                    Current Stock

                </a>
                @endcan

            </li>

            <!-- Stock Ledger -->

            <li class="nav-item">

                @can('stock-ledger.view')
                <a href="{{ route('stock-ledgers.index') }}"
                    class="nav-link {{ request()->routeIs('stock-ledgers.*') ? 'active' : '' }}">

                    <i class="fas fa-book me-2"></i>

                    Stock Ledger

                </a>
                @endcan

            </li>

            <!-- Stock Adjustment -->

            <li class="nav-item">

                @can('stock-adjustment.view')
                <a href="{{route('stock-adjustments.index')}}"
                    class=" nav-link {{ request()->routeIs('stock-adjustments.*') ? 'active' : '' }}">

                    <i class="fas fa-balance-scale me-2"></i>

                    Stock Adjustment

                </a>
                @endcan

            </li>

            <!-- Stock Transfer -->

            <li class="nav-item">

                @can('stock-transfer.view')
                <a href="#"
                    class="nav-link">

                    <i class="fas fa-exchange-alt me-2"></i>

                    Stock Transfer

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>
                @endcan

            </li>

        </ul>

    </div>

</li>

@endcanany