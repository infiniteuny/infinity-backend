<!-- Header Area -->
<div class="tm-header tm-header-fixed tm-sticky-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-3 col-md-9 col-9">
                <a class="tm-header-logo" href="{{ route('landing') }}">
                    <img src="{{ asset('assets/images/infinite_light.svg') }}" alt="infinite logo">
                </a>
            </div>
            <div class="col-lg-9 col-md-3 col-3">
                <nav class="tm-navigation tm-header-navigation">
                    <ul>
                        @if (Route::is('landing'))
                            <li class="current"><a href="#tm-area-heroslider">Home</a></li>
                            <li><a href="#tm-area-about">Tentang</a></li>
                            <li><a href="#tm-area-team">Tim Kami</a></li>
                            <li><a href="#tm-area-blog">Event</a></li>
                            <li><a href="#tm-area-contact">Kontak</a></li>
                            <li><a href="leaderboard">Leaderboard</a></li>
                            <li class="tm-navigation-dropdown"><a href="#tm-area-contact">Tools</a>
                                <ul>
                                    <li><a href="member">Cek Keanggotaan</a></li>
                                    <li><a href="tools">Inficreation</a></li>
                                </ul>
                            </li>
                        @else
                            <li class="current"><a href="{{ url('') }}#tm-area-heroslider">Home</a></li>
                            <li><a href="{{ url('') }}#tm-area-about">Tentang</a></li>
                            <li><a href="{{ url('') }}#tm-area-team">Tim Kami</a></li>
                            <li><a href="{{ route('event') }}">Event</a></li>
                            <li><a href="{{ url('') }}#tm-area-contact">Kontak</a></li>
                            <li><a href="{{ url('') }}/leaderboard">Leaderboard</a></li>
                            <li class="tm-navigation-dropdown"><a href="#">Tools</a>
                                <ul>
                                    <li><a href="{{ route('member') }}">Cek Keanggotaan</a></li>
                                    <li><a href="{{ url('') }}">Inficreation</a></li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
        <div class="tm-mobilenav"></div>
    </div>
</div>
<!--// Header Area -->
