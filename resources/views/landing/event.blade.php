@extends('landing.components.layout')

@section('title', 'Event')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('landing/fonts/icomoon/style.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/jquery.fancybox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/jquery.mb.YTPlayer.min.css') }}" media="all" type="text/css">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/fonts/flaticon/font/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/bootstrap-datepicker.css') }}">
@endsection

@section('content')
    <!-- EVENT SECTION START  -->
    <div class="container" id="events-section">
        <h3 class="event-title d-flex justify-content-center">Event Infinite</h3>
      
        <!-- EVENT ROW START  -->
        <div class="row d-flex justify-content-center">
            <div class="d-flex col-lg-4 mb-4 col-md-6">
                <div class="card">
                    <div class="card-banner">
                        <p class="category-tag px-4 py-2">Webinar</p>
                        <img class="banner-img" src="{{ asset('landing/assets/images/webinar-girlskode.jpg') }}" alt="webinar girls kode" />
                    </div>
                    <div class="card-body">
                        <h4 class="blog-title">KODECAMP 101 - "Flutter, Your Next Mobile Framework</h4>
                        <p class="blog-description">
                            Kamu seorang developer? Mau mimin kenalin sama salah satu teknologi yang bakal mempermudah kamu dalam mengembangkan aplikasi mobile? We hear you!🥳 Meet Flutter. Sederhananya, Flutter adalah teknologi open source dari Google
                            yang bisa digunakan untuk mempermudah kamu dalam pembuatan aplikasi Android dan iOS. Wah makin penasaran nih!
                        </p>
                        <p class="event-schedule">Sabtu, 21 Agustus 2021 pukul 10.00 WIB</p>
                        <a href="#" class="btn btn-secondary btn-lg disabled d-flex justify-content-center text-center" tabindex="-1" role="button" aria-disabled="true">Acara Sudah Selesai</a>
                    </div>
                </div>
            </div>

            <div class="d-flex col-lg-4 mb-4 col-md-6">
                <div class="card">
                    <div class="card-banner">
                        <p class="category-tag px-4 py-2">Webinar</p>
                        <img class="banner-img" src="{{ asset('landing/assets/images/webinar-laravel.jpg') }}" alt="webinar girls kode" />
                    </div>
                    <div class="card-body">
                        <h4 class="blog-title">Get Your Hands Dirty on Clean Architecture | Laravel</h4>
                        <p class="blog-description">
                            Developer Student Clubs Yogyakarta State University is back!! Have you ever wondering how to use laravel? After searching on the internet, you still confused about the code ?⠀Get Your Hands Dirty on Clean Architecture Laravel A Hands-on Guide to Creating Clean Web Application in Laravel. A Webinar with Dwi Setiawan DSC UNY Lead 2020
                        </p>
                        <p class="event-schedule">Minggu, 4 April 2021 19.00 WIB</p>
                        <a href="#" class="btn btn-secondary btn-lg disabled d-flex justify-content-center text-center" tabindex="-1" role="button" aria-disabled="true">Acara Sudah Selesai</a>
                    </div>
                </div>
            </div>
        
            <div class="d-flex col-lg-4 mb-4 col-md-6">
                <div class="card">
                    <div class="card-banner">
                        <p class="category-tag px-4 py-2">Webinar</p>
                        <img class="banner-img" src="{{ asset('landing/assets/images/Webinar-Cybersecurity.png') }}" alt="webinar girls kode" />
                    </div>
                    <div class="card-body">
                        <h4 class="blog-title">Improve Cyber Security Skills with CTF</h4>
                        <p class="blog-description">
                            Keamanan Siber merupakan hal yang penting di abad 21 ini, orang berlomba-lomba untuk menciptakan keamanan terbaik agar dunia cyber tetap aman, kamu tertarik dalam keamanan cyber? tertarik dengan kompetisi Capture The Flag? yuk ikuti webinar ini bersama Pembicara kita Defri Indra Mahardika yang merupakan Backend Developer di Gayatri
                        </p>
                        <p class="event-schedule">Minggu, 14 Maret 2021 13.00 WIB</p>
                        <a href="https://www.youtube.com/watch?v=NAi3YfHyNdc" target="blank" class="btn btn-join d-flex justify-content-center text-center">Tonton di Youtube</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- EVENT ROW END  -->

        <!-- EVENT ROW START  -->
        <div class="row d-flex justify-content-center">    
            <div class="d-flex col-lg-4 mb-4 col-md-6">
                <div class="card">
                    <div class="card-banner">
                        <p class="category-tag px-4 py-2">Webinar</p>
                        <img class="banner-img" src="{{ asset('landing/assets/images/webinar-competiton 101.png') }}" alt="webinar girls kode" />
                    </div>
                    <div class="card-body">
                        <h4 class="blog-title">Infinite Kickstart : Competition 101 (Dialog Kepenulisan Proposal)</h4>
                        <p class="blog-description">
                            Kompetisi rutin diadakan setiap tahun dan suatu kompetisi membutuhkan proposal sebagai syarat mengikuti kompetisi tersebut yuk ikuti webinar ini bersama Pembicara kita bapak Muhammad Izzudin Mahali, S.Pd.T, M.Cs yang akan menjelaskan tentatng bagaimana penulisan proposal kompetisi yang tepat dan efektif
                        </p>
                        <p class="event-schedule">Minggu, 7 Februari 2021 13.00 WIB</p>
                        <a href="https://www.youtube.com/watch?v=9Nv74Q9UjMg" target="blank" class="btn btn-join d-flex justify-content-center text-center">Tonton di Youtube</a>
                    </div>
                </div>
            </div>    

            <div class="d-flex col-lg-4 mb-4 col-md-6">
                <div class="card">
                    <div class="card-banner">
                        <p class="category-tag px-4 py-2">Webinar</p>
                        <img class="banner-img" src="{{ asset('landing/assets/images/webinar-dsc.png') }}" alt="webinar girls kode" />
                    </div>
                    <div class="card-body">
                        <h4 class="blog-title">Infinite UNY X DSC UNY | Cloud Study Jams 2021</h4>
                        <p class="blog-description">
                            Want to get started on the Google Cloud, but don't know where to begin? Join us for our Cloud Study Jam! Get official Google training, a $70+ value, free of charge. Together we'll work through 4 Google Cloud labs. You will get hands-on experience with the cloud console, Kubernetes and Machine Learning. Then after the meetup, you will have free access to 3 more labs you can finish at home.
                        </p>
                        <p class="event-schedule">Sabtu, 30 Januari 2021 13.00 WIB</p>
                        <a href="https://www.youtube.com/watch?v=UDLmUvyE0fM" target="blank" class="btn btn-join d-flex justify-content-center text-center">Tonton di Youtube</a>
                    </div>
                </div>
            </div>

            <div class="d-flex col-lg-4 mb-4 col-md-6">
                <div class="card">
                    <div class="card-banner">
                        <p class="category-tag px-4 py-2">Webinar</p>
                        <img class="banner-img" src="{{ asset('landing/assets/images/webinar-android.jpg') }}" alt="webinar girls kode" />
                    </div>
                    <div class="card-body">
                        <h4 class="blog-title">Android Study Jams with Ar Firman Syahputra</h4>
                        <p class="blog-description">
                            Developer Student Clubs Yogyakarta State University is back with our 3rd event !! Have you ever wondering how to make your own android app ? After searching on the internet, you still confused about the code ?⠀Don’t worry, let’s learn with us! using curriculum provided by Google. With⠀Ar Firman Syahputra | Software Development at Bukalapak.com
                        </p>
                        <p class="event-schedule">Sabtu, 19 Desember 2020 13.00 WIB</p>
                        <a href="#" class="btn btn-secondary btn-lg disabled d-flex justify-content-center text-center" tabindex="-1" role="button" aria-disabled="true">Acara Sudah Selesai</a>
                    </div>
                </div>
            </div>
            <!-- EVENT ROW END  -->
        </div>
        <!-- CONTAINER END  -->
    </div>
    <!-- EVENT SECTION END  -->
@endsection

@section('js')
    <script src="{{ asset('landing/assets/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery-migrate-3.0.1.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.stellar.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.easing.1.3.js') }}"></script>
    <script src="{{ asset('landing/assets/js/aos.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.sticky.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.mb.YTPlayer.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/main.js') }}"></script>
@endsection