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

<div class="container py-5">

    <div class="text-center mb-5">

        <h1 class="display-4 fw-bold">
            RR Motors ERP
        </h1>

        <p class="lead">
            Inventory & Billing Management System
        </p>

        <p class="text-muted">
            Manage Products, Purchases, Sales, Inventory, Suppliers and Reports from one place.
        </p>

    </div>

    <div class="row g-4">

        <div class="col-md-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h2>📦</h2>
                    <h5>Inventory</h5>
                    <p>Real-time stock management.</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h2>🛒</h2>
                    <h5>Purchase</h5>
                    <p>Supplier purchase management.</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h2>💰</h2>
                    <h5>Sales</h5>
                    <p>Fast GST billing and invoices.</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h2>📊</h2>
                    <h5>Reports</h5>
                    <p>Powerful business analytics.</p>
                </div>
            </div>
        </div>

    </div>

    <div class="text-center mt-5">

        @guest
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5">
                Login to ERP
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="btn btn-success btn-lg px-5">
                Go to Dashboard
            </a>
        @endguest

    </div>

</div>

<footer class="bg-dark text-white py-3 text-center mt-5">
    © {{ date('Y') }} RR Motors ERP | Version 1.0
</footer>

</body>
</html>