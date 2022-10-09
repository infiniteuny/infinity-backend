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
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/vendors/bootstrap.css') }}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/style.css') }}">
    <link id="color" rel="stylesheet" href="{{ asset('admin-panel/assets/css/color-1.css') }}" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-panel/assets/css/responsive.css') }}">

    <style>
        .font-info {
            color: #27835d !important;
        }

        .btn-info-gradien {
            background-image: linear-gradient(to right, #27835d, 0%, #27835d, 100%, #fff) !important;
        }

        .btn-info-gradien:hover,
        .btn-info-gradien:focus,
        .btn-info-gradien:active,
        .btn-info-gradien.active,
        .btn-info-gradien.hover {
            background-image: linear-gradient(to right, #27835d, 0%, #196b49, 100%, #fff) !important;
        }
    </style>
</head>

<body>
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- error-400 start-->
        <div class="error-wrapper">
            <div class="container"><img class="img-100"
                    src="{{ asset('admin-panel/assets/images/other-images/sad.png') }}" alt="">
                <div class="error-heading">
                    <h2 class="headline font-info">@yield('code')</h2>
                </div>
                <div class="col-md-8 offset-md-2">
                    <p class="sub-content">@yield('message')</p>
                </div>
                <div><a class="btn btn-info-gradien btn-lg" href="{{ route('landing') }}">KEMBALI KE JALAN YANG
                        BENAR</a></div>
            </div>
        </div>
        <!-- error-400 end-->
    </div>
    <!-- latest jquery-->
    <script src="{{ asset('admin-panel/assets/js/jquery-3.5.1.min.js') }}"></script>
    <!-- Bootstrap js-->
    <script src="{{ asset('admin-panel/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- feather icon js-->
    <script src="{{ asset('admin-panel/assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <!-- scrollbar js-->
    <!-- Sidebar jquery-->
    <script src="{{ asset('admin-panel/assets/js/config.js') }}"></script>
    <!-- Plugins JS start-->
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="{{ asset('admin-panel/assets/js/script.js') }}"></script>
    <!-- login js-->
    <!-- Plugin used-->
</body>

</html>
