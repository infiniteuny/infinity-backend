@extends('landing.components.layout')

@section('title', 'Home')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('landing/fonts/icomoon/style.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/jquery.fancybox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/jquery.mb.YTPlayer.min.css') }}" media="all" type="text/css">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/style.css') }}">

    <style>
        .intro-section::before{
            background-image: url({{ asset('storage/images').'/'.$data['latestYear']->year.'/banner_2.png' }});
        }
    </style>
    
@endsection

@section('content')

    <!-- HOME SECTION START -->
    <div class="intro-section" id="home-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12 mx-auto text-center" data-aos="fade-up">
                    <h1 class="mb-2">INFINITE UNY</h1>
                    <h3 class="mx-auto mb-3">Tempat para pecinta teknologi berkumpul 🤝</h3>
                    <p class="mx-auto desc mb-4">Divisi Teknologi Informasi • UKM Rekayasa Teknologi UNY</p>
                    <a href="#about-section" class="btn btn-outline-white py-2 px-4 text-center">Kenalan Yuk</a>
                </div>
            </div>
        </div>
    </div>
    <!-- HOME SECTION END -->

    <!-- ABOUT SECTION START -->
    <div class="site-section" id="about-section">
        <div class="container">
            <div class="row justify-content-center text-center mb-3" data-aos="fade-up">
                <div class="col-md-8  section-heading">
                    <h2 class="heading mb-3">Tentang Kami</h2>
                    <p>INFINITE merupakan Divisi Teknologi Informasi yang berada dibawah naungan UKM Rekayasa Teknologi UNY.
                    Sejak tahun 2014, INFINITE mewadahi mahasiswa dari berbagai jurusan yang memiliki minat dan bakat di bidang Teknologi Informasi.
                    <br><br>Terdapat 3 Role:
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 mb-4 col-md-6" data-aos="fade-up" data-aos-delay="">
                    <div class="ftco-feature-1">
                        <span class=""> <svg width="4em" height="4em" viewBox="0 0 16 16" class="bi bi-people-fill" fill="white" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                        </svg> </span>
                        <div class="ftco-feature-1-text">
                            <h2>Hustler</h2>
                            <p>Seseorang yang memiliki peran dalam memperkenalkan dan memasarkan produk kepada konsumen.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 col-md-6" data-aos="fade-up" data-aos-delay="50">
                    <div class="ftco-feature-1">
                        <span class=""> <svg width="4em" height="4em" viewBox="0 0 16 16" class="bi bi-bezier" fill="white" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M0 10.5A1.5 1.5 0 0 1 1.5 9h1A1.5 1.5 0 0 1 4 10.5v1A1.5 1.5 0 0 1 2.5 13h-1A1.5 1.5 0 0 1 0 11.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zm10.5.5A1.5 1.5 0 0 1 13.5 9h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zM6 4.5A1.5 1.5 0 0 1 7.5 3h1A1.5 1.5 0 0 1 10 4.5v1A1.5 1.5 0 0 1 8.5 7h-1A1.5 1.5 0 0 1 6 5.5v-1zM7.5 4a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1z"/>
                            <path d="M6 4.5H1.866a1 1 0 1 0 0 1h2.668A6.517 6.517 0 0 0 1.814 9H2.5c.123 0 .244.015.358.043a5.517 5.517 0 0 1 3.185-3.185A1.503 1.503 0 0 1 6 5.5v-1zm3.957 1.358A1.5 1.5 0 0 0 10 5.5v-1h4.134a1 1 0 1 1 0 1h-2.668a6.517 6.517 0 0 1 2.72 3.5H13.5c-.123 0-.243.015-.358.043a5.517 5.517 0 0 0-3.185-3.185z"/>
                        </svg> </span>
                        <div class="ftco-feature-1-text">
                            <h2>Hipster</h2>
                            <p>Seseorang yang memiliki peran dalam menyajikan dan memastikan keestetikan tampilan dari sebuah produk. </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="ftco-feature-1">
                        <span class=""><svg width="4em" height="4em" viewBox="0 0 16 16" class="bi bi-laptop" fill="white" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M13.5 3h-11a.5.5 0 0 0-.5.5V11h12V3.5a.5.5 0 0 0-.5-.5zm-11-1A1.5 1.5 0 0 0 1 3.5V12h14V3.5A1.5 1.5 0 0 0 13.5 2h-11z"/>
                            <path d="M0 12h16v.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 12.5V12z"/>
                        </svg> </span>
                        <div class="ftco-feature-1-text">
                            <h2>Hacker</h2>
                            <p>Seseorang yang memiliki peran dalam mengembangkan teknologi yang ada dalam produk (coding).</p>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
    </div>
    <!-- HOME SECTION END -->

    <!-- EVENT SECTION START -->
    <div class="bgimg" id="events-section" style="background-image: url('{{ asset('storage/images').'/'.$data['latestYear']->year.'/banner_1.png' }}');"  data-stellar-background-ratio="0.9">
        <div class="container">
            <div class="row align-items-center justify-content-center text-center">
                <div class="col-md-6 acara">
                    <h2 class="">EVENT</h2>
                    <p class="lead mx-auto desc mb-2">Ikuti beragam event yang diselenggarakan secara rutin</p>
                    <a href="{{ route('event') }}" class="btn btn-outline-white py-2 px-4 text-center">Lihat Event</a>
                </div>
            </div>
        </div>
    </div>
    <!-- EVENT SECTION END -->

    <!-- TIM SECTION START -->
    <div class="site-section" id="team-section">
        <div class="container">
            <div class="row justify-content-center text-center mb-5" data-aos="fade-up">
                <div class="col-md-8 section-heading">
                    <h2 class="heading mb-3">Pengurus INFINITE 2021</h2>
                </div>
            </div>

            <div class="row mb-4">
                @foreach ($data['organization'] as $item)
                    <div class="col-lg-3 mb-4 mb-lg-0 col-md-6 text-center" data-aos="fade-up" data-aos-delay="">
                        <div class="person">
                            <img src="{{ $item->avatar ? asset('storage/images').'/'.$data['latestYear']->year.'/'.$item->avatar : asset('storage/images/default.png') }}" alt="Image" class="img-fluid">
                            <h3 style="color:black;">{{ $item->name }}</h3>
                            <p class="position">{{ $item->position }}</p>
                            <p>{{ $item->studyProgram.' '.$item->studyYear }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="schedule-wrap2 clearfix">
            <div class="d-md-flex align-items-center">
                <div class="hours mr-md-4 mb-4 mb-lg-0">
                    <strong class="d-block">Tak kenal maka tak kenal!</strong>
                    Yuk Kenalan dengan pengurus yang lainnya!
                </div>
                <div class="cta ml-4">
                    <a href="{{ route('team') }}" class="d-flex d-md-flex align-items-center btn">
                        <span class="mx-auto"> <span>Pengurus Lain</span> <span class="arrow icon-keyboard_arrow_right"></span></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- TIM SECTION END -->
@endsection

@section('js')
    <script src="{{ asset('landing/assets/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery-migrate-3.0.1.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.stellar.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.easing.1.3.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.sticky.js') }}"></script>
    <script src="{{ asset('landing/assets/js/jquery.mb.YTPlayer.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/aos.js') }}"></script>
    <script src="{{ asset('landing/assets/js/main.js') }}"></script>
@endsection