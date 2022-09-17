@extends('layouts.app')

@section('css')
    <style>
        .tm-sectiontitle-divider::before,
        .tm-sectiontitle-divider::after {
            background-image: url({{ asset('assets/images/title-shape.png') }});
        }
    </style>
@endsection

@section('content')
    <!-- Heroslider Area -->
    <div id="tm-area-heroslider" class="tm-heroslider">
        <div class="tm-heroslider-inner">
            <img src="{{ asset('assets/images/heroslider-overlay-shape.png') }}" alt="heroslider ovelay"
                class="tm-heroslider-ovelayshape">
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
                            <img src="{{ asset('assets/images/inf.png') }}" alt="appmyil logo 3d">
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
                            <h6><i>Let's Make Infinite Technology</i></h6>
                            <p>INFINITE merupakan Divisi Teknologi Informasi yang berada dibawah naungan UKM Rekayasa
                                Teknologi UNY. Sejak tahun 2014, INFINITE mewadahi mahasiswa dari berbagai jurusan yang
                                memiliki minat dan bakat di bidang Teknologi Informasi.</p>
                            <ul class="stylish-list">
                                <li><i class="zmdi zmdi-check"></i> Berkolaborasi menciptakan karya dalam bentuk mobile
                                    apps, web apps, IoT, ML untuk menjawab permasalahan di masyarakat.</li>
                                <li><i class="zmdi zmdi-check"></i> Kesempatan mengikuti lomba baik skala nasional dan
                                    internasional.</li>
                                <li><i class="zmdi zmdi-check"></i> Kesempatan mengembangkan cv dan portfolio.</li>
                                <li><i class="zmdi zmdi-check"></i> Basecamp 24 jam.</li>
                                <li><i class="zmdi zmdi-check"></i> Networking.</li>
                            </ul>
                            {{-- <a href="#" class="tm-button"><span>Read More</span></a> --}}
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
                                        <p>Skill: Management, Negotiation, Writing, Critical Thinking</p>
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
                                            tampilan serta pengalaman dari sebuah produk.</p>
                                        <p>Skill: Design, Copywriting, Research, Ideation</p>
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
                                        <p>Skill: Mobile Apps, Web Apps, Desktop Apps, IoT, Network Security, etc</p>
                                    </div>
                                </div>
                            </div>
                            <!--// Single Features -->

                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="tm-service-image">
                            <div class="tm-service-image-1 is-visible">
                                <img src="{{ asset('assets/images/char-hustler.svg') }}" alt="hustler">
                            </div>
                            <div class="tm-service-image-2">
                                <img src="{{ asset('assets/images/char-hipster.svg') }}" alt="hipster">
                            </div>
                            <div class="tm-service-image-3">
                                <img src="{{ asset('assets/images/char-hacker.svg') }}" alt="hacker">
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
                <img src="{{ asset('assets/images/team-area-bgshape.png') }}" alt="appmyil bg shape">
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
                                        <li><a href="https://instagram.com/{{ $item->instagram ?: 'infinite.uny' }}"
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
                            <a href="{{ route('team') }}" class="btn btn-outline-success more-team">Selengkapnya</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Team Member Area -->

        <!-- Video & Funfact Area -->
        <div id="tm-area-video" class="tm-video-funfact-area tm-section tm-padding-section bg-grey">
            <div class="tm-video-funfact-bgshape">
                <img src="{{ asset('assets/images/funfact-bg-shape.png') }}" alt="bg shape">
            </div>
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="tm-videobox tm-videobox-circle tm-funfact-videobox">
                            <img src="{{ asset('assets/images/video-thumb.svg') }}" alt="appmyil video image">
                            <a href="{{ $config['PROFILE_VIDEO_URL'] }}" data-fancybox="video"><i
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
                                                data-count-to="{{ $count['achievement'] }}"></span>+</span>
                                        <h4>Total Member Achievements</h4>
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
                                                data-count-to="{{ $count['member'] }}"></span>+</span>
                                        <h4>Active Members</h4>
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
                <img src="{{ asset('assets/images/screenshot-background-shape.png') }}"
                    alt="screenshot background shape">
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <h2>Member Products</h2>
                            <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="tm-screenshots">
                            <div class="tm-screenshots-slider tm-slider-arrow">
                                @foreach ($products as $product)
                                    <div
                                        class="tm-screenshots-single {{ $product->type == 'web' ? 'tm-screenshots-single-web' : '' }}">
                                        <a href="{{ $product->url ?: '#' }}">
                                            <img src="{{ $product->photo }}" alt="{{ $product->description }}">
                                        </a>
                                    </div>
                                @endforeach
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
                <img src="{{ asset('assets/images/testimonial-bg-shape.png') }}" alt="appmyil bg shape">
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="tm-sectiontitle text-center">
                            <h2>What They Say</h2>
                            <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">

                        <div class="tm-testimonial-authors">
                            @foreach ($testimonials as $testimonial)
                                <div class="tm-testimonial-author">
                                    <div class="tm-testimonial-authorinner">
                                        <img src="{{ $testimonial->photo }}" alt="{{ $testimonial->name }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="tm-testimonial-contents tm-slider-arrow">

                            @foreach ($testimonials as $testimonial)
                                <div class="tm-testimonial-content">
                                    <p>{{ strip_tags($testimonial->testimonial) }}</p>
                                    <i class="zmdi zmdi-quote"></i>
                                    <h4>{{ $testimonial->name }}</h4>
                                    <h6>{{ $testimonial->position }}</h6>
                                </div>
                            @endforeach
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
                            <img src="{{ asset('assets/images/faq-image.svg') }}" alt="faq image">
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
                                            Bagaimana cara menjadi anggota INFINITE?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse1" class="collapse show"
                                    aria-labelledby="tm-accordion1-heading1" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>Mengikuti pendaftaran penerima anggota baru rekayasa teknologi, mengikuti seluruh
                                            tahapan seleksi, dan memilih INFINITE sebagai divisi yang diinginkan. Jangan
                                            lupa ya, karena PAB hanya dibuka satu kali se tahun</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading2">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse2" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse2">
                                            Dimana lokasi basecamp INFINITE?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse2" class="collapse"
                                    aria-labelledby="tm-accordion1-heading2" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>Gedung Aula Fakultas Teknik UNY, belakang LPPM Universitas Negeri Yogyakarta.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading3">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse3" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse3">
                                            Kapan basecamp buka dan tutup?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse3" class="collapse"
                                    aria-labelledby="tm-accordion1-heading3" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>Untuk keperluan riset basecamp INFINITE buka 24/7 ya!</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading4">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse4" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse4">
                                            Apakah anggota lama perlu melakukan pendaftaran kembali ketika PAB berlangsung?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse4" class="collapse"
                                    aria-labelledby="tm-accordion1-heading4" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>Anggota lama tidak perlu melakukan pendaftaran PAB INFINITE kembali, cukup
                                            melakukan daftar ulang di website.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading5">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse5" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse5">
                                            Bagaimana cara mengikuti lomba, mencari anggota team, dan mencari dosen
                                            pembimbing?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse5" class="collapse"
                                    aria-labelledby="tm-accordion1-heading5" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>Untuk mengikuti lomba, teman-teman dibebaskan mencari dan merencanakan lomba apa
                                            yang akan diikuti.

                                            Jika sudah menentukan lomba, teman-teman wajib membentuk tim, apabila
                                            membutuhkan bantuan INFINITE dapat memberikan rekomendasi, harap menghubungi sie
                                            competition (usahakan team kamu memiliki komposisi hacker, hipster dan hustler
                                            ya).

                                            Setelah itu untuk keperluan dosen pembimbing, usahakan teman-teman mencari dosen
                                            sesuai dengan karya yang akan dikembangkan. Teman teman dapat mencari secara
                                            manual melalui web staff uny atau meminta bantuan kepada sie competition.

                                            Selamat berlomba!</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading6">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse6" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse6">
                                            Saya masih bingung dalam pengembangan karya saya, apa saya bisa melakukan
                                            konsultasi?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse6" class="collapse"
                                    aria-labelledby="tm-accordion1-heading6" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>Teman-teman dapat menghubungi Divisi Competition, kami akan merencanakan
                                            konsultasi
                                            bersama dengan kakak tingkat, alumni, ataupun dosen yang ahli di bidangnya.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading7">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse7" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse7">
                                            Bagaimana cara mengajukan pendanaan riset?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse7" class="collapse"
                                    aria-labelledby="tm-accordion1-heading7" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>Untuk mengajukan pendanaan, teman-teman dapat mengisi form pada link berikut</p>
                                        <p>Bahan-bahan yang harus disiapkan diantaranya:</p>
                                        <ul>
                                            <li>Booklet</li>
                                            <li>Timeline lomba</li>
                                            <li>RAB</li>
                                            <li>Anggota Tim</li>
                                            <li>LOA (dapat disusulkan)</li>
                                        </ul>

                                        <p>Setelah mengisi form, harap melakukan konfirmasi kepada CP tertera ya!</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header" id="tm-accordion1-heading8">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#tm-accordion1-collapse8" aria-expanded="false"
                                            aria-controls="tm-accordion1-collapse8">
                                            Jika saya pihak eksternal, bagaimana cara berkolaborasi dengan INFINITE?
                                        </button>
                                    </h5>
                                </div>
                                <div id="tm-accordion1-collapse8" class="collapse"
                                    aria-labelledby="tm-accordion1-heading8" data-parent="#tm-accordion1">
                                    <div class="card-body">
                                        <p>Silahkan hubungi CP beserta deskripsi kerja sama yang dikehendaki, apabila kedua
                                            belah pihak setuju, INFINITE dengan senang hati akan siap membantu.</p>
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
                                <a href="#" class="tm-button tm-button-lg tm-button-white"><i
                                        class="zmdi zmdi-android"></i><span>Google
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

                    @foreach ($events as $event)
                        <!-- Single Blog -->
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="tm-blog">
                                <div class="tm-blog-topside">
                                    <div class="tm-blog-image">
                                        <img src="{{ $event->media ?: 'assets/images/blog-image-1.jpg' }}"
                                            alt="appmyil blog image">
                                    </div>
                                    <span
                                        class="tm-blog-date">{{ Carbon\Carbon::parse($event->published_at)->format('F d, Y') }}</span>
                                </div>
                                <div class="tm-blog-content">
                                    <h4><a
                                            href="{{ route('event.detail', $event->id) }}">{{ strlen($event->title) > 40 ? substr($event->title, 0, 40) . '...' : $event->title }}</a>
                                    </h4>
                                    <p>{!! strlen(str_replace('&nbsp;', '', strip_tags($event->content))) > 150
                                        ? substr(str_replace('&nbsp;', '', strip_tags($event->content)), 0, 150) . '...'
                                        : str_replace('&nbsp;', '', strip_tags($event->content)) !!}</p>
                                    <a href="{{ route('event.detail', $event->id) }}"
                                        class="tm-button tm-button-sm tm-button-dark"><span>Read
                                            more</span></a>
                                </div>
                            </div>
                        </div>
                        <!--// Single Blog -->
                    @endforeach

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
                <img src="{{ asset('assets/images/bg-shape-contact.png') }}" alt="contact shape">
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
                                <div class="tm-form-field">
                                    <input type="text" name="subject" placeholder="Subject" required="required">
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
                                    <p>
                                        <a href="{{ $config['ADDRESS_URL'] }}">
                                            {!! $config['ADDRESS_NAME'] !!}
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div class="tm-contact-block">
                                <span class="tm-contact-block-icon">
                                    <i class="zmdi zmdi-whatsapp"></i>
                                </span>
                                <div class="tm-contact-block-content">
                                    <h6>Whatsapp</h6>
                                    <p><a href="{{ $config['WHATSAPP_URL'] }}">{{ $config['WHATSAPP_NAME'] }}</a></p>
                                </div>
                            </div>
                            <div class="tm-contact-block">
                                <span class="tm-contact-block-icon">
                                    <i class="zmdi zmdi-email"></i>
                                </span>
                                <div class="tm-contact-block-content">
                                    <h6>Email</h6>
                                    <p><a
                                            href="mailto:{{ strip_tags($config['EMAIL_1']) }}">{{ strip_tags($config['EMAIL_1']) }}</a>
                                    </p>
                                    <p><a
                                            href="mailto:{{ strip_tags($config['EMAIL_2']) }}">{{ strip_tags($config['EMAIL_2']) }}</a>
                                    </p>
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
