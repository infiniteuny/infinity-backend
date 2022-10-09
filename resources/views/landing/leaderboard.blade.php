@extends('landing.layouts.app')

@section('css')
    <style>
        .tm-sectiontitle-divider::before,
        .tm-sectiontitle-divider::after {
            background-image: url({{ asset('landing/assets/images/title-shape.png') }});
        }
    </style>
@endsection

@section('title', 'Leaderboard')

@section('js')
    <script>
        function selectYear() {
            var year = document.getElementById("year").value;
            var url = "{{ route('leaderboard', 'year=:year') }}";
            url = url.replace(':year', year);
            window.location.href = url;
        }
    </script>
@endsection

@section('content')
    <!-- Breadcrumb Area -->
    <div class="tm-breadcrumb-area tm-padding-section bg-gradient">
        <div class="tm-breadcrumb-bgshape">
            <img src="{{ asset('landing/assets/images/download-bgshape.png') }}" alt="bg shape">
        </div>
        <div class="container">
            <div class="tm-breadcrumb text-center">
                <h2>Infinite Competition Leaderboard {{ $year }}</h2>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                    </div>
                    <div class="col-lg-4">
                        <form action="#">
                            <select onchange="selectYear()" name="year" id="year" style="background-color: white">
                                @foreach ($yearSelect as $item)
                                    <option value="{{ $item }}" {{ $item == $year ? 'selected' : '' }}>
                                        {{ $item }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="col-lg-4">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--// Breadcrumb Area -->

    <!-- Page Content -->
    <main class="page-content mt-5 mb-5">

        <div class="container">
            <div class="row">
                <div class="col-md-12 table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Nama</th>
                                <th>Prodi</th>
                                <th>Jumlah Prestasi</th>
                                <th>Points <a class="points-info" href="#" data-toggle="tooltip" data-placement="top"
                                        title="Gimana caranya dapet poin?"><i class="zmdi zmdi-info-outline"></i>
                                    </a></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($members as $member)
                                <tr>
                                    <td style="text-align: center">
                                        @if ($loop->iteration == 1)
                                            <i class="fa-solid fa-medal" style="color: gold; font-size: 5vh"></i>
                                        @elseif ($loop->iteration == 2)
                                            <i class="fa-solid fa-medal" style="color: silver; font-size: 4vh"></i>
                                        @elseif ($loop->iteration == 3)
                                            <i class="fa-solid fa-medal" style="color:burlywood; font-size: 3vh"></i>
                                        @else
                                            {{ $loop->iteration }}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('leaderboard.detail', Crypt::encryptString($member['id'])) }}"
                                            style="color: #686b77">
                                            <img src="{{ asset('landing/assets/images/leadeboard_default.png') }}"
                                                class="rounded-circle mr-2" style="width: 50px;" alt="Avatar" />
                                            <b>
                                                {{ $member['name'] }}
                                            </b>
                                        </a>
                                    </td>
                                    <td>{{ $member['program_studies']['name'] . ' 20' . substr($member['student_id'], 0, 2) }}
                                    </td>
                                    <td>
                                        <a href="{{ route('leaderboard.detail', Crypt::encryptString($member['id'])) }}">
                                            <span
                                                class="badge badge-pill badge-success"><b>{{ $member['achievement_count'] }}</b>
                                                prestasi</span>
                                        </a>
                                    </td>
                                    <td><span class="badge badge-warning">{{ $member['points'] }}</span> total points
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
    <!--// Page Content -->
@endsection
