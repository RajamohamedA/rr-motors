<li class="nav-item">

    <a class="nav-link d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse"
       href="#settingsMenu"
       role="button"
       aria-expanded="{{ request()->is('users*','roles*','settings*','backup*') ? 'true' : 'false' }}">

        <span class="menu-text-wrapper">
            <i class="fas fa-boxes me-2"></i>
            <span class="menu-text">Settings</span>
        </span>

        <i class="fas fa-angle-down"></i>

    </a>

    <div class="collapse {{ request()->is('users*','roles*','settings*','backup*') ? 'show' : '' }}"
         id="settingsMenu">

        <ul class="nav flex-column ms-4">

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-users me-2"></i>

                    Users

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-user-shield me-2"></i>

                    Roles & Permissions

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

            <li class="nav-item">

                <a href="{{ route('companies.index') }}"
                   class="nav-link">

                    <i class="fas fa-building me-2"></i>

                    Company Settings

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-database me-2"></i>

                    Database Backup

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-history me-2"></i>

                    Audit Log

                    <span class="badge bg-warning text-dark float-end">
                        Soon
                    </span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-info-circle me-2"></i>

                    About ERP

                </a>

            </li>

        </ul>

    </div>

</li>