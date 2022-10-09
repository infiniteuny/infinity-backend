@extends('landing.layouts.app')

@section('css')
    <style>
        .tm-sectiontitle-divider::before,
        .tm-sectiontitle-divider::after {
            background-image: url({{ asset('landing/assets/images/title-shape.png') }});
        }
    </style>
@endsection

@section('title', $name . ' Board Detail')

@section('content')
    <!-- Breadcrumb Area -->
    <div class="tm-breadcrumb-area tm-padding-section bg-gradient">
        <div class="tm-breadcrumb-bgshape">
            <img src="{{ asset('landing/assets/images/download-bgshape.png') }}" alt="bg shape">
        </div>
        <div class="container">
            <div class="tm-breadcrumb text-center">
                <h2>{{ $name }}</h2>
            </div>
        </div>
    </div>
    <!--// Breadcrumb Area -->

    <!-- Page Content -->
    <main class="page-content mt-5 mb-5">
        <!-- Blog Area -->
        <div id="tm-area-blog" class="tm-blog-area tm-section tm-padding-section bg-grey">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="tm-sectiontitle text-center">
                            <h2>Detail Prestasi</h2>
                            <span class="tm-sectiontitle-divider"><i class="zmdi zmdi-fullscreen"></i></span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Kompetisi</th>
                                <th scope="col">Penyelenggara</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Pencapaian</th>
                                <th scope="col">Deskripsi</th>
                                <th scope="col">Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($member as $team)
                                <tr>
                                    <th scope="row">{{ $loop->iteration }}</th>
                                    <td>{{ $team->achievements->competition_name }}</td>
                                    <td>{{ $team->achievements->organizer }}</td>
                                    <td>{{ Carbon\Carbon::parse($team->achievements->date)->format('F d, Y') }}</td>
                                    <td><span
                                            class="badge badge-success">{{ $team->achievements->competitionRanks->name }}</span>
                                    </td>
                                    <td>{{ $team->achievements->description }}</td>
                                    <td><span class="badge badge-warning">{{ $team->points }} pts</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" style="text-align: center"><b>Total Point</b></th>
                                <th><span class="badge badge-warning">{{ $member->sum('points') }} pts</span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!--// Blog Area -->

    </main>
    <!--// Page Content -->
@endsection
