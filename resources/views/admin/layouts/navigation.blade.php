<nav class="sidebar-main">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
            <li class="back-btn"><a href="index.html"><img class="img-fluid"
                        src="{{ asset('landing/assets/images/logo_infinite_dark.svg') }}" alt=""></a>
                <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2"
                        aria-hidden="true"></i></div>
            </li>
            <li class="sidebar-main-title">
                <div>
                    <h6 class="lan-1">General</h6>
                    <p class="lan-2">Dashboards, Member, Akun, Prestasi.</p>
                </div>
            </li>
            <li class="sidebar-list">
                <a class="sidebar-link sidebar-title link-nav active" href="{{ route('admin.dashboard') }}"><i
                        data-feather="home"></i><span class="lan-3">Dashboards </span></a>
            </li>
            <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav"
                    href="{{ route('admin.member.index') }}"><i data-feather="users"></i><span class="lan-6">Members
                    </span></a>
            </li>
            <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav"
                    href="{{ route('admin.achievement.index') }}"><i data-feather="award"></i><span
                        class="lan-7">Prestasi </span></a>
            </li>
            <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav"
                    href="{{ route('admin.user.index') }}"><i data-feather="user"></i><span class="lan-7">Akun
                    </span></a>
            </li>
            <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav"
                    href="{{ route('admin.config.index') }}"><i data-feather="settings"></i><span
                        class="lan-7">Settings
                    </span></a>
            </li>
        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
</nav>
