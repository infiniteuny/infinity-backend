<!-- Header Area -->
<div class="tm-header tm-header-fixed tm-sticky-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-3 col-md-9 col-9">
                <a class="tm-header-logo" href="{{ route('landing') }}">
                    <img src="{{ asset('landing/assets/images/infinite_light.svg') }}" alt="infinite logo">
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
                            <li class="tm-navigation-dropdown"><a href="#">Tools</a>
                                <ul>
                                    <li><a href="member">Cek Keanggotaan</a></li>
                                    <li><a target="_blank" href="https://unyku.id/">UNYKU.ID Shortlink</a></li>
                                    <li><a target="_blank" href="https://infisibill.infiniteuny.id/">INFINITE
                                            Splitbill</a></li>
                                </ul>
                            </li>
                            @auth
                                @if (Auth::user()->role == 'admin')
                                    <li class="tm-navigation-dropdown"><a href="{{ route('admin.dashboard') }}">Dasbor</a>
                                        <ul>
                                            <li><a href="{{ route('logout') }}">Keluar</a></li>
                                        </ul>
                                    </li>
                                @else
                                    <li class="tm-navigation-dropdown"><a href="{{ route('student.dashboard') }}">Dasbor</a>
                                        <ul>
                                            <li><a href="{{ route('logout') }}">Keluar</a></li>
                                        </ul>
                                    </li>
                                @endif
                            @endauth
                            @guest
                                <li><a href="{{ route('login') }}">Masuk</a></li>
                            @endguest
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
                                    <li><a target="_blank" href="https://unyku.id/">UNYKU.ID Shortlink</a></li>
                                    <li><a target="_blank" href="https://infisibill.infiniteuny.id/">INFINITE
                                            Splitbill</a></li>
                                </ul>
                            </li>
                            <li><a href="{{ route('login') }}">Login</a></li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
        <div class="tm-mobilenav"></div>
    </div>
</div>
<!--// Header Area -->
