<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-header d-flex align-items-center justify-content-between p-3 border-bottom">

        <div class="d-flex align-items-center">

            <i class="fas fa-cogs fs-4 me-2 text-warning"></i>

            <span class="logo-text fw-bold">
                RR MOTORS ERP
            </span>

        </div>

    </div>

    <!-- User -->

    <div class="text-center user-info py-4 border-bottom">

        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}"
            width="65"
            class="rounded-circle shadow">

        <h6 class="mt-2 text-white mb-0">
            {{ Auth::user()->name }}
        </h6>

        <small class="text-success">
            ● Online
        </small>

    </div>

    <!-- Menu -->

    <ul class="nav flex-column mt-2">

        @include('layouts.menu.dashboard')

        @include('layouts.menu.masters')

        @include('layouts.menu.purchase')

        @include('layouts.menu.sales')

        @include('layouts.menu.inventory')

        @include('layouts.menu.accounts')

        @include('layouts.menu.reports')

        @include('layouts.menu.settings')

    </ul>

</aside>