<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title','RR Motors ERP')</title>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])

</head>

<body>

    <div class="wrapper">

        @include('layouts.sidebar')

        <div class="main-wrapper">

            @include('layouts.navbar')

            <main class="content">

                {{-- Flash Messages --}}
                <div class="container-fluid mt-3">

                    @if(session('success'))

                    <div class="alert alert-success alert-dismissible fade show">

                        <i class="fas fa-check-circle"></i>

                        {{ session('success') }}

                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>

                    </div>

                    @endif

                    @if(session('error'))

                    <div class="alert alert-danger alert-dismissible fade show">

                        <i class="fas fa-times-circle"></i>

                        {{ session('error') }}

                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>

                    </div>

                    @endif

                    @if(session('warning'))

                    <div class="alert alert-warning alert-dismissible fade show">

                        <i class="fas fa-exclamation-triangle"></i>

                        {{ session('warning') }}

                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>

                    </div>

                    @endif

                    @if(session('info'))

                    <div class="alert alert-info alert-dismissible fade show">

                        <i class="fas fa-info-circle"></i>

                        {{ session('info') }}

                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>

                    </div>

                    @endif
                    
                    @if ($errors->any())

                    <div class="alert alert-danger alert-dismissible fade show">

                        <i class="fas fa-exclamation-circle"></i>

                        <strong>Please fix the following errors:</strong>

                        <ul class="mb-0 mt-2">

                            @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>

                    </div>

                    @endif

                </div>

                @yield('content')

            </main>

            @include('layouts.footer')

        </div>

    </div>

    @stack('scripts')

</body>

</html>