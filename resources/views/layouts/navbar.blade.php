<nav class="navbar navbar-expand-lg navbar-dark bg-primary">

    <div class="container-fluid">

        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            RR Motors ERP
        </a>

        <div class="ms-auto d-flex align-items-center">

            <span class="text-white me-3">
                {{ auth()->user()->name }}
            </span>

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button class="btn btn-light btn-sm">
                    Logout
                </button>

            </form>

        </div>

    </div>

</nav>