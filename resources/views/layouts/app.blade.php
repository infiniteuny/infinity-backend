<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="Official INFINITE UNY Website">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo_infinite_green.ico') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/logo_infinite_green.ico') }}">

    <!-- Google Font (font-family: 'Roboto', sans-serif;) -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,400i,500,700" rel="stylesheet">
    <!-- Google Font ('Poppins', sans-serif;) -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    @yield('css')

    {{-- @vite(['resources/css/custom.css']) --}}
</head>

<body>

    <!-- Preloader -->
    <div class="tm-preloader">
        <span class="tm-preloader-box"></span>
        <button class="tm-button tm-button-sm tm-button-white"><span>Close Loader</span></button>
    </div>
    <!--// Preloader -->

    <!-- Wrapper -->
    <div id="wrapper" class="wrapper">

        @include('layouts.navigation')

        @yield('content')

        @include('layouts.footer')

    </div>
    <!--// Wrapper -->

    <!-- Google Map -->
    {{-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgwgIuDRkO7HlxvpWN-vPePnGVWss5r5g"></script> --}}
    {{-- <script src="assets/js/google-map.js"></script> --}}

    <!-- Js Files -->
    <script src="{{ asset('assets/js/modernizr-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins.js') }}"></script>
    <script src="{{ asset('assets/js/chart.min.js') }}"></script>
    {{-- <script src="assets/js/chartactive.js"></script> --}}
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <!--// Js Files -->

    <!-- google analytic -->
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-S6EQGGKXPV"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-S6EQGGKXPV');
    </script>
</body>

</html>
