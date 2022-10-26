@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Admin Panel</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"> <i data-feather="home"></i></a>
                        </li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('admin-panel/assets/js/chart/morris-chart/raphael.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/chart/morris-chart/morris.js') }}"></script>
    <script src="{{ asset('admin-panel/assets/js/chart/morris-chart/prettify.min.js') }}"></script>
    @if (session()->has('success'))
        <script>
            $(document).ready(function() {
                var name = {!! json_encode(Auth::user()->name) !!};
                var notify = $.notify(
                    '<i class="fa fa-bell-o"></i><strong>Selamat</strong> datang ' + name + '...', {
                        type: 'theme',
                        allow_dismiss: true,
                        delay: 5000,
                        showProgressbar: true,
                        timer: 300,
                        animate: {
                            enter: 'animated fadeInDown',
                            exit: 'animated fadeOutUp'
                        }
                    });

                setTimeout(function() {
                    notify.update('message',
                        '<i class="fa fa-bell-o"></i><strong>Ada</strong> keperluan apa hari ini?');
                }, 2000);
            });
        </script>
    @endif

    <script src="{{ asset('admin-panel/assets/js/dashboard/default.js') }}"></script>

    {{-- page chart --}}
    <script>
        var most_visited_label = JSON.parse(@json($analitics['most_visited_url']));
        var most_visited_data = JSON.parse(@json($analitics['most_visited_pageViews']));

        var visitors_label = JSON.parse(@json($analitics['visitors_and_page_date']));
        var visitors_data_visitors = JSON.parse(@json($analitics['visitors_and_page_visitors']));
        var visitors_data_page = JSON.parse(@json($analitics['visitors_and_page_pageViews']));

        var top_referrers_label = JSON.parse(@json($analitics['top_referrers_url']));
        var top_referrers_data = JSON.parse(@json($analitics['top_referrers_page_view']));

        var user_type_label = JSON.parse(@json($analitics['user_type']));
        var user_type_data = JSON.parse(@json($analitics['user_type_sessions']));

        var most_visited_barData = {
            labels: most_visited_label,
            datasets: [{
                label: "Halaman yang paling banyak dikunjungi",
                fillColor: "rgba(145, 46, 252, 0.4)",
                strokeColor: CubaAdminConfig.primary,
                highlightFill: "rgba(145, 46, 252, 0.6)",
                highlightStroke: CubaAdminConfig.primary,
                data: most_visited_data
            }]
        };

        var visitors_barData = {
            labels: visitors_label,
            datasets: [{
                    label: "Pengunjung",
                    fillColor: "rgba(145, 46, 252, 0.4)",
                    strokeColor: CubaAdminConfig.primary,
                    highlightFill: "rgba(145, 46, 252, 0.6)",
                    highlightStroke: CubaAdminConfig.primary,
                    data: visitors_data_visitors
                },
                {
                    label: "Halaman",
                    fillColor: "rgba(45, 66, 252, 0.4)",
                    strokeColor: CubaAdminConfig.primary,
                    highlightFill: "rgba(45, 66, 252, 0.6)",
                    highlightStroke: CubaAdminConfig.primary,
                    data: visitors_data_page
                }
            ]
        };

        var referrers_barData = {
            labels: top_referrers_label,
            datasets: [{
                label: "Referrers",
                fillColor: "rgba(145, 46, 252, 0.4)",
                strokeColor: CubaAdminConfig.primary,
                highlightFill: "rgba(145, 46, 252, 0.6)",
                highlightStroke: CubaAdminConfig.primary,
                data: top_referrers_data
            }]
        };

        var barOptions = {
            scaleBeginAtZero: true,
            scaleShowGridLines: true,
            scaleGridLineColor: "rgba(0,0,0,0.1)",
            scaleGridLineWidth: 1,
            scaleShowHorizontalLines: true,
            scaleShowVerticalLines: true,
            barShowStroke: true,
            barStrokeWidth: 2,
            barValueSpacing: 5,
            barDatasetSpacing: 1
        };
        var mostVisitedCtx = document.getElementById("mostVisitedGraph").getContext("2d");
        var mostVisitedChart = new Chart(mostVisitedCtx).Bar(most_visited_barData, barOptions);

        var visitorsCtx = document.getElementById("visitorsGraph").getContext("2d");
        var visitorsChart = new Chart(visitorsCtx).Bar(visitors_barData, barOptions);

        var referrersCtx = document.getElementById("topReferrers").getContext("2d");
        var referrersChart = new Chart(referrersCtx).Bar(referrers_barData, barOptions);
    </script>

    {{-- user type chart --}}
    <script>
        $(document).ready(function() {
            var user_type_label = JSON.parse(@json($analitics['user_type']));
            var user_type_data = JSON.parse(@json($analitics['user_type_sessions']));

            var user_type_chart = Morris.Donut({
                element: 'user-type-chart',
                data: [{
                        value: user_type_data[0],
                        label: user_type_label[0]
                    },
                    {
                        value: user_type_data[1],
                        label: user_type_label[1]
                    }
                ],
                colors: [CubaAdminConfig.primary, CubaAdminConfig.secondary, "#f8d62b", "#51bb25",
                    "rgba(248, 214, 43, 1)", "#51bb25", "#f8d62b"
                ],
                formatter: function(a) {
                    return a + " orang"
                }
            });
        });
    </script>

    {{-- faculty chart --}}
    <script>
        var faculty_label = JSON.parse(@json($data['faculty_name']));
        var faculty_member_data = JSON.parse(@json($data['faculty_member_count']));

        var options1 = {
            chart: {
                height: 380,
                type: 'radar',
                toolbar: {
                    show: false
                },
            },
            series: [{
                name: 'Persebaran Fakultas Anggota',
                data: faculty_member_data,
            }],
            stroke: {
                width: 3,
                curve: 'smooth',
            },
            labels: faculty_label,
            plotOptions: {
                radar: {
                    size: 140,
                    polygons: {
                        fill: {
                            colors: ['#fcf8ff', '#f7eeff']
                        },

                    }
                }
            },
            colors: [CubaAdminConfig.primary],

            markers: {
                size: 6,
                colors: ['#fff'],
                strokeColor: CubaAdminConfig.primary,
                strokeWidth: 3,
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val
                    }
                }
            },
            yaxis: {
                tickAmount: 7,
                labels: {
                    formatter: function(val, i) {
                        if (i % 2 === 0) {
                            return val
                        } else {
                            return ''
                        }
                    }
                }
            }
        }

        var chart1 = new ApexCharts(
            document.querySelector("#memberchart"),
            options1
        );

        chart1.render();
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row second-chart-list third-news-update">
            <div class="col-xl-12 col-lg-12 xl-100 morning-sec box-col-12">
                <div class="card profile-greeting">
                    <div class="card-body pb-0">
                        <div class="media">
                            <div class="media-body">
                                <div class="greeting-user">
                                    <h4 class="f-w-600 font-primary" id="greeting">Selamat Pagi</h4>
                                    <p>Ada apa aja yang baru nih hari ini?</p>
                                    <div class="whatsnew-btn"><a class="btn btn-primary">Apa yang Baru ?</a>
                                    </div>
                                </div>
                            </div>
                            <div class="badge-groups">
                                <div class="badge f-10"><i class="me-1" data-feather="clock"></i><span
                                        id="txt"></span></div>
                            </div>
                        </div>
                        <div class="cartoon" style="text-align: center"><img class="img-fluid"
                                src="{{ asset('admin-panel/assets/images/dashboard/cartoon.png') }}" alt=""></div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3 col-lg-6">
                <div class="card o-hidden static-top-widget-card">
                    <div class="card-body">
                        <div class="media static-top-widget">
                            <div class="media-body">
                                <h6 class="font-roboto">Prestasi Tahun Ini</h6>
                                <h4 class="mb-0 counter">{{ $data['achievements'] }}</h4>
                            </div>
                            <svg width="36" height="46" viewBox="0 0 36 46" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M31.3968 42.7019C31.4588 42.7142 31.5205 42.7265 31.5821 42.7388H31.4104L31.3968 42.7019ZM28.9218 42.2239C29.7582 42.3832 30.5947 42.5426 31.3968 42.7019L27.291 31.5821H27.6343H27.806C28.1493 31.7537 29.0075 31.7537 29.5224 31.2388C30.0373 30.7239 30.0373 29.8657 30.0373 29.3507C30.0373 28.6642 30.3806 28.1493 31.0672 27.9776H31.2388C31.7537 27.9776 32.4403 27.806 32.7836 27.1194C33.1269 26.4328 32.9552 25.7463 32.7836 25.2313C32.6119 24.7164 32.9552 24.0299 33.4701 23.6866H33.6418C33.733 23.5954 33.8483 23.5042 33.9718 23.4066C34.3134 23.1367 34.7172 22.8177 34.8433 22.3134C35.0149 21.4552 34.6716 20.7687 34.3284 20.4254C33.9851 19.9104 33.9851 19.2239 34.5 18.709L34.6716 18.5373C34.7441 18.4286 34.8242 18.3199 34.9055 18.2096C35.2094 17.7973 35.5298 17.3625 35.5298 16.8209C35.5298 16.2405 35.0393 15.7828 34.6801 15.4478C34.6144 15.3864 34.5531 15.3292 34.5 15.2761C33.9851 14.7612 33.8134 14.0746 34.1567 13.5597V13.3881C34.5 12.8731 34.8433 12.1866 34.5 11.5C34.1567 10.8134 33.4701 10.4701 32.9552 10.2985C32.2687 10.1269 31.9254 9.61194 32.097 8.92537V8.75373C32.097 8.65788 32.103 8.55014 32.1093 8.43493C32.1371 7.93121 32.1728 7.28475 31.7537 6.86567C31.4104 6.1791 30.5522 6.1791 30.0373 6.1791C29.5224 6.00746 29.0075 5.66418 28.8358 4.97761V4.80597C28.7953 4.68441 28.7643 4.54373 28.7317 4.3952C28.6259 3.91455 28.5021 3.3518 27.9776 3.08955C27.291 2.57463 26.6045 2.74627 26.0895 2.91791C25.403 3.08955 24.8881 2.74627 24.5448 2.23134V2.0597C24.3731 1.54478 24.0298 0.858209 23.3433 0.686567C22.8463 0.562328 22.3494 0.7978 21.9175 1.00243C21.7527 1.08051 21.5974 1.15409 21.4552 1.20149C20.9403 1.54478 20.0821 1.54478 19.7388 1.02985L19.5672 0.858209C19.2239 0.514925 18.709 0 18.0224 0C17.2781 0 16.792 0.516346 16.4522 0.87736C16.4002 0.932636 16.3516 0.984269 16.306 1.02985C15.791 1.37313 15.1045 1.54478 14.5896 1.20149C14.2463 1.02985 13.5597 0.686567 12.8731 0.858209C12.0149 1.02985 11.6716 1.71642 11.5 2.23134C11.1567 2.74627 10.6418 3.08955 9.95522 2.91791H9.78358C9.66202 2.91791 9.5309 2.90835 9.39247 2.89825C8.9445 2.86557 8.42001 2.82731 7.89552 3.08955C7.20895 3.43284 7.03731 4.29104 7.03731 4.80597C7.03731 5.49254 6.52239 6.00746 5.83582 6.00746H5.66418C5.14925 6.00746 4.46269 6.1791 3.94776 6.69403C3.52868 7.11311 3.56437 7.75956 3.59217 8.26329C3.59853 8.3785 3.60448 8.48624 3.60448 8.58209C3.60448 9.09701 3.26119 9.78358 2.74627 9.95522H2.57463C2.23134 10.1269 1.54478 10.4701 1.20149 11.1567C1.05965 11.7241 1.26946 12.2915 1.44346 12.762C1.48001 12.8608 1.51498 12.9554 1.54478 13.0448C1.88806 13.7313 1.71642 14.4179 1.20149 14.7612L1.02985 14.9328C0.976744 14.9859 0.915423 15.0432 0.849698 15.1045C0.490573 15.4395 0 15.8973 0 16.4776C0 17.1642 0.514925 17.8507 0.858209 18.194C1.37313 18.709 1.37313 19.3955 1.02985 19.9104C0.858209 19.9104 0.858209 20.0821 0.858209 20.0821C0.828105 20.1423 0.787441 20.2131 0.741773 20.2925C0.527062 20.6662 0.201746 21.2324 0.343284 21.7985C0.514925 22.4851 1.20149 23 1.71642 23.1716C2.40298 23.5149 2.57463 24.2015 2.40298 24.7164V24.8881C2.23134 25.403 2.0597 26.0896 2.40298 26.7761C2.87221 27.4017 3.48396 27.5998 3.97848 27.7599C4.02665 27.7755 4.0737 27.7907 4.1194 27.806C4.63433 27.9776 5.14925 28.4925 5.14925 29.1791V29.3507C5.14925 29.8657 5.14925 30.5522 5.66418 31.0672C6.08326 31.4862 6.72971 31.4506 7.23344 31.4228C7.34865 31.4164 7.45639 31.4104 7.55224 31.4104H7.89552L3.77612 42.5672C4.63438 42.3955 5.53555 42.2239 6.43673 42.0522C7.33779 41.8806 8.23886 41.7089 9.09701 41.5373C9.69776 42.2239 10.2556 42.9534 10.8134 43.6828C11.3713 44.4123 11.9291 45.1418 12.5298 45.8284L16.306 35.3582C16.3918 35.2724 16.4776 35.2295 16.5634 35.1866C16.6493 35.1437 16.7351 35.1007 16.8209 35.0149C17.3358 34.6716 18.0224 34.6716 18.5373 35.0149L18.709 35.1866C18.709 35.2926 18.7745 35.3332 18.8651 35.3892C18.9211 35.4238 18.9867 35.4643 19.0522 35.5299C19.653 37.2463 20.2966 39.0056 20.9403 40.7649C21.584 42.5243 22.2276 44.2836 22.8284 46C23.4291 45.3134 23.9869 44.584 24.5448 43.8545C25.1026 43.125 25.6604 42.3955 26.2612 41.709C27.1194 41.8806 28.0206 42.0523 28.9218 42.2239ZM4.80597 17.8507C4.80597 25.0597 10.6418 30.7239 17.6791 30.7239C24.8881 30.7239 30.5522 24.8881 30.5522 17.8507C30.5522 10.6418 24.7164 4.97761 17.6791 4.97761C10.4701 4.97761 4.80597 10.8134 4.80597 17.8507ZM3.43284 18.0224C3.43284 10.1269 9.78358 3.77612 17.6791 3.77612C25.5746 3.77612 31.9254 10.1269 31.9254 18.0224C31.9254 25.9179 25.5746 32.2687 17.6791 32.2687C9.78358 32.2687 3.43284 25.9179 3.43284 18.0224ZM20.2582 14.2447L17.512 8.75214L14.9373 14.2447L8.75824 15.1029L13.2209 19.3939L12.1911 25.573L17.512 22.6551L23.0045 25.573L21.9747 19.3939L26.4373 15.1029L20.2582 14.2447Z"
                                    fill="#F73164" />
                            </svg>
                        </div>
                        <div class="progress-widget">
                            <div class="progress sm-progress-bar progress-animate">
                                <div class="progress-gradient-secondary" role="progressbar" style="width: 75%"
                                    aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><span
                                        class="animate-circle"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 col-lg-6">
                <div class="card o-hidden static-top-widget-card">
                    <div class="card-body">
                        <div class="media static-top-widget">
                            <div class="media-body">
                                <h6 class="font-roboto">Produk Member</h6>
                                <h4 class="mb-0 counter">{{ $data['products'] }}</h4>
                            </div>
                            <svg class="fill-success" width="45" height="39" viewBox="0 0 45 39" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M5.92047 8.49509C5.81037 8.42629 5.81748 8.25971 5.93378 8.20177C7.49907 7.41686 9.01464 6.65821 10.5302 5.89775C14.4012 3.95495 18.2696 2.00762 22.1478 0.0792996C22.3387 -0.0157583 22.6468 -0.029338 22.8359 0.060288C28.2402 2.64315 33.6357 5.24502 39.033 7.84327C39.0339 7.84327 39.0339 7.84417 39.0348 7.84417C39.152 7.90121 39.1582 8.06869 39.0472 8.1375C38.9939 8.17009 38.9433 8.20087 38.8918 8.22984C33.5398 11.2228 28.187 14.2121 22.8385 17.2115C22.5793 17.3572 22.3839 17.3762 22.1131 17.2296C16.7851 14.3507 11.4518 11.4826 6.12023 8.61188C6.05453 8.57748 5.98972 8.53855 5.92047 8.49509Z">
                                </path>
                                <path
                                    d="M21.1347 23.3676V38.8321C21.1347 38.958 21.0042 39.0386 20.895 38.9806C20.4182 38.7271 19.9734 38.4918 19.5295 38.2528C14.498 35.5441 9.46833 32.8317 4.43154 30.1339C4.12612 29.97 4.02046 29.7944 4.02224 29.4422C4.03822 26.8322 4.03023 24.2222 4.02934 21.6122C4.02934 21.4719 4.02934 21.3325 4.02934 21.1659C4.02934 21.0428 4.15542 20.9622 4.26373 21.0147C4.35252 21.0581 4.43065 21.0962 4.50434 21.1396C8.18539 23.2888 11.8664 25.438 15.5457 27.5909C16.5081 28.154 17.0622 28.0453 17.7627 27.1464C18.7748 25.8472 19.7896 24.5508 20.8045 23.2535C20.8053 23.2526 20.8062 23.2517 20.8071 23.2499C20.9172 23.1132 21.1347 23.192 21.1347 23.3676Z">
                                </path>
                                <path
                                    d="M23.83 23.3784C23.83 23.2019 24.0484 23.1241 24.1567 23.2626C25.2168 24.6178 26.2192 25.9016 27.2233 27.1835C27.8928 28.039 28.4504 28.1494 29.3719 27.6117C33.0521 25.4643 36.7323 23.316 40.4133 21.1686C40.4914 21.1233 40.5713 21.0799 40.6592 21.0337C40.7613 20.9803 40.8856 21.0473 40.8972 21.164C40.9025 21.2184 40.9069 21.2691 40.9069 21.3189C40.9087 23.928 40.9052 26.5371 40.9132 29.1462C40.914 29.4006 40.8421 29.5518 40.6131 29.6794C35.1057 32.7539 29.6037 35.8365 24.099 38.9163C24.0892 38.9218 24.0803 38.9263 24.0706 38.9317C23.9605 38.9879 23.8309 38.9082 23.8309 38.7833L23.83 23.3784Z">
                                </path>
                                <path
                                    d="M28.4752 24.454C27.2908 22.9385 26.118 21.4384 24.9203 19.9066C24.6983 19.6232 24.7809 19.2031 25.0925 19.0293L41.3092 9.95809C41.5746 9.80962 41.9076 9.89743 42.0692 10.1582C43.0147 11.6791 43.9541 13.1891 44.9103 14.7264C45.0852 15.0079 44.9946 15.3818 44.7114 15.5475C39.5414 18.5649 34.3875 21.5742 29.2086 24.5979C28.9627 24.74 28.651 24.6794 28.4752 24.454Z">
                                </path>
                                <path
                                    d="M20.0132 19.931C18.819 21.4592 17.6506 22.9539 16.4804 24.4512C16.3037 24.6767 15.9921 24.7373 15.747 24.5943C10.586 21.5814 5.45504 18.5857 0.288619 15.5701C6.65486e-05 15.4017 -0.087831 15.0188 0.0968427 14.7372C1.02554 13.3204 1.94269 11.9208 2.86872 10.5085C3.03209 10.2596 3.35349 10.1763 3.61363 10.3157C9.018 13.2254 14.3975 16.1215 19.833 19.0483C20.1508 19.2194 20.2378 19.644 20.0132 19.931Z">
                                </path>
                            </svg>
                        </div>
                        <div class="progress-widget">
                            <div class="progress sm-progress-bar progress-animate">
                                <div class="progress-gradient-success" role="progressbar" style="width: 75%"
                                    aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><span
                                        class="animate-circle"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 col-lg-6">
                <div class="card o-hidden">
                    <div class="card-body">
                        <div class="media static-top-widget">
                            <div class="media-body">
                                <h6 class="font-roboto">Event/Artikel</h6>
                                <h4 class="mb-0 counter">{{ $data['events'] }}</h4>
                            </div>
                            <svg class="fill-primary" width="44" height="46" viewBox="0 0 44 46"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9.73709 35.2337C6.17884 31.58 4.00316 26.8452 3.49802 21.7377C1.60687 24.237 0.581465 27.3024 0.586192 30.5195C0.589372 32.612 1.03986 34.692 1.89348 36.5729L0.1333 41.9282C-0.169286 42.8488 0.0517454 43.8484 0.7102 44.5369C1.17358 45.0213 1.78451 45.2794 2.4128 45.2794C2.67714 45.2794 2.94458 45.2337 3.2054 45.14L8.32806 43.2997C10.1272 44.1922 12.1167 44.6631 14.1182 44.6665C17.2557 44.6709 20.2418 43.558 22.657 41.5068C17.8005 41.0474 13.2702 38.8615 9.73709 35.2337Z">
                                </path>
                                <path
                                    d="M43.8418 35.7427L41.2863 27.9674C42.5181 25.3348 43.1691 22.407 43.1735 19.4611C43.181 14.3388 41.2854 9.49561 37.8357 5.82369C34.3853 2.15096 29.7875 0.0836476 24.889 0.00251856C19.8097 -0.0814855 15.0354 1.93839 11.446 5.69081C7.85665 9.44332 5.92425 14.4346 6.00469 19.7451C6.08229 24.8661 8.05972 29.673 11.5726 33.2803C15.078 36.8798 19.6988 38.861 24.5879 38.8608C24.5975 38.8608 24.6077 38.8608 24.6171 38.8608C27.435 38.8563 30.2356 38.1757 32.7537 36.8879L40.1911 39.5596C40.501 39.671 40.8188 39.7252 41.1329 39.7252C41.8795 39.7252 42.6055 39.4187 43.1563 38.8428C43.9388 38.0247 44.2014 36.8369 43.8418 35.7427ZM26.3834 26.1731H16.7865C16.0633 26.1731 15.477 25.5601 15.477 24.804C15.477 24.0479 16.0633 23.435 16.7865 23.435H26.3833C27.1066 23.435 27.6929 24.048 27.6929 24.804C27.6929 25.5602 27.1067 26.1731 26.3834 26.1731ZM32.3894 20.5426H16.7866C16.0633 20.5426 15.4771 19.9296 15.4771 19.1736C15.4771 18.4176 16.0634 17.8046 16.7866 17.8046H32.3894C33.1127 17.8046 33.6989 18.4176 33.6989 19.1736C33.6989 19.9296 33.1127 20.5426 32.3894 20.5426ZM32.3894 14.912H16.7866C16.0633 14.912 15.4771 14.299 15.4771 13.543C15.4771 12.7869 16.0634 12.1739 16.7866 12.1739H32.3894C33.1127 12.1739 33.6989 12.787 33.6989 13.543C33.6989 14.299 33.1127 14.912 32.3894 14.912Z">
                                </path>
                            </svg>
                        </div>
                        <div class="progress-widget">
                            <div class="progress sm-progress-bar progress-animate">
                                <div class="progress-gradient-primary" role="progressbar" style="width: 75%"
                                    aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><span
                                        class="animate-circle"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 col-lg-6">
                <div class="card o-hidden">
                    <div class="card-body">
                        <div class="media static-top-widget">
                            <div class="media-body">
                                <h6 class="font-roboto">Member</h6>
                                <h4 class="mb-0 counter">{{ $data['members'] }}</h4>
                            </div>
                            <svg class="fill-danger" width="41" height="46" viewBox="0 0 41 46"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17.5245 23.3155C24.0019 23.3152 26.3325 16.8296 26.9426 11.5022C27.6941 4.93936 24.5906 0 17.5245 0C10.4593 0 7.35423 4.93899 8.10639 11.5022C8.71709 16.8296 11.047 23.316 17.5245 23.3155Z">
                                </path>
                                <path
                                    d="M31.6878 26.0152C31.8962 26.0152 32.1033 26.0214 32.309 26.0328C32.0007 25.5931 31.6439 25.2053 31.2264 24.8935C29.9817 23.9646 28.3698 23.6598 26.9448 23.0998C26.2511 22.8273 25.6299 22.5567 25.0468 22.2485C23.0787 24.4068 20.5123 25.5359 17.5236 25.5362C14.536 25.5362 11.9697 24.4071 10.0019 22.2485C9.41877 22.5568 8.79747 22.8273 8.10393 23.0998C6.67891 23.6599 5.06703 23.9646 3.82233 24.8935C1.6698 26.5001 1.11351 30.1144 0.676438 32.5797C0.315729 34.6148 0.0734026 36.6917 0.00267388 38.7588C-0.0521202 40.36 0.738448 40.5846 2.07801 41.0679C3.75528 41.6728 5.48712 42.1219 7.23061 42.4901C10.5977 43.2011 14.0684 43.7475 17.5242 43.7719C19.1987 43.76 20.8766 43.6249 22.5446 43.4087C21.3095 41.6193 20.5852 39.4517 20.5852 37.1179C20.5853 30.9957 25.5658 26.0152 31.6878 26.0152Z">
                                </path>
                                <path
                                    d="M31.6878 28.2357C26.7825 28.2357 22.8057 32.2126 22.8057 37.1179C22.8057 42.0232 26.7824 46 31.6878 46C36.5932 46 40.57 42.0232 40.57 37.1179C40.57 32.2125 36.5931 28.2357 31.6878 28.2357ZM35.5738 38.6417H33.2118V41.0037C33.2118 41.8453 32.5295 42.5277 31.6879 42.5277C30.8462 42.5277 30.1639 41.8453 30.1639 41.0037V38.6417H27.802C26.9603 38.6417 26.278 37.9595 26.278 37.1177C26.278 36.276 26.9602 35.5937 27.802 35.5937H30.1639V33.2318C30.1639 32.3901 30.8462 31.7078 31.6879 31.7078C32.5296 31.7078 33.2118 32.3901 33.2118 33.2318V35.5937H35.5738C36.4155 35.5937 37.0978 36.276 37.0978 37.1177C37.0977 37.9595 36.4155 38.6417 35.5738 38.6417Z">
                                </path>
                            </svg>
                        </div>
                        <div class="progress-widget">
                            <div class="progress sm-progress-bar progress-animate">
                                <div class="progress-gradient-danger" role="progressbar" style="width: 75%"
                                    aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><span
                                        class="animate-circle"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-12 xl-100 chart_data_right box-col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="media-body right-chart-content">
                                <h4>{{ $data['achievements'] }}/{{ isset($data['configs']['competition_target']) ? $data['configs']['competition_target'] : 25 }}
                                </h4>
                                <span>Target Prestasi Tahun Ini</span>
                            </div>
                            <div class="knob-block text-center">
                                <input class="knob1" data-width="10" data-height="70" data-thickness=".3"
                                    data-angleoffset="0" data-linecap="round" data-fgcolor="#7366ff"
                                    data-bgcolor="#eef5fb"
                                    value="{{ ($data['achievements'] / (int) (isset($data['configs']['competition_target']) ? $data['configs']['competition_target'] : 25)) * 100 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-12 box-col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Halaman paling sering dikunjungi 1 minggu terakhir</h5>
                    </div>
                    <div class="card-body chart-block">
                        <canvas id="mostVisitedGraph"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-12 box-col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Pengunjung 1 minggu terakhir</h5>
                    </div>
                    <div class="card-body chart-block">
                        <canvas id="visitorsGraph"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-12 box-col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Sumber klik masuk ke website</h5>
                    </div>
                    <div class="card-body chart-block">
                        <canvas id="topReferrers"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-sm-12 box-col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Tipe Pengunjung</h5>
                    </div>
                    <div class="card-body chart-block">
                        <div class="flot-chart-container">
                            <div class="flot-chart-placeholder" id="user-type-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 xl-50 appointment-sec box-col-6">
                <div class="row">
                    <div class="col-xl-12 appointment">
                        <div class="card">
                            <div class="card-header card-no-border">
                                <div class="header-top">
                                    <h5 class="m-0">Akun Baru Mendaftar</h5>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="appointment-table table-responsive">
                                    <table class="table table-bordernone">
                                        <tbody>
                                            @foreach ($data['users'] as $user)
                                                <tr>
                                                    <td><img class="img-fluid img-40 rounded-circle mb-3"
                                                            src="{{ $user['avatar'] }}" alt="Image description">
                                                        <div class="status-circle bg-primary"></div>
                                                    </td>
                                                    <td class="img-content-box"><span
                                                            class="d-block">{{ $user['name'] }}</span><span
                                                            class="font-roboto">{{ $user['created_at_hour'] }}</span>
                                                    </td>
                                                    <td>
                                                        <p class="m-0 font-primary">{{ $user['created_at_date'] }}
                                                        </p>
                                                    </td>
                                                    <td class="text-end">
                                                        <div
                                                            class="button btn {{ $user['is_verified'] ? 'btn-primary' : 'btn-warning' }}">
                                                            {{ $user['is_verified_text'] }}</div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 xl-50 appointment box-col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="header-top">
                            <h5 class="m-0">Persebaran Fakultas Anggota</h5>
                        </div>
                    </div>
                    <div class="card-Body">
                        <div class="radar-chart">
                            <div id="memberchart"> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
