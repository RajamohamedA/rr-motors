<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RR Motors')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">

    @include('layouts.navbar')

    @include('layouts.sidebar')

    <div class="content-wrapper">

        <section class="content pt-3">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>

    </div>

    @include('layouts.footer')

</div>

</body>
</html>