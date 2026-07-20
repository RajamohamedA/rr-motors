<li class="nav-item">

    <a href="{{ route('dashboard') }}"
       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">

        <span class="menu-text-wrapper">
            <i class="fas fa-home me-2"></i>
            <span class="menu-text">Dashboard</span>
       </span>

    </a>

</li>