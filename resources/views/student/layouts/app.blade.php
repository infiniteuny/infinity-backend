<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Official INFINITE UNY Website">
    <meta name="keywords" content="INFINITE, UNY, INFINITE UNY, Universitas Negeri Yogyakarta">
    <meta name="author" content="pixelstrap | INFINITE UNY">
    <link rel="icon" href="{{ asset('landing/assets/images/logo_infinite_green.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('landing/assets/images/logo_infinite_green.ico') }}" type="image/x-icon">
    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/font-awesome.css') }}">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/icofont.css') }}">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/themify.css') }}">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/flag-icon.css') }}">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/feather-icon.css') }}">
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/animate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/chartist.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/date-picker.css') }}">
    <!-- Plugins css Ends-->
    @yield('plugins')
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/bootstrap.css') }}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/style.css') }}">
    <link id="color" rel="stylesheet" href="{{ asset('admin-panel/assets/css/color-1.css') }}" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/responsive.css') }}">
    @yield('css')
</head>

<body onload="startTime()">
    <!-- loader starts-->
    <div class="loader-wrapper">
        <div class="loader-index"><span></span></div>
        <svg>
            <defs></defs>
            <filter id="goo">
                <fegaussianblur in="SourceGraphic" stddeviation="11" result="blur"></fegaussianblur>
                <fecolormatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo">
                </fecolormatrix>
            </filter>
        </svg>
    </div>
    <!-- loader ends-->
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Header Start-->
        @include('student.layouts.navbar')
        <!-- Page Header Ends-->
        <!-- Page Body Start-->

        <div class="page-body-wrapper">
            <!-- Page Sidebar Start-->
            <div class="sidebar-wrapper">
                <div>
                    <div class="logo-wrapper"><a href="{{ route('student.dashboard') }}"><img
                                class="img-fluid for-light"
                                src="{{ asset('landing/assets/images/infinite_dark.svg') }}" alt=""><img
                                class="img-fluid for-dark" src="{{ asset('landing/assets/images/infinite_dark.svg') }}"
                                alt=""></a>
                        <div class="back-btn"><i class="fa fa-angle-left"></i></div>
                        <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid">
                            </i></div>
                    </div>
                    <div class="logo-icon-wrapper"><a href="index.html"><img class="img-fluid"
                                src="{{ asset('landing/assets/images/logo_infinite_dark.svg') }}" alt=""></a>
                    </div>
                    @include('student.layouts.navigation')
                </div>
            </div>
            <!-- Page Sidebar Ends-->
            <div class="page-body">
                @yield('breadcrumb')
                <!-- Container-fluid starts-->
                @yield('content')
                <!-- Container-fluid Ends-->
            </div>
            <!-- footer start-->
            @include('student.layouts.footer')
        </div>
    </div>
    <!-- latest jquery-->
    <script src="{{ asset('admin-panel/assets/js/jquery-3.5.1.min.js') }}"></script>
    <!-- Bootstrap js-->
    <script src="{{ asset('admin-panel/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- feather icon js-->
    <script src="{{ asset('admin-panel/assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <!-- scrollbar js-->
    <script src="{{ asset('admin-panel/assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/scrollbar/custom.js') }}"></script>
    <!-- Sidebar jquery-->
    <script src="{{ asset('admin-panel/assets/js/config.js') }}"></script>
    <!-- Plugins JS start-->
    <script src="{{ asset('admin-panel/assets/js/sidebar-menu.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/chart/chartist/chartist.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/chart/chartist/chartist-plugin-tooltip.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/chart/knob/knob.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/chart/knob/knob-chart.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/chart/apex-chart/apex-chart.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/chart/apex-chart/stock-prices.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/chart/chartjs/chart.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/notify/bootstrap-notify.min.js') }}"></script>
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="{{ asset('admin-panel/assets/js/script.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/theme-customizer/customizer.js') }}"></script>
    <!-- login js-->
    <!-- Plugin used-->
    <!-- Custom js -->
    @yield('js')
</body>

</html>
