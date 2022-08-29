<!-- NAVBAR-SECTION START -->
<!-- Mobile Navbar -->
<div class="site-mobile-menu site-navbar-target">
    <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close mt-3">
            <span class="icon-close2 js-menu-toggle"></span>
        </div>
    </div>
    <div class="site-mobile-menu-body">
    </div>
</div>

<!-- navbar -->
<header class="site-navbar js-sticky-header site-navbar-target" role="banner">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <div class="site-logo">
                <a href="{{ route('landing') }}"> 
                    @if(Route::is('landing'))
                        <img src="{{ asset('landing/assets/images/logo_infinite.svg') }}" height="80px">
                    @elseif(Route::is('event') || Route::is('team'))
                        <img src="{{ asset('landing/assets/images/logo_infinite_green.svg') }}" height="80px">
                    @endif
                </a>
            </div>
            <div class="ml-auto">
                <nav class="site-navigation position-relative text-right" role="navigation">
                    <ul class="site-menu main-menu js-clone-nav mr-auto d-none d-lg-block">
                        @if(Route::is('landing'))
                            <li><a href="{{ url('/') }}/#about-section" class="nav-link">Tentang</a></li>
                            <li><a href="{{ url('/') }}/#events-section" class="nav-link">Event</a></li>
                            <li><a href="{{ url('/') }}/#team-section" class="nav-link">Tim Kami</a></li>
                            <li><a href="{{ url('/') }}/#footer-section" class="nav-link">Kontak</a></li>
                        @elseif(Route::is('event') || Route::is('team'))
                            <li><a href="{{ url('/') }}/#about-section" style="color: #27835d;" class="nav-link">Tentang</a></li>
                            <li><a href="{{ url('/') }}/#events-section" style="color: #27835d;" class="nav-link">Event</a></li>
                            <li><a href="{{ url('/') }}/#team-section" style="color: #27835d;" class="nav-link">Tim Kami</a></li>
                            <li><a href="{{ url('/') }}/#footer-section" style="color: #27835d;" class="nav-link">Kontak</a></li>
                        @endif
                    </ul> 
                </nav>
                <a href="#" class="d-inline-block d-lg-none site-menu-toggle js-menu-toggle float-right"><span class="icon-menu h3"></span></a>
            </div>
        </div>
    </div>
</header>
<!-- NAVBAR-SECTION END -->