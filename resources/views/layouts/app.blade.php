<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'RR Motors')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    @include('layouts.sidebar')

    <div class="main-content">

        @include('layouts.navbar')

        <div class="container-fluid mt-4">

            @yield('content')

        </div>

        @include('layouts.footer')

    </div>
    @stack('scripts')
</body>

</html>
