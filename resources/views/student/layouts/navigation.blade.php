<nav class="sidebar-main">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
            <li class="back-btn"><a href="{{ route('student.dashboard') }}"><img class="img-fluid"
                        src="{{ asset('landing/assets/images/logo_infinite_dark.svg') }}" alt=""></a>
                <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2"
                        aria-hidden="true"></i></div>
            </li>
            <li class="sidebar-main-title">
                <div>
                    <h6 class="lan-1">General</h6>
                    <p class="lan-2">Dashboards, Prestasi, Pengajuan Dana, Daftar Ulang, Freepik Downloader</p>
                </div>
            </li>
            <li class="sidebar-list">
                <a class="sidebar-link sidebar-title link-nav active" href="{{ route('student.dashboard') }}"><i
                        data-feather="home"></i><span class="lan-3">Dashboards </span></a>
            </li>
            <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav"
                    href="{{ route('student.achievement.index') }}"><i data-feather="award"></i><span
                        class="lan-6">Prestasi
                    </span></a>
            </li>
            <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav"
                    href="{{ route('student.fund-application.index') }}"><i data-feather="dollar-sign"></i><span
                        class="lan-6">Pengajuan Dana
                    </span></a>
            </li>
            <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav"
                    href="{{ route('student.re-registration.index') }}"><i data-feather="repeat"></i><span
                        class="lan-7">Daftar Ulang </span></a>
            </li>
            <li class="sidebar-list"><a class="sidebar-link sidebar-title" href="#"><i
                        data-feather="airplay"></i><span>Freepiks</span></a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('student.freepik.index') }}">Downloader</a></li>
                    <li><a href="{{ route('student.freepik.asset') }}">Asset</a></li>
                </ul>
            </li>
        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
</nav>
