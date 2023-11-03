<div class="page-header">
    <div class="header-wrapper row m-0">
        <form class="form-inline search-full col" action="#" method="get">
            <div class="form-group w-100">
                <div class="Typeahead Typeahead--twitterUsers">
                    <div class="u-posRelative">
                        <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text"
                            placeholder="Search Cuba .." name="q" title="" autofocus>
                        <div class="spinner-border Typeahead-spinner" role="status"><span
                                class="sr-only">Loading...</span></div><i class="close-search" data-feather="x"></i>
                    </div>
                    <div class="Typeahead-menu"></div>
                </div>
            </div>
        </form>
        <div class="header-logo-wrapper col-auto p-0">
            <div class="logo-wrapper"><a href="{{ route('student.dashboard') }}"><img class="img-fluid"
                        src="{{ asset('landing/assets/images/logo_infinite_dark.svg') }}" alt=""></a>
            </div>
            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i>
            </div>
        </div>
        <div class="left-header col horizontal-wrapper ps-0">
            <ul class="horizontal-menu">
                <li class="mega-menu outside"><a class="nav-link" href="{{ route('landing') }}"><i
                            data-feather="home"></i><span>Landing</span></a>
                </li>
            </ul>
        </div>
        <div class="nav-right col-8 pull-right right-header p-0">
            <ul class="nav-menus">
                <li class="onhover-dropdown">
                    <div class="notification-box"><i data-feather="bell"> </i><span
                            class="badge rounded-pill badge-secondary">3</span></div>
                    <div class="onhover-show-div notification-dropdown">
                        <h6 class="f-18 mb-0 dropdown-title">Link Grub</h6>
                        <ul>
                            <li class="b-l-success border-4">
                                <p>Join Server Discord<b><a
                                            href="https://discord.gg/AQqRfgErA8">INFINITE UNY</a></b>
                                </p>
                            </li>
                            <li class="b-l-success border-4">
                                <p>Join Grub WhatsApp<b><a
                                            href="https://chat.whatsapp.com/EnM9v1r8Fdv8EkJK38gPf6">INFINITE UNY</a></b>
                                </p>
                            </li>
                            <li class="b-l-info border-4">
                                <p>Join Grub Telegram<b><a href="https://t.me/+7qXyyGquWFAxN2I1">GDSC UNY</a></b></p>
                            </li>
                            {{-- <li><a class="f-w-700" href="#">Check all</a></li> --}}
                        </ul>
                    </div>
                </li>
                <li>
                    <div class="mode"><i class="fa fa-moon-o"></i></div>
                </li>
                <li class="maximize"><a class="text-dark" href="#!" onclick="javascript:toggleFullScreen()"><i
                            data-feather="maximize"></i></a></li>
                <li class="profile-nav onhover-dropdown p-0 me-0">
                    <div class="media profile-media"><img style="max-width: 37px;" class="b-r-10"
                            src="{{ Auth::user()->avatar }}" alt="">
                        <div class="media-body"><span>{{ Auth::user()->name }}</span>
                            <p class="mb-0 font-roboto" style="text-transform: capitalize">{{ Auth::user()->role }} <i
                                    class="middle fa fa-angle-down"></i>
                            </p>
                        </div>
                    </div>
                    <ul class="profile-dropdown onhover-show-div">
                        <li><a href="https://auth.infiniteuny.id/if/user/#/settings" target="_blank"><i data-feather="user"></i><span>Pengaturan Akun</span></a></li>
                        <li><a href="{{ route('logout') }}"><i data-feather="log-in"> </i><span>Keluar</span></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog"
    aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Ubah Password</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('change-password') }}" method="POST"
                    enctype="application/x-www-form-urlencoded">
                    @csrf
                    @if (!auth()->user()->provider == 'google')
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="password_old">Password Lama</label>
                                    <input type="password" class="form-control" id="password_old" name="password_old"
                                        placeholder="Masukkan password lama" value="" required>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label for="password">Password Baru</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Masukkan password baru" value="" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label for="password_confirmation">Ulangi Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" placeholder="Ulangi password baru" value=""
                                    required>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary" type="submit">Ubah</button>
                </form>
            </div>
        </div>
    </div>
</div>
