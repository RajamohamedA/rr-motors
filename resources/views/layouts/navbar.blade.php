<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            RR Motors
        </a>

        <span class="text-white">
            {{ auth()->user()->name }}
        </span>
    </div>
</nav>