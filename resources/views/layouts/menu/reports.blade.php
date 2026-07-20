<li class="nav-item">

    <a class="nav-link d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse"
       href="#reportsMenu"
       role="button"
       aria-expanded="{{ request()->is('reports*') ? 'true' : 'false' }}">

        <span class="menu-text-wrapper">
            <i class="fas fa-chart-bar me-2"></i>
            <span class="menu-text">Reports</span>
        </span>

        <i class="fas fa-angle-down"></i>

    </a>

    <div class="collapse {{ request()->is('reports*') ? 'show' : '' }}"
         id="reportsMenu">

        <ul class="nav flex-column ms-4">

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-shopping-cart me-2"></i>

                    Purchase Report

                    <span class="badge bg-warning text-dark float-end">Soon</span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-file-invoice-dollar me-2"></i>

                    Sales Report

                    <span class="badge bg-warning text-dark float-end">Soon</span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-boxes me-2"></i>

                    Stock Report

                    <span class="badge bg-warning text-dark float-end">Soon</span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-users me-2"></i>

                    Customer Report

                    <span class="badge bg-warning text-dark float-end">Soon</span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-truck me-2"></i>

                    Supplier Report

                    <span class="badge bg-warning text-dark float-end">Soon</span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-chart-line me-2"></i>

                    Profit & Loss

                    <span class="badge bg-warning text-dark float-end">Soon</span>

                </a>

            </li>

            <li class="nav-item">

                <a href="#"
                   class="nav-link">

                    <i class="fas fa-file-invoice me-2"></i>

                    GST Reports

                    <span class="badge bg-warning text-dark float-end">Soon</span>

                </a>

            </li>

        </ul>

    </div>

</li>