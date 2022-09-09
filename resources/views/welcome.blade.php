@extends('layouts.app')


@section('content')
    <!-- Heroslider Area -->
    <div id="tm-area-heroslider" class="tm-heroslider">
        <div class="tm-heroslider-inner">
            <img src="assets/images/heroslider-overlay-shape.png" alt="heroslider ovelay" class="tm-heroslider-ovelayshape">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-8 col-lg-7 order-2 order-lg-1">
                        <div class="tm-heroslider-content">
                            <h1>INFINITE UNY<br> Tempat para pecinta teknologi berkumpul 🤝</h1>
                            <p>Divisi Teknologi Informasi • UKM Rekayasa Teknologi UNY</p>
                            <div class="tm-buttongroup">
                                <a href="#tm-area-about"
                                    class="tm-button tm-button-lg tm-button-white tm-button-transparent"><i
                                        class="zmdi zmdi-info-outline"></i><span>Kenalan Yuk</span></a>
                                {{-- <a href="#" class="tm-button tm-button-lg tm-button-white tm-button-transparent"><i
                                        class="zmdi zmdi-apple"></i><span>IOS App Store</span></a> --}}
                            </div>
                            <a href="#tm-area-features" class="tm-heroslider-scrolldown">
                                <i class="zmdi zmdi-square-down"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-5 order-1 order-lg-2">
                        <div class="tm-heroslider-mobileshow">
                            <img src="assets/images/inf.png" alt="appmyil logo 3d">
                            <div class="tm-heroslider-mobileshowanim">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--// Heroslider Area -->

    <!-- Page Content -->
    <main class="page-content">

        <!-- Features Area -->
        {{-- <div id="tm-area-features" class="tm-features-area tm-section tm-padding-section bg-white">
        <div class="container">
            <div class="row justify-content-center mt-30-reverse">

                <!-- Single Features -->
                <div class="col-lg-4 col-md-6 col-12 mt-30">
                    <div class="tm-feature">
                        <span class="tm-feature-icon">
                            <i class="flaticon-keyword"></i>
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="70px" height="72px">
                                <path fill-rule="evenodd" d="M52.556,54.307 C64.348,37.302 73.747,12.982 68.512,4.877 C60.795,-7.064 13.848,4.659 2.850,20.520 C2.466,21.074 2.124,21.636 1.831,22.199 C-6.854,38.889 17.104,75.968 33.774,71.638 C39.513,70.148 46.364,63.237 52.556,54.307 " />
                            </svg>
                        </span>
                        <div class="tm-feature-content">
                            <h4>Quick Access</h4>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing sed do eiusmod tempor inc
                                ididunt ut labore</p>
                        </div>
                    </div>
                </div>
                <!--// Single Features -->

                <!-- Single Features -->
                <div class="col-lg-4 col-md-6 col-12 mt-30">
                    <div class="tm-feature">
                        <span class="tm-feature-icon">
                            <i class="flaticon-document"></i>
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="70px" height="72px">
                                <path fill-rule="evenodd" d="M52.556,54.307 C64.348,37.302 73.747,12.982 68.512,4.877 C60.795,-7.064 13.848,4.659 2.850,20.520 C2.466,21.074 2.124,21.636 1.831,22.199 C-6.854,38.889 17.104,75.968 33.774,71.638 C39.513,70.148 46.364,63.237 52.556,54.307 " />
                            </svg>
                        </span>
                        <div class="tm-feature-content">
                            <h4>Secured Data</h4>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing sed do eiusmod tempor inc
                                ididunt ut labore</p>
                        </div>
                    </div>
                </div>
                <!--// Single Features -->

                <!-- Single Features -->
                <div class="col-lg-4 col-md-6 col-12 mt-30">
                    <div class="tm-feature">
                        <span class="tm-feature-icon">
                            <i class="flaticon-cover"></i>
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="70px" height="72px">
                                <path fill-rule="evenodd" d="M52.556,54.307 C64.348,37.302 73.747,12.982 68.512,4.877 C60.795,-7.064 13.848,4.659 2.850,20.520 C2.466,21.074 2.124,21.636 1.831,22.199 C-6.854,38.889 17.104,75.968 33.774,71.638 C39.513,70.148 46.364,63.237 52.556,54.307 " />
                            </svg>
                        </span>
                        <div class="tm-feature-content">
                            <h4>Layout Flexible</h4>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing sed do eiusmod tempor inc
                                ididunt ut labore</p>
                        </div>
                    </div>
                </div>
                <!--// Single Features -->

            </div>
        </div>
    </div> --}}
        <!--// Features Area -->

        <!-- About Us Area -->
        <div id="tm-area-about" class="tm-about-area tm-section tm-padding-section bg-grey">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <h2>Tentang Kami</h2>
                            <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="tm-about-image">
                            <img class="tm-about-mobilethumb" src="assets/images/about-image.svg" alt="about mobile">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="tm-about-content">
                            <h4>INFINITE UNY?</h4>
                            <h6>A mobile app or mobile application is a computer program designed to run on a
                                mobile device.</h6>
                            <p>INFINITE merupakan Divisi Teknologi Informasi yang berada dibawah naungan UKM Rekayasa
                                Teknologi UNY. Sejak tahun 2014, INFINITE mewadahi mahasiswa dari berbagai jurusan yang
                                memiliki minat dan bakat di bidang Teknologi Informasi.</p>
                            <ul class="stylish-list">
                                <li><i class="zmdi zmdi-check"></i> Apps are generally downloaded from application.</li>
                                <li><i class="zmdi zmdi-check"></i> Most mobile devices are sold with several apps
                                    bundled.</li>
                                <li><i class="zmdi zmdi-check"></i> Mobile apps were originally offered for general
                                    productivity.</li>
                                <li><i class="zmdi zmdi-check"></i> App presents the service's technology news.</li>
                            </ul>
                            <a href="#" class="tm-button"><span>Read More</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// About Us Area -->

        <!-- Services Area -->
        <div id="tm-area-services" class="tm-services-area tm-section tm-padding-section bg-white">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <h2>Infinite Roles</h2>
                            <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="row mt-40-reverse">

                            <!-- Single Features -->
                            <div class="col-lg-12 col-md-6 col-12 mt-40">
                                <div class="tm-service">
                                    <span class="tm-service-icon">
                                        <img src="assets/images/hustler.svg" alt="hustler">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                            width="70px" height="72px">
                                            <path fill-rule="evenodd"
                                                d="M52.556,54.307 C64.348,37.302 73.747,12.982 68.512,4.877 C60.795,-7.064 13.848,4.659 2.850,20.520 C2.466,21.074 2.124,21.636 1.831,22.199 C-6.854,38.889 17.104,75.968 33.774,71.638 C39.513,70.148 46.364,63.237 52.556,54.307 " />
                                        </svg>
                                    </span>
                                    <div class="tm-service-content">
                                        <h4>Hustler</h4>
                                        <p>Seseorang yang memiliki peran dalam memperkenalkan dan memasarkan produk kepada
                                            konsumen.</p>
                                    </div>
                                </div>
                            </div>
                            <!--// Single Features -->

                            <!-- Single Features -->
                            <div class="col-lg-12 col-md-6 col-12 mt-40">
                                <div class="tm-service">
                                    <span class="tm-service-icon">
                                        <img src="assets/images/hipster.svg" alt="hipster">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                            width="70px" height="72px">
                                            <path fill-rule="evenodd"
                                                d="M52.556,54.307 C64.348,37.302 73.747,12.982 68.512,4.877 C60.795,-7.064 13.848,4.659 2.850,20.520 C2.466,21.074 2.124,21.636 1.831,22.199 C-6.854,38.889 17.104,75.968 33.774,71.638 C39.513,70.148 46.364,63.237 52.556,54.307 " />
                                        </svg>
                                    </span>
                                    <div class="tm-service-content">
                                        <h4>Hipster</h4>
                                        <p>Seseorang yang memiliki peran dalam menyajikan dan memastikan keestetikan
                                            tampilan dari sebuah produk.</p>
                                    </div>
                                </div>
                            </div>
                            <!--// Single Features -->

                            <!-- Single Features -->
                            <div class="col-lg-12 col-md-6 col-12 mt-40">
                                <div class="tm-service">
                                    <span class="tm-service-icon">
                                        <img src="assets/images/hacker.svg" alt="hacker">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                            width="70px" height="72px">
                                            <path fill-rule="evenodd"
                                                d="M52.556,54.307 C64.348,37.302 73.747,12.982 68.512,4.877 C60.795,-7.064 13.848,4.659 2.850,20.520 C2.466,21.074 2.124,21.636 1.831,22.199 C-6.854,38.889 17.104,75.968 33.774,71.638 C39.513,70.148 46.364,63.237 52.556,54.307 " />
                                        </svg>
                                    </span>
                                    <div class="tm-service-content">
                                        <h4>Hacker</h4>
                                        <p>Seseorang yang memiliki peran dalam mengembangkan teknologi yang ada dalam produk
                                            (coding).</p>
                                    </div>
                                </div>
                            </div>
                            <!--// Single Features -->

                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="tm-service-image">
                            <div class="tm-service-image-1 is-visible">
                                <img src="assets/images/char-hustler.svg" alt="hustler">
                            </div>
                            <div class="tm-service-image-2">
                                <img src="assets/images/char-hipster.svg" alt="hipster">
                            </div>
                            <div class="tm-service-image-3">
                                <img src="assets/images/char-hacker.svg" alt="hacker">
                            </div>
                            <div class="tm-service-mobileshowanim">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Services Area -->

        <!-- Team Member Area -->
        <div id="tm-area-team" class="tm-team-area tm-section tm-padding-section bg-grey">
            <div class="tm-team-areabgshape">
                <img src="assets/images/team-area-bgshape.png" alt="appmyil bg shape">
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <h2>Meet The Core Team</h2>
                            <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row tm-team-slider tm-slider-arrow">

                    @foreach ($data as $item)
                        <!-- Team Member -->
                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="tm-team text-center">
                                <div class="tm-team-top">
                                    <div class="tm-team-image">
                                        <div class="tm-team-imageinner">
                                            <img src="{{ $item->photo }}" alt="{{ $item->name }}">
                                        </div>
                                    </div>
                                    <button class="tm-team-socialtrigger"><i class="zmdi zmdi-share"></i></button>
                                    <ul class="tm-team-socialicons">
                                        <li><a href="https:/instagram.com/{{ $item->instagram ?: 'infinite.uny' }}"
                                                target="_blank"><i class="zmdi zmdi-instagram"></i></a></li>
                                    </ul>
                                </div>
                                <div class="tm-team-content">
                                    <h6>{{ $item->division }}</h6>
                                    <h4>{{ $item->name }}</h4>
                                    <h6>{{ $item->study_program . ' ' . $item->year }}</h6>
                                </div>
                            </div>
                        </div>
                        <!--// Team Member -->
                    @endforeach

                </div>
                <br>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <a href="#" class="btn btn-outline-success more-team">Selengkapnya</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Team Member Area -->

        <!-- Video & Funfact Area -->
        <div id="tm-area-video" class="tm-video-funfact-area tm-section tm-padding-section bg-grey">
            <div class="tm-video-funfact-bgshape">
                <img src="assets/images/funfact-bg-shape.png" alt="bg shape">
            </div>
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="tm-videobox tm-videobox-circle tm-funfact-videobox">
                            <img src="assets/images/video-thumb.svg" alt="appmyil video image">
                            <a href="https://www.youtube.com/watch?v=mHUOCxVT5ro" data-fancybox="video"><i
                                    class="flaticon-play-button"></i></a>
                            <span class="tm-videobox-roundicon"></span>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row tm-funfact-wrapper">

                            <!-- Single Funfact -->
                            <div class="col-lg-6 col-md-6 col-sm-6 col-12 tm-funfact-masonryitem">
                                <div class="tm-funfact text-center">
                                    <span class="tm-funfact-icon">
                                        <i class="flaticon-inbox"></i>
                                    </span>
                                    <div class="tm-funfact-content">
                                        <span class="tm-funfact-number"><span class="odometer"
                                                data-count-to="100"></span>K+</span>
                                        <h4>Visitors</h4>
                                    </div>
                                </div>
                            </div>
                            <!--// Single Funfact -->

                            <!-- Single Funfact -->
                            <div class="col-lg-6 col-md-6 col-sm-6 col-12 tm-funfact-masonryitem">
                                <div class="tm-funfact text-center">
                                    <span class="tm-funfact-icon">
                                        <i class="flaticon-goal"></i>
                                    </span>
                                    <div class="tm-funfact-content">
                                        <span class="tm-funfact-number"><span class="odometer"
                                                data-count-to="1000"></span>+</span>
                                        <h4>Total Member Achivement</h4>
                                    </div>
                                </div>
                            </div>
                            <!--// Single Funfact -->

                            <!-- Single Funfact -->
                            <div class="col-lg-6 col-md-6 col-sm-6 col-12 tm-funfact-masonryitem">
                                <div class="tm-funfact text-center">
                                    <span class="tm-funfact-icon">
                                        <i class="flaticon-team-1"></i>
                                    </span>
                                    <div class="tm-funfact-content">
                                        <span class="tm-funfact-number"><span class="odometer"
                                                data-count-to="100"></span>+</span>
                                        <h4>Members</h4>
                                    </div>
                                </div>
                            </div>
                            <!--// Single Funfact -->

                            <!-- Single Funfact -->
                            <div class="col-lg-6 col-md-6 col-sm-6 col-12 tm-funfact-masonryitem">
                                <div class="tm-funfact text-center">
                                    <span class="tm-funfact-icon">
                                        <i class="flaticon-review"></i>
                                    </span>
                                    <div class="tm-funfact-content">
                                        <span class="tm-funfact-number"><span class="odometer"
                                                data-count-to="10"></span>+</span>
                                        <h4>Positive Project Reviews</h4>
                                    </div>
                                </div>
                            </div>
                            <!--// Single Funfact -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Video & Funfact Area -->

        <!-- Screenshot Slideshow Area -->
        <div id="tm-area-screenshots" class="tm-screenshot-area tm-section tm-padding-section bg-white">
            <div class="tm-screenshots-bgshape">
                <img src="assets/images/screenshot-background-shape.png" alt="screenshot background shape">
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <h2>Portofolio Kami</h2>
                            <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="tm-screenshots">
                            <div class="tm-screenshots-slider tm-slider-arrow">
                                <div class="tm-screenshots-single">
                                    <img src="assets/images/screenshot-1.jpg" alt="appmyil screenshot">
                                </div>
                                <div class="tm-screenshots-single">
                                    <img src="assets/images/screenshot-2.jpg" alt="appmyil screenshot">
                                </div>
                                <div class="tm-screenshots-single">
                                    <img src="assets/images/screenshot-3.jpg" alt="appmyil screenshot">
                                </div>
                                <div class="tm-screenshots-single">
                                    <img src="assets/images/screenshot-4.jpg" alt="appmyil screenshot">
                                </div>
                                <div class="tm-screenshots-single">
                                    <img src="assets/images/screenshot-5.jpg" alt="appmyil screenshot">
                                </div>
                                <div class="tm-screenshots-single">
                                    <img src="assets/images/screenshot-1.jpg" alt="appmyil screenshot">
                                </div>
                                <div class="tm-screenshots-single">
                                    <img src="assets/images/screenshot-2.jpg" alt="appmyil screenshot">
                                </div>
                                <div class="tm-screenshots-single">
                                    <img src="assets/images/screenshot-3.jpg" alt="appmyil screenshot">
                                </div>
                                <div class="tm-screenshots-single">
                                    <img src="assets/images/screenshot-4.jpg" alt="appmyil screenshot">
                                </div>
                                <div class="tm-screenshots-single">
                                    <img src="assets/images/screenshot-5.jpg" alt="appmyil screenshot">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Screenshot Slideshow Area -->

        <!-- Testimonial Area -->
        <div id="tm-area-testimonial" class="tm-testimonial-area tm-section tm-padding-section bg-white">
            <div class="tm-testimonial-bgshape">
                <img src="assets/images/testimonial-bg-shape.png" alt="appmyil bg shape">
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <h2>Testimonials</h2>
                            <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">

                        <div class="tm-testimonial-authors">
                            <div class="tm-testimonial-author">
                                <div class="tm-testimonial-authorinner">
                                    <img src="assets/images/author-image-1.jpg" alt="appmyil author image">
                                </div>
                            </div>
                            <div class="tm-testimonial-author">
                                <div class="tm-testimonial-authorinner">
                                    <img src="assets/images/author-image-2.jpg" alt="appmyil author image">
                                </div>
                            </div>
                            <div class="tm-testimonial-author">
                                <div class="tm-testimonial-authorinner">
                                    <img src="assets/images/author-image-3.jpg" alt="appmyil author image">
                                </div>
                            </div>
                            <div class="tm-testimonial-author">
                                <div class="tm-testimonial-authorinner">
                                    <img src="assets/images/author-image-4.jpg" alt="appmyil author image">
                                </div>
                            </div>
                        </div>

                        <div class="tm-testimonial-contents tm-slider-arrow">
                            <div class="tm-testimonial-content">
                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit sed do eiuod incididunt
                                    ametfh consectetur dolore magna aliqua. Ut enim admim veniam dolor sit amet
                                    magnaelit ate consectetur. Progravida nibh vel velit auctor alinean
                                    sollicitudin. </p>
                                <i class="zmdi zmdi-quote"></i>
                                <h4>Albert Barnes</h4>
                                <h6>Visual Designer</h6>
                            </div>
                            <div class="tm-testimonial-content">
                                <p>Atque dicta voluptas et eos sint accusantium aut laboriosam. Omnis voluptatum ea
                                    aut tempora recusandae qui facere. Consectetur hic tempore deleniti ea sit.
                                    repellendus reprehenderit est veritatis quam laudantium. consequatur qui nihil
                                    eos quibusdam</p>
                                <i class="zmdi zmdi-quote"></i>
                                <h4>Frankie Anderson</h4>
                                <h6>Frontend Developer</h6>
                            </div>
                            <div class="tm-testimonial-content">
                                <p>Architecto aut error explicabo. Voluptatem eaque sequi qui beatae velit vero aut
                                    autem. Et adipisci perferendis corporis et similique vitae consequatur. Aut
                                    eveniet quam. Et ut placeat consequatur qui nihil eos quibusdam. Impedit
                                    blanditiis delectus.</p>
                                <i class="zmdi zmdi-quote"></i>
                                <h4>Tyreek Hoppe</h4>
                                <h6>UI/UX Designer</h6>
                            </div>
                            <div class="tm-testimonial-content">
                                <p>Eos asperiores minima non eaque. Quisquam voluptas impedit nihil. Repellat
                                    dolorum et repellat earum voluptates ut voluptatibus quo. Autem eius non
                                    quibusdam dolore. Neque doloribus repellendus reprehenderit est veritatis quam
                                    laudantium.</p>
                                <i class="zmdi zmdi-quote"></i>
                                <h4>Darrick Kuvalis</h4>
                                <h6>Web Developer</h6>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!--// Testimonial Area -->

        <!-- Pricing Area -->
        {{-- <div id="tm-area-pricing" class="tm-pricebox-area tm-section tm-padding-section bg-grey">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="tm-sectiontitle text-center">
                        <h2>Pricing Plans</h2>
                        <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                    </div>
                </div>
            </div>
            <div class="tm-pricebox-switcher text-center mb-3">
                <div class="tm-pricebox-switchbuttons">
                    <button data-keyvalue="monthly">Monthly</button>
                    <button class="is-active" data-keyvalue="yearly">Yearly</button>
                </div>
            </div>
            <div class="row justify-content-center">

                <!-- Single Pricebox -->
                <div class="col-lg-4 col-md-6 col-12 mt-30">
                    <div class="tm-pricebox text-center">
                        <div class="tm-pricebox-header">
                            <h4>Basic</h4>
                        </div>
                        <div class="tm-pricebox-body">
                            <div class="tm-pricebox-price">
                                <h2>
                                    <span class="tm-pricebox-price-unit">$</span>
                                    <span class="tm-pricebox-price-digit" data-pricebox-price-monthly="29"
                                        data-pricebox-price-yearly="99"></span>
                                </h2>
                                <span class="tm-pricebox-price-time" data-monthly-text="Per month"
                                    data-yearly-text="Per year"></span>
                            </div>
                            <ul>
                                <li>100 MB Disk Space</li>
                                <li>5 Email Accounts</li>
                                <li>Webmail Access</li>
                                <li class="disabled">Unlimited attachment</li>
                                <li class="disabled">SSL Security</li>
                            </ul>
                        </div>
                        <div class="tm-pricebox-footer">
                            <a href="#" class="tm-button tm-button-sm tm-button-dark"><span>Purchase</span></a>
                        </div>
                    </div>
                </div>
                <!--// Single Pricebox -->

                <!-- Single Pricebox -->
                <div class="col-lg-4 col-md-6 col-12 mt-30">
                    <div class="tm-pricebox text-center is-active">
                        <div class="tm-pricebox-header">
                            <h4>Standard</h4>
                        </div>
                        <div class="tm-pricebox-body">
                            <div class="tm-pricebox-price">
                                <h2>
                                    <span class="tm-pricebox-price-unit">$</span>
                                    <span class="tm-pricebox-price-digit" data-pricebox-price-monthly="39"
                                        data-pricebox-price-yearly="139"></span>
                                </h2>
                                <span class="tm-pricebox-price-time" data-monthly-text="Per month"
                                    data-yearly-text="Per year"></span>
                            </div>
                            <ul>
                                <li>100 MB Disk Space</li>
                                <li>5 Email Accounts</li>
                                <li>Webmail Access</li>
                                <li>Unlimited attachment</li>
                                <li class="disabled">SSL Security</li>
                            </ul>
                        </div>
                        <div class="tm-pricebox-footer">
                            <a href="#" class="tm-button tm-button-sm tm-button-dark"><span>Purchase</span></a>
                        </div>
                    </div>
                </div>
                <!--// Single Pricebox -->

                <!-- Single Pricebox -->
                <div class="col-lg-4 col-md-6 col-12 mt-30">
                    <div class="tm-pricebox text-center">
                        <div class="tm-pricebox-header">
                            <h4>Premium</h4>
                        </div>
                        <div class="tm-pricebox-body">
                            <div class="tm-pricebox-price">
                                <h2>
                                    <span class="tm-pricebox-price-unit">$</span>
                                    <span class="tm-pricebox-price-digit" data-pricebox-price-monthly="49"
                                        data-pricebox-price-yearly="179"></span>
                                </h2>
                                <span class="tm-pricebox-price-time" data-monthly-text="Per month"
                                    data-yearly-text="Per year"></span>
                            </div>
                            <ul>
                                <li>100 MB Disk Space</li>
                                <li>5 Email Accounts</li>
                                <li>Webmail Access</li>
                                <li>Unlimited attachment</li>
                                <li>SSL Security</li>
                            </ul>
                        </div>
                        <div class="tm-pricebox-footer">
                            <a href="#" class="tm-button tm-button-sm tm-button-dark"><span>Purchase</span></a>
                        </div>
                    </div>
                </div>
                <!--// Single Pricebox -->

            </div>
        </div>
    </div> --}}
        <!--// Pricing Area -->

        <!-- Frequently Ask Question -->
        <div id="tm-area-faq" class="tm-faq-area tm-section tm-padding-section bg-white">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <h2>Frequently Asked Questions</h2>
                            <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-5">
                        <div class="tm-faq-image">
                            <img src="assets/images/faq-image.svg" alt="faq image">
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <!-- Accordion Wrapper -->
                        <div id="tm-accordion1" class="tm-accordion">

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading1">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse1" aria-expanded="true"
                                            aria-controls="tm-accordion1-collapse1">
                                            How can I download the app ?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse1" class="collapse show"
                                    aria-labelledby="tm-accordion1-heading1" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>An App is a term to describe a web-based program. These web-based or
                                            mobile applications perform a specific task for the user. website where
                                            one can download these small programs for use on mobile devices such as
                                            a Smart phone.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading2">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse2" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse2">
                                            How can I install the app ?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse2" class="collapse"
                                    aria-labelledby="tm-accordion1-heading2" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>An App is a term to describe a web-based program. These web-based or
                                            mobile applications perform a specific task for the user. website where
                                            one can download these small programs for use on mobile devices such as
                                            a Smart phone.</p>
                                        <ul>
                                            <li>Libero exercitationem omnis blanditiis ea quo et rem.</li>
                                            <li>Beatae veritatis aliquid vel quisquam saepe incidunt.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading3">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse3" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse3">
                                            How can I active all the features ?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse3" class="collapse"
                                    aria-labelledby="tm-accordion1-heading3" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>An App is a term to describe a web-based program. These web-based or
                                            mobile applications perform a specific task for the user. website where
                                            one can download these small programs for use on mobile devices such as
                                            a Smart phone.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading4">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse4" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse4">
                                            What platform should we target?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse4" class="collapse"
                                    aria-labelledby="tm-accordion1-heading4" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>An App is a term to describe a web-based program. These web-based or
                                            mobile applications perform a specific task for the user. website where
                                            one can download these small programs for use on mobile devices such as
                                            a Smart phone.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading5">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse5" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse5">
                                            Do I have to buy the Mobile App?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse5" class="collapse"
                                    aria-labelledby="tm-accordion1-heading5" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>An App is a term to describe a web-based program. These web-based or
                                            mobile applications perform a specific task for the user. website where
                                            one can download these small programs for use on mobile devices such as
                                            a Smart phone.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading6">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse6" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse6">
                                            Will I need to test my app fully?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse6" class="collapse"
                                    aria-labelledby="tm-accordion1-heading6" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>An App is a term to describe a web-based program. These web-based or
                                            mobile applications perform a specific task for the user. website where
                                            one can download these small programs for use on mobile devices such as
                                            a Smart phone.</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!--// Accordion Wrapper -->
                    </div>
                </div>
            </div>
        </div>
        <!--// Frequently Ask Question -->

        <!-- Downlaod Area -->
        {{-- <div id="tm-area-download" class="tm-downlaod-area tm-section tm-padding-section bg-gradient">
        <div class="tm-download-bgshape">
            <img src="assets/images/download-bgshape.png" alt="bg shape">
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="tm-download-content">
                        <img src="assets/images/logo-white.png" alt="appmyil">
                        <h4>Get The App</h4>
                        <h3>It’s Free to Download for Everyone</h3>
                        <div class="tm-buttongroup">
                            <a href="#" class="tm-button tm-button-lg tm-button-white"><i class="zmdi zmdi-android"></i><span>Google
                                    Play</span></a>
                            <a href="#" class="tm-button tm-button-lg tm-button-white tm-button-transparent"><i
                                    class="zmdi zmdi-apple"></i><span>IOS App Store</span></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="tm-download-graph text-center">
                        <h5>Download Statics (Last 6 month)</h5>
                        <canvas id="downloadgraph" height="185"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
        <!--// Downlaod Area -->

        <!-- Subscribe Area -->
        {{-- <div id="tm-area-subscribe" class="tm-subscribe-area tm-section tm-padding-section bg-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="tm-subscribe-image text-center">
                        <img src="assets/images/subscribe-area-image.png" alt="subscribe image">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="tm-subscribe-content text-center">
                        <h2>Subscribe Our Newsletter!</h2>
                        <p>Subscribe our newsletter and get notifications to stay update</p>
                        <form id="tm-mailchimp-form" class="tm-subscribe-form text-center">
                            <input id="mc-email" type="text" placeholder="Email address" required="required">
                            <button id="mc-submit" type="submit" class="tm-button"><span>Subscribe</span></button>
                        </form>
                        <!-- Mailchimp Alerts -->
                        <div class="tm-mailchimp-alerts">
                            <div class="tm-mailchimp-submitting"></div>
                            <div class="mailchimp-success"></div>
                            <div class="tm-mailchimp-error"></div>
                        </div>
                        <!--// Mailchimp Alerts -->
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
        <!--// Subscribe Area -->

        <!-- Blog Area -->
        <div id="tm-area-blog" class="tm-blog-area tm-section tm-padding-section bg-grey">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <h2>Event Terbaru</h2>
                            <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row tm-blog-slider tm-slider-arrow">

                    <!-- Single Blog -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="tm-blog">
                            <div class="tm-blog-topside">
                                <div class="tm-blog-image">
                                    <img src="assets/images/blog-image-1.jpg" alt="appmyil blog image">
                                </div>
                                <span class="tm-blog-date">September 16, 2019</span>
                            </div>
                            <div class="tm-blog-content">
                                <h4><a href="blog-details.html">How To Active New Features In Current App?</a></h4>
                                <p>Lorem ipsum dolor sit amet, consectetur adipis
                                    cing elit. Nunc mauris arcu, lobortis id interdum vitae, interdum eget elit.
                                    Curabitur quis urna nulla. Suspendisse potenti.</p>
                                <a href="blog-details.html" class="tm-button tm-button-sm tm-button-dark"><span>Read
                                        more</span></a>
                            </div>
                        </div>
                    </div>
                    <!--// Single Blog -->

                    <!-- Single Blog -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="tm-blog">
                            <div class="tm-blog-topside">
                                <div class="tm-blog-image">
                                    <img src="assets/images/blog-image-2.jpg" alt="appmyil blog image">
                                </div>
                                <span class="tm-blog-date">December 16, 2019</span>
                            </div>
                            <div class="tm-blog-content">
                                <h4><a href="blog-details.html">Developing apps for mobile
                                        devices</a></h4>
                                <p>Lorem ipsum dolor sit amet, consectetur adipis
                                    cing elit. Nunc mauris arcu, lobortis id interdum vitae, interdum eget elit.
                                    Curabitur quis urna nulla. Suspendisse potenti.</p>
                                <a href="blog-details.html" class="tm-button tm-button-sm tm-button-dark"><span>Read
                                        more</span></a>
                            </div>
                        </div>
                    </div>
                    <!--// Single Blog -->

                    <!-- Single Blog -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="tm-blog">
                            <div class="tm-blog-topside">
                                <div class="tm-blog-image">
                                    <img src="assets/images/blog-image-3.jpg" alt="appmyil blog image">
                                </div>
                                <span class="tm-blog-date">September 16, 2019</span>
                            </div>
                            <div class="tm-blog-content">
                                <h4><a href="blog-details.html">Mobile applications are first
                                        tested within the development</a></h4>
                                <p>Lorem ipsum dolor sit amet, consectetur adipis
                                    cing elit. Nunc mauris arcu, lobortis id interdum vitae, interdum eget elit.
                                    Curabitur quis urna nulla. Suspendisse potenti.</p>
                                <a href="blog-details.html" class="tm-button tm-button-sm tm-button-dark"><span>Read
                                        more</span></a>
                            </div>
                        </div>
                    </div>
                    <!--// Single Blog -->

                    <!-- Single Blog -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="tm-blog">
                            <div class="tm-blog-topside">
                                <div class="tm-blog-image">
                                    <img src="assets/images/blog-image-3.jpg" alt="appmyil blog image">
                                </div>
                                <span class="tm-blog-date">September 16, 2019</span>
                            </div>
                            <div class="tm-blog-content">
                                <h4><a href="blog-details.html">Mobile applications are first
                                        tested within the development</a></h4>
                                <p>Lorem ipsum dolor sit amet, consectetur adipis
                                    cing elit. Nunc mauris arcu, lobortis id interdum vitae, interdum eget elit.
                                    Curabitur quis urna nulla. Suspendisse potenti.</p>
                                <a href="blog-details.html" class="tm-button tm-button-sm tm-button-dark"><span>Read
                                        more</span></a>
                            </div>
                        </div>
                    </div>
                    <!--// Single Blog -->

                </div>
                <br>
                <br>
                <br>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <a href="{{ route('event') }}" class="btn btn-outline-success more-team">Selengkapnya</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Blog Area -->

        <!-- Contact Area -->
        <div id="tm-area-contact" class="tm-contact-area tm-section tm-padding-section bg-white">
            <div class="tm-contact-bgshape">
                <img src="assets/images/bg-shape-contact.png" alt="contact shape">
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="tm-sectiontitle text-center">
                        <h2>Hubungi Kami</h2>
                        <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <form id="tm-contactform" action="assets/php/mailer.php" method="post"
                            class="tm-form tm-contact-form">
                            <div class="tm-form-inner">
                                <div class="tm-form-field tm-form-fieldhalf">
                                    <input type="text" name="name" placeholder="Full Name" required="required">
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field tm-form-fieldhalf">
                                    <input type="email" name="email" placeholder="Email Address"
                                        required="required">
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field tm-form-fieldhalf">
                                    <input type="text" name="subject" placeholder="Subject" required="required">
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field tm-form-fieldhalf">
                                    <input name="phone" type="text" placeholder="Phone">
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field">
                                    <textarea name="message" cols="30" rows="4" placeholder="Message"></textarea>
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field">
                                    <button type="submit" class="tm-button"><span>Kirim Pesan</span></button>
                                </div>
                            </div>
                        </form>
                        <p class="form-messages"></p>
                    </div>
                    <div class="col-lg-4">
                        <div class="tm-contact-content">
                            <div class="tm-contact-block">
                                <span class="tm-contact-block-icon">
                                    <i class="zmdi zmdi-pin"></i>
                                </span>
                                <div class="tm-contact-block-content">
                                    <h6>Basecamp Infinite UNY</h6>
                                    <p>Gedung Aula Fakultas Teknik UNY Karangmalang, Caturtunggal, Depok, Sleman,
                                        Yogyakarta, 55281</p>
                                </div>
                            </div>
                            <div class="tm-contact-block">
                                <span class="tm-contact-block-icon">
                                    <i class="zmdi zmdi-whatsapp"></i>
                                </span>
                                <div class="tm-contact-block-content">
                                    <h6>Whatsapp</h6>
                                    <p><a href="http://wa.me/6288295389203">(+62) - 882 953 892 03</a></p>
                                </div>
                            </div>
                            <div class="tm-contact-block">
                                <span class="tm-contact-block-icon">
                                    <i class="zmdi zmdi-email"></i>
                                </span>
                                <div class="tm-contact-block-content">
                                    <h6>Email</h6>
                                    <p><a href="mailto:infiniteuny@gmail.com">infiniteuny@gmail.com</a></p>
                                    <p><a href="mailto:infinite@student.uny.ac.id">infinite@student.uny.ac.id</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Contact Area -->

        <!-- Contact Form -->
        {{-- <div class="google-map-area bg-white">
        <div id="google-map" class="tm-google-map"></div>
    </div> --}}
        <!--// Contact Form -->

    </main>
    <!--// Page Content -->
@endsection
