
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

            @yield('content')

        </main>

        @include('layouts.footer')

    </div>

</div>

@stack('scripts')

</body>

</html>
