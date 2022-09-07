@extends('landing.components.layout')

@section('title', 'Team')

@section('css')
    <link rel="stylesheet" href="{{ asset('landing/fonts/icomoon/style.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/fonts/flaticon/font/flaticon.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="{{ asset('landing/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/jquery.fancybox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/css/style.css') }}">

    <link rel="stylesheet" href="{{ asset('landing/assets/css/jquery.mb.YTPlayer.min.css') }}" media="all" type="text/css">

    <style>
        .site-navbar .site-navigation .site-menu > li > a {
            color: #27835d;
        }
    </style>
@endsection

@section('content')
    <!-- TEAMS SECTION START  -->
    <div class="site-section" id="team-section">
      <div class="container">
        <div class="row justify-content-center text-center pt-5 mb-5" data-aos="fade-up">
          <div class="col-md-8  section-heading">
            <h2 class="heading mb-3">PENGURUS INFINITE {{ $data[0]->cabinet }}</h2>
          </div>
        </div>

        <!-- TEAMS ROW START  -->
        <div class="row">
            @foreach ($data as $item)
                <div class="col-lg-3 mb-4 mb-lg-0 col-md-6 text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="person">
                        <a href="http://www.instagram.com/{{ $item->instagram ? $item->instagram : 'infinite.uny' }}" target="blank" >
                            <img src="{{ $item->photo }}" alt="Image" class="img-fluid">
                        </a>
                        <h3 style="color:black;">{{ $item->name }}</h3>
                        <p class="position">{{ $item->division }}</p>
                        <p>{{ $item->study_program.' '.$item->year }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <br> <br>
        <!-- TEAMS ROW END  -->

      </div>

      <div class="schedule-wrap2 clearfix">
       <div class="d-md-flex align-items-center">
         <div class="hours mr-md-4 mb-4 mb-lg-0">
           <h1> Salam kenal ya! </h1>
         </div>
       </div>
     </div>
    </div>
    <!-- TEAMS SECTION END  -->
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