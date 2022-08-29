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
            <h2 class="heading mb-3">PENGURUS INFINITE {{ $data['latestYear']->year }}</h2>
          </div>
        </div>

        <!-- TEAMS ROW START  -->
        <div class="row">
            @foreach ($data['organization'] as $item)
                <div class="col-lg-3 mb-4 mb-lg-0 col-md-6 text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="person">
                        <a href="http://www.instagram.com/" target="blank" >
                            <img src="{{ $item->avatar ? asset('storage/images').'/'.$data['latestYear']->year.'/'.$item->avatar : asset('storage/images/default.png') }}" alt="Image" class="img-fluid">
                        </a>
                        <h3 style="color:black;">{{ $item->name }}</h3>
                        <p class="position">{{ $item->position }}</p>
                        <p>{{ $item->studyProgram.' '.$item->studyYear }}</p>
                    </div>
                </div>
            @endforeach
          {{-- <div class="col-lg-3 mb-4 mb-lg-0 col-md-6 text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="person">
              <a href="http://www.instagram.com/maulidinalif/" target="blank"> <img src="assets/images/alif.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Alifah Maulidin Nur Ikhsan</h3>
              <p class="position">Ketua Divisi</p>
              <p>Teknologi Informasi 2019</p>
            </div>
          </div>
          <div class="col-lg-3 mb-4 mb-lg-0 col-md-6 text-center" data-aos="fade-up" data-aos-delay="200">
            <div class="person">
              <a href="http://www.instagram.com/hajidahsaf_" target="blank"> <img src="assets/images/bila.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Hajidah Salsabila</h3>
              <p class="position">Wakil Kepala Divisi</p>
              <p>Pendidikan Kimia 2019</p>
            </div>
          </div>
          <div class="col-lg-3 mb-4 mb-lg-0 col-md-6 text-center" data-aos="fade-up" data-aos-delay="200">
            <div class="person">
              <a href="http://www.instagram.com/yuyul_31/" target="blank"> <img src="assets/images/yuli.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Yuli Setyowati</h3>
              <p class="position">Admin</p>
              <p>Matematika 2019</p>
            </div>
          </div>    --}}
        </div>
        <br> <br>
        <!-- TEAMS ROW END  -->

        {{-- <!-- TEAMS ROW START  -->
         <div class="row">
           <div class="col-lg-3 mb-4 mb-lg-0 col-md-6 text-center" data-aos="fade-up" data-aos-delay="300">
            <div class="person">
              <a href="http://www.instagram.com/larasatilatifah/" target="blank"> <img src="assets/images/laras.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Larasati Nur Latifah</h3>
              <p class="position">Admin</p>
              <p>Teknologi Informasi 2019</p>
            </div>
          </div>
          <div class="col-lg-3 mb-4 mb-lg-0 col-md-6 text-center" data-aos="fade-up" data-aos-delay="">
            <div class="person">
              <a href="http://www.instagram.com/salastanm" target="blank">  <img src="assets/images/salasta.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Salasta Nastika Maulidya</h3>
              <p class="position">Staff AdminKeu</p>
              <p>Pend. Teknik Informatika 2019</p>
            </div>
          </div>
          <div class="col-lg-3 mb-4 mb-lg-0 col-md-6 text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="person">
              <a href="http://www.instagram.com/atul.id/" target="blank"> <img src="assets/images/anisatul.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Anisatul Afita</h3>
              <p class="position">Staff AdminKeu</p>
              <p>Pendidikan IPS 2020</p>
            </div>
          </div>
          <div class="col-lg-3 mb-4 mb-lg-0 col-md-6 text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="person">
              <a href="http://www.instagram.com/firsty_dian/" target="blank"> <img src="assets/images/firsty.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Firsty Dian Pratiwi</h3>
              <p class="position">Staff AdminKeu</p>
              <p>Teknologi Informasi 2020</p>
            </div>
          </div>    
        </div>
        <br> <br>
        <!-- TEAMS ROW END  -->

        <!-- TEAMS ROW START  -->
        <div class="row">
          <div class="col-lg-4 mb-3 mb-lg-2 col-md-4 text-center" data-aos="fade-up" data-aos-delay="">
            <div class="person">
              <a href="http://www.instagram.com/fahrul.site/" target="blank"> <img src="assets/images/fahrul.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Fahrul Ahmad Fauzi</h3>
              <p class="position"> RESEARCH AND DEVELOPMENT</p>
              <p>Pend. Teknik Informatika 2019</p>
            </div>
          </div>
          <div class="col-lg-4 mb-3 mb-lg-2 col-md-4 text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="person">
              <a href="http://www.instagram.com/zulfikarisvhny/" target="blank"> <img src="assets/images/zulfikar.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Zulfikar Isvahany</h3>
              <p class="position">STAFF 
                RESEARCH AND DEVELOPMENT</p>
              <p>Matematika 2019</p>
            </div>
          </div>
          <div class="col-lg-4 mb-3 mb-lg-2 col-md-4 text-center" data-aos="fade-up" data-aos-delay="200">
            <div class="person">
              <a href="http://www.instagram.com/dany_christian/" target="blank"> <img src="assets/images/dany.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Dany Christian</h3>
              <p class="position">STAFF 
                RESEARCH AND DEVELOPMENT</p>
              <p>Teknologi Informasi 2020</p>
            </div>
          </div>
        </div>
        <br> <br>
        <!-- TEAMS ROW END  -->

        <!-- TEAMS ROW START  -->
        <div class="row">
          <div class="col-lg-4 mb-3 mb-lg-2 col-md-4 text-center" data-aos="fade-up" data-aos-delay="">
            <div class="person">
              <a href="http://www.instagram.com/mrif.45/" target="blank"> <img src="assets/images/adam.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Rifqi Muhammad Adam</h3>
              <p class="position"> MEDIA AND INFORMATION</p>
              <p>Pend. Teknik Informatika 2019</p>
            </div>
          </div>
          <div class="col-lg-4 mb-3 mb-lg-2 col-md-4 text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="person">
              <a href="http://www.instagram.com/salsabilarizkip_/" target="blank"> <img src="assets/images/sabil.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Salsabila Rizki Prasasti</h3>
              <p class="position">STAFF 
                MEDIA AND INFORMATION</p>
              <p>Pendidikan Fisika 2020</p>
            </div>
          </div>
          <div class="col-lg-4 mb-3 mb-lg-2 col-md-4 text-center" data-aos="fade-up" data-aos-delay="200">
            <div class="person">
              <a href="http://www.instagram.com/azizmu.ii/" target="blank"> <img src="assets/images/aziz.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Aziz Muzaki</h3>
              <p class="position">STAFF 
                MEDIA AND INFORMATION</p>
              <p>Pendidikan Fisika 2019</p>
            </div>
          </div>
        </div>
        <br> <br>
        <!-- TEAMS ROW END  -->

        <!-- TEAMS ROW START  -->
        <div class="row">
          <div class="col-lg-4 mb-3 mb-lg-2 col-md-4 text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="person">
              <a href="http://www.instagram.com/menarawas/" target="blank"> <img src="assets/images/ltg.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Menara Lintang Was</h3>
              <p class="position">
                Entrepreneurship</p>
              <p>Pend. Teknik Informatika 2019</p>
            </div>
          </div>
          <div class="col-lg-4 mb-3 mb-lg-2 col-md-4 text-center" data-aos="fade-up" data-aos-delay="">
            <div class="person">
              <a href="http://www.instagram.com/ikhwanizh_/" target="blank"> <img src="assets/images/ikhwan.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Ikhwan Inzhagi Siswanto</h3>
              <p class="position"> STAFF
                Entrepreneurship</p>
              <p>Teknologi Informasi 2020</p>
            </div>
          </div>
          
          <div class="col-lg-4 mb-3 mb-lg-2 col-md-4 text-center" data-aos="fade-up" data-aos-delay="200">
            <div class="person">
              <a href="http://www.instagram.com/onion.zzzzz/" target="blank">  <img src="assets/images/aulia.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Aulia Azzahra</h3>
              <p class="position">STAFF 
                Entrepreneurship</p>
              <p>Teknologi Informasi 2020</p>
            </div>
          </div>
        </div>
        <br> <br>
        <!-- TEAMS ROW END  -->
        
        <!-- TEAMS ROW START  -->
        <div class="row">
          <div class="col-lg-6 mb-3 mb-lg-4 mb-md-2 col-md-6 text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="person">
              <a href="http://www.instagram.com/ahistadrdiansyah_/" target="blank"> <img src="assets/images/dhista.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Dhista Dwi Nur Ardhiansyah</h3>
              <p class="position">
                Competition</p>
              <p>Pend. Teknik Informatika 2019</p>
            </div>
          </div>
          <div class="col-lg-6 mb-3 mb-lg-4 mb-md-2 col-md-6 text-center" data-aos="fade-up" data-aos-delay="">
            <div class="person">
              <a href="http://www.instagram.com/mujib.luth/" target="blank"> <img src="assets/images/mujib.png" alt="Image" class="img-fluid"> </a>
              <h3 style="color:black;">Abdul Mujiburrohman Luthfi</h3>
              <p class="position"> STAFF
                Competition</p>
              <p>D4 Teknik Elektronika 2019</p>
            </div>
          </div>
        </div>
        <!-- TEAMS ROW END  --> --}}
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