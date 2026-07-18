<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'RR Motors')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    @include('layouts.navbar')

    <div class="container-fluid">

        <div class="row">

            @include('layouts.sidebar')

            <main class="col-md-10 ms-sm-auto px-4 py-4">

                @yield('content')

            </main>

        </div>

    </div>

    @include('layouts.footer')

</body>

</html>