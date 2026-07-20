<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>RR Motors ERP</title>

        @vite(['resources/css/app.css','resources/js/app.js'])
    </head>

    <body class="bg-light">

        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">

            <div class="container">

                <a class="navbar-brand fw-bold" href="/">
                    RR Motors ERP
                </a>

                <div class="ms-auto">

                    @auth

                        <a href="{{ route('dashboard') }}" class="btn btn-light">
                            Dashboard
                        </a>

                    @else

                        <a href="{{ route('login') }}" class="btn btn-outline-light me-2">
                            Login
                        </a>

                        <a href="{{ route('register') }}" class="btn btn-warning">
                            Register
                        </a>

                    @endauth

                </div>

            </div>

        </nav>

        <!-- Hero Section -->
        <section class="py-5 bg-primary text-white">

            <div class="container">

                <div class="row align-items-center">

                    <div class="col-lg-6">

                        <h1 class="display-4 fw-bold">
                            RR Motors ERP
                        </h1>

                        <p class="lead mt-3">
                            Complete Inventory, Billing and Stock Management System for Modern Businesses.
                        </p>

                        <div class="mt-4">

                            <a href="{{ route('login') }}" class="btn btn-warning btn-lg me-3">
                                Login
                            </a>

                            <a href="#features" class="btn btn-outline-light btn-lg">
                                Learn More
                            </a>

                        </div>

                    </div>

                    <div class="col-lg-6 text-center">

                        <!-- <img src="{{ asset('images/erp-dashboard.png') }}"
                            class="img-fluid"
                            alt="ERP Dashboard"> -->

                    </div>

                </div>

            </div>

        </section>

        <!-- ERP Features -->
        <section id="features" class="py-5">

            <div class="container">

                <div class="text-center mb-5">
                    <h2 class="fw-bold">ERP Features</h2>
                    <p class="text-muted">
                        Everything you need to manage your business efficiently.
                    </p>
                </div>

                <div class="row g-4">

                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 h-100 text-center">
                            <div class="card-body p-4">
                                <div class="display-4 mb-3">📦</div>
                                <h5 class="fw-bold">Inventory</h5>
                                <p class="text-muted">
                                    Manage products, stock, barcode, and inventory movements.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 h-100 text-center">
                            <div class="card-body p-4">
                                <div class="display-4 mb-3">🛒</div>
                                <h5 class="fw-bold">Purchase</h5>
                                <p class="text-muted">
                                    Create purchase orders and manage supplier transactions.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 h-100 text-center">
                            <div class="card-body p-4">
                                <div class="display-4 mb-3">💰</div>
                                <h5 class="fw-bold">Sales</h5>
                                <p class="text-muted">
                                    Fast billing with GST support and customer management.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 h-100 text-center">
                            <div class="card-body p-4">
                                <div class="display-4 mb-3">📊</div>
                                <h5 class="fw-bold">Reports</h5>
                                <p class="text-muted">
                                    View sales, purchase, stock, and profit reports instantly.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </section>

        <!-- Why Choose RR Motors ERP -->
        <section class="py-5 bg-light">

            <div class="container">

                <div class="text-center mb-5">
                    <h2 class="fw-bold">Why Choose RR Motors ERP?</h2>
                    <p class="text-muted">
                        Built for modern businesses with speed, security, and scalability.
                    </p>
                </div>

                <div class="row g-4">

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="display-5 mb-3">🏪</div>
                                <h5 class="fw-bold">Multi Store</h5>
                                <p class="text-muted">
                                    Manage multiple stores and warehouses from one ERP system.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="display-5 mb-3">📱</div>
                                <h5 class="fw-bold">Mobile Ready</h5>
                                <p class="text-muted">
                                    Future Android and iPhone apps using the same Laravel API.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="display-5 mb-3">🧾</div>
                                <h5 class="fw-bold">GST Billing</h5>
                                <p class="text-muted">
                                    GST-ready invoices, purchase entries, and tax reports.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="display-5 mb-3">🔒</div>
                                <h5 class="fw-bold">Secure Authentication</h5>
                                <p class="text-muted">
                                    User login, password reset, profile management, and role-based access.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="display-5 mb-3">⚡</div>
                                <h5 class="fw-bold">Fast & Scalable</h5>
                                <p class="text-muted">
                                    Built with Laravel 13 and Bootstrap 5 for speed, reliability, and future growth.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </section>

        <!-- Call To Action -->
        <section class="py-5 bg-primary text-white">

            <div class="container text-center">

                <h2 class="fw-bold mb-3">
                    Ready to Streamline Your Business?
                </h2>

                <p class="lead mb-4">
                    Manage Inventory, Purchases, Sales, Billing and Reports from one secure ERP platform.
                </p>

                @guest

                    <a href="{{ route('login') }}" class="btn btn-warning btn-lg me-3">
                        Login
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">
                            Create Account
                        </a>
                    @endif

                @else

                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg">
                        Go to Dashboard
                    </a>

                @endguest

            </div>

        </section>

        <!-- Footer -->
        <footer class="bg-dark text-white pt-5 pb-3">

            <div class="container">

                <div class="row">

                    <div class="col-md-4">

                        <h4 class="fw-bold">
                            RR Motors ERP
                        </h4>

                        <p class="text-light">
                            Inventory & Billing Management System built with Laravel and Bootstrap.
                        </p>

                    </div>

                    <div class="col-md-4">

                        <h5>Modules</h5>

                        <ul class="list-unstyled">

                            <li>Inventory</li>
                            <li>Purchase</li>
                            <li>Sales</li>
                            <li>Reports</li>

                        </ul>

                    </div>

                    <div class="col-md-4">

                        <h5>Technology</h5>

                        <ul class="list-unstyled">

                            <li>Laravel 13</li>
                            <li>Bootstrap 5</li>
                            <li>MySQL</li>
                            <li>REST API Ready</li>

                        </ul>

                    </div>

                </div>

                <hr class="border-secondary">

                <div class="text-center">

                    <small>
                        © {{ date('Y') }} RR Motors ERP. All Rights Reserved.
                    </small>

                </div>

            </div>

        </footer>

    </body>
</html>